<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class FileController extends Controller
{
    public function view($filename)
    {
        // Build full path to file
        $filePath = WRITEPATH . 'uploads/' . $filename;

        // Check if file exists
        if (!is_file($filePath)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File not found');
        }

        // Serve file securely
        return $this->response
            ->setHeader('Content-Type', mime_content_type($filePath))
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($filePath) . '"')
            ->setBody(file_get_contents($filePath));
    }

    public function avatar($filename)
    {
        // Build full path to file
        $filePath = WRITEPATH . 'uploads/avatars/' . $filename;

        // Check if file exists
        if (!is_file($filePath)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File not found');
        }

        // Serve file securely
        return $this->response
            ->setHeader('Content-Type', mime_content_type($filePath))
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($filePath) . '"')
            ->setBody(file_get_contents($filePath));
    }
}
