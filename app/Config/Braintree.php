<?php namespace Config;

use Braintree\Gateway;

class Braintree
{
    public static function gateway()
    {
        return new Gateway([
            'environment' => env('braintree.environment'),
            'merchantId'  => env('braintree.merchantId'),
            'publicKey'   => env('braintree.publicKey'),
            'privateKey'  => env('braintree.privateKey'),
        ]);
    }
}