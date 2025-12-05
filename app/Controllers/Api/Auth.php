<?php

namespace App\Controllers\Api;

use App\Models\Users;
use App\Models\ApiTokenModel;
use CodeIgniter\RESTful\ResourceController;

class Auth extends ResourceController
{
    public function login()
    {
        $data = $this->request->getJSON(true);

        $user = (new Users())->asArray()->where('email', $data['email'])->first();
        if (!$user) return $this->failUnauthorized('Invalid email');

        if (!password_verify($data['password'], $user['password'])) {
            return $this->failUnauthorized('Invalid password');
        }

        $token = bin2hex(random_bytes(32));

        (new ApiTokenModel())->insert([
            'user_id'    => $user['id'],
            'token'      => $token,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
        ]);

        return $this->respond([
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $token
        ]);
    }
}
