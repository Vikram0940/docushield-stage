<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class BearerAuth implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // $authHeader = $request->getHeaderLine("Authorization");
        # Newly Added Lines while running some tests on MAMP - MacOS, the Authorization header wasn't being received in the request
        $authHeader = $request->getServer('HTTP_AUTHORIZATION')
            ?? $request->getServer('REDIRECT_HTTP_AUTHORIZATION')
            ?? $request->getHeaderLine('Authorization');

        if (! $authHeader || ! preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['message' => 'Missing or invalid Authorization header']);
        }

        $token = $matches[1];

        $tokenModel = new \App\Models\ApiTokenModel();
        $tokenData = $tokenModel->asArray()->where('token', $token)->first();

        if (! $tokenData) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['message' => 'Invalid token']);
        }

        // Optional: check expiration
        if ($tokenData['expires_at'] !== null && strtotime($tokenData['expires_at']) < time()) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['message' => 'Token expired']);
        }

        // Make user ID available to controllers if needed
        $request->user_id = $tokenData['user_id'];
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
