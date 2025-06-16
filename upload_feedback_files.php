<?php
session_start();
require_once 'confg.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Not logged in']));
}

function generateUniqueFileName($originalName, $type) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid('feedback_' . $type . '_') . '_' . time() . '.' . $extension;
}

function validateFile($file, $type) {
    $maxSize = 10 * 1024 * 1024; // 10MB
    
    if ($file['size'] > $maxSize) {
        return "File is too large. Maximum size is 10MB.";
    }
    
    if ($type === 'photo') {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            return "Only JPG, PNG, and GIF images are allowed.";
        }
    } else if ($type === 'video') {
        $allowedTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo'];
        if (!in_array($file['type'], $allowedTypes)) {
            return "Only MP4, MOV, and AVI videos are allowed.";
        }
    }
    
    return null;
}

try {
    if (isset($_FILES['photo']) || isset($_FILES['video'])) {
        $uploadDir = 'uploads/feedback/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $response = ['success' => true, 'files' => []];
        
        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $error = validateFile($_FILES['photo'], 'photo');
            if ($error) {
                die(json_encode(['error' => $error]));
            }
            
            $fileName = generateUniqueFileName($_FILES['photo']['name'], 'photo');
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
                $response['files']['photo'] = $filePath;
            }
        }
        
        // Handle video upload
        if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
            $error = validateFile($_FILES['video'], 'video');
            if ($error) {
                die(json_encode(['error' => $error]));
            }
            
            $fileName = generateUniqueFileName($_FILES['video']['name'], 'video');
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['video']['tmp_name'], $filePath)) {
                $response['files']['video'] = $filePath;
            }
        }
        
        echo json_encode($response);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?> 