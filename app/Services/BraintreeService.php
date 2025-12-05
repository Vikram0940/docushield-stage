<?php namespace App\Services;

use Config\Braintree;
use Braintree\Exception\NotFound;

class BraintreeService
{
    protected $gateway;

    public function __construct()
    {
        $this->gateway = Braintree::gateway(); // using your config file
    }

    /**
     * Generate client token for Drop-in UI
     */
    public function generateClientToken($customerId = null)
    {
        $options = [];
        if ($customerId) {
            $options['customerId'] = $customerId;
        }

        return $this->gateway->clientToken()->generate($options);
    }

    /**
     * Create a new customer
     */
    public function createCustomer($firstName, $lastName, $email, $nonce = null)
    {
        $data = [
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'email'     => $email,
        ];

        if ($nonce) {
            $data['paymentMethodNonce'] = $nonce;
        }

        return $this->gateway->customer()->create($data);
    }

    /**
     * Create or find customer by local user data
     */
    public function createOrFindCustomer(array $userData)
    {
        $customerId = 'customer_' . $userData['id'];

        try {
            // Try to fetch existing customer
            return $this->gateway->customer()->find($customerId);
        } catch (\Braintree\Exception\NotFound $e) {
            // Not found → create new
            $nameParts = explode(' ', trim($userData['name']), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName  = $nameParts[1] ?? '';

            $result = $this->gateway->customer()->create([
                'id'        => $customerId,
                'firstName' => $firstName,
                'lastName'  => $lastName,
                'email'     => $userData['email'] ?? null,
            ]);

            if ($result->success) {
                return $result->customer;
            }

            $errors = [];
            foreach ($result->errors->deepAll() as $error) {
                $errors[] = $error->code . ': ' . $error->message;
            }

            throw new \Exception('Braintree customer creation failed: ' . implode(', ', $errors));
        }
    }

    /**
     * Delete customer
     */
    public function deleteCustomer($customerId)
    {
        try {
            return $this->gateway->customer()->delete($customerId)->success;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create a subscription
     */
    public function createSubscription($customerId, $paymentMethodToken, $planId)
    {
        // If it looks like a nonce, vault it first
        if (strpos($paymentMethodToken, "fake-") === 0 || strlen($paymentMethodToken) > 25) {
            $paymentMethodToken = $this->createPaymentMethod($customerId, $paymentMethodToken);
        } else {
            $paymentMethodToken = $paymentMethodToken; // already a token
        }

        return $this->gateway->subscription()->create([
            'paymentMethodToken' => $paymentMethodToken,
            'planId'             => $planId,
        ]);
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription($subscriptionId)
    {
        return $this->gateway->subscription()->cancel($subscriptionId);
    }

    /**
     * Charge one-time payment
     */
    public function charge($amount, $nonce)
    {
        return $this->gateway->transaction()->sale([
            'amount' => $amount,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'submitForSettlement' => true
            ]
        ]);
    }

    /**
     * Create a payment method for customer
     */
    public function createPaymentMethod($customerId, $nonce, $makeDefault = true)
    {
        $result = $this->gateway->paymentMethod()->create([
            'customerId'         => $customerId,
            'paymentMethodNonce' => $nonce,
            'options'            => ['makeDefault' => $makeDefault]
        ]);
        if (!$result->success) {
            return null;
        }

        // Normalize response
        return (object)[
            'token'   => $result->paymentMethod->token,
            'details' => $result->paymentMethod
        ];
    }

    /**
     * Update a customer's payment method
     */
    public function updatePaymentMethod($token, $nonce, $makeDefault = true)
    {
        return $this->gateway->paymentMethod()->update($token, [
            'paymentMethodNonce' => $nonce,
            'options' => [
                'makeDefault' => $makeDefault
            ]
        ]);
    }

    /**
     * Delete a payment method
     */
    public function deletePaymentMethod($token)
    {
        return $this->gateway->paymentMethod()->delete($token);
    }

    /**
     * Update subscription with new payment method
     */
    public function updateSubscriptionPayment($subscriptionId, $paymentMethodToken)
    {
        return $this->gateway->subscription()->update($subscriptionId, [
            'paymentMethodToken' => $paymentMethodToken
        ]);
    }
}