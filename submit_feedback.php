<?php
session_start();
require_once 'confg.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get form data
        $user_id = $_SESSION['user_id'];
        $package_id = isset($_POST['package_id']) ? (int)$_POST['package_id'] : null;
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;
        $comment = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';

        // Validate inputs
        if (!$package_id) {
            echo json_encode(['status' => 'error', 'message' => 'Please select a package']);
            exit();
        }

        if (!$rating || $rating < 1 || $rating > 5) {
            echo json_encode(['status' => 'error', 'message' => 'Please provide a valid rating']);
            exit();
        }

        if (empty($comment)) {
            echo json_encode(['status' => 'error', 'message' => 'Please provide feedback comment']);
            exit();
        }

        // Start transaction
        $conn->beginTransaction();

        // Get the booking_id
        $stmt = $conn->prepare("
            SELECT booking_id 
            FROM bookings 
            WHERE user_id = ? AND package_id = ? AND status = 'paid'
            ORDER BY booking_id DESC 
            LIMIT 1
        ");
        $stmt->execute([$user_id, $package_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            throw new Exception("No valid booking found for this package");
        }

        $booking_id = $booking['booking_id'];

        // Handle file uploads
        $photo_path = null;
        $video_path = null;
        $uploadDir = 'uploads/feedback/';

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photo = $_FILES['photo'];
            $photoExt = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
            $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($photoExt, $allowedImageTypes)) {
                $photoName = 'feedback_photo_' . time() . '_' . uniqid() . '.' . $photoExt;
                $photoPath = $uploadDir . $photoName;
                
                if (move_uploaded_file($photo['tmp_name'], $photoPath)) {
                    $photo_path = $photoPath;
                }
            }
        }

        // Handle video upload
        if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
            $video = $_FILES['video'];
            $videoExt = strtolower(pathinfo($video['name'], PATHINFO_EXTENSION));
            $allowedVideoTypes = ['mp4', 'mov', 'avi'];
            
            if (in_array($videoExt, $allowedVideoTypes)) {
                $videoName = 'feedback_video_' . time() . '_' . uniqid() . '.' . $videoExt;
                $videoPath = $uploadDir . $videoName;
                
                if (move_uploaded_file($video['tmp_name'], $videoPath)) {
                    $video_path = $videoPath;
                }
            }
        }

        // Insert feedback
        $stmt = $conn->prepare("
            INSERT INTO feedback (
                booking_id,
                user_id,
                package_id,
                rating,
                comment,
                photo_path,
                video_path
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $booking_id,
            $user_id,
            $package_id,
            $rating,
            $comment,
            $photo_path,
            $video_path
        ]);

        if (!$result) {
            throw new Exception("Failed to insert feedback");
        }

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Thank you for your feedback! Your opinion helps us improve our services. 🌟'
        ]);

    } catch (Exception $e) {
        // Rollback transaction
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        // Delete uploaded files if they exist
        if ($photo_path && file_exists($photo_path)) {
            unlink($photo_path);
        }
        if ($video_path && file_exists($video_path)) {
            unlink($video_path);
        }

        // Log error
        error_log("Feedback Error: " . $e->getMessage());
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Error submitting feedback: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
?>