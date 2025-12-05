<?php

namespace App\Controllers;

class Home extends BaseController
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

    public function index(): string
    {
        return view('welcome_message');
    }

    public function sign_up($page = "") {
        if ($page == "" || $page == "account-detail" || $page == "subscription-plan") {            
            $data = [
                "title" => ($page=="") ? "Sign Up : ".env("COMPANY_NAME") : "Personal Information : ".env("COMPANY_NAME")
            ];
            if ($page == "account-detail" || $page == "subscription-plan") {
                if (!$this->session->get("dc_loginid")) {
                    return redirect()->to('/sign-up');
                }
                $data["user_info"] = (new \App\Models\Users)->where(["email" => $this->session->get("dc_loginid")])->first();
                if (empty($data["user_info"])) {
                    return redirect()->to('/sign-up');
                }
            }
            if (isset($_GET['token'])) {
                $token_info = (new \App\Models\Invitee)->where(["token" => $this->request->getGet("token")])->first();
                if (empty($token_info)) {
                    throw new \CodeIgniter\Exceptions\PageNotFoundException("Invalid page access");
                }
                $data["token"] = $this->request->getGet("token");
            }
            $data['morecss'] = array(
              site_url('public/assets/css/sign-up.css?v='.$this->version)
            );
            $data['morejs'] = array(
                "https://js.braintreegateway.com/web/dropin/1.40.2/js/dropin.min.js",
                ($page=="") ? site_url('public/assets/js/pages/sign-up.js?v='.$this->version) : site_url('public/assets/js/pages/'.$page.'.js?v='.$this->version)
            );
            $html = view("templates/sign-in-up-header", $data);
            $html .= view(($page=="") ? "sign-up/sign-up" : "sign-up/".$page, $data);
            $html .= view("templates/sign-in-up-footer", $data);
            return $html;
        }
        else if ($page == "create-account") {
            $rules = [
                "email" => [
                    "label"  => "Email", 
                    "rules"  => "required|trim|is_unique_soft[tblusers.email]",
                    'errors' => ['is_unique_soft' => 'Email id already exists.']
                ],
                'password' => [
                    'label' => 'Password',
                    'rules' => 'required|min_length[8]|regex_match[/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}/]',
                    'errors' => [
                        'required' => 'The {field} field is required.',
                        'min_length' => 'The {field} must be at least 8 characters long.',
                        'regex_match' => 'Password must contain at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character.'
                    ]
                ]
            ];
            $inputs = $this->validate($rules, []);
            if (!$inputs) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
            }

            $password = password_hash($this->request->getPost("password"), PASSWORD_DEFAULT);
            $data = [
                "email"    => $this->request->getPost("email"),
                "password" => $password,
                "bt_signup"=> 1
            ];

            $this->db->transException(true)->transStart();

            try {
                $usersModel   = new \App\Models\Users;
                $inviteeModel = new \App\Models\Invitee;

                // Case 1: With token (invite flow)
                if ($this->request->getPost('token')) {
                    $token = $this->request->getPost('token');
                    $email = $this->request->getPost('email');

                    $invitee_info = $inviteeModel->where(["token" => $token])->first();
                    if (empty($invitee_info)) {
                        throw new \Exception("Sorry! invalid request.");
                    }

                    if ($invitee_info->email !== $email) {
                        throw new \Exception("Please enter the correct email address you used to receive the invitation.");
                    }

                    // Optional: get source info
                    if ($invitee_info->source_type == 1) {
                        $projectInfo = (new \App\Models\Project)->find($invitee_info->source_id);
                        if (!empty($projectInfo)) {
                            $data["created_by"] = $projectInfo->user_id;
                        }
                    }

                    // Create user
                    $data["created_by"] = $invitee_info->invited_by;
                    $data["role"] = "ROLE_USER";
                    $result = $usersModel->insert($data); // returns insert ID

                    // Update invitee
                    $inviteeModel
                        ->set(["user_id" => $result])
                        ->where(["token" => $token])
                        ->update();

                    // Set session
                    $session_data = [
                        "dc_loginid" => $email,
                        "dc_userid"  => $result,
                        "dc_roles"   => $data["role"]
                    ];
                    $this->session->set($session_data);
                    $target_url = site_url("sign-up/account-detail?token=".$token);

                } else {
                    // Case 2: No token (default admin)
                    $data["role"] = "ROLE_ADMIN";
                    $result = $usersModel->insert($data);

                    $session_data = [
                        "dc_loginid" => $this->request->getPost("email"),
                        "dc_userid"  => $result,
                        "dc_roles"   => $data["role"]
                    ];
                    $this->session->set($session_data);
                    $target_url = site_url("sign-up/account-detail");
                }

                // Commit transaction
                $this->db->transComplete();

                return $this->response->setJSON([
                    "response" => 1,
                    "url"      => $target_url
                ]);

            } catch (\Throwable $e) {
                // Rollback transaction
                $this->db->transRollback();

                return $this->response->setJSON([
                    "response" => 0,
                    "title"    => "Failed!",
                    "text"     => $e->getMessage(),
                    "class"    => "error",
                    "token"    => csrf_hash()
                ]);
            }
        }
        else if ($page == "submit-account-detail") {
            $rules = [
                "business_type" => [
                    "label"  => "Business type", 
                    "rules"  => "required|trim"
                ],
                'first_name' => [
                    'label' => 'First name',
                    'rules' => 'required|trim'
                ],
                'last_name' => [
                    'label' => 'Last name',
                    'rules' => 'required|trim'
                ],
                'phone' => [
                    'label' => 'Phone',
                    'rules' => 'required|trim'
                ]
            ];
            $inputs = $this->validate($rules, []);
            if (!$inputs) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
            }
            // CHECK OTHER BUSINESS TYPE
            if ($this->request->getPost("business_type") == "Other" && empty($this->request->getPost("otherBusinessType"))) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => "Please enter the business type", "class" => "error", "token"  => csrf_hash()]);
            }

            $data = [
                "id"            => $this->session->get("dc_userid"),
                "business_type" => ($this->request->getPost("business_type")=="Other") ? $this->request->getPost("otherBusinessType") : $this->request->getPost("business_type"),
                "name"          => $this->request->getPost("first_name") ." ". $this->request->getPost("last_name"),
                "phone"         => $this->request->getPost("phone"),
                "company_name"  => $this->request->getPost("company_name")
            ];

            $file_path = 'public/uploads/profile/'.$this->session->get("dc_userid").'/documents/';
            if (!is_dir($file_path)) {
                mkdir($file_path, 0777, true);
            }
            $file_path = ROOTPATH.$file_path;
            $verification_documents = $this->request->getFiles();
            if ($verification_documents && isset($verification_documents["file"])) {
                $file = $verification_documents["file"];
                if (!$file->isValid()) {
                    return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => "Please select verification document", "class" => "error", "token"  => csrf_hash()]);
                }
                else {
                    $verification_document = $file->getRandomName();
                    $file->move($file_path, $verification_document);
                    $data["verification_documents"] = ["bar_card_image" => $verification_document];
                }
            }

            $usersModel = new \App\Models\Users;
            $userInfo = $usersModel->find($this->session->get("dc_userid"));

            if ($userInfo->role == "ROLE_USER") {
                $data["status"] = 1; // Activate the normal user
            }
            
            $result = $usersModel->save($data);
            if ($result) {
                
                $this->session->set(["new_user" => true]);
                $target_url = ($userInfo->role=="ROLE_USER") ? site_url("dpanel/dashboard") : site_url("sign-up/subscription-plan");
                return $this->response->setJSON(["response" => 1, "url" => $target_url]);
            }
            else {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => ERROR_MSG, "class" => "error", "token"  => csrf_hash()]);
            }
        }
        else if ($page == "get-braintree-token") {
            // If logged in, pass customerId to reuse card
            $user = session()->get('user');
            $customer = $this->braintree->createOrFindCustomer($user);

            $token = $this->braintree->generateClientToken($customer ? $customer->id : null);
            return $this->response->setJSON(['clientToken' => $token]);
        }
        else if ($page == "create-subscription") {
            $this->form_validation->set_rules('b_first_name', "First name", "required|trim");
            $this->form_validation->set_rules('b_last_name', "Last name", "required|trim");
            $this->form_validation->set_rules('credit_card_no', "Credit card no.", "required|trim");
            $this->form_validation->set_rules('expiry_date', "Expiry date", "required|trim");
            $this->form_validation->set_rules('cvv', "CVV", "required|trim");

            if ($this->form_validation->run() == FALSE) {
                echo json_encode(["response" => 0, "title" => "Failed!", "text" => strip_tags(validation_errors()), "class" => "error"]);
                exit();
            }

            $expiry_date = explode("/", $this->input->post("expiry_date"));
            $exp_month = $expiry_date[0]; $exp_year = $expiry_date[1];
            $exp_date = date($exp_year."-".$exp_month."-01");
            if ($exp_date <= date("Y-m-d")) {
                echo json_encode(["response" => 0, "title" => "Failed!", "text" => "Please enter the valid card expiry date", "class" => "error"]);
                exit();
            }

            try {
                $customerResponse = $this->bt_gateway->customer()->create([
                    'firstName' => $this->input->post("b_first_name"),
                    'lastName'  => $this->input->post("b_last_name"),
                    'company'   => $this->input->post("company_name"),
                    'email'     => $this->input->post("userid"),
                    'phone'     => $this->input->post("phone"),
                    'creditCard'=> [
                        'cardholderName' => $this->input->post("b_first_name") . " " . $this->input->post("b_last_name"),
                        'cvv'            => $this->input->post("cvv"),
                        'expirationDate' => $exp_month . "/" . $exp_year,
                        'number'         => $this->input->post("credit_card_no"),
                        'billingAddress' => [
                            'firstName'     => $this->input->post("b_first_name"),
                            'lastName'      => $this->input->post("b_last_name"),
                            'company'       => $this->input->post("company_name")
                        ],
                        'options'        => [
                            'verifyCard'         => true,
                            'makeDefault'        => true
                        ]
                    ]
                ]);
                if (isset($customerResponse->success) && $customerResponse->success == 1) {
                    $customerProfileId = $customerResponse->customer->id;
                    $customerCreditCardToken = $customerResponse->customer->creditCards[ 0 ]->token;
                    /* subscribe customer */
                    $subscriptionResponse = $this->bt_gateway->subscription()->create([
                        'paymentMethodToken' => $customerCreditCardToken,
                        'planId' => BT_PLAN_ID
                    ]);
                    /*print_r($subscriptionResponse);
                    exit();*/
                    if (isset($subscriptionResponse->success) && $subscriptionResponse->success) {
                        $customerSubscriptionId = $subscriptionResponse->subscription->id;
                    }
                    else {
                        $message = "Sorry! something went wrong with payment gateway";
                        echo json_encode(["response" => 0, "title" => "Failed!", "text" => $messages, "class" => "error"]);
                        exit();
                    }
                    /* subscribe customer */
                }
                else {
                    echo json_encode(["response" => 0, "title" => "Failed!", "text" => "Credit card Validation and Verfication Failed. Please try again with valid Credit Card. If error Persist contact your respective bank.", "class" => "error"]);
                    exit();
                }
            }
            catch (Exception $ex) {
                echo json_encode(["response" => 0, "title" => "Failed!", "text" => $ex->getMessage(), "class" => "error"]);
                exit();
            }

            $password = password_hash($this->input->post("password"), PASSWORD_DEFAULT);
            $data = [
                "name"         => trim($this->input->post("first_name")." ".$this->input->post("last_name")),
                "company_name" => $this->input->post("company_name"),
                "phone"        => $this->input->post("phone"),
                "email"        => $this->input->post("userid"),
                "password"     => $password,
                /*"verification_documents" => json_encode([
                    "bar_card_image" => $this->input->post("bar_card"),
                    "dl_image"       => $this->input->post("driving_license_card")
                ]),*/
                "bt_signup"    => 1,
                "role"         => "ROLE_USER"
            ];

            if (!empty($_POST['bar_card'])) {
                $data["verification_documents"]["bar_card_image"] = $this->input->post("bar_card");
            }
            if (!empty($_POST['driving_license_card'])) {
                $data["verification_documents"]["dl_image"] = $this->input->post("driving_license_card");
            }

            if (isset($customerProfileId)) {
                $data["bt_profile"] = json_encode([
                    "customerId"      => $customerProfileId,
                    "creditCardtoken" => $customerCreditCardToken,
                    "subscriptionId"  => $customerSubscriptionId
                ]);
            }

            if (isset($_POST['token'])) {
                $share_info = $this->master->get("tbldocumentshares", "", ["token" => $this->input->post("token")], true);
                $data["role"] = $share_info->doc_role;

                /* create comet chat user */
                $comet_response = $this->app->createCometUser($this->chat_client, $data);
                if (!empty($comet_response)) {
                    $data["chat_id"] = $comet_response["chat_id"];
                    $data["chat_auth_token"] = $comet_response["chat_auth_token"];
                }
                /* create comet chat user */

                $result = $this->master->insert("tblusers", $data);

                if ($result) {
                    $document_info = $this->master->get("tbldocuments", "", ["id" => $share_info->document_id], true);
                    if (!empty($document_info)) {
                        $data["created_by"] = $document_info->created_by;
                    }

                    $update = $this->master->update("tbldocumentshares", ["user_id" => $result], ["token" => $this->input->post("token")]);

                    $this->session->set_flashdata(["account" => "confirm"]);
                    echo json_encode(["response" => 1, "url" => site_url("sign-up/confirmation")]);
                    exit();
                }
                else {
                    $result = $this->bt_gateway->customer()->delete($customerProfileId);
                    echo json_encode(["response" => 0, "title" => "Failed!", "text" => "Sorry! something went wrong. Please try later", "class" => "error"]);
                    exit();
                }

                /*$session_data = [
                    "dc_loginid" => $this->input->post("userid"),
                    "dc_userid"  => $result,
                    "dc_name"    => $this->input->post("name"),
                    "dc_roles"   => $data["role"]
                ];
                $this->session->set_userdata($session_data);
                echo json_encode(array("response" => 1, "url" => site_url("dpanel/documents/edit/".$share_info->document_id), "username" => $this->input->post("name")));
                exit();*/
            }

            $data["role"] = "ROLE_ADMIN";

            /* create comet chat user */
            try {
                $comet_response = $this->app->createCometUser($this->chat_client, $data);
                if (!empty($comet_response)) {
                    $data["chat_id"] = $comet_response["chat_id"];
                    $data["chat_auth_token"] = $comet_response["chat_auth_token"];
                }
            }
            catch (Exception $ex){}
            /* create comet chat user */

            $result = $this->master->insert("tblusers", $data);
            if ($result) {
                $this->session->set_flashdata(["account" => "confirm"]);
                echo json_encode(array("response" => 1, "url" => site_url("sign-up/confirmation")));
                exit();
                /*$session_data = [
                    "dc_loginid" => $this->input->post("userid"),
                    "dc_userid"  => $result,
                    "dc_name"    => $this->input->post("name"),
                    "dc_roles"   => $data["role"]
                ];
                $this->session->set_userdata($session_data);
                echo json_encode(array("response" => 1, "url" => site_url("dpanel/dashboard"), "username" => $this->input->post("name")));
                exit();*/
            }
            else {
                $result = $this->bt_gateway->customer()->delete($customerProfileId);
                echo json_encode(["response" => 0, "title" => "Failed!", "text" => "Sorry! something went wrong. Please try later", "class" => "error"]);
                exit();
            }
        }
        else if ($page == "confirmation") {
            $data = [];
            if (!$this->session->flashdata("account")) {
                header("Location:".site_url("sign-up-bt"));
                exit();
            }
            $this->load->view('sign-up-confirmation', $data);
        }
    }

    public function forgot_password($page = "") {
        if ($page == "" || $page == "reset-password") {
            $data = [
                "title" => ($page=="") ? env("COMPANY_NAME"). " : Password Recovery" : env("COMPANY_NAME"). " : Reset Password"
            ];
            $data['morecss'] = array(
                ($page=="reset-password") ? site_url('public/assets/css/sign-up.css?v='.$this->version) : site_url('public/assets/css/sign-in.css?v='.$this->version)
            );
            $data['morejs'] = array(
                ($page=="reset-password") ? site_url('public/assets/js/pages/reset-password.js?v='.$this->version) : site_url('public/assets/js/pages/sign-in.js?v='.$this->version)
            );

            if ($page == "reset-password") {
                if (!$this->request->getGet("token")) {
                    throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! page you are trying to access doesn't exist");
                }
                $usersModel = new \App\Models\Users;
                $user_information = $usersModel->where("token", $this->request->getGet("token"))->first();
                if (empty($user_information)) {
                    throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! page you are trying to access doesn't exist");
                }
                if (strtotime($user_information->token_expires_at) < time()) {
                    throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! This reset link has expired.");
                }
                $data["token"] = $this->request->getGet("token");
            }

            $html = view("templates/sign-in-up-header", $data);
            if ($page == "") {
                $html .= view("forgot-password", $data);
            }
            else {
                $html .= view("reset-password", $data);
            }
            $html .= view("templates/sign-in-up-footer", $data);
            return $html;
        }
        else if ($page == "send-link") {
            $inputs = $this->validate([
                    'email' => 'required|valid_email'
                ]
            );
            if (!$inputs) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
            }

            $usersModel = new \App\Models\Users;
            $user_information = $usersModel->where("email", $this->request->getPost('email'))->first();
            if (empty($user_information)) { // check if user id exists
                return $this->response->setJSON(["response" => 0, "title" => "Failed", "text" => "Sorry! email id doesn't exist", "class" => "error", "token"  => csrf_hash()]);
            }

            /* Send mail to invitee */
            $token = bin2hex(random_bytes(32)); // secure random string
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $result = $usersModel->update($user_information->id, [
                "token"            => $token,
                "token_expires_at" => $expires
            ]);
            if ($result) {
                $link = site_url("reset-password?token=".$token);
                $emailService = service('myEmail');
                $mailStatus = $emailService->sendEmail(
                    [$this->request->getPost('email')], // To
                    "Reset Your Password for ".env("COMPANY_NAME"), // Subject
                    '',
                    null,
                    null,
                    'email-templates/forgot-password', // Mail body
                    [ 'name' => $user_information->name, 'link' => $link ], // Body dynamic data
                    [],
                    ["vishdhanu@gmail.com", "george@iotedgemarketing.com"],
                    [],
                    null,
                    null
                );
                $this->session->setFlashdata(["message" => "A password reset link has been sent to your registered email address. Please check your inbox and follow the instructions to reset your password."]);
                return $this->response->setJSON(["response" => 1, "url" => site_url("forgot-password")]);
            }
            else {
                return $this->response->setJSON(["response" => 0, "title" => "Failed", "text" => "Sorry! something went wrong", "class" => "error", "token"  => csrf_hash()]);
            }
            /* Send mail to invitee */
        }
        else if ($page == "change-password") {
            $rules = [
                'password' => [
                    'label' => 'Password',
                    'rules' => 'required|min_length[8]|regex_match[/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}/]',
                    'errors' => [
                        'required' => 'The {field} field is required.',
                        'min_length' => 'The {field} must be at least 8 characters long.',
                        'regex_match' => 'Password must contain at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character.'
                    ]
                ],
                'confirm' => [
                    'label' => 'Confirm password',
                    'rules' => 'required|matches[password]'
                ]
            ];
            $inputs = $this->validate($rules, []);
            if (!$inputs) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
            }

            $usersModel = new \App\Models\Users;
            $user_information = $usersModel->where("token", $this->request->getPost("token"))->first();
            if (empty($user_information)) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed", "text" => "Sorry! invalid request made.", "class" => "error", "token"  => csrf_hash()]);
            }
            if (strtotime($user_information->token_expires_at) < time()) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed", "text" => "Sorry! This reset link has expired.", "class" => "error", "token"  => csrf_hash()]);
            }

            $password = password_hash($this->request->getPost("password"), PASSWORD_DEFAULT);

            /* Send mail to invitee */
            $result = $usersModel->update($user_information->id, [
                "password"         => $password,
                "token"            => null,
                "token_expires_at" => null
            ]);
            if ($result) {
                $emailService = service('myEmail');
                $mailStatus = $emailService->sendEmail(
                    [$this->request->getPost('email')], // To
                    "Your ".env("COMPANY_NAME")." Password Was Changed", // Subject
                    '',
                    null,
                    null,
                    'email-templates/reset-password-notification', // Mail body
                    [ 'name' => $user_information->name ], // Body dynamic data
                    [],
                    ["vishdhanu@gmail.com", "george@iotedgemarketing.com"],
                    [],
                    null,
                    null
                );
                $this->session->setFlashdata(["success" => "Your password has been changed successfully. You can now log in with your new credentials."]);
                return $this->response->setJSON(["response" => 1, "url" => site_url()]);
            }
            else {
                return $this->response->setJSON(["response" => 0, "title" => "Failed", "text" => "Sorry! something went wrong", "class" => "error", "token"  => csrf_hash()]);
            }
            /* Send mail to invitee */
        }
    }

    public function get_token() {
        return $this->response->setJSON([
            'csrf_token' => csrf_hash(),
            'csrf_name'  => csrf_token()
        ]);
    }
}
