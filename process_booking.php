<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
checkCustomerSession();

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
        header("Location: booking_form.php?error=Please+select+both+arrival+and+departure+dates");
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
            throw new Exception('Pricing not found for the selected package.');
        }

        // Slot check
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

        if ($slot_info && $slot_info['slot_limit'] !== null && $slot_info['slot_limit'] > 0 && $slot_info['booked_slots'] >= $slot_info['slot_limit']) {
            throw new Exception('Sorry, this slot is fully booked.');
        }

        // Total price calculation
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

        // Optional notification functions
        if (function_exists('createBookingNotification')) {
            createBookingNotification($user_id, $booking_id);
        }

        $days_until_camp = $datetime1->diff(new DateTime())->days;
        if ($days_until_camp <= 7 && function_exists('createReminderNotification')) {
            createReminderNotification($user_id, $booking_id);
        }

        mysqli_commit($conn);
        header("Location: my_bookings.php?success=1");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $errorMsg = urlencode("Booking failed: " . $e->getMessage());
        header("Location: booking_form.php?error=$errorMsg");
        exit();
    }
} else {
    header("Location: booking_form.php?error=Invalid+request+method");
    exit();
}
?>
