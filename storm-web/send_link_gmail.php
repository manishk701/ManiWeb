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

if (!isset($data['link']) || empty($data['link']) || !isset($data['email']) || empty($data['email'])) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'Missing link or email']));
}

$link = $data['link'];
$email = $data['email'];

// Validate URL
if (!filter_var($link, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'Invalid URL']));
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@gmail\.com$/', $email)) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'Invalid Gmail address']));
}

// Save to log
$logMessage = date('Y-m-d H:i:s') . " - Link sent to Gmail: " . $email . " - " . $link . "\n";
file_put_contents('gmail_link_log.txt', $logMessage, FILE_APPEND);

// Send link to Gmail (you can implement your own method here)
// For example, using a webhook or other communication method

header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'message' => 'Link sent successfully to ' . $email,
    'link' => $link,
    'email' => $email
]);
?> 