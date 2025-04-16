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

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mimeTypes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webm' => 'video/webm',
    'mp4' => 'video/mp4',
    'mp3' => 'audio/mpeg',
    'wav' => 'audio/wav'
];

if (!isset($mimeTypes[$ext])) {
    http_response_code(400);
    exit('Unsupported file type');
}

header('Content-Type: ' . $mimeTypes[$ext]);
readfile($path);
?> 