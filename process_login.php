<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Debug information
    error_log("Login attempt - Username: $username, Role: $role");

    try {
        if ($role === 'admin') {
            // Check admin table
            $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['full_name'] = $admin['full_name'];
                $_SESSION['role'] = 'admin';
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Invalid admin credentials";
                header("Location: login.php");
                exit();
            }
        } else if ($role === 'staff') {
            // Check staff table
            $stmt = $conn->prepare("SELECT * FROM staff WHERE staff_username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $staff = $result->fetch_assoc();

            if ($staff && password_verify($password, $staff['staff_password'])) {
                $_SESSION['staff_id'] = $staff['staff_id'];
                $_SESSION['username'] = $staff['staff_username'];
                $_SESSION['full_name'] = $staff['staff_name'];
                $_SESSION['role'] = 'staff';
                header("Location: staff_dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Invalid staff credentials";
                header("Location: login.php");
                exit();
            }
        } else {
            // Check users table for customers
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = 'customer'");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $users = $result->fetch_assoc();

            // Debug information
            error_log("Customer login - User found: " . ($users ? "Yes" : "No"));
            if ($users) {
                error_log("Password verification: " . (password_verify($password, $users['password']) ? "Success" : "Failed"));
            }

            if ($users && password_verify($password, $users['password'])) {
                $_SESSION['customer_id'] = $users['user_id'];
                $_SESSION['username'] = $users['username'];
                $_SESSION['full_name'] = $users['full_name'];
                $_SESSION['role'] = 'customer';
                
                // Debug information
                error_log("Session variables set - Redirecting to customer_dashboard.php");
                error_log("Session data: " . print_r($_SESSION, true));
                
                header("Location: customer_dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Invalid username or password";
                header("Location: login.php");
                exit();
            }
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error'] = "Login failed: " . $e->getMessage();
        header("Location: login.php");
        exit();
    }
}
?>