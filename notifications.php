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
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5ede3 0%, #bca48a 100%) !important;
            background-attachment: fixed;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            position: relative;
            overflow-x: hidden;
        }
        .main-container {
            min-height: calc(100vh - 60px);
            padding: 2rem 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        .notifications-container {
            max-width: 800px;
            width: 100%;
            background: rgba(255, 255, 255, 0.92);
            padding: 2rem;
            border-radius: 32px;
            box-shadow: 0 8px 32px rgba(255,182,193,0.13), 0 2px 8px rgba(140,109,82,0.10);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 2.5px solid #ffe5ec;
            margin-top: 2rem;
        }
        .notifications-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .notifications-header h1 {
            font-size: 2.2rem;
            color: #e29578;
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
            font-family: 'Baloo 2', 'Segoe UI', Arial, sans-serif;
        }
        .notifications-header p {
            color: #bca48a;
            font-size: 1.1rem;
        }
        .notification-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #ffe5ec;
            padding-bottom: 1rem;
            justify-content: center;
        }
        .tab-button {
            padding: 0.5rem 1.2rem;
            border: none;
            background: none;
            color: #e29578;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 18px 18px 0 0;
            transition: background 0.2s, color 0.2s;
            cursor: pointer;
            font-family: 'Baloo 2', 'Segoe UI', Arial, sans-serif;
        }
        .tab-button.active, .tab-button:hover {
            background: #ffe5ec;
            color: #b5838d;
        }
        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .notification-item {
            background: #f5ede3 !important;
            border-radius: 28px;
            box-shadow: 0 8px 32px rgba(140,109,82,0.18), 0 2px 8px rgba(140,109,82,0.13);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: flex-start;
            gap: 1.2rem;
            border-left: 8px solid #ffd6e0;
            position: relative;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .notification-item:hover {
            box-shadow: 0 8px 32px rgba(255,182,193,0.18);
            transform: translateY(-3px) scale(1.01);
        }
        .notification-icon {
            font-size: 2.2rem;
            color: #e29578;
            margin-top: 2px;
            position: relative;
        }
        .cute-star {
            position: absolute;
            top: -10px;
            right: -12px;
            font-size: 1.1rem;
            color: #ffd166;
            filter: drop-shadow(0 1px 2px #fffbe6);
        }
        .notification-content {
            flex: 1;
        }
        .notification-title {
            font-weight: bold;
            color: #b5838d;
            font-size: 1.13rem;
            margin-bottom: 0.2rem;
            font-family: 'Baloo 2', 'Segoe UI', Arial, sans-serif;
        }
        .notification-date {
            color: #bca48a;
            font-size: 0.98rem;
            margin-left: 0.5rem;
        }
        .notification-message {
            color: #6d6875;
            font-size: 1.05rem;
            margin-top: 0.2rem;
        }
        .notification-actions {
            margin-top: 0.7rem;
        }
        .notification-action {
            color: #6ecbff;
            text-decoration: none;
            font-size: 1.02rem;
            margin-right: 1.2rem;
            transition: color 0.2s;
            font-weight: 500;
        }
        .notification-action:hover {
            color: #4e9cff;
            text-decoration: underline;
        }
        /* Decorative SVGs */
        .bg-svg {
            position: absolute;
            z-index: 1;
            opacity: 0.10;
            pointer-events: none;
        }
        .bg-svg.topleft {
            top: 0;
            left: 0;
            width: 180px;
            height: 180px;
        }
        .bg-svg.bottomright {
            bottom: 0;
            right: 0;
            width: 200px;
            height: 200px;
        }
        .bg-svg.cloud {
            top: 40px;
            left: 50%;
            transform: translateX(-50%);
            width: 220px;
            height: 60px;
            opacity: 0.13;
        }
        @media (max-width: 700px) {
            .notifications-container { padding: 1rem 0.2rem; }
            .bg-svg.topleft, .bg-svg.bottomright { width: 100px; height: 100px; }
        }
    </style>
    <!-- Decorative SVGs -->
    <svg class="bg-svg topleft" viewBox="0 0 200 200" fill="none"><ellipse cx="100" cy="100" rx="100" ry="100" fill="#ffd6e0"/></svg>
    <svg class="bg-svg bottomright" viewBox="0 0 200 200" fill="none"><rect x="0" y="0" width="200" height="200" rx="60" fill="#b5838d"/></svg>
    <svg class="bg-svg cloud" viewBox="0 0 220 60" fill="none"><ellipse cx="60" cy="30" rx="60" ry="30" fill="#fff"/><ellipse cx="160" cy="30" rx="50" ry="25" fill="#fff"/></svg>
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
                    <div class="notification-item <?php echo $camp['priority']; ?>">
                        <div class="notification-icon">
                            <i class="fas fa-campground"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title"><?php echo htmlspecialchars($camp['package_name']); ?></div>
                            <div class="notification-date"><?php echo formatDate($camp['arrival_date']); ?></div>
                            <div class="notification-message"><?php echo htmlspecialchars($camp['message']); ?></div>
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
                        <div class="notification-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">Payment Received</div>
                            <div class="notification-date"><?php echo formatDate($payment['payment_date']); ?></div>
                            <div class="notification-message">Payment of RM<?php echo number_format($payment['amount'], 2); ?> has been processed.</div>
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
                notification.style.display = notification.classList.contains('high') || 
                                          notification.classList.contains('medium') || 
                                          notification.classList.contains('low') ? 'block' : 'none';
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
