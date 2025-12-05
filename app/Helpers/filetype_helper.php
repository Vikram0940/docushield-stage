<?php

if (! function_exists('getFileType')) {
    /**
     * Detects file type based on extension or MIME.
     *
     * @param string $path File path (can be relative or full path).
     * @return string One of: image, pdf, word, excel, unknown
     */
    function getFileType(string $path): string
    {
        if (empty($path)) {
            return 'unknown';
        }

        // First check extension
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $map = [
            'jpg'  => 'image',
            'jpeg' => 'image',
            'png'  => 'image',
            'gif'  => 'image',
            'webp' => 'image',
            'pdf'  => 'pdf',
            'doc'  => 'doc',
            'docx' => 'doc',
            'xls'  => 'file',
            'xlsx' => 'file',
            'csv'  => 'file',
            'zip'  => 'file'
        ];

        if (array_key_exists($ext, $map)) {
            return $map[$ext];
        }

        // If file exists on disk, try MIME detection
        if (is_file($path) && function_exists('mime_content_type')) {
            $mime = mime_content_type($path);

            if (str_starts_with($mime, 'image/')) {
                return 'image';
            }

            $mimeMap = [
                'application/pdf' => 'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'doc',
                'application/vnd.ms-excel' => 'excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'excel',
            ];

            if (array_key_exists($mime, $mimeMap)) {
                return $mimeMap[$mime];
            }
        }

        return 'unknown';
    }
}

if (! function_exists('getFileSize')) {
    /**
     * Get human-readable file size.
     *
     * @param string $path
     * @return string
     */
    function getFileSize(string $path): string
    {
        if (!is_file($path)) {
            return '0 B';
        }

        $size = filesize($path);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }
}

if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $pow = min($pow, count($units) - 1);

        // Calculate size
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
