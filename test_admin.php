<?php
require_once 'db_connection.php';

// Create admin table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100)
)";

if ($conn->query($sql)) {
    echo "Admin table exists or was created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Check if admin user exists
$stmt = $conn->prepare("SELECT * FROM admin WHERE username = 'admin'");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Create default admin user if it doesn't exist
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $fullname = 'System Administrator';
    
    $stmt = $conn->prepare("INSERT INTO admin (username, password, full_name) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $fullname);
    
    if ($stmt->execute()) {
        echo "Default admin user created successfully<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Error creating admin user: " . $stmt->error . "<br>";
    }
} else {
    echo "Admin user already exists<br>";
}

// Test admin credentials
$test_password = 'admin123';
$stmt = $conn->prepare("SELECT * FROM admin WHERE username = 'admin'");
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

if (password_verify($test_password, $admin['password'])) {
    echo "Password verification successful<br>";
} else {
    echo "Password verification failed<br>";
}

?> 