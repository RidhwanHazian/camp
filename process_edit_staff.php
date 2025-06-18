<?php
session_start();                    // Start session after enabling error reporting
include 'db_connection.php';
include 'session_check.php';        // Load session check functions
checkAdminSession(); 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $staff_id = $_POST['staff_id'];
        $staff_name = $_POST['staff_name'];
        $staff_username = $_POST['staff_username'];
        $staff_email = $_POST['staff_email'];
        $staff_notel = $_POST['staff_notel'];
        $staff_password = $_POST['staff_password'];

        // If a new password is provided, update it
        if (!empty($staff_password)) {
            $hashed_password = password_hash($staff_password, PASSWORD_DEFAULT);
            $sql = "UPDATE staff SET 
                    staff_name = ?, 
                    staff_username = ?, 
                    staff_email = ?, 
                    staff_notel = ?,
                    staff_password = ?
                    WHERE staff_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $staff_name, $staff_username, $staff_email, $staff_notel, $hashed_password, $staff_id);
        } else {
            // If no new password, update everything except password
            $sql = "UPDATE staff SET 
                    staff_name = ?, 
                    staff_username = ?, 
                    staff_email = ?, 
                    staff_notel = ? 
                    WHERE staff_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $staff_name, $staff_username, $staff_email, $staff_notel, $staff_id);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Staff updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating staff: " . $stmt->error;
        }
        
        $stmt->close();

    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating staff: " . $e->getMessage();
    }
}

header("Location: manage_staff.php");
exit();
?>