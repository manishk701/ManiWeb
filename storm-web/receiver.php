<?php
session_start();
include "./assets/components/login-arc.php";

// Check authentication
if(!isset($_SESSION['IAm-logined'])) {
    http_response_code(403);
    exit('Unauthorized');
}

// Create screenshots directory if it doesn't exist
$screenshotsDir = 'screenshots';
if (!file_exists($screenshotsDir)) {
    mkdir($screenshotsDir, 0777, true);
}

// Handle screen sharing data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['screen'])) {
    $file = $_FILES['screen'];
    
    // Generate unique filename
    $filename = $screenshotsDir . '/' . time() . '_' . uniqid() . '.webm';
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filename)) {
        // Log the screenshot
        $logMessage = date('Y-m-d H:i:s') . " - Screen capture saved: " . basename($filename) . "\n";
        file_put_contents('screen_log.txt', $logMessage, FILE_APPEND);
        
        echo json_encode(['status' => 'success', 'message' => 'Screen capture saved']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to save screen capture']);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>