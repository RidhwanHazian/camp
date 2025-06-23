<?php
session_start();
// Check if user is logged in as admin or staff
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header('Location: login.php');
    exit();
}

require_once 'db_connection.php';

try {
    $id = $_POST['id'] ?? '';
    if (empty($id)) {
        throw new Exception("No booking ID provided");
    }

    // First check if the booking exists
    $stmt = $conn->prepare("SELECT booking_id FROM bookings WHERE booking_id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        throw new Exception("Booking not found");
    }

    // Delete related payments first
    $stmt = $conn->prepare("DELETE FROM payments WHERE booking_id = ?");
    $stmt->execute([$id]);
    
    // Then delete the booking
    $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['success'] = "Booking #$id has been deleted successfully";
    
    // Redirect based on role
    if ($_SESSION['role'] === 'admin') {
        header('Location: manage_bookings.php');
    } else {
        header('Location: staff_dashboard.php');
    }
    exit();

} catch(Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    if ($_SESSION['role'] === 'admin') {
        header('Location: manage_bookings.php');
    } else {
        header('Location: staff_dashboard.php');
    }
    exit();
}
?>
