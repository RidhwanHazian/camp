<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
checkAdminSession();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirmed']) && $_POST['confirmed'] === 'yes') {
    $booking_id = $_POST['id'];

    // First, delete from payments table
    $deletePayments = $conn->prepare("DELETE FROM payments WHERE booking_id = ?");
    $deletePayments->bind_param("i", $booking_id);
    $deletePayments->execute();

    // Then, delete from bookings table
    $deleteBooking = $conn->prepare("DELETE FROM bookings WHERE booking_id = ?");
    $deleteBooking->bind_param("i", $booking_id);

    if ($deleteBooking->execute()) {
        header("Location: manage_bookings.php?deleted=1");
        exit();
    } else {
        die("Failed to delete booking: " . $conn->error);
    }
} else {
    header("Location: manage_bookings.php?error=invalid_request");
    exit();
}
