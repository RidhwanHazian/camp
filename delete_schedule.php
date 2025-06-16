<?php
include 'session_check.php';
checkAdminSession(); // Only admins can delete schedules
include 'db_connection.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid schedule ID provided.";
    header('Location: manage_staff.php');
    exit();
}

$id = intval($_GET['id']);

try {
    $stmt = $conn->prepare("DELETE FROM task_assignment WHERE task_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Schedule deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting schedule: " . $stmt->error;
    }
    
    $stmt->close();
} catch (Exception $e) {
    $_SESSION['error'] = "Error deleting schedule: " . $e->getMessage();
}

header("Location: manage_staff.php");
exit();
?>