<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Documents extends BaseController
{
    public $session;
    public $version;
    public function __construct() {
        $this->session = \Config\Services::session();
        $this->version = uniqid();
    }

    public function index()
    {
        //
    }

    public function event($page = "", $id = 0) {
        $data['pagename'] = "projects";
        $data["version"] = $this->version;
        $data["session"] = $this->session;
        $data["segments"] = $this->request->uri->getSegments();
        $data["agent"] = $this->request->getUserAgent();
        if ($page == "add-new" || $page == "detail" || $page == "version-history") {
            $data["page_title"] = ucfirst(strtolower(str_replace("-", " ", $page)))." : ".env("COMPANY_NAME");

            $jsArray = [];
            $cssArray = [];

            array_push($jsArray, site_url("public/assets/libs/ckeditor/ckeditor.js"));
            if ($page == "add-new") {
                if ($this->session->get("dc_roles") != "ROLE_ADMIN") {
                    return service('response')
                        ->setStatusCode(403);
                }
                array_push($jsArray, site_url("public/assets/js/pages/ckeditor-2.js?v=".$this->version));
                array_push($jsArray, site_url("public/assets/js/pages/ckeditor-placeholder.js?v=".$this->version));
            }

            /*$data['morejs'] = array(
                site_url("public/assets/libs/ckeditor/ckeditor.js"),
                site_url("public/assets/js/pages/ckeditor-2.js?v=".$this->version),
                site_url("public/assets/js/pages/ckeditor-placeholder.js?v=".$this->version),
                site_url("public/assets/js/pages/ckeditor-complete.js?v=".$this->version),
                site_url('public/assets/js/pages/review-document.js?v='.$this->version),
                site_url('public/assets/js/pages/complete-document.js?v='.$this->version),
                site_url('public/assets/js/pages/revisions.js?v='.$this->version),
                site_url('public/assets/js/pages/documents.js?v='.$this->version)
            );*/

            if ($page == "detail" || $page == "version-history") {
                $userId = $this->session->get("dc_userid");
                $builder = (new \App\Models\Storage)->builder();
                $builder->select("*,(SELECT tblprojects.name FROM tblprojects WHERE tblprojects.id=tblstorage.parent_id AND tblstorage.parent_type=1) as project_name");
                if ($this->session->get("dc_roles") === "ROLE_ADMIN") {
                    $builder->where('user_id', $userId);
                } else {

                    /* Get invited items */
                    $invitedLists = (new \App\Models\Invitee)->select("GROUP_CONCAT(source_id) as projectids")->where(["source_type" => 1, "user_id" => $userId])->first();
                    /* Get invited items */

                    if (!empty($invitedLists)) {
                        $builder->groupStart()
                            ->where('user_id', $userId)
                            ->orWhere("tblstorage.id IN (
                                SELECT tblinvitees.source_id
                                FROM tblinvitees
                                WHERE tblinvitees.user_id = $userId
                                  AND tblinvitees.source_type = 2
                            )")
                            ->orWhere("(tblstorage.parent_id IN (".$invitedLists->projectids.") AND tblstorage.parent_type=1)")
                            ->groupEnd();
                    }
                    else {
                        $builder->groupStart()
                            ->where('user_id', $userId)
                            ->orWhere("tblstorage.id IN (
                                SELECT tblinvitees.source_id
                                FROM tblinvitees
                                WHERE tblinvitees.user_id = $userId
                                  AND tblinvitees.source_type = 2
                            )")
                            ->groupEnd();
                    }
                }
                $data['document'] = $builder->where("id", $id)->get()->getRow();
                if (empty($data["document"])) {
                    throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! document you are trying to access doesn't exist");
                }

                /*if ($data["document"]->status == 4 && $data["document"]->user_id != $this->session->get("dc_userid")) {
                    $page = "view-only";
                }*/

                if ($data["document"]->user_id != $this->session->get("dc_userid")) {
                    $data["invitedInfo"] = (new \App\Models\Invitee)->where(["source_type" => 2, "source_id" => $id, "user_id" => $userId])->first();
                    if (!empty($data["invitedInfo"]) && $data["invitedInfo"]->role == 1) {
                        $page = "view-only";
                    }
                }

                if ($data['document']->status == 7) {
                    if ($page != "version-history") {
                        array_push($jsArray, site_url("public/assets/js/pages/ckeditor-complete.js?v=".$this->version));
                    }
                    array_push($jsArray, site_url("public/assets/js/pages/complete-document.js?v=".$this->version));
                }
                else {
                    if ($page != "version-history") {
                        array_push($jsArray, site_url("public/assets/js/pages/ckeditor-2.js?v=".$this->version));
                        //array_push($jsArray, site_url("public/assets/js/pages/ckeditor-placeholder.js?v=".$this->version));
                        array_push($jsArray, site_url('public/assets/js/pages/review-document.js?v='.$this->version));
                        //array_push($jsArray, site_url('public/assets/js/pages/revisions.js?v='.$this->version));                    
                    }
                }
                if ($page != "version-history") {
                    array_push($jsArray, site_url('public/assets/js/pages/autosave.js?v='.$this->version));
                }

                if ($page == "version-history") {
                    array_push($cssArray, "https://cdn.jsdelivr.net/npm/diff2html/bundles/css/diff2html.min.css");
                    array_push($jsArray, "https://cdn.jsdelivr.net/npm/diff@5/dist/diff.min.js");
                    array_push($jsArray, "https://cdn.jsdelivr.net/npm/diff2html/bundles/js/diff2html-ui.min.js");
                    array_push($jsArray, site_url('public/assets/js/pages/version-history.js?v='.$this->version));
                }

                if ($page == "detail") {
                    $documentHistory =(new \App\Models\Revisions)->where("document_id", $id)->first();
                    if (empty($documentHistory)) {
                        (new \App\Models\Revisions)->insert([
                            "document_id" => $id,
                            "user_id"     => $data["document"]->user_id,
                            "content"     => $data["document"]->content,
                            "version"     => 1
                        ]);
                    }
                }
            }

            array_push($jsArray, site_url('public/assets/js/pages/documents.js?v='.$this->version));

            $data["morejs"] = $jsArray;
            if (!empty($cssArray)) {
                $data["morecss"] = $cssArray;
            }

            $html  = view("templates/header", $data);
            $html .= view("templates/left-nav", $data);
            $html .= view("templates/top-bar", $data);
            $html .= view("documents/".$page, $data);
            $html .= view("templates/footer", $data);
            return $html;
        }
        else if ($page == "submit") {
            $rules = [
                "name" => [
                    "label"  => "Name", 
                    "rules"  => "required|trim"
                ],
                "project" => [
                    "label"  => "Project", 
                    "rules"  => "required|trim"
                ],
                "content" => [
                    "label"  => "Content", 
                    "rules"  => "required|trim"
                ]
            ];
            $inputs = $this->validate($rules, [], ['id' => $id]);
            if (!$inputs) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
            }

            $documentParentId = $this->request->getPost("project");
            $parentType = 1;
            if (str_contains($documentParentId, 'folder-')) {
                if (preg_match('/^folder-(\d+)$/', $documentParentId, $matches)) {
                    $documentParentId = $matches[1];
                    $parentType = 2;
                }
            }

            $storageModel = new \App\Models\Storage;
            $data = [
                "title"       => $this->request->getPost("name"),
                "content"     => $this->request->getPost("content"),
                "parent_id"   => $documentParentId
            ];
            if ($id == 0) {
                $data["user_id"]      = $this->session->get("dc_userid");
                $data["status"]       = 4; // Draft status
                $data["storage_type"] = 1;
                $data["parent_type"] = $parentType;
                $result = $storageModel->insert($data);
                $id = $result;
            }
            else {
                $data["id"] = $id;
                $result = $storageModel->save($data);
            }
            if ($result) {

                /* save document revisions */
                $revisionModel = new \App\Models\Revisions;
                $lastVersion = $revisionModel->where("document_id", $id)->orderBy("version", "DESC")->first();
                $newVersion = $lastVersion ? ((int)$lastVersion->version + 1) : 1;
                $revisionModel->insert([
                    'document_id' => $id,
                    'content'     => $data["content"],
                    'version'     => $newVersion,
                    "user_id"     => session()->get("dc_userid")
                ]);
                /* save document revisions */

                $this->session->setFlashdata([
                    'message' => "Document informations have been saved successfully.",
                    'alert'   => 'success' // optional for styling
                ]);
                return $this->response->setJSON(["response" => 1, "url" => site_url("dpanel/document/detail/".$id)]);
            }
            else {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => "Sorry! something went wrong", "class" => "error", "token"  => csrf_hash()]);
            }
        }
        else if ($page == "send-for-review" || $page == "share") {
            if ($page == "send-for-review" && $id == 0) {
                $rules = [
                    "name" => [
                        "label"  => "Name", 
                        "rules"  => "required|trim"
                    ],
                    "project" => [
                        "label"  => "Project", 
                        "rules"  => "required|trim"
                    ],
                    "content" => [
                        "label"  => "Content", 
                        "rules"  => "required|trim"
                    ]
                ];
                $inputs = $this->validate($rules, [], ['id' => $id]);
                if (!$inputs) {
                    return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
                }
            }

            if (!$this->request->getPost('invitee')) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => "Please add at least one invitee to send document for review", "class" => "error", "token"  => csrf_hash()]);
            }

            $inviteeModel = new \App\Models\Invitee;
            $userModel = new \App\Models\Users;
            $storageModel = new \App\Models\Storage;

            $invitees = $this->request->getPost('invitee');
            $inviteeIds = [];
            $documentID = $id;

            /* Get document information */
            if ($id != 0) {
                $document_info = $storageModel->find($id);
                if (empty($document_info)) {
                    throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! document you are trying to access doesn't exist");
                }
                $documentTitle = $document_info->title;
            }
            else {
                $documentParentId = $this->request->getPost("project");
                $parentType = 1;
                if (str_contains($documentParentId, 'folder-')) {
                    if (preg_match('/^folder-(\d+)$/', $documentParentId, $matches)) {
                        $documentParentId = $matches[1];
                        $parentType = 2;
                    }
                }

                $data = [
                    "title"       => $this->request->getPost("name"),
                    "content"     => $this->request->getPost("content"),
                    "parent_id"   => $documentParentId,
                    "parent_type" => $parentType,
                    "user_id"     => $this->session->get("dc_userid"),
                    "status"      => 5, // send for review
                    "storage_type"=> 1
                ];
                $documentID = $storageModel->insert($data);
                $documentTitle = $data['title'];
            }
            /* Get document information */

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
                        'source_id'   => $documentID,
                        'token'       => date('YmdHis') . md5(uniqid(rand(), true)),
                        'user_id'     => $invitee['userid'],
                        'invited_by'  => $this->session->get("dc_userid"),
                        'role'        => $invitee['permission'],
                        'source_type' => 2,
                    ]);
                    $inviteeIds[] = $newId;
                }

                if (!is_int($invId)) {
                    $userInfo = $userModel->find($invitee["userid"]);
                    $receiverName = empty($userInfo) ? "User" : $userInfo->name;
                    $entity_link = empty($userInfo) ? site_url("sign-up?token=".$inviteeToken) : site_url("dpanel/document/detail/".$documentID);

                    /* Send mail to invitee */
                    $emailService = service('myEmail');
                    $mailStatus = $emailService->sendEmail(
                        [$invitee["email"]], // To
                        $this->session->get("dc_name")." Shared a Document for Review", // Subject
                        '',
                        null,
                        null,
                        'email-templates/entity-share', // Mail body
                        [ 'name' => $receiverName, 'sender_name' => $this->session->get("dc_name"), 'entity_type' => 'Document', 'entity_name' => $documentTitle, 'entity_link' => $entity_link ], // Body dynamic data
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
                $inviteeModel->where(["source_id" => $documentID, "source_type" => 2])->whereNotIn('id', $inviteeIds)->delete();
            }
            if ($page == "send-for-review") {
                $storageModel->update($documentID, ["status" => 5]); // Review status
            }
            $this->session->setFlashdata([
                'message' => "Document has been sent successfully to review",
                'alert'   => 'success' // optional for styling
            ]);
            return $this->response->setJSON(["response" => 1, "url" => site_url("dpanel/document/detail/".$documentID)]);
        }
        else if ($page == "get-json-detail") {
            $storageModel = new \App\Models\Storage;
            $inviteeModel = new \App\Models\Invitee;
            $project_info = null;
            $inviteesHTML = "";
            $documents = "";
            if ($id != 0) { // this condition is for inviting all members with project select box
                /* Get project information */
                $document_info = $storageModel->find($id);

                /* Get invitees */
                $invitees = $inviteeModel->where(["source_id" => $id, "source_type" => 2])->findAll();
                $inviteesHTML = "";
                if (!empty($invitees)) {
                    $inviteesHTML = view("documents/invitees-list", ["invitees" => $invitees]);
                }
                /* Get invitees */
            }
            else {
                $documents = $storageModel->where("user_id", $this->session->get("dc_userid"))->where("parent_type", 1)->orderBy("name")->findAll();
            }

            /* Get my users */
            $myUsersHtml = "";
            $userModel = new \App\Models\Users;
            $my_users = $userModel->where(["created_by" => session()->get("dc_userid"), "status" => 1])->where("email NOT IN (SELECT tblinvitees.email FROM tblinvitees WHERE tblinvitees.deleted_at IS NULL AND tblinvitees.source_id=".$id." AND tblinvitees.source_type=2)")->orderBy("name")->findAll();
            if (!empty($my_users)) {
                $myUsersHtml = view("projects/my-users-list", ["users" => $my_users]);
            }

            return $this->response->setJSON(["response" => 1, "info" => $document_info, "users" => $myUsersHtml, "invitees" => $inviteesHTML, "documents" => $documents]);
        }
        else if ($page == "return-to-draft") {
            $storageModel = new \App\Models\Storage;
            $document_info = $storageModel->find($id);
            if (empty($document_info)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! document you are trying to access doesn't exist");
            }
            $result = $storageModel->update($id, [
                "status" => 4
            ]);
            $this->session->setFlashdata([
                'message' => "Document has been moved to draft status successfully",
                'alert'   => 'success' // optional for styling
            ]);
            return $this->response->setJSON(["response" => 1, "url" => site_url("dpanel/document/detail/".$id)]);
        }
        else if ($page == "accept-and-sign") {
            $storageModel = new \App\Models\Storage;
            $document_info = $storageModel->find($id);
            if (empty($document_info)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! document you are trying to access doesn't exist");
            }
            $result = $storageModel->update($id, [
                "status" => 7 // Completed
            ]);
            $this->session->setFlashdata([
                'message' => "Document has been moved to completed status successfully",
                'alert'   => 'success' // optional for styling
            ]);
            return $this->response->setJSON(["response" => 1, "url" => site_url("dpanel/document/detail/".$id)]);
        }
        else if ($page == "duplicate") {
            $storageModel = new \App\Models\Storage;
            $document_info = $storageModel->asArray()->find($id);
            if (empty($document_info)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! document you are trying to access doesn't exist");
            }

            unset($document_info["id"]);
            $document_info["status"] = 4;
            $document_info["user_id"] = $this->session->get("dc_userid");
            $result = $storageModel->insert($document_info);
            if ($result) {
                $this->session->setFlashdata([
                    'message' => "Copy of document ".$document_info["title"]." has been created successfully",
                    'alert'   => 'success' // optional for styling
                ]);
                return $this->response->setJSON(["response" => 1, "url" => site_url("dpanel/document/detail/".$result)]);
            }
            else {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
            }
        }
        else if ($page == "archive") {
            $storageModel = new \App\Models\Storage;
            $document_info = $storageModel->where("user_id", $this->session->get("dc_userid"))->find($id);
            if (empty($document_info)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! document you are trying to access doesn't exist");
            }
            $result = $storageModel->update($id, [
                "status" => 6 // Archive status
            ]);
            if ($result) {
                return $this->response->setJSON(["response" => 1, "title" => "Successfull!", "text" => "Document has been archived successfully!", "class" => "success", "token"  => csrf_hash()]);
            }
            else {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
            }
        }
    }

    public function autosave()
    {
        $docId = $this->request->getPost('document_id');
        $content = $this->request->getPost('content');

        $storageModel = new \App\Models\Storage;
        $revisionModel = new \App\Models\Revisions;

        $document = $storageModel->find($docId);
        if (!$document) {
            return $this->response->setJSON(["success" => false, "message" => "Document not found"]);
        }

        // Only save if content changed
        if (trim($document->content) !== trim($content)) {
            $storageModel->update($docId, [
                'content'    => $content,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $lastVersion = $revisionModel
                ->where('document_id', $docId)
                ->orderBy('version', 'DESC')
                ->first();

            $newVersion = $lastVersion ? ((int)$lastVersion->version + 1) : 1;

            $revisionModel->insert([
                'document_id' => $docId,
                'content'     => $content,
                'version'     => $newVersion,
                "user_id"     => session()->get("dc_userid")
            ]);

            return $this->response->setJSON(['success' => true, 'version' => $newVersion]);
        }

        return $this->response->setJSON(['success' => false, "message" => "No change"]);
    }

    /**
     * Fetch content of a specific revision for AJAX diff view.
     * URL: /document/getVersionContent/{revisionId}
     */
    public function getVersionContent($revisionId)
    {
        $revisionModel = new \App\Models\Revisions;
        $revision = $revisionModel->find($revisionId);

        if (!$revision) {
            return $this->response->setStatusCode(404)->setBody('Revision not found');
        }

        // Return plain content (HTML or text)
        return $this->response->setHeader('Content-Type', 'text/plain')
                              ->setBody($revision->content);
    }
}
