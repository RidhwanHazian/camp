<?php
include 'session_check.php'; // Include session check functions
checkAdminSession(); // Ensure only admins can access this page
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = $_POST['staff_id'];
    $booking_id = $_POST['booking_id'];
    $admin_id = $_SESSION['admin_id']; // Get admin_id from verified session

    // Fetch booking details based on booking_id
    $bookingStmt = $conn->prepare("
        SELECT arrival_date
        FROM bookings
        WHERE booking_id = ?
    ");
    $bookingStmt->bind_param("i", $booking_id);
    $bookingStmt->execute();
    $bookingResult = $bookingStmt->get_result();
    $booking = $bookingResult->fetch_assoc();

    if (!$booking) {
        $_SESSION['error'] = "Booking not found.";
        header("Location: add_schedule.php?error=1");
        exit();
    }

    $task_date = $booking['arrival_date'];

    // 1. Check if staff is already assigned to another booking on this date
    $conflictQuery = $conn->prepare("
        SELECT booking_id FROM bookings
        WHERE staff_id = ? AND arrival_date = ?
    ");
    $conflictQuery->bind_param("is", $staff_id, $task_date);
    $conflictQuery->execute();
    $conflictResult = $conflictQuery->get_result();

    if ($conflictResult->num_rows > 0) {
        $errorMsg = urlencode("This staff member is already assigned to another booking on $task_date.");
        header("Location: add_schedule.php?error=$errorMsg");
        exit();
    }

    // 2. Check if this booking is already assigned to another staff
    $bookingAssignedQuery = $conn->prepare("SELECT staff_id FROM bookings WHERE booking_id = ? AND staff_id IS NOT NULL");
    $bookingAssignedQuery->bind_param("i", $booking_id);
    $bookingAssignedQuery->execute();
    $bookingAssignedResult = $bookingAssignedQuery->get_result();

    if ($bookingAssignedResult->num_rows > 0) {
        $errorMsg = urlencode("This booking is already assigned to a staff member.");
        header("Location: add_schedule.php?error=$errorMsg");
        exit();
    }

    // 3. If both checks pass, assign staff to booking
    $stmt = $conn->prepare("UPDATE bookings SET staff_id = ? WHERE booking_id = ?");
    $stmt->bind_param("ii", $staff_id, $booking_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Schedule added successfully!";
        header("Location: add_schedule.php?success=1");
    } else {
        $_SESSION['error'] = "Failed to update booking: " . $stmt->error;
        header("Location: add_schedule.php?error=1");
    }
    exit();
}
?>
