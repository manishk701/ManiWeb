<?php
session_start();
include "./assets/components/login-arc.php";

// Check authentication
if(!isset($_SESSION['IAm-logined'])) {
    http_response_code(403);
    exit(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

// Define allowed media directories
$mediaDirs = [
    'screenshots',
    'templates',
    'assets/images'
];

$mediaFiles = [];

// Scan directories for media files
foreach ($mediaDirs as $dir) {
    if (file_exists($dir)) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            
            // Check if file is a media file
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webm', 'mp4', 'mp3', 'wav'])) {
                $mediaFiles[] = [
                    'name' => $file,
                    'path' => $path,
                    'type' => $ext,
                    'size' => filesize($path),
                    'modified' => filemtime($path)
                ];
            }
        }
    }
}

// Sort files by modification time (newest first)
usort($mediaFiles, function($a, $b) {
    return $b['modified'] - $a['modified'];
});

header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'files' => $mediaFiles]);
?> 