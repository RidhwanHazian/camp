<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'confg.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


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

// Get the latest booking for display in the summary
$latest_booking = $bookings[0];

// Format dates for display
$start_date = new DateTime($latest_booking['arrival_date']);
$end_date = new DateTime($latest_booking['departure_date']);
$formatted_start = $start_date->format('d F Y');
$formatted_end = $end_date->format('d F Y');

// Calculate days for display
$interval = $start_date->diff($end_date);
$num_days = $interval->days + 1; // Including both start and end day

// Calculate total price
$adult_total = $latest_booking['package_price'] * $latest_booking['num_adults'];
$child_total = $latest_booking['child_price'] * $latest_booking['num_children'];
$total_price = $adult_total + $child_total;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - TasikBiruCamps</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        .main-container {
            background-image: url('campback.jpg');
            background-size: cover;
            background-position: center;
            min-height: calc(100vh - 60px); /* Adjusted for header height */
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .booking-summary {
            text-align: center;
            color: white;
            margin-bottom: 2rem;
        }

        .booking-summary h1 {
            font-size: 3rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .form-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr) 400px;
            gap: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .input-group {
            background: rgba(165, 142, 130, 0.7);
            padding: 1.5rem;
            border-radius: 15px;
            backdrop-filter: blur(5px);
            color: white;
        }

        .input-group i {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .input-group label {
            display: block;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .input-group .value {
            background: rgba(255, 255, 255, 0.9);
            padding: 0.8rem;
            border-radius: 8px;
            color: #333;
        }

        .summary-box {
            background: rgba(165, 142, 130, 0.8);
            padding: 2rem;
            border-radius: 15px;
            color: white;
            backdrop-filter: blur(5px);
            grid-column: 3;
            grid-row: 1 / span 3;
        }

        .date-range {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .date-range i {
            color: white;
        }

        .stay-info {
            margin: 1rem 0;
            padding: 1rem 0;
            border-top: 1px solid rgba(255,255,255,0.3);
            border-bottom: 1px solid rgba(255,255,255,0.3);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn {
            flex: 1;
            padding: 0.8rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }

        .btn-print {
            background: rgba(255,255,255,0.9);
            color: #333;
        }

        .btn-payment {
            background: #ff0000;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .bookings-container {
            max-width: 1200px;
            margin: 2rem auto;
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
            color: #6b4423;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .tab-button.active {
            background: #6b4423;
            color: white;
        }

        .booking-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .booking-id {
            font-weight: 600;
            color: #6b4423;
        }

        .booking-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .detail-group {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.3rem;
        }

        .detail-value {
            font-weight: 500;
            color: #333;
        }

        .booking-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .action-button {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .edit-button {
            background: #8b5e34;
            color: white;
        }

        .cancel-button {
            background: #dc3545;
            color: white;
        }

        .pay-button {
            background: #28a745;
            color: white;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="main-container">
    <div class="booking-summary">
        <h1>Booking Summary</h1>
    </div>

    <div class="form-container">
        <div class="input-group">
            <i class="fas fa-user"></i>
            <label>Full Name</label>
            <div class="value"><?php echo htmlspecialchars($latest_booking['full_name']); ?></div>
        </div>

        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <label>E-mail</label>
            <div class="value"><?php echo htmlspecialchars($latest_booking['email']); ?></div>
        </div>

        <div class="summary-box">
            <div class="date-range">
                <span><?php echo $formatted_start; ?></span>
                <i class="fas fa-arrow-right"></i>
                <span><?php echo $formatted_end; ?></span>
            </div>

            <div class="stay-info">
                <div><?php echo $num_days; ?>D<?php echo ($num_days-1); ?>N</div>
                <div>Total Guest: <?php echo $latest_booking['num_adults']; ?> Adults
                <?php if ($latest_booking['num_children'] > 0): ?>
                    and <?php echo $latest_booking['num_children']; ?> Children
                <?php endif; ?></div>
            </div>

            <div class="options">
                Options:
                <div class="price-item">
                    <span>1x - <?php echo htmlspecialchars($latest_booking['package_name']); ?></span>
                    <span>RM <?php echo number_format($total_price, 2); ?></span>
                </div>
            </div>

            <div class="total-section">
                <div class="total-price">
                    <span>TOTAL</span>
                    <span>RM<?php echo number_format($total_price, 2); ?></span>
                </div>
            </div>

            <div class="action-buttons">
                <button class="btn btn-print" onclick="printSummary()">Print</button>
                <a href="payment.php?booking_id=<?php echo $latest_booking['booking_id']; ?>" class="btn btn-payment">Payment</a>
            </div>
        </div>

        <div class="input-group">
            <i class="fas fa-phone"></i>
            <label>Phone</label>
            <div class="value"><?php echo htmlspecialchars($latest_booking['phone_no']); ?></div>
        </div>

        <div class="input-group">
            <i class="fas fa-users"></i>
            <label>Adult</label>
            <div class="value"><?php echo $latest_booking['num_adults']; ?></div>
        </div>

        <div class="input-group">
            <i class="fas fa-child"></i>
            <label>Children</label>
            <div class="value"><?php echo $latest_booking['num_children']; ?></div>
        </div>

        <div class="input-group">
            <i class="fas fa-campground"></i>
            <label>Package</label>
            <div class="value"><?php echo htmlspecialchars($latest_booking['package_name']); ?></div>
        </div>

        <div class="input-group">
            <i class="fas fa-calendar"></i>
            <label>Start Date</label>
            <div class="value"><?php echo $formatted_start; ?></div>
        </div>

        <div class="input-group">
            <i class="fas fa-calendar"></i>
            <label>End Date</label>
            <div class="value"><?php echo $formatted_end; ?></div>
        </div>
    </div>
</div>

<div class="bookings-container">
    <div class="booking-tabs">
        <button class="tab-button active" onclick="filterBookings('all')">All Bookings</button>
        <button class="tab-button" onclick="filterBookings('upcoming')">Upcoming</button>
        <button class="tab-button" onclick="filterBookings('past')">Past</button>
    </div>

    <div id="bookingsList">
        <?php foreach ($bookings as $booking):
            $arrival = new DateTime($booking['arrival_date']);
            $departure = new DateTime($booking['departure_date']);
            $today = new DateTime();
            $isPast = $departure < $today;
            $isUpcoming = $arrival >= $today;
            
            // Calculate total price for this booking
            $adult_total = $booking['package_price'] * $booking['num_adults'];
            $child_total = $booking['child_price'] * $booking['num_children'];
            $booking_total = $adult_total + $child_total;
            ?>
            <div class="booking-card" data-type="<?php echo $isPast ? 'past' : 'upcoming'; ?>">
                <div class="booking-header">
                    <span class="booking-id">Booking #<?php echo $booking['booking_id']; ?></span>
                    <span class="booking-status status-<?php echo strtolower($booking['status']); ?>">
                        <?php echo ucfirst($booking['status']); ?>
                    </span>
                </div>
                <div class="booking-details">
                    <div class="detail-group">
                        <span class="detail-label">Package</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking['package_name']); ?></span>
                    </div>
                    <div class="detail-group">
                        <span class="detail-label">Dates</span>
                        <span class="detail-value">
                            <?php echo $arrival->format('d M Y') . ' - ' . $departure->format('d M Y'); ?>
                        </span>
                    </div>
                    <div class="detail-group">
                        <span class="detail-label">Guests</span>
                        <span class="detail-value">
                            <?php echo $booking['num_adults']; ?> Adults
                            <?php if ($booking['num_children'] > 0): ?>
                                , <?php echo $booking['num_children']; ?> Children
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="detail-group">
                        <span class="detail-label">Total Price</span>
                        <span class="detail-value">RM<?php echo number_format($booking_total, 2); ?></span>
                    </div>
                </div>
                <?php if ($booking['status'] === 'pending' && !$isPast): ?>
                    <div class="booking-actions">
                        <button class="action-button pay-button" onclick="window.location.href='payment.php?booking_id=<?php echo $booking['booking_id']; ?>'">
                            Make Payment
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function filterBookings(type) {
    // Update active tab
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
        if (button.textContent.toLowerCase().includes(type)) {
            button.classList.add('active');
        }
    });

    // Filter bookings
    document.querySelectorAll('.booking-card').forEach(card => {
        if (type === 'all' || card.dataset.type === type) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });
}

function printSummary() {
    // Create new window content with only the essential summary info
    let printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Booking Summary</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    padding: 20px;
                    width: 300px;
                    margin: 20px auto;
                    background: #f5f5f5;
                }
                .summary-content {
                    background: white;
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .date-line {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 15px;
                    font-weight: bold;
                }
                .arrow {
                    margin: 0 10px;
                }
                .stay-info {
                    padding: 10px 0;
                    border-bottom: 1px solid #ddd;
                    margin-bottom: 15px;
                }
                .package-info {
                    margin: 15px 0;
                }
                .price-line {
                    display: flex;
                    justify-content: space-between;
                    margin: 5px 0;
                }
                .total-section {
                    background: #f5f5f5;
                    padding: 10px;
                    border-radius: 5px;
                    margin-top: 15px;
                }
                .total-price {
                    display: flex;
                    justify-content: space-between;
                    font-weight: bold;
                    font-size: 1.2em;
                }
            </style>
        </head>
        <body>
            <div class="summary-content">
                <div class="date-line">
                    <span><?php echo $formatted_start; ?></span>
                    <span class="arrow">→</span>
                    <span><?php echo $formatted_end; ?></span>
                </div>
                
                <div class="stay-info">
                    <div><?php echo $num_days; ?>D<?php echo ($num_days-1); ?>N</div>
            <div>Total Guest: <?php echo $latest_booking['num_adults']; ?> Adults
            <?php if ($latest_booking['num_children'] > 0): ?>
                and <?php echo $latest_booking['num_children']; ?> Children
                <?php endif; ?></div>
                </div>
                
                <div class="package-info">
                    Options:
                    <div class="price-line">
                <span>1x - <?php echo htmlspecialchars($latest_booking['package_name']); ?></span>
                    <span>RM <?php echo number_format($total_price, 2); ?></span>
                </div>
                </div>
                
                <div class="total-section">
                    <div class="total-price">
                        <span>TOTAL</span>
                        <span>RM<?php echo number_format($total_price, 2); ?></span>
                    </div>
                </div>
            </div>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}
</script>
</body>
</html>




