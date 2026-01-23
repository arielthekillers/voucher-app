<?php
// Simple image proxy
$path = $_GET['path'] ?? '';

// Basic security: prevent directory traversal
if (strpos($path, '..') !== false) {
    http_response_code(403);
    exit('Access Denied');
}

$baseDir = __DIR__ . '/../storage/';
$realPath = realpath($baseDir . $path);

// Security: Ensure the file is actually inside the storage directory
if ($realPath === false || strpos($realPath, realpath($baseDir)) !== 0) {
    http_response_code(404);
    exit('File Not Found');
}

if (!file_exists($realPath)) {
    http_response_code(404);
    exit('File Not Found');
}

// Get mimetype
$mime = mime_content_type($realPath);
// Allow only images
if (strpos($mime, 'image/') !== 0) {
    http_response_code(403);
    exit('Not an image');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($realPath));
readfile($realPath);
