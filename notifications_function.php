<?php
require_once 'confg.php';

/**
 * Add a basic notification
 */
function addNotification($user_id, $title, $message, $type, $icon) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, title, message, type, icon, notify_date)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([$user_id, $title, $message, $type, $icon]);
    } catch (PDOException $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get relative time string
 */
function getTimeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d == 0) {
        if ($diff->h == 0) {
            if ($diff->i == 0) {
                return "Just now";
            }
            return $diff->i . " minute" . ($diff->i > 1 ? "s" : "") . " ago";
        }
        return "Today at " . $ago->format('g:i A');
    }
    if ($diff->d == 1) {
        return "Yesterday at " . $ago->format('g:i A');
    }
    if ($diff->d < 7) {
        return $diff->d . " days ago";
    }
    return $ago->format('M j, Y');
}

/**
 * Mark notification as read
 */
function markNotificationAsRead($notification_id, $user_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET is_read = TRUE 
        WHERE notification_id = ? AND user_id = ?
    ");
    
    return $stmt->execute([$notification_id, $user_id]);
}

/**
 * Get unread notification count
 */
function getUnreadCount($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE user_id = ? AND is_read = FALSE
    ");
    
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['count'];
}

/**
 * Create a booking notification
 */
function createBookingNotification($user_id, $booking_id) {
    global $conn;
    try {
        // Get booking details
        $stmt = $conn->prepare("
            SELECT b.*, p.package_name 
            FROM bookings b 
            JOIN packages p ON b.package_id = p.package_id 
            WHERE b.booking_id = ?
        ");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($booking) {
            $title = "Booking Confirmation";
            $message = "Your booking for {$booking['package_name']} has been received. " .
                      "Arrival: {$booking['arrival_date']}, Departure: {$booking['departure_date']}. " .
                      "Total amount: RM{$booking['total_price']}";
            $icon = "fa-check-circle"; // Font Awesome icon

            return addNotification($user_id, $title, $message, 'success', $icon);
        }
    } catch(PDOException $e) {
        error_log("Notification Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a payment notification
 */
function createPaymentNotification($user_id, $amount) {
    global $conn;
    try {
        $title = "Payment Confirmation";
        $message = "Payment of RM{$amount} has been successfully processed.";
        $icon = "fa-credit-card"; // Font Awesome icon

        return addNotification($user_id, $title, $message, 'payment', $icon);
    } catch(PDOException $e) {
        error_log("Notification Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a reminder notification
 */
function createReminderNotification($user_id, $booking_id) {
    global $conn;
    try {
        // Get booking details
        $stmt = $conn->prepare("
            SELECT b.*, p.package_name 
            FROM bookings b 
            JOIN packages p ON b.package_id = p.package_id 
            WHERE b.booking_id = ?
        ");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($booking) {
            $arrival = new DateTime($booking['arrival_date']);
            $today = new DateTime();
            $days_until = $today->diff($arrival)->days;
            
            // Create appropriate message based on timing
            if ($days_until == 0) {
                $title = "Your camp starts today!";
                $message = "Get ready for your {$booking['package_name']} adventure! Don't forget your essentials!";
            } else if ($days_until == 1) {
                $title = "Your camp starts tomorrow!";
                $message = "Time to pack for your {$booking['package_name']} camp! Check your camping essentials list.";
            } else {
                $title = "Upcoming Camp Reminder";
                $message = "Your {$booking['package_name']} camp starts in {$days_until} days on {$booking['arrival_date']}. " .
                          "Start preparing your camping gear!";
            }
            
            $icon = "fa-bell"; // Font Awesome icon

            return addNotification($user_id, $title, $message, 'reminder', $icon);
        }
    } catch(PDOException $e) {
        error_log("Notification Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a feedback request notification
 */
function createFeedbackRequestNotification($user_id, $booking_id) {
    global $conn;
    try {
        // Get booking details
        $stmt = $conn->prepare("
            SELECT b.*, p.package_name 
            FROM bookings b 
            JOIN packages p ON b.package_id = p.package_id 
            WHERE b.booking_id = ?
        ");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($booking) {
            $title = "Share Your Experience";
            $message = "How was your {$booking['package_name']} experience? " .
                      "Please take a moment to share your feedback and help us improve!";
            $icon = "fa-star"; // Font Awesome icon

            return addNotification($user_id, $title, $message, 'feedback', $icon);
        }
    } catch(PDOException $e) {
        error_log("Notification Error: " . $e->getMessage());
        return false;
    }
}
?>