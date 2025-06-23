<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// Get booking ID from URL
$booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : null;

if (!$booking_id) {
    header("Location: my_bookings.php");
    exit();
}

// Get booking and payment details
$stmt = $conn->prepare("
    SELECT b.*, p.package_name, p.description, p.duration,
           pp.adult_price as package_price,
           pp.child_price,
           pm.payment_method, pm.payment_date, pm.amount, pm.payment_details
    FROM bookings b 
    LEFT JOIN packages p ON b.package_id = p.package_id 
    LEFT JOIN package_prices pp ON p.package_id = pp.package_id
    LEFT JOIN payments pm ON b.booking_id = pm.booking_id
    WHERE b.booking_id = ? AND b.user_id = ?
");
$stmt->bind_param("ii", $booking_id, $_SESSION['customer_id']);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    header("Location: my_bookings.php");
    exit();
}

$payment_date = new DateTime($booking['payment_date']);
$start_date = new DateTime($booking['arrival_date']);
$end_date = new DateTime($booking['departure_date']);

// Safely decode payment details
$payment_details = [];
if (!empty($booking['payment_details'])) {
    $payment_details = json_decode($booking['payment_details'], true) ?? [];
}

$status_message = '';
if ($booking['status'] == 'not complete') {
    $status_message = 'Your payment is not complete. Please make another payment to complete your booking.';
} elseif ($booking['status'] == 'paid') {
    $status_message = '<span style="color:#ff9800;font-weight:600;font-size:1.05em;background:#fff3e0;padding:6px 16px;border-radius:8px;display:inline-block;">Your payment is received and is <u>waiting for staff verification</u>.</span>';
} elseif ($booking['status'] == 'confirmed' || $booking['status'] == 'complete') {
    $status_message = 'Your payment has been confirmed!';
} else {
    $status_message = 'Your booking status: ' . ucfirst($booking['status']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - TasikBiruCamps</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        .receipt-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            padding: 2.5rem 2.5rem 1.5rem 2.5rem;
            position: relative;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .camp-logo {
            font-size: 2.2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.2rem;
        }
        .receipt-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
        }
        .status-message {
            font-size: 1.1rem;
            color: #2980b9;
            margin-bottom: 1.2rem;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }
        .details-table th, .details-table td {
            text-align: left;
            padding: 8px 0;
            font-size: 1rem;
        }
        .details-table th {
            width: 40%;
            color: #888;
            font-weight: 500;
        }
        .details-table td {
            color: #222;
        }
        .thankyou {
            text-align: center;
            font-size: 1.15rem;
            margin: 2rem 0 1rem 0;
            color: #27ae60;
            font-weight: 600;
        }
        .footer {
            text-align: center;
            font-size: 0.95rem;
            color: #888;
            margin-top: 2rem;
            border-top: 1px dashed #ccc;
            padding-top: 1rem;
        }
        .paid-stamp {
            position: absolute;
            top: 30px;
            right: 30px;
            font-size: 1.2rem;
            color: #27ae60;
            border: 2px solid #27ae60;
            border-radius: 8px;
            padding: 0.3rem 1.2rem;
            font-weight: bold;
            transform: rotate(-8deg);
            opacity: 0.85;
        }
        @media print {
            body {
                background: #fff !important;
            }
            .receipt-container {
                box-shadow: none !important;
                margin: 0 !important;
                border-radius: 0 !important;
                padding: 1.5rem 0.5rem 1rem 0.5rem !important;
            }
            .paid-stamp {
                color: #222 !important;
                border-color: #222 !important;
            }
            .footer {
                color: #444 !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
<div class="receipt-container">
    <div class="receipt-header">
        <div class="camp-logo">TasikBiruCamps</div>
        <div class="receipt-title">Payment Receipt</div>
        <div class="status-message"><?php echo $status_message; ?></div>
    </div>
    <?php if ($booking['status'] == 'confirmed' || $booking['status'] == 'complete'): ?>
        <div class="paid-stamp">PAID</div>
    <?php endif; ?>
    <table class="details-table">
        <tr><th>Booking ID</th><td>#<?php echo $booking_id; ?></td></tr>
        <tr><th>Customer Name</th><td><?php echo htmlspecialchars($booking['full_name'] ?? ''); ?></td></tr>
        <tr><th>Package</th><td><?php echo htmlspecialchars($booking['package_name']); ?></td></tr>
        <tr><th>Stay Duration</th><td><?php echo $start_date->format('d M Y') . ' - ' . $end_date->format('d M Y'); ?></td></tr>
        <tr><th>Number of Guests</th><td><?php echo $booking['num_adults']; ?> Adults<?php if ($booking['num_children'] > 0): ?>, <?php echo $booking['num_children']; ?> Children<?php endif; ?></td></tr>
        <tr><th>Payment Method</th><td><?php if ($booking['payment_method'] === 'card'): ?>Credit/Debit Card (ending in <?php echo $payment_details['card_number'] ?? '****'; ?>)<?php else: ?>Bank Transfer (<?php echo htmlspecialchars($payment_details['bank_name'] ?? 'N/A'); ?>)<?php endif; ?></td></tr>
        <tr><th>Payment Date</th><td><?php echo $payment_date->format('d M Y, h:i A'); ?></td></tr>
        <tr><th>Total Amount Paid</th><td>RM<?php echo number_format($booking['amount'], 2); ?></td></tr>
    </table>
    <div class="thankyou">Thank you for your payment and for choosing TasikBiruCamps!</div>
    <div class="footer">
        TasikBiruCamps, Jalan Tasik Biru, 12345 Kampung Damai, Malaysia<br>
        Phone: 012-3456789 &nbsp;|&nbsp; Email: info@tasikbirucamps.com<br>
        <em>This is a computer-generated receipt. No signature is required.</em>
    </div>
    <div class="action-buttons no-print" style="text-align:center; margin-top:2rem;">
        <a href="javascript:window.print()" class="btn btn-outline">
            <i class="fas fa-print"></i> Print Receipt
        </a>
        <a href="my_bookings.php" class="btn btn-primary">
            <i class="fas fa-list"></i> View My Bookings
        </a>
    </div>
</div>
</body>
</html>
