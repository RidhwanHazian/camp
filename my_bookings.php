<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
checkCustomerSession();

// Get all bookings for the user
$stmt = $conn->prepare("
    SELECT b.*, p.package_name, p.description, p.duration,
           pp.adult_price as package_price,
           pp.child_price
    FROM bookings b 
    LEFT JOIN packages p ON b.package_id = p.package_id 
    LEFT JOIN package_prices pp ON p.package_id = pp.package_id
    WHERE b.user_id = ? 
    ORDER BY b.booking_id DESC 
");
$stmt->bind_param("i", $_SESSION['customer_id']);
$stmt->execute();
$result = $stmt->get_result();
$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

if (empty($bookings)) {
    header("Location: makeBooking.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - TasikBiruCamps</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            position: relative;
            min-height: 100vh;
            font-family: 'Segoe UI', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: -1;
            background: url('campback.jpg') center/cover no-repeat;
            filter: blur(7px) brightness(0.65);
        }
        .bookings-container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 1rem;
        }
        .booking-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .tab-button {
            padding: 0.8rem 1.5rem;
            border: none;
            background: #f8f4e9;
            border-radius: 10px 10px 0 0;
            font-weight: bold;
            color: #8c6d52;
            cursor: pointer;
            font-size: 1.1em;
            transition: background 0.2s, color 0.2s;
        }
        .tab-button.active {
            background: #bca48a;
            color: #fff;
        }
        .bookings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 2rem;
        }
        .booking-card {
            background: rgba(255, 255, 255, 0.80);
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(140, 109, 82, 0.13), 0 2px 8px rgba(140, 109, 82, 0.10);
            padding: 1.7rem 2rem 1.3rem 2rem;
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
            position: relative;
            transition: box-shadow 0.2s, transform 0.2s, background 0.2s;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1.5px solid rgba(140, 109, 82, 0.13);
        }
        .booking-card:hover {
            box-shadow: 0 12px 36px rgba(140, 109, 82, 0.18), 0 4px 16px rgba(140, 109, 82, 0.13);
            transform: translateY(-4px) scale(1.018);
            background: rgba(255, 255, 255, 0.92);
        }
        .booking-card .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .booking-card .header .booking-id {
            font-weight: bold;
            font-size: 1.18em;
            color: #3a2a16;
            letter-spacing: 0.5px;
        }
        .booking-card .header .status {
            background: #fff7d6;
            color: #bfa100;
            padding: 0.32em 1.1em;
            border-radius: 16px;
            font-size: 0.98em;
            font-weight: bold;
            box-shadow: 0 1px 4px rgba(191,161,0,0.07);
            letter-spacing: 0.5px;
            margin-left: 10px;
        }
        .booking-card .row {
            display: flex;
            gap: 1.2rem;
            font-size: 1.05em;
        }
        .booking-card .row .label {
            color: #bca48a;
            min-width: 90px;
            font-weight: 600;
        }
        .booking-card .row .value {
            color: #3a2a16;
            font-weight: 600;
        }
        .booking-card .actions {
            margin-top: 1.1rem;
        }
        .booking-card .actions .btn {
            background: linear-gradient(90deg, #6edc7b 0%, #4ecb5f 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 0.7em 1.7em;
            font-size: 1.08em;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(110,220,123,0.10);
            transition: background 0.2s, box-shadow 0.2s, transform 0.15s;
        }
        .booking-card .actions .btn:hover {
            background: linear-gradient(90deg, #4ecb5f 0%, #6edc7b 100%);
            box-shadow: 0 4px 16px rgba(110,220,123,0.18);
            transform: scale(1.04);
        }
        .btn-view {
            background: linear-gradient(90deg, #4e9cff 0%, #6ecbff 100%) !important;
        }
        .btn-view:hover {
            background: linear-gradient(90deg, #6ecbff 0%, #4e9cff 100%) !important;
        }
        @media (max-width: 800px) {
            .bookings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="bookings-container">
    <div class="booking-tabs">
        <button class="tab-button active" data-tab="all" onclick="filterBookings('all', this)">All Bookings</button>
        <button class="tab-button" data-tab="upcoming" onclick="filterBookings('upcoming', this)">Upcoming</button>
        <button class="tab-button" data-tab="past" onclick="filterBookings('past', this)">Past</button>
    </div>
    <div class="bookings-grid" id="bookingsGrid">
    <?php
    $today = new DateTime();
    foreach ($bookings as $booking):
        $arrival = new DateTime($booking['arrival_date']);
        $departure = new DateTime($booking['departure_date']);
        $isPast = $departure < $today;
        $isUpcoming = $arrival >= $today;
        $dataType = $isPast ? 'past' : 'upcoming';

        // Check if payment exists for this booking
        $payment_exists = false;
        $payment_stmt = $conn->prepare("SELECT payment_id FROM payments WHERE booking_id = ?");
        $payment_stmt->bind_param("i", $booking['booking_id']);
        $payment_stmt->execute();
        $payment_result = $payment_stmt->get_result();
        if ($payment_result && $payment_result->fetch_assoc()) {
            $payment_exists = true;
        }
    ?>
        <div class="booking-card" data-type="<?php echo $dataType; ?>">
            <div class="header">
                <span class="booking-id">Booking #<?php echo $booking['booking_id']; ?></span>
                <span class="status">
                    <?php
                    if ($booking['status'] == 'paid' && !$payment_exists) {
                        echo 'Pending';
                    } else {
                        echo ucfirst($booking['status']);
                    }
                    ?>
                </span>
            </div>
            <div class="row">
                <span class="label">Package</span>
                <span class="value"><?php echo htmlspecialchars($booking['package_name']); ?></span>
            </div>
            <div class="row">
                <span class="label">Dates</span>
                <span class="value"><?php echo date('d M Y', strtotime($booking['arrival_date'])) . ' - ' . date('d M Y', strtotime($booking['departure_date'])); ?></span>
            </div>
            <div class="row">
                <span class="label">Guests</span>
                <span class="value"><?php echo $booking['num_adults'] . ' Adults' . ($booking['num_children'] ? ' , ' . $booking['num_children'] . ' Children' : ''); ?></span>
            </div>
            <div class="row">
                <span class="label">Total Price</span>
                <span class="value">RM<?php echo number_format($booking['total_price'], 2); ?></span>
            </div>
            <div class="actions">
                <?php
                // Calculate outstanding amount for not complete bookings
                $outstanding_amount = null;
                if ($booking['status'] == 'not complete') {
                    $sum_stmt = $conn->prepare("SELECT SUM(amount) as total_paid FROM payments WHERE booking_id = ?");
                    $sum_stmt->bind_param("i", $booking['booking_id']);
                    $sum_stmt->execute();
                    $sum_result = $sum_stmt->get_result();
                    $total_paid = 0;
                    if ($sum_row = $sum_result->fetch_assoc()) {
                        $total_paid = floatval($sum_row['total_paid']);
                    }
                    $outstanding_amount = floatval($booking['total_price']) - $total_paid;
                    if ($outstanding_amount < 0) $outstanding_amount = 0;
                }
                ?>
                <?php if ($booking['status'] == 'not complete' && $outstanding_amount > 0): ?>
                    <a class="btn" href="payment.php?booking_id=<?php echo $booking['booking_id']; ?>">Make Payment</a>
                    <?php if ($payment_exists): ?>
                        <a class="btn btn-view" style="background: linear-gradient(90deg, #4e9cff 0%, #6ecbff 100%);" href="payment_success.php?booking_id=<?php echo $booking['booking_id']; ?>">View Payment</a>
                    <?php endif; ?>
                <?php elseif ((($booking['status'] == 'paid' || $booking['status'] == 'confirmed' || $booking['status'] == 'complete') && $payment_exists)): ?>
                    <a class="btn btn-view" style="background: linear-gradient(90deg, #4e9cff 0%, #6ecbff 100%);" href="payment_success.php?booking_id=<?php echo $booking['booking_id']; ?>">View Payment</a>
                <?php elseif ($booking['status'] == 'pending' || ($booking['status'] == 'paid' && !$payment_exists)): ?>
                    <a class="btn" href="payment.php?booking_id=<?php echo $booking['booking_id']; ?>">Make Payment</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>
<script>
function filterBookings(type, btn) {
    // Update active tab
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });
    btn.classList.add('active');

    // Filter bookings
    document.querySelectorAll('.booking-card').forEach(card => {
        if (type === 'all' || card.dataset.type === type) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
</body>
</html>