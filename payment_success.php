<?php
session_start();
require_once 'confg.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
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
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - TasikBiruCamps</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        html {
            scroll-behavior: smooth;
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

        .success-container {
            max-width: 800px;
            width: 100%;
            background: white;
            padding: 3rem 2rem;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .success-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .success-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: black;
        }

        .success-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: black;
            font-weight: bold;
        }

        .success-header p {
            color: black;
            font-size: 1.25rem;
            font-weight: normal;
        }

        .details-section {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 15px;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #dee2e6;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: black;
            font-size: 1.1rem;
            font-weight: normal;
        }

        .detail-value {
            color: black;
            font-size: 1.1rem;
            font-weight: normal;
            text-align: right;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #28a745;
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #28a745;
            color: #28a745;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        @media print {
            body {
                background: white;
                padding: 20px;
            }

            .main-container {
                background: none;
                padding: 0;
                min-height: 0;
            }

            .success-container {
                box-shadow: none;
                padding: 0;
                margin: 0;
            }

            .success-header {
                margin-bottom: 2rem;
            }

            .details-section {
                border: 1px solid #dee2e6;
                margin: 1.5rem 0;
                page-break-inside: avoid;
            }

            .detail-row {
                padding: 1rem 1.5rem;
                page-break-inside: avoid;
            }

            .detail-label,
            .detail-value {
                color: black;
            }

            /* Hide all header elements and navigation */
            header,
            nav,
            .header,
            .action-buttons,
            .btn {
                display: none !important;
                height: 0 !important;
                visibility: hidden !important;
                position: absolute !important;
                width: 0 !important;
                overflow: hidden !important;
            }

            /* Reset any fixed heights that might cause spacing issues */
            html, body {
                min-height: 0 !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Ensure clean page breaks */
            .success-container {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            * {
                color: black !important;
                background: white !important;
            }
        }

        @media (max-width: 768px) {
            .success-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="main-container">
    <div class="success-container">
        <div class="success-header">
            <i class="fas fa-check success-icon"></i>
            <h1>Payment Successful!</h1>
            <p>Your booking has been confirmed and payment has been processed.</p>
        </div>

        <div class="details-section">
            <div class="detail-row">
                <span class="detail-label">Booking ID</span>
                <span class="detail-value">#<?php echo $booking_id; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Package</span>
                <span class="detail-value"><?php echo htmlspecialchars($booking['package_name']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Stay Duration</span>
                <span class="detail-value">
                    <?php echo $start_date->format('d M Y') . ' - ' . $end_date->format('d M Y'); ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Number of Guests</span>
                <span class="detail-value">
                    <?php echo $booking['num_adults']; ?> Adults<?php if ($booking['num_children'] > 0): ?>, <?php echo $booking['num_children']; ?> Children<?php endif; ?>
                </span>
            </div>
        </div>

        <div class="details-section">
            <div class="detail-row">
                <span class="detail-label">Payment Method</span>
                <span class="detail-value">
                    <?php if ($booking['payment_method'] === 'card'): ?>
                        Credit/Debit Card (ending in <?php echo $payment_details['card_number'] ?? '****'; ?>)
                    <?php else: ?>
                        Bank Transfer (<?php echo htmlspecialchars($payment_details['bank_name'] ?? 'N/A'); ?>)
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Date</span>
                <span class="detail-value"><?php echo $payment_date->format('d M Y, h:i A'); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Amount Paid</span>
                <span class="detail-value">RM<?php echo number_format($booking['amount'], 2); ?></span>
            </div>
        </div>

        <div class="action-buttons">
            <a href="javascript:window.print()" class="btn btn-outline">
                <i class="fas fa-print"></i> Print Receipt
            </a>
            <a href="my_bookings.php" class="btn btn-primary">
                <i class="fas fa-list"></i> View My Bookings
            </a>
        </div>
    </div>
</div>

</body>
</html>