<?php
session_start();
include "./assets/components/login-arc.php";

// Check authentication
if(!isset($_SESSION['IAm-logined'])) {
    http_response_code(403);
    exit(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['link']) || empty($data['link'])) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'No link provided']));
}

$link = $data['link'];

// Validate URL
if (!filter_var($link, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'Invalid URL']));
}

// Save link to log
$logMessage = date('Y-m-d H:i:s') . " - Link sent: " . $link . "\n";
file_put_contents('link_log.txt', $logMessage, FILE_APPEND);

// Send link to target (you can implement your own method here)
// For example, using a webhook or other communication method

header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'message' => 'Link sent successfully',
    'link' => $link
]);
?> 