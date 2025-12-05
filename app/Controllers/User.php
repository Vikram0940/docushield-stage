<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class User extends BaseController
{
    public $session;
    public $version;
    public function __construct() {
        $this->session = \Config\Services::session();
        $this->version = uniqid();
    }

    public function get_user_info() {
        $rules = [
            "email" => [
                "label"  => "Collaborator email", 
                "rules"  => "required|trim|valid_email"
            ],
            "permission" => [
                "label"  => "Permission", 
                "rules"  => "required|trim"
            ]
        ];
        $inputs = $this->validate($rules, []);
        if (!$inputs) {
            return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
        }

        $user_info = (new \App\Models\Users)->where(["email" => $this->request->getPost("email"), "status" => 1])->first();
        if (!empty($user_info)) {
            $name = $user_info->name;
            $email = $user_info->email;
            $avatar = user_avatar($user_info->avatar);
            $user_id = $user_info->id;
        }
        else {
            $name = "";
            $email = $this->request->getPost("email");
            $avatar = site_url('public/assets/images/no-avatar.png');
            $user_id = 0;
        }
        $view = ($this->request->getPost("permission")==1) ? "selected='selected'" : "";
        $comment = ($this->request->getPost("permission")==2) ? "selected='selected'" : "";
        $id = date('YmdHis').md5(uniqid(rand(), true));
        $html = "<li>
            <div class=\"avatar\">
                <img src=\"".$avatar."\">
            </div>
            <div class=\"collab-permission\">
                <div class=\"collaborator flex-grow-1\">
                    <div class=\"name\">".$name."</div>
                    <div class=\"email\">".$email."</div>
                </div>
                <div class=\"invite-permissions\">
                    <select class=\"permission\" name='invitee[".$id."][permission]'>
                        <option value=\"1\" ".$view.">Can view</option>
                        <option value=\"2\" ".$comment.">Can edit</option>
                    </select>
                </div>
                <input type='hidden' name='invitee[".$id."][email]' value='".$email."'>
                <input type='hidden' name='invitee[".$id."][userid]' value='".$user_id."'>
            </div>
        </li>";
        return $this->response->setJSON(["response" => 1, "html" => $html, "token"  => csrf_hash()]);
    }

    public function get_li_list() {
        $userModel = new \App\Models\Users;
        $my_users = $userModel->where(["created_by" => session()->get("dc_userid"), "status" => 1])->orderBy("name")->findAll();        
        $myUsersHtml = empty($my_users) ? "" : view("projects/my-users-list", ["users" => $my_users]);
        return $this->response->setJSON(["response" => 1, "users" => $myUsersHtml, "token"  => csrf_hash()]);
    }

    public function favorites($page = "", $id = 0) {
        if ($page == "") {
            $data['pagename'] = "favorites";
            $data["version"] = $this->version;
            $data["session"] = $this->session;
            $data["segments"] = $this->request->uri->getSegments();
            $data["agent"] = $this->request->getUserAgent();
            $data["page_title"] = "Favorites : ".env("COMPANY_NAME");
            $data['morejs'] = array(
                site_url('public/assets/libs/datatables.net/js/jquery.dataTables.min.js'),
                site_url('public/assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js'),
                site_url('public/assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js'),
            site_url('public/assets/libs/magnific-popup/jquery.magnific-popup.min.js'),
                site_url('public/assets/js/pages/projects.js?v='.$this->version),
                site_url('public/assets/js/pages/favorites.js?v='.$this->version)
            );
            $data['morecss'] = array(
                site_url('public/assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css'),
                site_url('public/assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'),
                site_url('public/assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css'),
                site_url('public/assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css'),
                site_url('public/assets/libs/magnific-popup/magnific-popup.css')
            );

            $html  = view("templates/header", $data);
            $html .= view("templates/left-nav", $data);
            $html .= view("templates/top-bar", $data);
            $html .= view("favorites/index", $data);
            $html .= view("templates/footer", $data);
            return $html;
        }
        else if ($page == "add-remove") {
            $rules = [
                "source_id" => [
                    "label"  => "Id", 
                    "rules"  => "required|trim|numeric"
                ],
                "source_type" => [
                    "label"  => "Type", 
                    "rules"  => "required|trim|numeric"
                ]
            ];
            $inputs = $this->validate($rules, [], ['id' => $id]);
            if (!$inputs) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
            }
            $favoritesModel = new \App\Models\Favorites;

            $params = [
                "source_id"   => $this->request->getPost("source_id"),
                "source_type" => $this->request->getPost("source_type"),
                "user_id"     => $this->session->get("dc_userid"),
            ];
            /* Check if favorite already exist */
            $favoriteInfo = $favoritesModel->where($params)->first();

            if (!empty($favoriteInfo)) {
                // Remove from db
                $result = $favoritesModel->where($params)->delete();
                $message = "Item has been removed from your favorite list successfully.";
                $event = "remove";
            }
            else {
                // Save favorite in db
                $result = $favoritesModel->insert($params);
                $message = "Item has been added to your favorite list successfully.";
                $event = "add";
            }
            
            if ($result) {
                return $this->response->setJSON(["response" => 1, "title" => "Successfull!", "text" => $message, "class" => "success", "event" => $event, "token"  => csrf_hash()]);
            }
            else {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => "Sorry! something went wrong", "class" => "error", "event" => $event, "token"  => csrf_hash()]);
            }
        }
        else if ($page == "get-json-data") {
            // Define columns for global search
            $aColumns = ["title", "content", "source_id", "source_type", "user_id", "status", "template", "bs_response", "bs_data", "chat_group_id", "storage_type", "storage_path"];

            // Pagination
            $limit  = (int) $this->request->getPost('iDisplayLength');
            $offset = (int) $this->request->getPost('iDisplayStart');

            // Sorting
            $columns = [
                0 => 'created_at',
                1 => 'title',
                2 => 'storage_type',
                //3 => 'size',
                //4 => 'activity'
                5 => 'updated_at',
                6 => 'created_at',
                //6 => ''
            ];

            $sortCol = (int) $this->request->getPost('iSortCol_0') ?? 6;
            $sortDir = $this->request->getPost('sSortDir_0') ?? 'desc';
            $orderBy = $columns[$sortCol] ?? 'created_at';

            // Start Query
            $favoritesModel = new \App\Models\Favorites();
            $builder = $favoritesModel->builder();
            $builder->from($favoritesModel->getTable() . ' f', true)->select("
                f.id as id,
                f.source_type,f.source_id,
                CASE 
                    WHEN f.source_type = 1 THEN p.name 
                    ELSE s.title 
                END as title,
                CASE 
                    WHEN f.source_type = 1 THEN p.status 
                    ELSE s.status 
                END as status,
                CASE 
                    WHEN f.source_type = 1 THEN 'Project'
                    WHEN f.source_type = 2 THEN 'Document'
                    WHEN f.source_type = 3 THEN 'File'
                    WHEN f.source_type = 4 THEN 'Image'
                END as file_type,
                CASE 
                    WHEN f.source_type = 1 THEN 'pdf'
                    WHEN f.source_type = 2 THEN 'document'
                    WHEN f.source_type = 3 THEN 'document'
                    WHEN f.source_type = 4 THEN 'image'
                END as file_icon_type,
                CASE 
                    WHEN f.source_type = 1 THEN p.created_at 
                    ELSE s.created_at 
                END as created_at,
                CASE 
                    WHEN f.source_type = 1 THEN p.updated_at 
                    ELSE s.updated_at 
                END as updated_at,
                s.storage_path,
                s.storage_type,
                s.user_id,
                s.parent_type,
                s.parent_id,
                s.file_size,
                s.file_mime_type,
                p.case_id
            ", false)  // 🚀 prevent auto-escaping
            ->where("f.deleted_at IS NULL")
            ->join('tblprojects p', 'p.id = f.source_id AND f.source_type=1', 'left')
            ->join('tblstorage s', 's.id = f.source_id AND f.source_type IN (2,3,4)', 'left');


            // ✅ Global search
            $search = $this->request->getPost('sSearch');
            if (!empty($search)) {
                $builder->groupStart();
                foreach ($aColumns as $col) {
                    $builder->orLike($col, $search);
                }
                $builder->groupEnd();
            }

            // ✅ Filters
            $filters = [
                'status'     => 'p.status',
                'updated_at' => 'updated_at',
                'people'     => 'user_id',
                'type'       => 'storage_type'
            ];

            foreach ($filters as $param => $column) {
                $value = $this->request->getGet($param);

                if ($value == "") {
                    continue;
                }

                if ($param === "updated_at") {
                    switch ((int) $value) {
                        case 1: // Today
                            $builder->groupStart();
                            $builder->orWhere("DATE(p.updated_at)", date("Y-m-d"));
                            $builder->orWhere("DATE(s.updated_at)", date("Y-m-d"));
                            $builder->groupEnd();
                            break;
                        case 2: // Yesterday
                            $builder->groupStart();
                            $builder->orWhere("DATE(p.updated_at)", date("Y-m-d", strtotime("-1 day")));
                            $builder->orWhere("DATE(s.updated_at)", date("Y-m-d", strtotime("-1 day")));
                            $builder->groupEnd();
                            break;
                        case 3: // Last 7 days
                            $builder->groupStart();
                            $builder->orWhere("(DATE(p.updated_at) BETWEEN '".date("Y-m-d", strtotime("-1 week"))."' AND '".date("Y-m-d")."')");
                            $builder->orWhere("(DATE(s.updated_at) BETWEEN '".date("Y-m-d", strtotime("-1 week"))."' AND '".date("Y-m-d")."')");
                            $builder->groupEnd();
                            break;
                    }
                } elseif ($param === "people") {
                    $builder->where("f.id IN (
                        SELECT tblinvitees.source_id
                        FROM tblinvitees
                        WHERE tblinvitees.source_type = 1
                          AND tblinvitees.user_id = " . (int) $value . "
                    )");
                } else {
                    $builder->where("LOWER($column)", strtolower($value));
                }
            }

            // ✅ Role-based restrictions
            $userId = (int) $this->session->get("dc_userid");
            $builder->where('f.user_id', $userId);

            // Get total count with same filters
            // Clone for total count
            $countBuilder = clone $builder;

            // If using soft deletes in model, CI4 automatically excludes deleted rows
            $total = $countBuilder->countAllResults(false); // false keeps builder intact

            // ✅ Execute query
            $data['favorites'] = $builder
                ->orderBy($orderBy, $sortDir)
                ->limit($limit, $offset)
                ->get()
                ->getResult();
            //echo $this->db->getLastQuery();

            $iFilteredTotal = sizeof($data['favorites']);
            $iTotal = $total;
            
            /*
             * Output
             */
            $output = array(
                "sEcho"                => intval($this->request->getPost('sEcho')),
                "iTotalRecords"        => $iTotal,
                "iTotalDisplayRecords" => $iTotal,
                "iDisplayStart"        => "iDisplayLength",
                "token"                => csrf_hash(),
                "aaData"               => array()
            );

            /*print_r($data["favorites"]);
            exit();*/
            
            $row = array();
            $output["gridFiles"] = view("favorites/grid-view", ["files" => $data["favorites"]]);

            $index = $this->request->getPost('iDisplayStart') + 1;
            $favoritesModel = new \App\Models\Favorites;

            foreach ($data['favorites'] as $value) {
                $updated_at = empty($value->updated_at) ? time_ago($value->created_at) : time_ago($value->updated_at);

                //$name = "<a href=\"#\" class=\"no-underline text-primary item-name\">".$value->title."</a>";
                if ($value->storage_type == 1) {
                    $file_size = "";
                    $favorite_type = 1;
                    $activities = "<span class=\"activity text-primary\"><i class=\"ds ds-message text-primary\"></i> New comments</span>";
                    $name = "<a href=\"".site_url("dpanel/document/detail/".$value->id)."\" class=\"no-underline text-primary item-name\"><span class='text-muted small item-type'>".$value->title."</span></a>";
                }
                else {
                    if ($value->source_type == 1) {
                        $name = "<a href=\"".site_url("dpanel/project/detail/".$value->case_id)."\" class=\"no-underline text-primary item-name\"><span class='text-muted small item-type'>".$value->title."</span></a>";
                        $activities = "";
                        $favorite_type = 1;
                        $file_size = "";
                    }
                    else {
                        $file_size = empty($value->storage_path) ? "" : getFileSize("uploads/files/".$value->storage_path);
                        $favorite_type = 1;
                        $activities = "";
                        if (strpos($value->file_mime_type, 'image/') === 0) {
                            $name = "<div class='gallery'><a href=\"".site_url("dpanel/files/view/".$value->storage_path)."\" class=\"no-underline text-primary item-name\"><span class='text-muted small item-type'>".$value->title."</span></a></div>";
                        }
                        else {
                            $name = "<a href=\"".site_url("dpanel/files/view/".$value->storage_path)."\" class=\"no-underline text-primary item-name\"><span class='text-muted small item-type'>".$value->title."</span></a>";
                        }
                    }
                }

                if (!empty($value->file_size)) {
                    $file_size = formatBytes($value->file_size);
                }

                $collaborators = view("files/file-collaborators", ["file_id" => $value->id, "showCount" => true, "value" => $value]);

                $favorite = "<a href=\"javascript:;\" class=\"star markfavorite starred\" data-id='".$value->source_id."' data-type='".$value->source_type."'><i class=\"ds ds-star icon-grey\"></i></a>";
                /* Check favorite is or not */

                $row = array( $favorite, $name, "<span class='text-muted small item-type'>".$value->file_type."</span>", "<span class='text-muted small item-size'>".$file_size."</span>", $activities, "<span class='text-muted small item-updated'>".$updated_at."</span>", "<span class='text-muted small item-added'>".date("M d, Y", strtotime($value->created_at))."</span>", $collaborators, "DT_RowId" => "tr$value->id");
                $index += 1;
                $output['aaData'][] = $row;
            }
            
            return $this->response->setJSON( $output );
        }
    }

    public function event($page = "", $id = 0) {
        if ($page == "get-history") {
            $user_info = (new \App\Models\Users)->find($id);
            if (empty($user_info)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! user you are trying to access doesn't exist");
            }

            $html = "";

            $storageModel = new \App\Models\Storage;
            $projectModel = new \App\Models\Project;

            $myInvitedLists = (new \App\Models\Invitee)->where("user_id", $id)->orderBy("created_at")->findAll();
            if (!empty($myInvitedLists)) {
                $html .= "<table class=\"table align-middle mb-0 table-striped ds-table table-borderless table-project-files w-100\"><tbody>";
                foreach ($myInvitedLists as $value) {
                    $view = ($value->role==1) ? "selected='selected'" : "";
                    $comment = ($value->role==2) ? "selected='selected'" : "";
                    if ($value->source_type == 1) {
                        $projectInfo = $projectModel->find($value->source_id);
                        if (!empty($projectInfo)) {
                            $html .= "<tr>
                                <td width='70%'>".$projectInfo->name."<br>#".$projectInfo->case_id."</td>
                                <td>
                                    <div class=\"invite-permissions\">
                                        <select class=\"form-control-sm\" name='invitee[".$value->id."][permission]'>
                                            <option value=\"1\" ".$view.">Can view</option>
                                            <option value=\"2\" ".$comment.">Can comment</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>";
                        }
                    }
                    else {
                        $documentInfo = $storageModel->find($value->source_id);
                        if (!empty($documentInfo)) {
                            $html .= "<tr>
                                <td width='70%'>".$documentInfo->title."</td>
                                <td>
                                    <div class=\"invite-permissions\">
                                        <select class=\"form-control-sm\" name='invitee[".$value->id."][permission]'>
                                            <option value=\"1\" ".$view.">Can view</option>
                                            <option value=\"2\" ".$comment.">Can edit</option>
                                        </select>
                                    </div>
                                </td>
                            </tr>";
                        }
                    }
                }
                $html .= "</tbody></table>";
            }

            return $this->response->setJSON(["response" => 1, "html" => $html]);
        }
        else if ($page == "update-roles") {
            $invitee = $this->request->getPost("invitee");
            if (empty($invitee)) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => "Sorry! nothing to update", "class" => "error", "token"  => csrf_hash()]);
            }

            $inviteeModel = new \App\Models\Invitee;
            foreach ($invitee as $iid => $value) {
                $result = $inviteeModel->update($iid, [
                    "role" => $value["permission"]
                ]);
            }

            return $this->response->setJSON(["response" => 1, "title" => "Successfull!", "text" => "User roles are updated successfully", "class" => "success", "token"  => csrf_hash()]);
        }
        else if ($page == "password") {
            $data['pagename'] = "Password";
            $data["version"] = $this->version;
            $data["session"] = $this->session;
            $data["segments"] = $this->request->uri->getSegments();
            $data["agent"] = $this->request->getUserAgent();
            $data["page_title"] = "Password : ".env("COMPANY_NAME");

            $data['morecss'] = array(
                site_url('public/assets/css/sign-up.css?v='.$this->version)
            );
            $data['morejs'] = array(
                site_url('public/assets/js/pages/update-password.js?v='.$this->version)
            );

            $html  = view("templates/header", $data);
            $html .= view("templates/left-nav", $data);
            $html .= view("templates/top-bar", $data);
            $html .= view("profile/update-password", $data);
            $html .= view("templates/footer", $data);
            return $html;
        }
        else if ($page == "change-password") {
            $rules = [
                'current' => [
                    'label' => 'Current password',
                    'rules' => 'required|trim'
                ],
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
            $user_information = $usersModel->find($this->session->get("dc_userid"));
            if (empty($user_information)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! request you are trying to access doesn't exist");
            }
            if (!password_verify($this->request->getPost("current"), $user_information->password)) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed", "text" => "Sorry! invalid current password entered", "class" => "error", "token"  => csrf_hash()]);
            }

            $password = password_hash($this->request->getPost("password"), PASSWORD_DEFAULT);

            /* Send mail to invitee */
            $result = $usersModel->update($this->session->get("dc_userid"), [
                "password" => $password
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
                $this->session->setFlashdata(["message" => "Your password has been changed successfully. Please use the new password in next login."]);
                return $this->response->setJSON(["response" => 1, "url" => site_url("dpanel/user/password")]);
            }
            else {
                return $this->response->setJSON(["response" => 0, "title" => "Failed", "text" => "Sorry! something went wrong", "class" => "error", "token"  => csrf_hash()]);
            }
            /* Send mail to invitee */
        }
        else if ($page == "profile") {
            $data['pagename'] = "Profile";
            $data["version"] = $this->version;
            $data["session"] = $this->session;
            $data["segments"] = $this->request->uri->getSegments();
            $data["agent"] = $this->request->getUserAgent();
            $data["page_title"] = "My Profile : ".env("COMPANY_NAME");

            $data["user"] = (new \App\Models\Users)->select("*,(SELECT tblcountries.name FROM tblcountries WHERE tblcountries.id=tblusers.country) as country_name")->find($this->session->get("dc_userid"));
            if (empty($data["user"])) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! user profile you are trying to access doesn't exist");
            }

            $data['morejs'] = array(
                site_url('public/assets/js/pages/update-profile.js?v='.$this->version)
            );

            $html  = view("templates/header", $data);
            $html .= view("templates/left-nav", $data);
            $html .= view("templates/top-bar", $data);
            $html .= view("profile/update", $data);
            $html .= view("templates/footer", $data);
            return $html;
        }
        else if ($page == "upload-avatar") {
            $usersModel = new \App\Models\Users;
            $userInfo = $usersModel->find($this->session->get("dc_userid"));
            if (empty($userInfo)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! document you are trying to access doesn't exist");
            }

            $oldAvatar = $userInfo->avatar;

            $file = $this->request->getFile('profile_image');
            if (!$file->isValid()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'title'  => 'Failed',
                    'text'   => 'Invalid file upload',
                    'class'  => 'error'
                ]);
            }

            // Validate file type
            //$validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            //if (!in_array($file->getMimeType(), $validTypes)) {
            $mimeType = $file->getMimeType();
            if (strpos($mimeType, 'image/') !== 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'title'  => 'Failed',
                    'text'   => 'Please upload a valid image file',
                    'class'  => 'error'
                ]);
            }

            if (!is_dir(WRITEPATH."uploads/avatars")) {
                mkdir(WRITEPATH."uploads/avatars", 0777, true);
            }

            // Move to upload folder
            $newName = date("YmdHis").$file->getRandomName();
            $file->move(WRITEPATH . 'uploads/avatars', $newName);

            $result = (new \App\Models\Users)->update($this->session->get("dc_userid"), [
                "avatar" => $newName
            ]);
            if ($result) {
                // Remove old avatar file
                if (!empty($oldAvatar)) {
                    $filePath = WRITEPATH . 'uploads/avatars/' . $oldAvatar;
                    // Check if file exists
                    if (is_file($filePath)) {
                        unlink($filePath);
                    }
                }
                session()->set("dc_avatar", $newName);
                return $this->response->setJSON([
                    'status'    => 'success',
                    'image_url' => site_url("dpanel/user/avatar/".$newName)
                ]);
            }
            else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'title'  => 'Failed',
                    'text'   => 'Sorry! something went wrong',
                    'class'  => 'error'
                ]);
            }
        }
        else if ($page == "update-profile") {
            $rules = [                
                'name' => [
                    'label' => 'First name',
                    'rules' => 'required|trim'
                ],
                'phone' => [
                    'label' => 'Phone',
                    'rules' => 'required|trim'
                ],
                "business_type" => [
                    "label"  => "Business type", 
                    "rules"  => "required|trim"
                ],
                "country" => [
                    "label"  => "Country", 
                    "rules"  => "required|trim|numeric"
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
                "name"          => $this->request->getPost("name"),
                "phone"         => $this->request->getPost("phone"),
                "company_name"  => $this->request->getPost("company_name"),
                "country"       => $this->request->getPost("country"),
                "state"         => $this->request->getPost("state"),
                "city"          => $this->request->getPost("city"),
                "postal_code"   => $this->request->getPost("postal_code"),
                "address"       => $this->request->getPost("address")
            ];
            $usersModel = new \App\Models\Users;
            $userInfo = $usersModel->find($this->session->get("dc_userid"));
            if (empty($userInfo)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! document you are trying to access doesn't exist");
            }
            
            $result = $usersModel->update($this->session->get("dc_userid"), $data);
            if ($result) {                
                $this->session->setFlashdata(["message" => "Profile information has been updated successfully."]);
                return $this->response->setJSON(["response" => 1, "url" => site_url("dpanel/user/profile")]);
            }
            else {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => ERROR_MSG, "class" => "error", "token"  => csrf_hash()]);
            }
        }
        else if ($page == "team-members") {
            $data['pagename'] = "Team Members";
            $data["version"] = $this->version;
            $data["session"] = $this->session;
            $data["segments"] = $this->request->uri->getSegments();
            $data["agent"] = $this->request->getUserAgent();
            $data["page_title"] = "My Team Members : ".env("COMPANY_NAME");

            $data["user"] = (new \App\Models\Users)->find($this->session->get("dc_userid"));
            if (empty($data["user"])) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! user profile you are trying to access doesn't exist");
            }

            //$data["team"] = (new \App\Models\Invitee)->where("invited_by", $this->session->get("dc_userid"))->where("user_id<>0 and user_id IS NOT NULL")->findAll();
            $data["team"] = (new \App\Models\Users)->where("id IN (SELECT tblinvitees.user_id FROM tblinvitees WHERE tblinvitees.invited_by=".$this->session->get("dc_userid").")")->findAll();

            $html  = view("templates/header", $data);
            $html .= view("templates/left-nav", $data);
            $html .= view("templates/top-bar", $data);
            $html .= view("profile/team-members", $data);
            $html .= view("templates/footer", $data);
            return $html;
        }
    }

    public function moreAvatars($source_type, $source_id) {
        $html = "";

        $storageModel = new \App\Models\Storage;
        $projectModel = new \App\Models\Project;
        $usersModel = new \App\Models\Users;

        $myInvitedLists = (new \App\Models\Invitee)->where(["source_id" => $source_id, "source_type" => $source_type])->findAll();
        if (!empty($myInvitedLists)) {
            $html .= "<table class=\"table align-middle mb-0 table-striped ds-table table-borderless table-project-files w-100\"><tbody>";
            foreach ($myInvitedLists as $value) {
                if ($value->user_id != 0) {
                    $userInfo = $usersModel->find($value->user_id);
                    $name = $userInfo->name;
                    $email = $userInfo->email;
                    $avatar = $userInfo->avatar;
                    $geturl = "/user/get-invited-lists/".$value->user_id;
                    $url = "/user/update-roles/".$value->user_id;
                    $avatarClass = "avatar_info";
                }
                else {
                    $name   = "";
                    $email  = $value->email;
                    $avatar = null;
                    $geturl = "";
                    $url = "";
                    $avatarClass = "";
                }
                $view_comment = ($value->role==1) ? "View" : "Comment";
                $html .= "<tr>
                    <td width='13%'><a href=\"javascript:void(0);\" class=\"avatar ".$avatarClass."\" data-bs-toggle=\"tooltip\" title=\"".$name."\" data-geturl=\"".$geturl."\" data-url=\"".$url."\" data-name=\"".$name."\"><img src='".user_avatar($avatar)."' alt='".$name."' class=\"rounded-circle w-100\"></a></td>
                    <td>
                        <small>".$name."<br>".$email."</small><br><small class='text-muted fs-12'>".$view_comment."</small>
                    </td>
                </tr>";
            }
            $html .= "</tbody></table>";
        }

        return $this->response->setJSON(["response" => 1, "html" => $html]);
    }
}
