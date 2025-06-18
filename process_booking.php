<?php
session_start();                    // Start session after enabling error reporting
include 'db_connection.php';
include 'session_check.php';        // Load session check functions
checkAdminSession(); 
require_once 'notifications_function.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please log in to make a booking']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get form data
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

        // Validate dates
        if (!$arrival_date || !$departure_date) {
            echo json_encode(['success' => false, 'error' => 'Please select both arrival and departure dates']);
            exit();
        }

        // Define package prices (using only weekend prices)
        $package_prices = [
            1 => [ // Package A
                'adult' => 180,
                'child' => 160
            ],
            2 => [ // Package B
                'adult' => 180,
                'child' => 160
            ],
            3 => [ // Package C
                'adult' => 180,
                'child' => 160
            ],
            4 => [ // Package D
                'adult' => 130,
                'child' => 110
            ],
            5 => [ // Package E
                'adult' => 120,
                'child' => 100
            ]
        ];

        // Validate package
        if (!isset($package_prices[$package_id])) {
            echo json_encode(['success' => false, 'error' => 'Invalid package selected']);
            exit();
        }

        // Calculate number of days
        $datetime1 = new DateTime($arrival_date);
        $datetime2 = new DateTime($departure_date);
        $interval = $datetime1->diff($datetime2);
        $num_days = $interval->days + 1; // Including both arrival and departure days

        // Validate dates
        if ($datetime1 > $datetime2) {
            echo json_encode(['success' => false, 'error' => 'Departure date must be after arrival date']);
            exit();
        }

        // Calculate total price using weekend rates for all days
        $adult_cost = $num_adults * $package_prices[$package_id]['adult'];
        $child_cost = $num_children * $package_prices[$package_id]['child'];
        $total_price = ($adult_cost + $child_cost) * $num_days;

        // Check if dates are already booked
        $check_stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM bookings 
            WHERE (arrival_date BETWEEN ? AND ? OR departure_date BETWEEN ? AND ?)
            AND status != 'cancelled'
        ");
        $check_stmt->execute([$arrival_date, $departure_date, $arrival_date, $departure_date]);
        
        if ($check_stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'error' => 'Some dates in your selected range are already booked. Please choose different dates.']);
            exit();
        }

        // Begin transaction
        $conn->beginTransaction();

        // Insert booking into database
        $stmt = $conn->prepare("
            INSERT INTO bookings 
            (user_id, full_name, email, phone_no, num_adults, num_children, 
             package_id, arrival_date, departure_date, status, total_price) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $user_id,
            $full_name,
            $email,
            $phone,
            $num_adults,
            $num_children,
            $package_id,
            $arrival_date,
            $departure_date,
            $status,
            $total_price
        ]);

        $booking_id = $conn->lastInsertId();

        // Create booking notification
        createBookingNotification($user_id, $booking_id);

        // Create reminder notification if the camp is within 7 days
        $days_until_camp = $datetime1->diff(new DateTime())->days;
        if ($days_until_camp <= 7) {
            createReminderNotification($user_id, $booking_id);
        }

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true, 
            'message' => 'Booking submitted successfully!',
            'booking_id' => $booking_id
        ]);

    } catch(PDOException $e) {
        // Rollback transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Database Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Booking failed: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>