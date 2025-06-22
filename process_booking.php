<?php
session_start();
require_once 'db_connection.php';
require_once 'notifications_function.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in - fix session variable name
if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please log in to make a booking']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $full_name = $_POST['fullName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $num_adults = $_POST['adults'];
    $num_children = $_POST['children'];
    $package_id = (int)$_POST['package'];
    $arrival_date = $_POST['arriveDate'];
    $departure_date = $_POST['departDate'];
    $status = 'pending';

    if (!$arrival_date || !$departure_date) {
        echo json_encode(['success' => false, 'error' => 'Please select both arrival and departure dates']);
        exit();
    }
    
    mysqli_begin_transaction($conn);

    try {
        // Fetch prices
        $price_sql = "SELECT adult_price, child_price FROM package_prices WHERE package_id = ?";
        $price_stmt = mysqli_prepare($conn, $price_sql);
        mysqli_stmt_bind_param($price_stmt, 'i', $package_id);
        mysqli_stmt_execute($price_stmt);
        $result = mysqli_stmt_get_result($price_stmt);
        $prices = mysqli_fetch_assoc($result);

        if (!$prices) {
            throw new Exception('Could not find pricing for the selected package.');
        }
        
        // Final Slot Check
        $slot_check_sql = "
            SELECT ps.slot_limit, 
                   (SELECT COUNT(*) FROM bookings b WHERE b.package_id = ? AND b.arrival_date = ?) as booked_slots
            FROM package_slots ps
            WHERE ps.package_id = ? AND ps.slot_date = ?
        ";
        $slot_stmt = mysqli_prepare($conn, $slot_check_sql);
        mysqli_stmt_bind_param($slot_stmt, 'isss', $package_id, $arrival_date, $package_id, $arrival_date);
        mysqli_stmt_execute($slot_stmt);
        $slot_result = mysqli_stmt_get_result($slot_stmt);
        $slot_info = mysqli_fetch_assoc($slot_result);

        // Debug: Log slot info
        error_log('Slot info: ' . print_r($slot_info, true));

        // Only block if slot_limit is set and reached; otherwise allow booking (unlimited)
        if ($slot_info && $slot_info['slot_limit'] !== null && $slot_info['slot_limit'] > 0 && $slot_info['booked_slots'] >= $slot_info['slot_limit']) {
            throw new Exception('Sorry, the last available slot for this package and date has just been booked.');
        }

        // Calculate total price
        $datetime1 = new DateTime($arrival_date);
        $datetime2 = new DateTime($departure_date);
        $interval = $datetime1->diff($datetime2);
        $num_days = $interval->days + 1;
        $total_price = (($num_adults * $prices['adult_price']) + ($num_children * $prices['child_price'])) * $num_days;
        
        // Insert booking
        $insert_sql = "
            INSERT INTO bookings (user_id, full_name, email, phone_no, num_adults, num_children, package_id, arrival_date, departure_date, status, total_price) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $insert_stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($insert_stmt, 'isssiiisssd', $user_id, $full_name, $email, $phone, $num_adults, $num_children, $package_id, $arrival_date, $departure_date, $status, $total_price);
        mysqli_stmt_execute($insert_stmt);
        
        $booking_id = mysqli_insert_id($conn);

        // Notifications
        createBookingNotification($user_id, $booking_id);
        $days_until_camp = $datetime1->diff(new DateTime())->days;
        if ($days_until_camp <= 7) {
            createReminderNotification($user_id, $booking_id);
        }

        mysqli_commit($conn);
        header("Location: my_bookings.php?success=1");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Booking Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Booking failed: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
