<?php
session_start();
include "./assets/components/login-arc.php";

// Check authentication
if(!isset($_SESSION['IAm-logined'])) {
    http_response_code(403);
    exit(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

// Get the action from POST data
$data = json_decode(file_get_contents('php://input'), true);
$action = isset($data['action']) ? $data['action'] : '';

$webDataFile = 'web_data.json';
$existingData = [];

if (file_exists($webDataFile)) {
    $existingData = json_decode(file_get_contents($webDataFile), true) ?: [];
}

switch ($action) {
    case 'load':
        // Return all collected web data
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $existingData
        ]);
        break;
        
    case 'remove':
        if (!isset($data['id'])) {
            http_response_code(400);
            exit(json_encode(['status' => 'error', 'message' => 'Missing ID']));
        }
        
        $id = $data['id'];
        $newData = array_filter($existingData, function($item) use ($id) {
            return $item['id'] !== $id;
        });
        
        file_put_contents($webDataFile, json_encode(array_values($newData), JSON_PRETTY_PRINT));
        
        // Log the removal
        $logMessage = date('Y-m-d H:i:s') . " - Removed web data with ID: " . $id . "\n";
        file_put_contents('web_data_log.txt', $logMessage, FILE_APPEND);
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Data removed successfully'
        ]);
        break;
        
    case 'send':
        if (!isset($data['id'])) {
            http_response_code(400);
            exit(json_encode(['status' => 'error', 'message' => 'Missing ID']));
        }
        
        $id = $data['id'];
        $item = array_filter($existingData, function($item) use ($id) {
            return $item['id'] === $id;
        });
        
        if (empty($item)) {
            http_response_code(404);
            exit(json_encode(['status' => 'error', 'message' => 'Data not found']));
        }
        
        $item = array_values($item)[0];
        
        // Here you would implement the logic to send the data
        // For now, we'll just log it
        $logMessage = date('Y-m-d H:i:s') . " - Sent web data: " . json_encode($item) . "\n";
        file_put_contents('web_data_log.txt', $logMessage, FILE_APPEND);
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Data sent successfully',
            'data' => $item
        ]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?> 