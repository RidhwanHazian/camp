<?php
require_once 'confg.php';
require_once 'notifications_function.php';

// Get all upcoming confirmed bookings
$stmt = $conn->prepare("
    SELECT b.booking_id, b.user_id, b.arrival_date 
    FROM bookings b
    WHERE b.arrival_date >= CURDATE()
    AND b.status = 'confirmed'
    AND NOT EXISTS (
        -- Check if we already sent a reminder today
        SELECT 1 FROM notifications n 
        WHERE n.user_id = b.user_id 
        AND n.type = 'reminder'
        AND DATE(n.notify_date) = CURDATE()
    )
");

$stmt->execute();
$upcoming_camps = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Send notifications for each upcoming camp
foreach ($upcoming_camps as $camp) {
    $arrival = new DateTime($camp['arrival_date']);
    $today = new DateTime();
    $days_until = $today->diff($arrival)->days;
    
    // Create reminders at specific intervals
    if ($days_until == 0 || // Day of camp
        $days_until == 1 || // Day before camp
        $days_until == 3 || // 3 days before
        $days_until == 7) { // Week before
        createReminderNotification($camp['user_id'], $camp['booking_id']);
    }
}

// Log the check
error_log("Checked for upcoming camps at " . date('Y-m-d H:i:s') . ". Found " . count($upcoming_camps) . " bookings.");
?>