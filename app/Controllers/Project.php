<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;

class Project extends BaseController
{
    public $session;
    public $version;
    public function __construct() {
        $this->session = \Config\Services::session();
        $this->version = uniqid();
    }

    public function event($page = "", $id = 0) {        
        $data['pagename'] = "projects";
        $data["version"] = $this->version;
        $data["session"] = $this->session;
        $data["segments"] = $this->request->uri->getSegments();
        $data["agent"] = $this->request->getUserAgent();
        if ($page == "") {
            $data["page_title"] = "Projects : ".env("COMPANY_NAME");
            $data['morejs'] = array(
                //base_url($this->assets_path."libs/morris-js/morris.min.js"),
                //base_url($this->assets_path.'libs/raphael/raphael.min.js'),
                //base_url($this->assets_path.'js/pages/ecommerce-dashboard.init.js'),
                site_url('public/assets/libs/datatables.net/js/jquery.dataTables.min.js'),
                site_url('public/assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js'),
                site_url('public/assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js'),
                site_url('public/assets/js/pages/projects.js?v='.$this->version)
            );
            $data['morecss'] = array(
                site_url('public/assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css'),
                site_url('public/assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'),
                site_url('public/assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css'),
                site_url('public/assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css')
            );

            $html  = view("templates/header", $data);
            $html .= view("templates/left-nav", $data);
            $html .= view("templates/top-bar", $data);
            $html .= view("projects/index", $data);
            $html .= view("templates/footer", $data);
            return $html;
        }
        else if ($page == "detail") {
            $data["project_info"] = (new \App\Models\Project)->where(["case_id" => $id])->first();
            if (empty($data["project_info"])) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! project you are trying to access doesn't exist");
            }
            $data["page_title"] = $data["project_info"]->name." : ".env("COMPANY_NAME");
            $data['morejs'] = array(
                site_url('public/assets/libs/datatables.net/js/jquery.dataTables.min.js'),
                site_url('public/assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js'),
                site_url('public/assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js'),
                site_url('public/assets/libs/magnific-popup/jquery.magnific-popup.min.js'),
                site_url('public/assets/js/pages/projects.js?v='.$this->version),
                site_url('public/assets/js/pages/files.js?v='.$this->version)
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
            $html .= view("projects/detail", $data);
            $html .= view("templates/footer", $data);
            return $html;
        }
        else if ($page == "submit" || $page == "archive") {
            $rules = [
                "name" => [
                    "label"  => "Name", 
                    "rules"  => "required|trim"
                ],
                "description" => [
                    "label"  => "Description", 
                    "rules"  => "required|trim"
                ]
            ];
            $inputs = $this->validate($rules, [], ['id' => $id]);
            if (!$inputs) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
            }

            $projectModel = new \App\Models\Project();
            $inviteeModel = new \App\Models\Invitee();
            $usersModel = new \App\Models\Users();

            $data = $this->request->getPost(['name', 'description']);

            if ($id == 0) {
                // Creating a new project
                $data = array_merge($data, [
                    'status'   => 1,
                    'user_id'  => session()->get('dc_userid'),
                    'case_id'  => $projectModel->generateCaseId(),
                ]);

                $projectID = $projectModel->insert($data); // returns new project ID
            } else {
                // Updating an existing project
                if ($page == "archive") {
                    $data["status"] = 2;
                }
                $data['id'] = $id;
                $projectModel->save($data);
                $projectID = $id;
            }
            
            if ($projectID) {
                if ($this->request->getPost('invitee')) {
                    $invitees = $this->request->getPost('invitee');
                    $inviteeIds = [];

                    $inviteeData = array_map(function ($invitee) use ($projectID) {
                        return [
                            'email'       => $invitee['email'],
                            'source_id'   => $projectID,
                            'token'       => date('YmdHis') . md5(uniqid(rand(), true)),
                            'user_id'     => $invitee['userid'],
                            'invited_by'  => $this->session->get("dc_userid"),
                            'role'        => $invitee['permission'],
                            'source_type' => 1,
                        ];
                    }, $invitees);

                    // New record case
                    if ($id == 0) {
                        if (!empty($inviteeData)) {
                            $inviteeModel->insertBatch($inviteeData);                            
                        }
                    } 
                    // Update existing record case
                    else {
                        foreach ($inviteeData as $invId => $invitee) {
                            if (is_int($invId)) {
                                // Update existing invitee
                                $inviteeModel->update($invId, [
                                    'role' => $invitee['role'],
                                ]);
                                $inviteeIds[] = $invId;
                            } else {
                                // Insert new invitee
                                $newId = $inviteeModel->insert([
                                    'email'       => $invitee['email'],
                                    'source_id'   => $invitee['source_id'],
                                    'token'       => $invitee['token'],
                                    'user_id'     => $invitee['user_id'],
                                    'invited_by'  => $this->session->get("dc_userid"),
                                    'role'        => $invitee['role'],
                                    'source_type' => $invitee["source_type"],
                                ]);
                                $inviteeIds[] = $newId;
                            }
                        }

                        // Delete removed invitees
                        if (!empty($inviteeIds)) {
                            $inviteeModel->where(["source_id" => $projectID, "source_type" => 1])->whereNotIn('id', $inviteeIds)->delete();
                        }
                    }

                    if ($page != "archive" && !empty($inviteeData)) {
                        foreach ($inviteeData as $invId => $invitee) {
                            if (!is_int($invId)) {
                                if ($invitee["user_id"] != 0) {
                                    $userInfo = $usersModel->find($invitee["user_id"]);
                                    $receiverName = empty($userInfo) ? "User" : $userInfo->name;
                                    $entity_link = empty($userInfo) ? site_url("sign-up?token=".$invitee["token"]) : site_url("dpanel/project/detail/".$invitee["source_id"]);
                                }
                                else {
                                    $receiverName = "User";
                                    $entity_link = site_url("sign-up?token=".$invitee["token"]);
                                }
                                /* Send mail to invitee */
                                $emailService = service('myEmail');
                                $mailStatus = $emailService->sendEmail(
                                    [$invitee["email"]], // To
                                    $this->session->get("dc_name")." Shared a Project with You", // Subject
                                    '',
                                    null,
                                    null,
                                    'email-templates/entity-share', // Mail body
                                    [ 'name' => $receiverName, 'sender_name' => $this->session->get("dc_name"), 'entity_type' => 'Project', 'entity_name' => $this->request->getPost("name"), 'entity_link' => $entity_link ], // Body dynamic data
                                    [],
                                    ["vishdhanu@gmail.com", "george@iotedgemarketing.com"],
                                    [],
                                    null,
                                    null
                                );
                                /* Send mail to invitee */
                            }
                        }
                    }
                }

                $recent_projects = view("projects/recent-projects-boxes");
                return $this->response->setJSON(["response" => 1, "title" => "Successful!", "text" => "Project informations have been saved successfully!", "class" => "success", "recent_projects" => $recent_projects, "token"  => csrf_hash()]);
            }
            else {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => "Sorry! something went wrong", "class" => "error", "token"  => csrf_hash()]);
            }
        }
        else if ($page == "delete") {
            $projectModel = new \App\Models\Project;
            $projectInfo = $projectModel->find($id);
            if (empty($projectInfo)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! project you are trying to access doesn't exist");
            }

            $result = $projectModel->delete($id);
            if ($result) {
                return $this->response->setJSON(["response" => 1, "title" => "Successful!", "text" => "Project has been deleted successfully.", "class" => "success", "token"  => csrf_hash()]);
            }
            else {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => "Sorry! something went wrong", "class" => "error", "token"  => csrf_hash()]);
            }
        }
        else if ($page == "bulk-delete") {
            $rules = [
                "rows" => [
                    "label"  => "Projects", 
                    "rules"  => "required|trim"
                ]
            ];
            $inputs = $this->validate($rules, [], ['id' => $id]);
            if (!$inputs) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
            }
            $rows = explode(",", $this->request->getPost("rows"));
            if (empty($rows)) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => "Please select at least one project to delete", "class" => "error", "token"  => csrf_hash()]);
            }

            $result = (new \App\Models\Project)->whereIn("id", $rows)->delete();
            if ($result) {
                return $this->response->setJSON(["response" => 1, "title" => "Successful!", "text" => "Selected project(s) are deleted successfully", "class" => "success", "token"  => csrf_hash()]);
            }
            else {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => "Sorry! something went wrong", "class" => "error", "token"  => csrf_hash()]);
            }
        }
        else if ($page == "share" || $page == "invite-members") {
            if (!$this->request->getPost('invitee')) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => "Please add at least one invitee to share the project", "class" => "error", "token"  => csrf_hash()]);
            }

            if ($page == "invite-members") {
                $rules = [
                    "project" => [
                        "label"  => "Project", 
                        "rules"  => "required|trim|numeric"
                    ]
                ];
                $inputs = $this->validate($rules, []);
                if (!$inputs) {
                    return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
                }
            }

            $inviteeModel = new \App\Models\Invitee;
            $userModel = new \App\Models\Users;
            $projectModel = new \App\Models\Project;

            $projectID = ($page=="invite-members") ? $this->request->getPost("project") : $id;
            $invitees = $this->request->getPost('invitee');
            $inviteeIds = [];

            /* Get project information */
            $project_info = $projectModel->find($projectID);
            if (empty($project_info)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! project you are trying to access doesn't exist");
            }

            foreach ($invitees as $invId => $invitee) {
                if (is_int($invId)) {
                    // Update existing invitee
                    $inviteeModel->update($invId, [
                        'role' => $invitee['permission'],
                    ]);
                    $inviteeIds[] = $invId;
                    
                } else {                    
                    // Insert new invitee
                    $inviteeToken = date('YmdHis') . md5(uniqid(rand(), true));
                    $newId = $inviteeModel->insert([
                        'email'       => $invitee['email'],
                        'source_id'   => $projectID,
                        'token'       => $inviteeToken,
                        'user_id'     => $invitee['userid'],
                        'invited_by'  => $this->session->get("dc_userid"),
                        'role'        => $invitee['permission'],
                        'source_type' => 1,
                    ]);
                    $inviteeIds[] = $newId;
                }

                if (!is_int($invId)) {
                    $userInfo = $userModel->find($invitee["userid"]);
                    $receiverName = empty($userInfo) ? "User" : $userInfo->name;
                    $entity_link = empty($userInfo) ? site_url("sign-up?token=".$inviteeToken) : site_url("dpanel/project/detail/".$projectID);

                    /* Send mail to invitee */
                    $emailService = service('myEmail');
                    $mailStatus = $emailService->sendEmail(
                        [$invitee["email"]], // To
                        $this->session->get("dc_name")." Shared a Project with You", // Subject
                        '',
                        null,
                        null,
                        'email-templates/entity-share', // Mail body
                        [ 'name' => $receiverName, 'sender_name' => $this->session->get("dc_name"), 'entity_type' => 'Project', 'entity_name' => $project_info->name, 'entity_link' => $entity_link ], // Body dynamic data
                        [],
                        ["vishdhanu@gmail.com", "george@iotedgemarketing.com"],
                        [],
                        null,
                        null
                    );
                    /* Send mail to invitee */
                }
            }

            // Delete removed invitees
            if (!empty($inviteeIds)) {
                $inviteeModel->where(["source_id" => $projectID, "source_type" => 1])->whereNotIn('id', $inviteeIds)->delete();
            }            
            
            $recent_projects = view("projects/recent-projects-boxes");
            return $this->response->setJSON(["response" => 1, "title" => "Successful!", "text" => "Project invitation link has been sent successfully to invitees!", "class" => "success", "recent_projects" => $recent_projects, "token"  => csrf_hash()]);
        }
        else if ($page == "get-json-data") {
            $aColumns = array('name','description');
            $sLimit = "";
            $sLimit = intval( $this->request->getPost('iDisplayLength') ).", ".intval( $this->request->getPost('iDisplayStart') );
            
            $sWhere = "";
            if ( $this->request->getPost('sSearch') && $this->request->getPost('sSearch') != "" ) {
                $sWhere = "(";
                for ( $i=0 ; $i<count($aColumns) ; $i++ ) {
                    $sWhere .= "`".$aColumns[$i]."` LIKE '%". $this->request->getPost('sSearch') ."%' OR ";
                }
                $sWhere = substr_replace( $sWhere, "", -3 );
                $sWhere .= ')';
            }

            $sortCol = isset($_POST['iSortCol_0']) ? $this->request->getPost('iSortCol_0') : 5;  // which column
            $sortDir = isset($_POST['sSortDir_0']) ? $this->request->getPost('sSortDir_0') : "desc";  // asc/desc

            // Map DataTables index → actual DB columns
            $columns = [
                0 => 'name',
                1 => 'case_id',
                2 => 'contents',
                3 => 'activity',
                4 => 'updated_at',
                5 => 'created_at',
                6 => 'teams'
            ];

            $orderBy = isset($columns[$sortCol]) ? $columns[$sortCol] : 'created_at';

            // filter the project by type            
            $filters = [
                'type'        => 'status',
                'other_status'=> 'status',
                'updated_at'  => 'updated_at',
                'people'      => 'user_id',
                'storage_type'=> 'storage_type'
            ];
            foreach ($filters as $param => $column) {
                if ($param == "updated_at") {
                    if (isset($_GET[$param])) {
                        $value = $this->request->getGet($param);
                        if (!empty($value)) {
                            if ($this->request->getGet($param) == 1) {
                                $condition = "DATE_FORMAT(LOWER($column), '%Y-%m-%d') = '" . date("Y-m-d") . "'";
                                $sWhere   .= ($sWhere !== "" ? " AND " : "") . $condition;
                            }
                            else if ($this->request->getGet($param) == 2) {
                                $condition = "DATE_FORMAT(LOWER($column), '%Y-%m-%d') = '" . date("Y-m-d", strtotime("-1 days")) . "'";
                                $sWhere   .= ($sWhere !== "" ? " AND " : "") . $condition;
                            }
                            else if ($this->request->getGet($param) == 3) {
                                $condition = "(DATE_FORMAT(LOWER($column), '%Y-%m-%d') BETWEEN '" . date("Y-m-d", strtotime("-1 weeks")) . "' AND '".date("Y-m-d")."')";
                                $sWhere   .= ($sWhere !== "" ? " AND " : "") . $condition;
                            }
                        }
                    }
                }
                else if ($param == "people") {
                    if (isset($_GET[$param])) {
                        $value = $this->request->getGet($param);
                        if (!empty($value)) {
                            $condition = "id IN (SELECT tblinvitees.source_id FROM tblinvitees WHERE tblinvitees.source_type=1 AND tblinvitees.user_id=".$value.")";
                            $sWhere   .= ($sWhere !== "" ? " AND " : "") . $condition;
                        }
                    }
                }
                else if ($param == "storage_type") {
                    if (isset($_GET[$param])) {
                        $value = $this->request->getGet($param);
                        if (!empty($value)) {
                            $condition = "id IN (SELECT tblstorage.parent_id FROM tblstorage WHERE tblstorage.storage_type=".$value." AND tblstorage.parent_type=1 AND tblstorage.deleted_at IS NULL)";
                            $sWhere   .= ($sWhere !== "" ? " AND " : "") . $condition;
                        }
                    }
                }
                else {
                    $value = ($this->request->getGet($param)=="archived") ? 2 : 1;
                    if (!empty($value)) {
                        $condition = "LOWER($column) = '" . strtolower($value) . "'";
                        $sWhere   .= ($sWhere !== "" ? " AND " : "") . $condition;
                    }
                }
            }

            if ($this->session->get("dc_roles") == "ROLE_ADMIN") {
                //$sWhere .= " AND user_id=".$this->session->get("dc_userid");
                $sWhere .= " AND (tblprojects.user_id = ".$this->session->get("dc_userid")." OR tblprojects.id IN (SELECT tblinvitees.source_id FROM tblinvitees WHERE tblinvitees.user_id = ".$this->session->get("dc_userid")." AND tblinvitees.source_type IN (1,2,3)))";
            }
            else {
                $sWhere .= " AND (tblprojects.user_id = ".$this->session->get("dc_userid")." OR tblprojects.id IN (SELECT tblinvitees.source_id FROM tblinvitees WHERE tblinvitees.user_id = ".$this->session->get("dc_userid")." AND tblinvitees.source_type IN (1,2,3)))";
            }

            $projectModel = new \App\Models\Project;
            $favoritesModel = new \App\Models\Favorites;

            $data['projects'] = $projectModel->where($sWhere)->orderBy($orderBy, $sortDir)->findAll($this->request->getPost('iDisplayLength'), $this->request->getPost('iDisplayStart'));
            //echo $projectModel->getLastQuery();
            
            $total = $projectModel->where($sWhere)->countAllResults();

            $iFilteredTotal = sizeof($data['projects']);
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
            
            $row = array();
            $output["gridProjects"] = view("projects/projects-grid-view", ["projects" => $data["projects"]]);
            $index = $this->request->getPost('iDisplayStart') + 1;
            foreach ($data['projects'] as $value) {
                $total_documents = $projectModel->getCount($value->id, 1);
                $total_files = $projectModel->getCount($value->id, 2);
                $total_images = $projectModel->getCount($value->id, 3);
                $updated_at = time_ago($value->updated_at);

                $name = "<span class='text-muted small'><a href=\"".site_url("dpanel/project/detail/".$value->case_id)."\" class=\"no-underline text-primary\">".$value->name."</a></span>";

                $contents = "<a href='".site_url("dpanel/files?parent=".$value->id."&parent_type=1&type=1")."'><span class=\"chip docs me-1\">Documents <span class=\"count\">".$total_documents."</span></span></a><a href='".site_url("dpanel/storage?parent=".$value->id."&parent_type=1&type=2")."'><span class=\"chip files me-1\">Files <span class=\"count\">".$total_files."</span></span></a><a href='".site_url("dpanel/storage?parent=".$value->id."&parent_type=1&type=3")."'><span class=\"chip images\">Images <span class=\"count\">".$total_images."</span></span></a>";

                $collaborators = view("projects/project-collaborators", ["project_id" => $value->id, "showCount" => true, "project" => $value]);

                /* Check favorite is or not */
                $favoriteInfo = $favoritesModel->where([
                    "source_id"   => $value->id,
                    "source_type" => 1
                ])->first();
                $starred = empty($favoriteInfo) ? "" : "starred";
                $favorite = "<a href=\"javascript:;\" class=\"star markfavorite ".$starred."\" data-id='".$value->id."' data-type='1'><i class=\"ds ds-star icon-grey\"></i></a>";
                /* Check favorite is or not */

                $name .= "<br><span class='text-muted small'>Case #".$value->case_id."</span>";

                $checkbox = "<label class=\"docushield-checkbox\">
                    <input type=\"checkbox\" name=\"proj".$value->id."\" id='proj".$value->id."' class='chkrow' value='".$value->id."' />
                    <span class=\"checkmark\"></span>
                </label>";

                $row = array( $checkbox, $favorite, $name, "<span class='text-muted small'>Case #".$value->case_id."</span>", $contents, "", "<span class='text-muted small'>".$updated_at."</span>", "<span class='text-muted small'>".date("M d, Y", strtotime($value->created_at))."</span>", $collaborators, $total_documents, $total_files, $total_images, $value->status, "DT_RowId" => "tr$value->id");
                $index += 1;
                $output['aaData'][] = $row;
            }
            
            return $this->response->setJSON( $output );
        }
        else if ($page == "get-json-detail") {
            $projectModel = new \App\Models\Project;
            $inviteeModel = new \App\Models\Invitee;
            $project_info = null;
            $inviteesHTML = "";
            $projects = "";
            if ($id != 0) { // this condition is for inviting all members with project select box
                /* Get project information */
                $project_info = $projectModel->find($id);

                /* Get invitees */
                $invitees = $inviteeModel->where(["source_id" => $id, "source_type" => 1])->findAll();
                $inviteesHTML = "";
                if (!empty($invitees)) {
                    $inviteesHTML = view("projects/invitees-list", ["invitees" => $invitees]);
                }
                /* Get invitees */
            }
            else {
                $projects = $projectModel->where("user_id", $this->session->get("dc_userid"))->orderBy("name")->findAll();
            }

            /* Get my users */
            $myUsersHtml = "";
            $userModel = new \App\Models\Users;
            $my_users = $userModel->where(["created_by" => session()->get("dc_userid"), "status" => 1])->where("email NOT IN (SELECT tblinvitees.email FROM tblinvitees WHERE tblinvitees.deleted_at IS NULL AND tblinvitees.source_id=".$id." AND tblinvitees.source_type=1)")->orderBy("name")->findAll();
            if (!empty($my_users)) {
                $myUsersHtml = view("projects/my-users-list", ["users" => $my_users]);
            }

            return $this->response->setJSON(["response" => 1, "info" => $project_info, "users" => $myUsersHtml, "invitees" => $inviteesHTML, "projects" => $projects]);
        }
        else if ($page == "populate") {
            $projects = (new \App\Models\Project)->where("user_id", $this->session->get("dc_userid"))->orderBy("name")->findAll();
            $project_arr = [];
            $foldersModel = new \App\Models\Folders;
            if (!empty($projects)) {
                foreach ($projects as $project) {
                    /* Get project folers */
                    $folders = $foldersModel->where(["parent_id" => $project->id])->orderBy("name")->findAll();
                    $project_arr[] = [
                        "id" => $project->id,
                        "name" => $project->name,
                        "folders" => empty($folders) ? null : $folders
                    ];
                    /* Get project folers */
                }
            }            
            return $this->response->setJSON(["success" => true, "projects" => $project_arr]);
        }
        else if ($page == "attach-file") {
            $id = $this->request->getPost("project_id");
            if ($id < 0) {
                return $this->response->setJSON(["response" => 1, "token"  => csrf_hash()]);
            }

            if (str_contains($id, 'folder-')) {
                if (preg_match('/^folder-(\d+)$/', $id, $matches)) {
                    $id = $matches[1];
                }
                $folder_info = (new \App\Models\Folders)->find($id);
                if (empty($folder_info)) {
                    throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! folder you are trying to access doesn't exist");
                }
                $parent_type = 2;
            }
            else {
                if (preg_match('/^project-(\d+)$/', $id, $matches)) {
                    $id = $matches[1];
                }
                $project_info = (new \App\Models\Project)->find($id);
                if (empty($project_info)) {
                    throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! project you are trying to access doesn't exist");
                }
                $parent_type = 1;
            }

            $fileId = $this->request->getPost("file_id");
            $storageModel = new \App\Models\Storage;
            $fileInfo = $storageModel->find($fileId);
            if (empty($fileInfo)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! file you are trying to access doesn't exist");
            }
            $result = $storageModel->save([
                "id"          => $fileId,
                "parent_type" => $parent_type,
                "parent_id"   => $id
            ]);
            if (!$result) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => "Sorry! something went wrong", "class" => "error", "token"  => csrf_hash()]);
            }
            else {
                return $this->response->setJSON(["response" => 1, "token"  => csrf_hash()]);
            }
        }
        else if ($page == "create-folder" || $page == "rename-folder") {
            $rules = [
                "name" => [
                    "label"  => "Name", 
                    "rules"  => "required|trim"
                ]
            ];
            $inputs = $this->validate($rules, [], ['id' => $id]);
            if (!$inputs) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
            }

            $foldersModel = new \App\Models\Folders;
            if ($page == "create-folder") {
                $result = $foldersModel->insert([
                    "name"           => $this->request->getPost("name"),
                    "user_id"        => $this->session->get("dc_userid"),
                    "parent_id"      => $id,
                    "last_open_date" => date("Y-m-d H:i:s")
                ]);
                $message = "Folder has been created successfully.";
            }
            else {
                $folderInfo = $foldersModel->find($id);
                $result = $foldersModel->update($id, [
                    "name" => $this->request->getPost("name")
                ]);
                $id = $folderInfo->parent_id;
                $message = "Folder has been updated successfully.";
            }
            if ($result) {
                $html = view("folders/folders-list", ["project_id" => $id]);
                return $this->response->setJSON(["response" => 1, "title" => "Success!", "text" => $message, "class" => "success", "token"  => csrf_hash(), "list" => $html]);
            }
            else {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => "Sorry! something went wrong", "class" => "error", "token"  => csrf_hash()]);
            }
        }
        else if ($page == "delete-folder") {
            $foldersModel = new \App\Models\Folders;
            $folder_info = $foldersModel->where("user_id", $this->session->get("dc_userid"))->find($id);
            if (empty($folder_info)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! folder you are trying to access doesn't exist");
            }
            $folderInfo = $foldersModel->find($id);
            $result = $foldersModel->delete($id);
            if ($result) {
                $html = view("folders/folders-list", ["project_id" => $folderInfo->parent_id]);
                return $this->response->setJSON(["response" => 1, "title" => "Successfull!", "text" => "Selected folder has been deleted successfully!", "class" => "success", "token"  => csrf_hash(), "list" => $html]);
            }
            else {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
            }
        }
        else if ($page == "folder-detail") {
            $data["folder_info"] = (new \App\Models\Folders)->where(["id" => $id, "user_id" => $this->session->get("dc_userid")])->first();
            if (empty($data["folder_info"])) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! folder you are trying to access doesn't exist");
            }

            $data["project_info"] = (new \App\Models\Project)->where(["id" => $data["folder_info"]->parent_id])->first();

            $data["page_title"] = $data["folder_info"]->name." : ".env("COMPANY_NAME");
            $data['morejs'] = array(
                site_url('public/assets/libs/datatables.net/js/jquery.dataTables.min.js'),
                site_url('public/assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js'),
                site_url('public/assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js'),
                site_url('public/assets/libs/magnific-popup/jquery.magnific-popup.min.js'),
                //site_url('public/assets/js/pages/projects.js?v='.$this->version),
                site_url('public/assets/js/pages/files.js?v='.$this->version)
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
            $html .= view("folders/index", $data);
            $html .= view("templates/footer", $data);
            return $html;
        }
    }
}
