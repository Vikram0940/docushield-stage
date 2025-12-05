<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Database\Exceptions\DatabaseException;

class DocumentController extends ResourceController
{
    protected $format = 'json';

    // GET /api/documents
    public function index()
    {
        $session = \Config\Services::session();

        $db = \Config\Database::connect();
        $data = $db->table('tbldocuments')
            ->select('id, title, created_date')
            ->where('created_by', $loggedInUserId)
            ->get()->getResultArray();

        return $this->respond([
            'status' => 'success',
            'data' => $data
        ]);
    }


    // ⭐ GET /api/document/{id}
    public function show($id = null)
    {
        if ($id === null) {
            return $this->fail('Document ID is required');
        }

        $db = \Config\Database::connect();

        $document = $db->table('tbldocuments')
            ->where('id', $id)
            // ->where('created_by', $loggedInUserId) 
            ->get()
            ->getRowArray();

        if (!$document) {
            return $this->failNotFound('Document not found or access denied.');
        }

        return $this->respond([
            'status' => 'success',
            'data' => $document
        ]);
    }

    private function validateTokenAndGetUserId($token)
    {
        $tokenModel = new \App\Models\ApiTokenModel();

        $tokenData = $tokenModel
            ->asArray()
            ->where('token', $token)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();
        
        return $tokenData ? $tokenData['user_id'] : null;
    }

    // ⭐ GET /api/comments/{threadId}
    public function getComments($threadId = null)
    {
        if (!$threadId) {
            return $this->fail('Thread ID is required');
        }

        // DB connect
        $db = \Config\Database::connect();

        // 📌 First check document exists
        $document = $db->table('tbldocuments')
            ->select('id, title, created_date')
            ->where('id', $threadId)
            ->get()
            ->getRowArray();

        if (!$document) {
            return $this->failNotFound('Document not found');
        }

        // 📌 Fetch comments
        $commentModel = new \App\Models\CommentModel();

        $comments = $commentModel
            ->where('threadId', $threadId)
            ->orderBy('createdAt', 'ASC')
            ->findAll();

        // 📌 Fetch comments with author info
        $comments = $db->table('comments')
            ->select('id, threadId, authorId, author, avatar, content, createdAt')
            ->where('threadId', $threadId)
            ->orderBy('createdAt', 'ASC')
            ->get()
            ->getResultArray();


        // Final Response
        return $this->respond([
            'status' => 'success',
            'document' => $document,
            'totalComments' => count($comments),
            'comments' => $comments
        ]);
    }


   public function upload()
    {
        $file = $this->request->getFile('file');
        if(!$file || !$file->isValid()){
            return $this->fail('Invalid or missing file.');
        }
        // Create Upload Folder If Missing
        $uploadFolder = FCPATH . "uploads/docs/";
        if (!is_dir($uploadFolder)) {
            mkdir($uploadFolder, 0777, true);
        }
        // Move File
        $newName = time() . "_" . $file->getName();
        $file->move($uploadFolder, $newName);
        $filePath = $uploadFolder . $newName;
        // Convert Folder
        $convertFolder = FCPATH . "uploads/converted/";
        if (!is_dir($convertFolder)) {
            mkdir($convertFolder, 0777, true);
        }
        // ================================
        //   OS Detection for LibreOffice
        // ================================
        $os = PHP_OS;
        $soffice = "soffice";  // Default Linux
        // Windows
        if (strtoupper(substr($os, 0, 3)) === 'WIN') {
            $soffice = "\"C:\\Program Files\\LibreOffice\\program\\soffice.exe\"";
        }
        // macOS
        elseif ($os === 'Darwin') {
            $soffice = "/Applications/LibreOffice.app/Contents/MacOS/soffice";
        }
        // Final Command
        $command = $soffice . " --headless --convert-to html --outdir \"$convertFolder\" \"$filePath\"";
        // Execute command + get error output
        exec($command . " 2>&1", $output, $result);
        // Error Handling
        if ($result !== 0) {
            return $this->fail("LibreOffice Error: " . implode("\n", $output));
        }
        // Converted File Name
        $convertedName = preg_replace('/\.(doc|docx)$/i', '.html', $newName);
        $convertedPath = $convertFolder . $convertedName;
        if (!file_exists($convertedPath)) {
            return $this->fail("Conversion failed. LibreOffice not installed or wrong path.");
        }
        // Read converted HTML
        $htmlContent = file_get_contents($convertedPath);
        return $this->respond([
            "status" => "success",
            "html" => $htmlContent,
            "url" => base_url("uploads/docs/".$newName)
        ]);
    }


   public function autosave()
    {
        $docId = $this->request->getPost('document_id');
        $content = $this->request->getPost('content');

        if (!$docId) {
            return $this->respond([
                "status" => "error",
                "message" => "Document ID missing"
            ]);
        }

        $db = \Config\Database::connect();

        $db->table('tbldocuments')
            ->where('id', $docId)
            ->update([
                'content' => $content,
                'updated_date' => date('Y-m-d H:i:s')
            ]);

        return $this->respond([
            "status" => "success",
            "message" => "Autosaved successfully"
        ]);
    }



}
