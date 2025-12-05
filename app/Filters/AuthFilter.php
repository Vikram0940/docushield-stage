<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use \App\Models\Users;

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
    public function before(RequestInterface $request, $arguments = null)
    {
        $session       = session();
        $userModel     = new Users();
        $sessionConfig = config('Session');

        // expiration (seconds) and cookie name
        $timeout    = (int) $sessionConfig->expiration;
        $cookieName = $sessionConfig->cookieName;

        // 1) get raw cookie value (if any)
        $rawCookieVal = $_COOKIE[$cookieName] ?? null;
        // Normalize cookie/session id strings
        $cookieSessionId = $rawCookieVal ? trim(urldecode($rawCookieVal)) : null;
        $currentSessionId = session_id() ?: $session->getId();

        /**
         * CASE A: Auto-logout detection:
         * Cookie exists, but our session has no user info (session expired)
         * Try to clear user(s) with this session id OR with updated_date older than timeout.
         */
        if ($cookieSessionId && !$session->get('dc_loginid')) {

            // 1) Clear by matching the cookie value exactly
            if (!empty($cookieSessionId)) {
                $user = $userModel->where('current_session_id', $cookieSessionId)->first();
                if ($user) {
                    $userModel->update($user->id, [
                        'current_session_id' => null,
                        'updated_date'      => null
                    ]);
                }
            }

            // 2) Defensive cleanup: clear any users whose updated_date has expired
            $expiryThreshold = date('Y-m-d H:i:s', time() - $timeout);
            $expiredUsers = $userModel->where('updated_date <', $expiryThreshold)->findAll();

            if (!empty($expiredUsers)) {
                foreach ($expiredUsers as $u) {
                    // Only clear if persisted session_id still present (avoid accidental clears)
                    if (!empty($u->current_session_id)) {
                        $userModel->update($u->id, [
                            'current_session_id' => null,
                            'updated_date'      => null
                        ]);
                    }
                }
            }

            // continue as guest (do not redirect here)
        }

        /**
         * CASE B: If user is not logged in, redirect to login (original behavior)
         */
        if (!$session->get("dc_loginid")) {
            if (!$request->isAJAX()) {
                $session->set('redirect_url', current_url());
                return redirect()->to('/');
            }
            return $request->setJSON(["url" => site_url()]);
        }

        /**
         * CASE C: User is logged in — enforce single device
         */
        $userId = $session->get("dc_userid");
        $user   = $userModel->find($userId);

        if (!$user) {
            $session->destroy();
            return redirect()->to('/')->with('error', 'User not found.');
        }

        // If DB has no session, set it (first login / cleaned up earlier)
        if (empty($user->current_session_id)) {
            $userModel->update($user->id, [
                'current_session_id' => $currentSessionId,
                'updated_date'      => date('Y-m-d H:i:s')
            ]);
            return;
        }

        // Timeout check using updated_date
        if ($user->updated_date && strtotime($user->updated_date) + $timeout < time()) {
            $userModel->update($user->id, [
                'current_session_id' => null,
                'updated_date'      => null
            ]);
            $session->destroy();
            if ($request->isAJAX()) {
                return $request->setJSON(["url" => site_url(), "error" => "Session expired"]);
            }
            return redirect()->to('/')->with('error', 'Session expired due to inactivity.');
        }

        // Session mismatch (another device)
        if ($user->current_session_id !== $currentSessionId) {

            // allow if we just regenerated in same browser
            if ($session->has('session_regenerated')) {
                $userModel->update($user->id, [
                    'current_session_id' => $currentSessionId
                ]);
                $session->remove('session_regenerated');
                return;
            }

            // Another device logged in -> logout current session
            $session->destroy();
            if ($request->isAJAX()) {
                return $request->setJSON(["url" => site_url()]);
            }
            return redirect()->to('/')->with('error', 'You were logged out because your account was used elsewhere.');
        }

        // All good — update updated_date
        $userModel->update($user->id, [
            'updated_date' => date('Y-m-d H:i:s')
        ]);
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
