<?php
require_once 'db_connection.php';

try {
    // Create notifications table
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        notification_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type VARCHAR(50) NOT NULL,
        icon VARCHAR(50) NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        notify_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )";
    
    $conn->exec($sql);
    echo "Notifications table created successfully";
    
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?> 
