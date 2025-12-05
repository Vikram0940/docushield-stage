<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
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
    private $timeout = 900; // 15 minutes
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = \Config\Services::session();
        if (!$session->get("dc_loginid")) {
            if (!$request->isAJAX()) {
                // Save the current URL to session
                $session->set('redirect_url', current_url());
                return redirect()->to('/');
            }
            else {
                return $request->setJSON(["url" => site_url()]);
            }
        }
        else {
            $userModel = new \App\Models\Users;
            $user = $userModel->find($session->get("dc_userid"));

            // If no record, skip
            if (!$user) return;

            $currentSessionId = session_id();
            // If no session stored in DB, set it
            if ($user && empty($user->current_session_id)) {
                $userModel->update($user->id, ['current_session_id' => $currentSessionId]);
                return;
            }

            if ($user && $user->current_session_id !== $currentSessionId) {
                // Check if session was regenerated (same browser)
                if ($session->has('session_regenerated')) {
                    // Update DB to new session ID
                    $userModel->update($user->id, ['current_session_id' => $currentSessionId]);
                    $session->remove('session_regenerated');
                    return;
                }

                // Otherwise, assume it’s another device
                $session->destroy();
                if ($request->isAJAX()) {
                    return $request->setJSON(["url" => site_url()]);
                }
                return redirect()->to('')->with('error', 'You were logged out because your account was used elsewhere.');
            }
        }
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
