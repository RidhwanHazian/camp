<?php
include 'session_check.php';
checkCustomerSession();

require_once 'db_connection.php';

// Debug: Check if connected to database
if (!$conn) {
    die("Database connection failed");
}

$user_id = $_SESSION['customer_id']; // Use customer_id

// Debug: Print user ID
echo "<!-- Debug: User ID = " . $user_id . " -->";
// Get all relevant information for the user
try {
    // Get recent bookings
    $booking_stmt = $conn->prepare("
        SELECT booking_id, arrival_date, status, total_price
        FROM bookings 
        WHERE user_id = ?
        ORDER BY booking_id DESC
        LIMIT 10
    ");
    $booking_stmt->bind_param("i", $user_id);
    $booking_stmt->execute();
    $booking_result = $booking_stmt->get_result();
    $bookings = [];
    while ($row = $booking_result->fetch_assoc()) {
        $bookings[] = $row;
    }

    // Debug: Print number of bookings found
    echo "<!-- Debug: Found " . count($bookings) . " bookings -->";

    // Get recent payments
    $payment_stmt = $conn->prepare("
        SELECT p.*, b.booking_id 
        FROM payments p
        JOIN bookings b ON p.booking_id = b.booking_id
        WHERE b.user_id = ?
        ORDER BY p.payment_date DESC
        LIMIT 10
    ");
    $payment_stmt->bind_param("i", $user_id);
    $payment_stmt->execute();
    $payment_result = $payment_stmt->get_result();
    $payments = [];
    while ($row = $payment_result->fetch_assoc()) {
        $payments[] = $row;
    }

    // Debug: Print number of payments found
    echo "<!-- Debug: Found " . count($payments) . " payments -->";

    // Get ALL upcoming camps (no date restriction)
    $upcoming_stmt = $conn->prepare("
        SELECT b.booking_id, b.arrival_date, b.departure_date, b.full_name, 
               b.total_price, b.status, p.package_name
        FROM bookings b
        JOIN packages p ON b.package_id = p.package_id
        WHERE b.user_id = ? 
        AND b.arrival_date >= CURDATE()
        ORDER BY b.arrival_date ASC
    ");
    $upcoming_stmt->bind_param("i", $user_id);
    $upcoming_stmt->execute();
    $upcoming_result = $upcoming_stmt->get_result();
    $upcoming_camps = [];
    while ($row = $upcoming_result->fetch_assoc()) {
        $upcoming_camps[] = $row;
    }

    // Debug: Print number of upcoming camps found
    echo "<!-- Debug: Found " . count($upcoming_camps) . " upcoming camps -->";

    // Format the notification messages
    foreach ($upcoming_camps as &$camp) {
        $arrival = new DateTime($camp['arrival_date']);
        $today = new DateTime();
        $days_until = $today->diff($arrival)->days;
        
        if ($days_until == 0) {
            $camp['message'] = "Your camp starts today!";
            $camp['priority'] = 'high';
        } else if ($days_until == 1) {
            $camp['message'] = "Your camp starts tomorrow!";
            $camp['priority'] = 'high';
        } else if ($days_until <= 7) {
            $camp['message'] = "Your camp starts in {$days_until} days";
            $camp['priority'] = 'medium';
        } else {
            $camp['message'] = "Your camp starts in {$days_until} days";
            $camp['priority'] = 'low';
        }
    }

} catch(Exception $e) { // Changed to generic Exception for mysqli errors
    error_log("Error fetching notifications: " . $e->getMessage());
    echo "<!-- Debug: Database error: " . htmlspecialchars($e->getMessage()) . " -->";
    $error = "Unable to load notifications";
}

// Function to format date
function formatDate($date) {
    $now = new DateTime();
    $date = new DateTime($date);
    $diff = $now->diff($date);

    if ($diff->d == 0) {
        if ($diff->h == 0) {
            if ($diff->i == 0) {
                return "Just now";
            }
            return $diff->i . " minute" . ($diff->i > 1 ? "s" : "") . " ago";
        }
        return "Today at " . $date->format('g:i A');
    }
    if ($diff->d == 1) {
        return "Yesterday at " . $date->format('g:i A');
    }
    if ($diff->d < 7) {
        return $diff->d . " days ago";
    }
    return $date->format('M j, Y');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TasikBiruCamps - Notifications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            min-height: 100vh;
        }

        .main-container {
            background-image: url('campback.jpg');
            background-size: cover;
            background-position: center;
            min-height: calc(100vh - 60px);
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .notifications-container {
            max-width: 800px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .notifications-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .notifications-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .notifications-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .notification-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 1rem;
        }

        .tab-button {
            padding: 0.5rem 1rem;
            border: none;
            background: none;
            color: #666;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .tab-button.active {
            color: #28a745;
            font-weight: 600;
        }

        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: -1rem;
            left: 0;
            width: 100%;
            height: 2px;
            background: #28a745;
        }

        .notification-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .notification-item {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            border-left: 4px solid #ddd;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s ease;
        }

        .notification-item:hover {
            transform: translateX(5px);
        }

        .notification-item.high-priority {
            border-left-color: #dc3545;
        }

        .notification-item.medium-priority {
            border-left-color: #ffc107;
        }

        .notification-item.low-priority {
            border-left-color: #28a745;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .notification-title {
            font-weight: 600;
            color: #333;
        }

        .notification-date {
            color: #666;
            font-size: 0.9rem;
        }

        .notification-content {
            color: #444;
            line-height: 1.4;
        }

        .notification-actions {
            margin-top: 0.5rem;
            display: flex;
            gap: 1rem;
        }

        .notification-action {
            color: #28a745;
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .notification-action:hover {
            text-decoration: underline;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #666;
        }

        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            .notifications-container {
                padding: 1.5rem;
            }

            .notifications-header h1 {
                font-size: 2rem;
            }

            .notification-tabs {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="main-container">
    <div class="notifications-container">
        <div class="notifications-header">
            <h1>Notifications</h1>
            <p>Stay updated with your camping activities</p>
        </div>

        <div class="notification-tabs">
            <button class="tab-button active" onclick="showTab('all')">All</button>
            <button class="tab-button" onclick="showTab('upcoming')">Upcoming Camps</button>
            <button class="tab-button" onclick="showTab('bookings')">Bookings</button>
            <button class="tab-button" onclick="showTab('payments')">Payments</button>
        </div>

        <div class="notification-list" id="allNotifications">
            <?php if (empty($upcoming_camps) && empty($bookings) && empty($payments)): ?>
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <p>No notifications to display</p>
                </div>
            <?php else: ?>
                <?php foreach ($upcoming_camps as $camp): ?>
                    <div class="notification-item <?php echo $camp['priority']; ?>-priority">
                        <div class="notification-header">
                            <span class="notification-title"><?php echo htmlspecialchars($camp['package_name']); ?></span>
                            <span class="notification-date"><?php echo formatDate($camp['arrival_date']); ?></span>
                        </div>
                        <div class="notification-content">
                            <?php echo htmlspecialchars($camp['message']); ?>
                        </div>
                        <div class="notification-actions">
                            <a href="my_bookings.php?id=<?php echo $camp['booking_id']; ?>" class="notification-action">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php foreach ($payments as $payment): ?>
                    <div class="notification-item">
                        <div class="notification-header">
                            <span class="notification-title">Payment Received</span>
                            <span class="notification-date"><?php echo formatDate($payment['payment_date']); ?></span>
                        </div>
                        <div class="notification-content">
                            Payment of RM<?php echo number_format($payment['amount'], 2); ?> has been processed.
                        </div>
                        <div class="notification-actions">
                            <a href="payment_success.php?id=<?php echo $payment['payment_id']; ?>" class="notification-action">
                                <i class="fas fa-receipt"></i> View Receipt
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Update active tab button
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });
    event.currentTarget.classList.add('active');

    // Filter notifications based on tab
    const notifications = document.querySelectorAll('.notification-item');
    notifications.forEach(notification => {
        switch(tabName) {
            case 'upcoming':
                notification.style.display = notification.classList.contains('high-priority') || 
                                          notification.classList.contains('medium-priority') || 
                                          notification.classList.contains('low-priority') ? 'block' : 'none';
                break;
            case 'bookings':
                notification.style.display = notification.querySelector('.notification-title').textContent.includes('Package') ? 'block' : 'none';
                break;
            case 'payments':
                notification.style.display = notification.querySelector('.notification-title').textContent.includes('Payment') ? 'block' : 'none';
                break;
            default:
                notification.style.display = 'block';
        }
    });

    // Show empty state if no notifications in current tab
    const visibleNotifications = Array.from(notifications).filter(n => n.style.display === 'block');
    const emptyState = document.querySelector('.empty-state');
    if (emptyState) {
        emptyState.style.display = visibleNotifications.length === 0 ? 'block' : 'none';
    }
}

// Initialize tooltips if needed
document.addEventListener('DOMContentLoaded', function() {
    // Add any initialization code here
});
</script>

</body>
</html>