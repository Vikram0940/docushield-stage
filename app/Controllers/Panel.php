<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Panel extends BaseController
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
        $data['pagename'] = "dashboard";
        $data["version"] = $this->version;
        $data["session"] = $this->session;
        $data["segments"] = $this->request->uri->getSegments();
        $data["agent"] = $this->request->getUserAgent();
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
        $html .= view("index", $data);
        $html .= view("templates/footer", $data);
        return $html;
    }

    public function search($page = "") {
        if ($page == "autocomplete") {
            $query = $this->request->getPost('query');

            // Suggestions (from history/keywords)
            $suggestions = $this->db->table('tblsearch_history')
                ->select('keyword')
                ->where("user_id", $this->session->get("dc_userid"))
                ->like('keyword', $query, 'after')
                ->orderBy('created_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();

            // Results (projects + files)
            $results = $this->db->query("
                (SELECT name AS name, 'project' as type, created_at as time 
                 FROM tblprojects 
                 WHERE user_id = ? AND name LIKE ?)
                UNION
                (SELECT title AS name, 'file' as type, created_at as time 
                 FROM tblstorage 
                 WHERE user_id = ? AND title LIKE ?)
                ORDER BY time DESC
                LIMIT 5
            ", [$this->session->get("dc_userid"), "%$query%", $this->session->get("dc_userid"), "%$query%"])->getResultArray();

            return $this->response->setJSON([
                "suggestions" => array_column($suggestions, 'keyword'),
                "results"     => $results
            ]);
        }
        else if ($page == "") {
            $data['pagename'] = "files";
            $data["version"] = $this->version;
            $data["session"] = $this->session;
            $data["segments"] = $this->request->uri->getSegments();
            $data["agent"] = $this->request->getUserAgent();
            $data["page_title"] = "All Files : ".env("COMPANY_NAME");
            $data['morejs'] = array(
                site_url('public/assets/libs/datatables.net/js/jquery.dataTables.min.js'),
                site_url('public/assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js'),
                site_url('public/assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js'),
                site_url('public/assets/js/pages/search-result.js?v='.$this->version)
            );
            $data['morecss'] = array(
                site_url('public/assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css'),
                site_url('public/assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'),
                site_url('public/assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css'),
                site_url('public/assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css')
            );
            if (isset($_GET['q']) && !empty($_GET['q'])) {
                $keywordInfo = (new \App\Models\SearchHistory)->where([
                    "keyword" => $this->request->getGet("q"),
                    "user_id" => $this->session->get("dc_userid")
                ])->first();
                if (empty($keywordInfo)) {
                    (new \App\Models\SearchHistory)->insert([
                        "keyword" => $this->request->getGet("q"),
                        "user_id" => $this->session->get("dc_userid")
                    ]);
                }
            }
            $html  = view("templates/header", $data);
            $html .= view("templates/left-nav", $data);
            $html .= view("templates/top-bar", $data);
            $html .= view("search-result", $data);
            $html .= view("templates/footer", $data);
            return $html;
        }
        else if ($page == "get-json-data") {
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
            // Escape search for LIKE
            $search = $this->request->getGet("q");
            $search = $this->db->escapeLikeString($search);

            // Builder for tblprojects
            $projectBuilder = $this->db->table('tblprojects')
                ->select("id, name as title, description, created_at, status, 'project' AS type, '0' as file_size, '0' as storage_type, '' as storage_path, updated_at, 'Project' as file_mime_type")
                ->where("user_id", $this->session->get("dc_userid"))
                ->where("deleted_at IS NULL")
                ->groupStart()
                    ->like('name', $search)
                    ->orLike('description', $search)
                ->groupEnd();

            // Builder for tblstorage
            $fileBuilder = $this->db->table('tblstorage')
                ->select("id,title,content AS description, created_at, status, 'file' AS type, file_size,storage_type,storage_path, updated_at, file_mime_type")
                ->where("user_id", $this->session->get("dc_userid"))
                ->where("deleted_at IS NULL")
                ->groupStart()
                    ->like('title', $search)
                    ->orLike('content', $search)
                ->groupEnd();


            // ✅ Filters
            $filters = [
                'status'     => 'status',
                'updated_at' => 'updated_at',
                'people'     => 'user_id',
                'type'       => 'storage_type'
            ];

            foreach ($filters as $param => $column) {
                $value = $this->request->getGet($param);

                if ($value == "") {
                    continue;
                }

                if ($param == "status" && strtolower($value) == "all") {
                    continue;
                }

                if ($param === "updated_at") {
                    switch ((int) $value) {
                        case 1: // Today
                            $projectBuilder->where("DATE($column)", date("Y-m-d"));
                            $fileBuilder->where("DATE($column)", date("Y-m-d"));
                            break;
                        case 2: // Yesterday
                            $projectBuilder->where("DATE($column)", date("Y-m-d", strtotime("-1 day")));
                            $fileBuilder->where("DATE($column)", date("Y-m-d", strtotime("-1 day")));
                            break;
                        case 3: // Last 7 days
                            $projectBuilder->where("DATE($column) >=", date("Y-m-d", strtotime("-1 week")));
                            $projectBuilder->where("DATE($column) <=", date("Y-m-d"));

                            $fileBuilder->where("DATE($column) >=", date("Y-m-d", strtotime("-1 week")));
                            $fileBuilder->where("DATE($column) <=", date("Y-m-d"));
                            break;
                    }
                } elseif ($param === "people") {
                    $projectBuilder->where("id IN (
                        SELECT tblinvitees.source_id
                        FROM tblinvitees
                        WHERE tblinvitees.source_type = 1
                          AND tblinvitees.user_id = " . (int) $value . "
                    )");
                    $fileBuilder->where("id IN (
                        SELECT tblinvitees.source_id
                        FROM tblinvitees
                        WHERE tblinvitees.source_type = 1
                          AND tblinvitees.user_id = " . (int) $value . "
                    )");
                } else {
                    $projectBuilder->where("LOWER($column)", strtolower($value));
                    $fileBuilder->where("LOWER($column)", strtolower($value));
                }
            }


            // Combine with UNION using builder->union()
            $projectBuilder->union($fileBuilder, true); // true = use UNION ALL

            // Compile full UNION query
            $unionQuery = $projectBuilder->getCompiledSelect();

            // Now apply ordering and pagination on the combined query
            $sql = "SELECT * FROM ({$unionQuery}) AS combined
            ORDER BY {$orderBy} {$sortDir}
            LIMIT {$offset}, {$limit}";

            $query = $this->db->query($sql);
            $data["results"] = $query->getResult();
            /*echo $this->db->getLastQuery();
            exit();*/
            $iFilteredTotal = sizeof($data['results']);

            // Get total count with same filters
            // Clone for total count

            $sqlTotal = "SELECT COUNT(*) as total FROM ({$unionQuery}) AS combined";
            $totalRecords = $this->db->query($sqlTotal)->getRow();
            $iTotal = $totalRecords->total ?? 0;
            
            /*
             * Output
             */
            $output = array(
                "sEcho"                => intval($this->request->getPost('sEcho')),
                "iTotalRecords"        => $iTotal,
                "iTotalDisplayRecords" => $iFilteredTotal,
                "iDisplayStart"        => "iDisplayLength",
                "token"                => csrf_hash(),
                "aaData"               => array()
            );
            
            $row = array();
            $output["gridFiles"] = view("files/files-grid-view", ["files" => $data["results"]]);

            $index = $this->request->getPost('iDisplayStart') + 1;
            $favoritesModel = new \App\Models\Favorites;

            foreach ($data['results'] as $value) {
                $updated_at = time_ago($value->updated_at);

                $name = "<a href=\"#\" class=\"no-underline text-primary item-name\"><span class='text-muted small item-type'>".$value->title."</span></a>";

                if ($value->storage_type == 0) {
                    $file_type = "Project";
                }
                else {
                    $file_type = str_contains($value->file_mime_type, "image") ? "Image" : "Document";
                }

                if (!empty($value->file_size)) {
                    $file_size = formatBytes($value->file_size);
                }
                else {
                    $file_size = "";
                }

                $activities = "<span class=\"activity text-primary\"><i class=\"ds ds-message text-primary\"></i> New comments</span>";

                $collaborators = view("files/file-collaborators", ["file_id" => $value->id, "showCount" => true, "value" => $value]);

                /* Check favorite is or not */
                $favoriteInfo = $favoritesModel->where([
                    "source_id"   => $value->id,
                    "source_type" => ($value->storage_type+1)
                ])->first();
                $starred = empty($favoriteInfo) ? "" : "starred";
                $favorite = "<a href=\"javascript:;\" class=\"star markfavorite ".$starred."\" data-id='".$value->id."' data-type='".($value->storage_type+1)."'><i class=\"ds ds-star icon-grey\"></i></a>";
                /* Check favorite is or not */

                $row = array( $favorite, $name, "<span class='text-muted small item-type'>".ucwords($file_type)."</span>", "<span class='text-muted small item-size'>".$file_size."</span>", $activities, "<span class='text-muted small item-updated'>".$updated_at."</span>", "<span class='text-muted small item-added'>".date("M d, Y", strtotime($value->created_at))."</span>", $collaborators, "DT_RowId" => "tr$value->id");
                $index += 1;
                $output['aaData'][] = $row;
            }
            
            return $this->response->setJSON( $output );
        }
    }

    public function logout() {
        (new \App\Models\Users)->update($this->session->get("dc_userid"), ['current_session_id' => null]);
        $this->session->destroy();
        return redirect()->to(site_url());
    }
}
