<?php
require_once 'db_connection.php';

try {
    // Get raw count
    $stmt = $conn->query("SELECT COUNT(*) as count FROM feedback");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total feedback entries: " . $count['count'] . "<br><br>";

    // Get all feedback entries
    $stmt = $conn->query("SELECT * FROM feedback");
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    if (!empty($feedbacks)) {
        foreach ($feedbacks as $feedback) {
            echo "Feedback ID: " . $feedback['feedback_id'] . "\n";
            echo "User ID: " . $feedback['user_id'] . "\n";
            echo "Package ID: " . $feedback['package_id'] . "\n";
            echo "Rating: " . $feedback['rating'] . "\n";
            echo "Comment: " . $feedback['comment'] . "\n";
            echo "Photo Path: " . ($feedback['photo_path'] ?? 'None') . "\n";
            echo "Video Path: " . ($feedback['video_path'] ?? 'None') . "\n";
            echo "----------------------------------------\n";
        }
    } else {
        echo "No feedback found in the database.";
    }
    echo "</pre>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 
