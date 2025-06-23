<?php
session_start();
require_once 'db_connection.php';

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid schedule ID provided.";
    header('Location: manage_staff.php');
    exit();
}

$id = intval($_GET['id']);

try {
    // Unassign staff from booking instead of deleting the booking
    $stmt = $conn->prepare("UPDATE bookings SET staff_id = NULL WHERE booking_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Staff unassigned from booking successfully!";
    } else {
        $_SESSION['error'] = "Error unassigning staff: " . $stmt->error;
    }
    
    $stmt->close();
} catch (Exception $e) {
    $_SESSION['error'] = "Error unassigning staff: " . $e->getMessage();
}

header("Location: manage_staff.php");
exit();
?>
