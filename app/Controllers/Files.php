<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Files extends BaseController
{
    public $session;
    public $version;
    public $db;
    public function __construct() {
        $this->session = \Config\Services::session();
        $this->version = uniqid();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $data['pagename'] = "files";
        $data["version"] = $this->version;
        $data["session"] = $this->session;
        $data["segments"] = $this->request->uri->getSegments();
        $data["agent"] = $this->request->getUserAgent();
        $data["page_title"] = "All Files : ".env("COMPANY_NAME");
        $data['morejs'] = array(
            //base_url($this->assets_path."libs/morris-js/morris.min.js"),
            //base_url($this->assets_path.'libs/raphael/raphael.min.js'),
            //base_url($this->assets_path.'js/pages/ecommerce-dashboard.init.js'),
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
        $html .= view("files/index", $data);
        $html .= view("templates/footer", $data);
        return $html;
    }

    public function event($page = "", $id = 0) {
        if ($page == "get-json-data") {
            // Define columns for global search
            $aColumns = ["title", "content", "parent_id", "parent_type", "user_id", "status", "template", "bs_response", "bs_data", "chat_group_id", "storage_type", "storage_path"];

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
            $storageModel = new \App\Models\Storage();
            $builder = $storageModel->builder();

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
                'status'      => 'status',
                'updated_at'  => 'updated_at',
                'people'      => 'user_id',
                'type'        => 'storage_type',
                'parent'      => 'parent_id',
                'parent_type' => 'parent_type',
                'storage_type'=> 'storage_type'
            ];

            foreach ($filters as $param => $column) {
                $value = $this->request->getGet($param);

                if ($value == "") {
                    continue;
                }

                if ($param === "updated_at") {
                    switch ((int) $value) {
                        case 1: // Today
                            $builder->where("DATE($column)", date("Y-m-d"));
                            break;
                        case 2: // Yesterday
                            $builder->where("DATE($column)", date("Y-m-d", strtotime("-1 day")));
                            break;
                        case 3: // Last 7 days
                            $builder->where("DATE($column) >=", date("Y-m-d", strtotime("-1 week")));
                            $builder->where("DATE($column) <=", date("Y-m-d"));
                            break;
                    }
                } elseif ($param === "people") {
                    $builder->where("id IN (
                        SELECT tblinvitees.source_id
                        FROM tblinvitees
                        WHERE tblinvitees.source_type = 1
                          AND tblinvitees.user_id = " . (int) $value . "
                    )");
                } elseif ($param === "storage_type") {
                    //$builder->where("storage_type IN (2,3)");
                    $builder->where("storage_type IN (".$value.")");
                }else {
                    $builder->where("LOWER($column)", strtolower($value));
                }
            }

            $builder->where("deleted_at IS NULL");

            // ✅ Role-based restrictions
            $userId = (int) $this->session->get("dc_userid");

            if ($this->session->get("dc_roles") === "ROLE_ADMIN") {
                //$builder->where('user_id', $userId);
                $builder->groupStart()
                    ->where('user_id', $userId)
                    ->orWhere("tblstorage.id IN (
                        SELECT tblinvitees.source_id
                        FROM tblinvitees
                        WHERE tblinvitees.user_id = $userId
                          AND tblinvitees.source_type IN (1,2,3)
                    )")
                    ->orWhere("tblstorage.parent_id IN (
                        SELECT tblinvitees.source_id
                        FROM tblinvitees
                        WHERE tblinvitees.user_id = $userId
                          AND tblinvitees.source_type IN (1,2,3)
                    )")
                    ->groupEnd();
            } else {
                $builder->groupStart()
                    ->where('user_id', $userId)
                    ->orWhere("tblstorage.id IN (
                        SELECT tblinvitees.source_id
                        FROM tblinvitees
                        WHERE tblinvitees.user_id = $userId
                          AND tblinvitees.source_type IN (1,2,3)
                    )")
                    ->orWhere("tblstorage.parent_id IN (
                        SELECT tblinvitees.source_id
                        FROM tblinvitees
                        WHERE tblinvitees.user_id = $userId
                          AND tblinvitees.source_type IN (1,2,3)
                    )")
                    ->groupEnd();
            }

            /*echo $builder->getCompiledSelect();
            exit();*/

            // Get total count with same filters
            // Clone for total count
            $countBuilder = clone $builder;

            // If using soft deletes in model, CI4 automatically excludes deleted rows

            $countBuilder->where("deleted_at IS NULL");
            $total = $countBuilder->countAllResults(true); // false keeps builder intact

            // ✅ Execute query
            $data['files'] = $builder
                ->orderBy($orderBy, $sortDir)
                ->limit($limit, $offset)
                ->get()
                ->getResult();
            //echo $this->db->getLastQuery();

            $iFilteredTotal = sizeof($data['files']);
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
            $output["gridFiles"] = view("files/files-grid-view", ["files" => $data["files"]]);

            $index = $this->request->getPost('iDisplayStart') + 1;
            $favoritesModel = new \App\Models\Favorites;
            $collaborators = "";
            foreach ($data['files'] as $value) {
                $updated_at = time_ago($value->updated_at);
                $checkbox = "";
                if ($value->storage_type == 1) {
                    $file_type = "Document";
                    $file_size = "";
                    $favorite_type = 1;
                    $activities = "<span class=\"activity text-primary\"><i class=\"ds ds-message text-primary\"></i> New comments</span>";
                    $name = "<a href=\"".site_url("dpanel/document/detail/".$value->id)."\" class=\"no-underline text-primary item-name\"><span class='text-muted small item-type'>".$value->title."</span></a>";
                    $collaborators = view("files/file-collaborators", ["file_id" => $value->id, "showCount" => true, "storage_type" => $value->storage_type, "value" => $value]);
                }
                else {
                    $file_type = empty($value->storage_path) ? "" : getFileType($value->storage_path);
                    $file_size = empty($value->storage_path) ? "" : getFileSize("uploads/files/".$value->storage_path);
                    $favorite_type = 1;
                    $activities = $file_size;
                    if (strpos($value->file_mime_type, 'image/') === 0) {
                        $name = "<div class='gallery'><a href=\"".site_url("dpanel/files/view/".$value->storage_path)."\" class=\"no-underline text-primary item-name\"><span class='text-muted small item-type'>".$value->title."</span></a></div>";
                    }
                    else {
                        $name = "<a href=\"".site_url("dpanel/files/view/".$value->storage_path)."\" class=\"no-underline text-primary item-name\"><span class='text-muted small item-type'>".$value->title."</span></a>";
                    }
                    if ($value->user_id == $this->session->get("dc_userid")) {
                        $collaborators = "<a href='javascript:;' class='btn btn-danger row-small-button delete' data-url='".site_url("dpanel/file/".$value->id)."' data-method='DELETE' data-message='Are you sure? you want to delete selected file.'><i class='fa fa-trash'></i> Delete</a>";
                    }
                }
                if (!empty($value->file_size)) {
                    $file_size = formatBytes($value->file_size);
                    $activities = "<span class='text-muted small item-type w-100 text-center'>".$file_size."</span>";
                }                

                /* Check favorite is or not */
                $favoriteInfo = $favoritesModel->where([
                    "source_id"   => $value->id,
                    "source_type" => ($value->storage_type+1)
                ])->first();
                $starred = empty($favoriteInfo) ? "" : "starred";
                $favorite = "<a href=\"javascript:;\" class=\"star markfavorite ".$starred."\" data-id='".$value->id."' data-type='".($value->storage_type+1)."'><i class=\"ds ds-star icon-grey\"></i></a>";
                /* Check favorite is or not */

                $documentStatus = document_status($value->status);
                if (!empty($documentStatus)) {
                    $documentStatus = "<span class=\"badge ds-".$documentStatus["color"]." bg-".$documentStatus["color"]." fw-300\">".$documentStatus["name"]."</span>";
                }
                else {
                    $documentStatus = "<span class=\"badge ds-success bg-success fw-300\">Published</span>";
                }

                if ($value->user_id == $this->session->get("dc_userid")) {
                    $checkbox = "<label class=\"docushield-checkbox\">
                        <input type=\"checkbox\" name=\"proj".$value->id."\" id='proj".$value->id."' class='chkrow' value='".$value->id."' />
                        <span class=\"checkmark\"></span>
                    </label>";
                }

                $row = array( $checkbox, $favorite, $name, "<span class='text-muted small item-type'>".ucwords($file_type)."</span>", "<span class='text-muted small item-size'>".$file_size."</span>", $activities, "<span class='text-muted small item-updated'>".$updated_at."</span>", "<span class='text-muted small item-added'>".date("M d, Y", strtotime($value->created_at))."</span>", $documentStatus, $collaborators, "DT_RowId" => "tr$value->id");
                $index += 1;
                $output['aaData'][] = $row;
            }
            
            return $this->response->setJSON( $output );
        }
        else if ($page == "rename") {
            $rules = [
                "file_name" => [
                    "label"  => "Name", 
                    "rules"  => "required|trim"
                ]
            ];
            $inputs = $this->validate($rules, [], ['id' => $id]);
            if (!$inputs) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
            }
            $storageModel = new \App\Models\Storage;
            $result = $storageModel->update($id, [
                "title"       => $this->request->getPost("file_name"),
                "parent_id"   => empty($this->request->getPost("new_location")) ? null : $this->request->getPost("new_location"),
                "parent_type" => 1
            ]);
            if ($result) {
                return $this->response->setJSON(["response" => 1, "title" => "Successful!", "text" => "Files have been saved successfully!", "class" => "success", "token" => csrf_hash()]);
            }
            else {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => "Sorry! something went wrong", "class" => "error", "token" => csrf_hash()]);
            }
        }
        else if ($page == "delete") {
            $storageModel = new \App\Models\Storage;
            $fileInfo = $storageModel->find($id);
            if (empty($fileInfo)) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException("Sorry! file you are trying to access doesn't exist");
            }

            $result = $storageModel->delete($id);
            if ($result) {
                if (!empty($fileInfo->storage_path)) {
                    $filePath = WRITEPATH . 'uploads/' . $fileInfo->storage_path;
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                $message = ($fileInfo->storage_type==1) ? "Document has been deleted successfully." : "File has been deleted successfully";
                return $this->response->setJSON(["response" => 1, "title" => "Successful!", "text" => $message, "class" => "success", "token"  => csrf_hash()]);
            }
            else {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => "Sorry! something went wrong", "class" => "error", "token"  => csrf_hash()]);
            }
        }
        else if ($page == "bulk-delete") {
            $rules = [
                "rows" => [
                    "label"  => "Files", 
                    "rules"  => "required|trim"
                ]
            ];
            $inputs = $this->validate($rules, [], ['id' => $id]);
            if (!$inputs) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => strip_tags($this->validation->listErrors()), "class" => "error", "token"  => csrf_hash()]);
            }
            $rows = explode(",", $this->request->getPost("rows"));
            if (empty($rows)) {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => "Please select at least one file to delete", "class" => "error", "token"  => csrf_hash()]);
            }

            $result = (new \App\Models\Storage)->whereIn("id", $rows)->delete();
            if ($result) {
                return $this->response->setJSON(["response" => 1, "title" => "Successful!", "text" => "Selected file(s) are deleted successfully", "class" => "success", "token"  => csrf_hash()]);
            }
            else {
                return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => "Sorry! something went wrong", "class" => "error", "token"  => csrf_hash()]);
            }
        }
    }

    public function upload($value='') {
        $file = $this->request->getFile('file');

        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(WRITEPATH.'uploads', $newName);
            return $this->response->setJSON(["response" => 1, "title" => "Successful!", "text" => "Files have been saved successfully!", "class" => "success", "token" => csrf_hash()]);
        }
        return $this->response->setJSON(["response" => 0, "title" => "Failed!", "text" => $file->getErrorString(), "class" => "error", "token"  => csrf_hash()]);
    }

    public function process()
    {
        try {
            $file = $this->request->getFile('file');
            if (!$file) {
                return $this->response->setStatusCode(400)->setBody('No file received');
            }
            
            if (!$file->isValid()) {
                $errorString = $file->getErrorString();                
                // Check for specific file size errors
                if ($file->getError() === UPLOAD_ERR_INI_SIZE || $file->getError() === UPLOAD_ERR_FORM_SIZE) {
                    $maxSize = ini_get('upload_max_filesize');
                    $postMaxSize = ini_get('post_max_size');
                    $errorString = "File too large. Maximum allowed: {$maxSize} (upload_max_filesize: {$maxSize}, post_max_size: {$postMaxSize})";
                }                
                return $this->response->setStatusCode(400)->setBody('Invalid file: ' . $errorString);
            }
            
            if ($file->hasMoved()) {
                return $this->response->setStatusCode(400)->setBody('File has already been moved');
            }
            
            $newName = $file->getRandomName();
            $originalName = $file->getClientName();
            $mimeType = $file->getMimeType();
            $fileSize = $file->getSize();
            
            // Move file to uploads directory
            if (!$file->move(WRITEPATH . 'uploads', $newName)) {
                return $this->response->setStatusCode(500)->setBody('Failed to save file');
            }
            
            $fileType = getFileType(WRITEPATH . 'uploads/' . $newName);

            // Save to DB
            $storageModel = new \App\Models\Storage;
            $fileID = $storageModel->insert([
                "title"          => $originalName,
                'user_id'        => session()->get('dc_userid'),
                "status"         => 1,
                "storage_type"   => (strpos($mimeType, 'image/') === 0) ? 3 : 2,
                'file_mime_type' => $mimeType,
                'file_size'      => $fileSize,
                'storage_path'   => $newName                        
            ]);
            
            if (!$fileID) {
                return $this->response->setStatusCode(500)->setBody('Failed to save file record');
            }
            return $this->response->setStatusCode(200)->setJSON(['fileId' => $fileID]);
            
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setBody('Upload failed: ' . $e->getMessage());
        }
    }

    public function revert()
    {
        $fileId = $this->request->getBody();

        $storageModel = new \App\Models\Storage();
        $file = $storageModel->find($fileId);

        if ($file) {
            $filePath = WRITEPATH . 'uploads/' . $file->storage_path;

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $storageModel->delete($fileId);
            return $this->response->setStatusCode(200);
        }

        return $this->response->setStatusCode(404)->setBody('File not found');
    }

}
