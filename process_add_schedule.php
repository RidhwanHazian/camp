<?php
session_start();                    // Start session after enabling error reporting
include 'db_connection.php';
include 'session_check.php';        // Load session check functions
checkAdminSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = $_POST['staff_id'];
    $booking_id = $_POST['booking_id'];
    $admin_id = $_SESSION['admin_id']; // Get admin_id from verified session

    // Fetch booking details based on booking_id
    $bookingStmt = $conn->prepare("
        SELECT b.arrival_date, p.package_name
        FROM bookings b
        LEFT JOIN packages p ON b.package_id = p.package_id
        WHERE b.booking_id = ?
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

    // Assign derived values
    $task_date = $booking['arrival_date'];
    $task_shiftTime = '09:00:00'; // Default shift time, or adjust as needed
    $task_location = 'Customer Site'; // Generic location, or derive if available
    $task_activity = $booking['package_name'];

    $sql = "INSERT INTO task_assignment (staff_id, admin_id, task_date, task_shiftTime, task_location, task_activity)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissss", $staff_id, $admin_id, $task_date, $task_shiftTime, $task_location, $task_activity);

    if ($stmt->execute()) {
        // Update the booking to assign the staff
        $stmt = $conn->prepare("UPDATE bookings SET staff_id = ? WHERE booking_id = ?");
        $stmt->bind_param("ii", $staff_id, $booking_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Schedule added successfully!";
            header("Location: add_schedule.php?success=1");
        } else {
            $_SESSION['error'] = "Failed to update booking: " . $stmt->error;
            header("Location: add_schedule.php?error=1");
        }
    } else {
        $_SESSION['error'] = "Failed to add schedule: " . $stmt->error;
        header("Location: add_schedule.php?error=1");
    }
    exit();
}
?>
