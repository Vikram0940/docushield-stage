<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Users;
use App\Libraries\CometChatService;
use Google_Client;
use Google_Service_Oauth2;

class Auth extends BaseController
{
    public $session;
    public $validation;
    public $view;
    private $version;
    public function __construct() {
        $this->validation = \Config\Services::validation();
        $this->session = \Config\Services::session();
        $this->view = \Config\Services::renderer();
        $this->version = uniqid();
    }

    public function index()
    {
        if ($this->session->get("dc_loginid")) {
            return redirect()->to('/dpanel/dashboard');
        }
        $data = [
            "title" => env("COMPANY_NAME"). " : Sign In"
        ];
        $data['morecss'] = array(
            site_url('public/assets/css/sign-in.css?v='.$this->version)
        );
        $data['morejs'] = array(
            site_url('public/assets/js/pages/sign-in.js?v='.$this->version)
        );
        if (isset($_GET["token"])) {
            $token_info = (new \App\Models\Invitee)->where(["token" => $this->request->getGet("token")])->first();
            if (empty($token_info)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Invitee sign in");
            }
            $data["token"] = $this->request->getGet("token");
        }
        $html = view("templates/sign-in-up-header", $data);
        $html .= view("sign-in", $data);
        $html .= view("templates/sign-in-up-footer", $data);
        return $html;
    }

    public function authenticate() {
        $inputs = $this->validate([
                'email'   => 'required',
                'password' => 'required'
            ]
        );
        if (!$inputs) {
            return $this->response->setJSON(["response" => 0, "title" => "Failed", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
        }

        $usersModel = new Users;
        $user_information = $usersModel->where("email", $this->request->getPost('email'))->first();
        if (empty($user_information)) { // check if user id exists
            return $this->response->setJSON(["response" => 0, "title" => "Failed", "text" => "Sorry! invalid email id entered", "class" => "error", "token"  => csrf_hash()]);
        }

        /* check if password match */
        if (!password_verify($this->request->getPost("password"), $user_information->password)) {
            return $this->response->setJSON(["response" => 0, "title" => "Failed", "text" => "Sorry! invalid password entered", "class" => "error", "token"  => csrf_hash()]);
        }

        if (!$user_information->status) {
            echo json_encode(["response" => 0, "title" => "Failed!", "text" => 'Sorry! it seems that your account is not active. Please contact system administrator to activate your account.', "class" => "warning"]);
            exit();
        }

        // ✅ Check if user already has active session
        if (!empty($user_information->current_session_id)) {
            // Check if that session is still valid
            $sessionPath = WRITEPATH . 'session/ci_session' . $user_information->current_session_id;
            if (file_exists($sessionPath) && $user_information->current_session_id != session_id()) {
                // Session file still exists → another device is active
                $this->session->set('pending_user_id', $user_information->id);
                return $this->response->setJSON(['response' => 1, "url" => site_url("force-logout-choice")]);
            } else {
                // File missing → session expired naturally → clear it
                $usersModel->update($user_information->id, ['current_session_id' => null]);
                $user_information = $usersModel->where("email", $this->request->getPost('email'))->first();
            }
        }

        // ✅ Login normally
        session()->regenerate(true); // Generate new session ID
        $currentSessionId = session_id();

        // Check if already logged in from another session
        if (!empty($user_information->current_session_id) && $user_information->current_session_id !== $currentSessionId) {
            // Store user temporarily in session for next step (force logout option)
            $this->session->set('pending_user_id', $user_information->id);
            return $this->response->setJSON(['response' => 1, "url" => site_url("force-logout-choice")]);
        }
        
        $session_data = [
            "dc_loginid" => $this->request->getPost("email"),
            "dc_userid"  => $user_information->id,
            "dc_name"    => $user_information->name,
            "dc_roles"   => $user_information->role,
            "dc_avatar"  => $user_information->avatar
        ];

        /* create comet chat user if not created */
        /*if ($user_information->role != "ROLE_SUPER_ADMIN" && empty($user_information->chat_id)) {
            $chatService = new CometChatService();
            $result = $chatService->createUser($user_information->email, $user_information->name);
            if (!empty($result)) {
                $data["chat_id"] = $result["chat_id"];
                $data["chat_auth_token"] = $result["chat_auth_token"];
                $usersModel->set([
                    "chat_id"         => $result["chat_id"],
                    "chat_auth_token" => $result["chat_auth_token"]
                ])->update($user_information->id);
            }
        }*/
        /* create comet chat user if not created */

        $this->session->set($session_data);
        //$target_url = env("LOGIN_URL").base64_encode($user_information->id);
        //$target_url = site_url("dpanel/dashboard");

        /* Update current session id in user table */
        $usersModel->update($user_information->id, ['current_session_id' => $currentSessionId]);

        // Redirect to previously saved URL or fallback
        $target_url = $this->session->get('redirect_url') ?? site_url('dpanel/dashboard');
        $this->session->remove('redirect_url');

        return $this->response->setJSON(['response' => 1, "url" => $target_url]);
    }

    public function google_signin() {
        $client = new Google_Client();
        $client->setClientId(getenv('google.clientId'));
        $client->setClientSecret(getenv('google.clientSecret'));
        $client->setRedirectUri(base_url('auth/google/callback')); // 👈 required!
        $client->addScope("email");
        $client->addScope("profile");
        if (isset($_GET['token'])) {
            $customData = [
                'token' => $this->request->getGet("token")
            ];
            $client->setState(base64_encode(json_encode($customData)));
        }
        //$data["google_url"] = $client->createAuthUrl();
        return $this->response->setJSON(["url" => $client->createAuthUrl()]);
    }

    public function google_callback()
    {
        $client = new Google_Client();
        $client->setClientId(getenv('google.clientId'));
        $client->setClientSecret(getenv('google.clientSecret'));
        $client->setRedirectUri(site_url('auth/google/callback'));
        
        if ($this->request->getVar('code')) {
            $token = $client->fetchAccessTokenWithAuthCode($this->request->getVar('code'));

            // Check for error before setting access token
            if (isset($token['error'])) {
                // Debug log
                log_message('error', 'Google Auth error: ' . $token['error_description']);
                return redirect()->to('/')->with('fail', 'Google login failed: ' . $token['error_description']);
            }

            $client->setAccessToken($token);

            // Check if token is expired
            if ($client->isAccessTokenExpired()) {
                // You can refresh if you saved refresh_token earlier
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                } else {
                    // Need to re-login
                    return redirect()->to('/');
                }
            }

            $google_service = new Google_Service_Oauth2($client);
            $google_data = $google_service->userinfo->get();
            $email = $google_data->email;

            $state = $this->request->getVar('state');
            if ($state) {
                $customData = json_decode(base64_decode($state), true);
                if (isset($customData["token"])) {
                    $inviteeModel = new \App\Models\Invitee;
                    $invitee_info = $inviteeModel->where(["token" => $customData["token"]])->first();
                    if (empty($invitee_info)) {
                        return redirect()->to('/sign-up?token='.$customData["token"])->with('fail', 'Google login failed with invalid invitee token');
                    }
                    if ($invitee_info->email != $email) {
                        return redirect()->to('/sign-up?token='.$customData["token"])->with('fail', 'Please choose the correct email address you used to receive the invitation.');
                    }
                }
            }

            $usersModel = new \App\Models\Users;
            $user_information = $usersModel->where(["email" => $email])->first();
            if (empty($user_information)) {
                $password = $this->generateRandomPassword(10);
                $password = password_hash($password, PASSWORD_DEFAULT);
                $data = [
                    "email"    => $email,
                    "name"     => $google_data->name,
                    "password" => $password,
                    "bt_signup"=> 1,
                    "role"     => isset($customData) ? "ROLE_USER" : "ROLE_ADMIN",
                    "avatar"   => $google_data->picture
                ];
                $userID = $usersModel->insert($data);
                if ($userID) {
                    if (isset($customData)) {
                        $update = $inviteeModel
                            ->set(["user_id" => $userID])
                            ->where(["token" => $customData["token"]])
                            ->update();
                    }
                    $session_data = [
                        "dc_loginid" => $email,
                        "dc_userid"  => $userID,
                        "dc_name"    => $google_data->name,
                        "dc_roles"   => $data["role"],
                        "dc_avatar"  => $google_data->picture
                    ];
                    
                    /* Update current session id in user table */
                    $currentSessionId = session_id();
                    $usersModel->update($userID, ['current_session_id' => $currentSessionId]);

                    $this->session->set($session_data);
                    return isset($customData["token"]) ? redirect()->to('sign-up/account-detail?token='.$customData["token"]) : redirect()->to('sign-up/account-detail');
                }
                else {
                    return redirect()->to('/')->with('fail', 'Google login failed');
                }
            }

            /* Update current session id in user table */
            $currentSessionId = session_id();
            $usersModel->update($user_information->id, ['current_session_id' => $currentSessionId]);

            // Example: Save to session
            session()->set([
                "dc_loginid" => $email,
                "dc_userid"  => $user_information->id,
                "dc_name"    => $user_information->name,
                "dc_roles"   => $user_information->role,
                "dc_avatar"  => $user_information->avatar
            ]);
            if ($user_information->bt_signup == 1 && empty($user_information->bt_profile)) {
                return redirect()->to('sign-up/account-detail');
            }
            else {
                return redirect()->to('dpanel/dashboard');
            }
        } else {
            return redirect()->to('/')->with('fail', 'Google login failed');
        }
    }

    public function forceLogout($page = "") {
        if ($page == "") {
            $data = [
                "title" => env("COMPANY_NAME"). " : Session Detected"
            ];
            $html = view("templates/sign-in-up-header", $data);
            $html .= view("force-logout-choice", $data);
            $html .= view("templates/sign-in-up-footer", $data);
            return $html;
        }
        else if ($page == "logout-other-device") {
            //$session = session();
            $userModel = new \App\Models\Users;
            $userId = $this->session->get('pending_user_id');

            if (!$userId) {
                return redirect()->to('')->with('error', 'Session expired, please login again.');
            }

            $user = $userModel->find($userId);
            if (empty($user)) {
                return redirect()->to('')->with('error', 'Session expired, please login again.');
            }

            // ✅ Remove old session file
            if (!empty($user->current_session_id)) {
                $oldSessionFile = WRITEPATH . 'session/ci_session' . $user->current_session_id;
                if (file_exists($oldSessionFile)) {
                    unlink($oldSessionFile);
                }
            }

            // Invalidate previous session (you can handle this via DB or cache)
            // ✅ Continue with new login
            session()->regenerate(true);
            $newSessionId = session_id();

            // Update user's active session
            $userModel->update($userId, ['current_session_id' => $newSessionId]);

            // Example: Save to session
            $user_information = $userModel->find($userId);
            session()->remove("pending_user_id");
            session()->set([
                "dc_loginid" => $user_information->email,
                "dc_userid"  => $user_information->id,
                "dc_name"    => $user_information->name,
                "dc_roles"   => $user_information->role                
            ]);
            if ($user_information->bt_signup == 1 && empty($user_information->bt_profile)) {
                return redirect()->to('sign-up/account-detail')->with('success', 'Logged out from other device.');
            }
            else {
                return redirect()->to('dpanel/dashboard')->with('success', 'Logged out from other device.');
            }            
        }
    }

}
