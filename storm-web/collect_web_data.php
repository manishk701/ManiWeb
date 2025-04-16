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

if (!isset($data['url']) || empty($data['url'])) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'Missing URL']));
}

$url = $data['url'];

// Validate URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'Invalid URL']));
}

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

// Execute cURL request
$response = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    exit(json_encode(['status' => 'error', 'message' => 'Error fetching URL: ' . curl_error($ch)]));
}

curl_close($ch);

// Extract data based on URL type
$collectedData = [];

// Check if it's a GitHub repository
if (preg_match('/github\.com\/([^\/]+)\/([^\/]+)/i', $url, $matches)) {
    $username = $matches[1];
    $repo = $matches[2];
    
    // Extract repository information
    $collectedData = [
        'type' => 'github_repository',
        'username' => $username,
        'repository' => $repo,
        'url' => $url,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Save to database or file
    $webDataFile = 'web_data.json';
    $existingData = [];
    
    if (file_exists($webDataFile)) {
        $existingData = json_decode(file_get_contents($webDataFile), true) ?: [];
    }
    
    $id = uniqid('web_', true);
    $collectedData['id'] = $id;
    $existingData[] = $collectedData;
    
    file_put_contents($webDataFile, json_encode($existingData, JSON_PRETTY_PRINT));
    
    // Log the collection
    $logMessage = date('Y-m-d H:i:s') . " - Collected data from: " . $url . "\n";
    file_put_contents('web_data_log.txt', $logMessage, FILE_APPEND);
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'Data collected successfully',
        'data' => $collectedData
    ]);
} else {
    // Handle other types of URLs
    $collectedData = [
        'type' => 'web_page',
        'url' => $url,
        'timestamp' => date('Y-m-d H:i:s'),
        'content_length' => strlen($response)
    ];
    
    // Save to database or file
    $webDataFile = 'web_data.json';
    $existingData = [];
    
    if (file_exists($webDataFile)) {
        $existingData = json_decode(file_get_contents($webDataFile), true) ?: [];
    }
    
    $id = uniqid('web_', true);
    $collectedData['id'] = $id;
    $existingData[] = $collectedData;
    
    file_put_contents($webDataFile, json_encode($existingData, JSON_PRETTY_PRINT));
    
    // Log the collection
    $logMessage = date('Y-m-d H:i:s') . " - Collected data from: " . $url . "\n";
    file_put_contents('web_data_log.txt', $logMessage, FILE_APPEND);
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'Data collected successfully',
        'data' => $collectedData
    ]);
}
?> 