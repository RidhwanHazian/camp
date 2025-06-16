<?php
require_once 'db_connection.php';

// Reset admin password
$username = 'admin';
$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE admin SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $hashed_password, $username);

if ($stmt->execute()) {
    echo "Admin password has been reset successfully!<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    
    // Verify the new password
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    
    if (password_verify($new_password, $admin['password'])) {
        echo "<br>Password verification successful - you can now login with these credentials.";
    } else {
        echo "<br>Error: Password verification failed after reset.";
    }
} else {
    echo "Error resetting password: " . $stmt->error;
}
?> 