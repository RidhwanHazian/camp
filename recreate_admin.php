<?php
require_once 'db_connection.php';

// First, drop the existing admin table
$sql = "DROP TABLE IF EXISTS admin";
if ($conn->query($sql)) {
    echo "Old admin table dropped successfully<br>";
} else {
    echo "Error dropping table: " . $conn->error . "<br>";
}

// Create fresh admin table
$sql = "CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100)
)";

if ($conn->query($sql)) {
    echo "New admin table created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Create new admin user with fresh password hash
$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$fullname = 'System Administrator';

$stmt = $conn->prepare("INSERT INTO admin (username, password, full_name) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $hashed_password, $fullname);

if ($stmt->execute()) {
    echo "New admin user created successfully<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    
    // Verify the password
    $verify_stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $verify_stmt->bind_param("s", $username);
    $verify_stmt->execute();
    $result = $verify_stmt->get_result();
    $admin = $result->fetch_assoc();
    
    if (password_verify($password, $admin['password'])) {
        echo "<br>✅ Password verification successful - you can now login with these credentials";
    } else {
        echo "<br>❌ Error: Password verification failed";
    }
} else {
    echo "Error creating admin user: " . $stmt->error;
}

// Display the current admin user details
$result = $conn->query("SELECT admin_id, username, full_name FROM admin");
if ($result->num_rows > 0) {
    echo "<br><br>Current admin in database:<br>";
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["admin_id"] . "<br>";
        echo "Username: " . $row["username"] . "<br>";
        echo "Full Name: " . $row["full_name"] . "<br>";
    }
}
?> 