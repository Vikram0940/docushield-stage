<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Jobs extends BaseController
{
    public function import_document_data()
    {
        $storage_data = [];
        $documents = (new \App\Models\Document)->findAll();
        if (!empty($documents)) {
            foreach ($documents as $document) {
                $storage_data[] = [
                    "title"         => $document->title,
                    "content"       => $document->content,
                    "parent_id"     => !empty($document->project_id) ? $document->project_id : $document->folder_id,
                    "parent_type"   => !empty($document->project_id) ? 1 : 2,
                    "user_id"       => $document->created_by,
                    "status"        => $document->status,
                    "template"      => $document->template,
                    "bs_response"   => $document->bs_response,
                    "bs_data"       => $document->bs_data,
                    "chat_group_id" => $document->chat_group_id,
                    "storage_type"  => 1,
                    "created_at"    => $document->created_date,
                    "updated_at"    => $document->updated_date,
                    "deleted_at"    => $document->deleted_date
                ];
            }
        }
        if (!empty($storage_data)) {
            $storageModel = new \App\Models\Storage;
            $storageModel->insertBatch($storage_data);
            return $this->response->setJSON(["status" => true, "message" => sizeof($storage_data)." data imported successfully!"]);
        }
        return $this->response->setJSON(["status" => false, "message" => "No data available"]);
    }
}
