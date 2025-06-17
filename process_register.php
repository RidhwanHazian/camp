<?php
session_start();
require_once 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: register.php");
        exit();
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Username already exists!";
        header("Location: register.php");
        exit();
    }
    $stmt->close();

    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Email already exists!";
        header("Location: register.php");
        exit();
    }
    $stmt->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, full_name, phone_no) VALUES (?, ?, ?, 'customer', '', '')");
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Registration successful! Please login.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Registration failed: " . $stmt->error;
        header("Location: register.php");
        exit();
    }
}
?>
