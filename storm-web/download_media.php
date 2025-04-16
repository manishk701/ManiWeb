<?php
session_start();
include "./assets/components/login-arc.php";

// Check authentication
if(!isset($_SESSION['IAm-logined'])) {
    http_response_code(403);
    exit('Unauthorized');
}

if (!isset($_GET['path'])) {
    http_response_code(400);
    exit('No file specified');
}

$path = $_GET['path'];

// Security check - prevent directory traversal
if (strpos($path, '..') !== false || !file_exists($path)) {
    http_response_code(400);
    exit('Invalid file path');
}

$filename = basename($path);
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

// Set headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

readfile($path);
?> 