<?php

namespace App\Filters;

use CodeIgniter\Filters\Honeypot as BaseHoneypot;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class HoneypotPostOnly extends BaseHoneypot
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Only apply honeypot for POST requests
        if ($request->getMethod() !== 'post') {
            return; // skip honeypot
        }

        // Run normal honeypot check
        return parent::before($request, $arguments);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Only inject honeypot field for POST forms
        if ($request->getMethod() !== 'get') {
            return parent::after($request, $response, $arguments);
        }

        return $response;
    }
}
