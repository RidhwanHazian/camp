<?php
session_start();
include 'db_connection.php';
include 'session_check.php'; // Include session check functions
checkAdminSession(); // Ensure only admins can access this page

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $staff_name = $_POST['staff_name'];
        $staff_username = $_POST['staff_username'];
        $staff_email = $_POST['staff_email'];
        $staff_notel = $_POST['staff_notel'];
        $staff_password = password_hash($_POST['staff_password'], PASSWORD_DEFAULT);

        // Insert into staff table
        $sql = "INSERT INTO staff (staff_name, staff_username, staff_email, staff_notel, staff_password) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $staff_name, $staff_username, $staff_email, $staff_notel, $staff_password);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Staff added successfully!";
        } else {
            $_SESSION['error'] = "Error adding staff: " . $stmt->error;
        }
        
        $stmt->close();

    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

header("Location: manage_staff.php");
exit();
?>
