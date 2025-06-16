<?php
include 'session_check.php';
checkAdminSession();
include 'db_connection.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid staff ID provided.";
    header('Location: manage_staff.php');
    exit();
}

$id = intval($_GET['id']);

try {
    // First delete any related records (like task assignments)
    $delete_tasks = $conn->prepare("DELETE FROM task_assignment WHERE staff_id = ?");
    $delete_tasks->bind_param("i", $id);
    $delete_tasks->execute();
    $delete_tasks->close();

    // Then delete the staff member
    $delete_staff = $conn->prepare("DELETE FROM staff WHERE staff_id = ?");
    $delete_staff->bind_param("i", $id);
    $delete_staff->execute();
    
    if ($delete_staff->affected_rows > 0) {
        $_SESSION['success'] = "Staff deleted successfully!";
    } else {
        $_SESSION['error'] = "Staff not found or could not be deleted.";
    }
    
    $delete_staff->close();

} catch (Exception $e) {
    $_SESSION['error'] = "Error deleting staff: " . $e->getMessage();
}

header("Location: manage_staff.php");
exit();
?>