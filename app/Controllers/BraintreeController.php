<?php namespace App\Controllers;

use App\Services\BraintreeService;

class BraintreeController extends BaseController
{
    protected $braintree;

    public function __construct()
    {
        $this->braintree = new BraintreeService();
    }

    public function getClientToken()
    {
        $customerId = null;
        if (session()->get("dc_userid")) {
            $user = (new \App\Models\Users)->find(session()->get("dc_userid"));
            $customer = $this->braintree->createOrFindCustomer((array)$user);
            $customerId = $customer->id;
        }
        $token = $this->braintree->generateClientToken($customerId);
        return $this->response->setJSON(['clientToken' => $token, 'token' => csrf_hash()]);
    }

    public function subscribe()
    {
        $nonce  = $this->request->getPost('paymentMethodNonce');
        //$planId = $this->request->getPost('planId');
        $planId = env("braintree.plan_id");

        if (!$nonce || !$planId) {
            return $this->response->setJSON([
                'success' => false,
                "title"   => "Failed!",
                "class"   => "error",
                'text'    => 'Missing required parameters',
                "token"   => csrf_hash()
            ]);
        }

        $userId = session()->get("dc_userid");
        if (!$userId) {
            return $this->response->setJSON(['success' => false, "title" => "Failed!", "class" => "error", 'text' => 'User not logged in', "token" => csrf_hash()]);
        }

        $user = (new \App\Models\Users)->asArray()->find($userId);
        if (empty($user)) {
            return $this->response->setJSON(['success' => false, "title" => "Failed!", "class" => "error", 'text' => 'User not found', "token" => csrf_hash()]);
        }

        // ✅ Service call
        $customer = $this->braintree->createOrFindCustomer($user);
        if (!$customer) {
            return $this->response->setJSON(['success' => false, "title" => "Failed!", "class" => "error", 'text' => 'Unable to create/find customer', "token" => csrf_hash()]);
        }

        try {
            // ✅ Create payment method
            $paymentMethod = $this->braintree->createPaymentMethod($customer->id, $nonce);
            if (!$paymentMethod) {
                $this->braintree->deleteCustomer($customer->id); // Cleanup
                return $this->response->setJSON(['success' => false, "title" => "Failed!", "class" => "error", 'text' => 'Failed to create payment method', "token" => csrf_hash()]);
            }
            // ✅ Create subscription
            $subscription = $this->braintree->createSubscription($customer->id, $paymentMethod->token, $planId);
            if (!$subscription) {
                $this->braintree->deleteCustomer($customer->id); // Cleanup
                return $this->response->setJSON(['success' => false, "title" => "Failed!", "class" => "error", 'text' => 'Failed to create subscription', "token" => csrf_hash()]);
            }
            // Save profile to DB
            $result = (new \App\Models\Users)->update($userId, [
                "status" => 1, // Activate the admin user
                "bt_profile" => json_encode([
                    "customerId"         => $customer->id,
                    "paymentMethodToken" => $paymentMethod->token,
                    "subscriptionId"     => $subscription->subscription->id,
                    "token"              => csrf_hash()
                ])
            ]);
            if ($result) {
                return $this->response->setJSON([
                    'success'        => true,
                    "title"          => "Successful!",
                    'subscriptionId' => $subscription->subscription->id,
                    'text'           => 'Subscription created successfully',
                    "class"          => "success",
                    "token"          => csrf_hash(),
                    //"redirect"       => env("LOGIN_URL").base64_encode($userId)
                    "redirect"       => site_url("dpanel/dashboard")
                ]);
            }
            else {
                return $this->response->setJSON([
                    'success' => false,
                    "title"   => "Failed!",
                    'text'    => 'Failed to create subscription',
                    "class"   => "error",
                    "token"   => csrf_hash()
                ]);
            }
        } catch (\Exception $e) {
            $this->braintree->deleteCustomer($customer->id); // Cleanup
            return $this->response->setJSON(['success' => false, "title" => "Failed!", 'text' => $e->getMessage(), "class" => "error", "token" => csrf_hash()]);
        }
    }
}
