<?php
session_start();
include "./assets/components/login-arc.php";

// Check authentication
if(!isset($_SESSION['IAm-logined'])) {
    http_response_code(403);
    exit(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

// Define Gmail storage file
$gmailFile = 'gmail_addresses.txt';

// Create file if it doesn't exist
if (!file_exists($gmailFile)) {
    file_put_contents($gmailFile, '');
}

// Read Gmail addresses
$emails = [];
if (file_exists($gmailFile)) {
    $emails = array_filter(explode("\n", file_get_contents($gmailFile)));
    $emails = array_map('trim', $emails);
    $emails = array_unique($emails);
}

// Scan for new Gmail addresses in templates
$templateDir = 'templates';
if (file_exists($templateDir)) {
    $files = array_diff(scandir($templateDir), array('.', '..'));
    foreach ($files as $file) {
        $content = file_get_contents($templateDir . '/' . $file);
        // Extract Gmail addresses using regex
        preg_match_all('/[a-zA-Z0-9._%+-]+@gmail\.com/i', $content, $matches);
        if (!empty($matches[0])) {
            $emails = array_merge($emails, $matches[0]);
        }
    }
}

// Remove duplicates and sort
$emails = array_unique($emails);
sort($emails);

header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'emails' => $emails]);
?> 