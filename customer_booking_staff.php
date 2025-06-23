<?php
include 'session_check.php';
checkStaffSession();

require_once 'db_connection.php';

// Handle payment verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_payment'])) {
    try {
        $booking_id = $_POST['booking_id'];
        $payment_id = $_POST['payment_id'];
        $status_note = isset($_POST['payment_status_note']) ? $_POST['payment_status_note'] : '';
        // Begin transaction
        $conn->begin_transaction();

        // Update payment verification status
        $update_payment = $conn->prepare("
            UPDATE payments 
            SET payment_details = JSON_OBJECT(
                'verified_by', ?,
                'verified_date', NOW(),
                'status', ?,
                'verification_notes', 'Payment confirmed by staff'
            )
            WHERE payment_id = ? AND booking_id = ?
        ");
        $staff_id_for_verification = $_SESSION['staff_id'];
        $new_status = $status_note ? $status_note : 'confirmed';
        $update_payment->bind_param("ssii", $staff_id_for_verification, $new_status, $payment_id, $booking_id);
        $update_payment->execute();

        // Update booking status to note or 'confirmed'
        $update_booking_status = $conn->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
        $update_booking_status->bind_param("si", $new_status, $booking_id);
        $update_booking_status->execute();

        $conn->commit();

        $_SESSION['success'] = "Payment has been confirmed successfully!";
        header("Location: customer_booking_staff.php");
        exit();
    } catch(Exception $e) { // Changed to generic Exception for mysqli errors
        $conn->rollback();
        $_SESSION['error'] = "Error confirming payment: " . $e->getMessage();
    }
}

try {
    // Get all bookings with payment information
    $stmt = $conn->prepare("
        SELECT 
            b.booking_id,
            b.full_name as customer_name,
            b.email as customer_email,
            b.phone_no as customer_phone,
            b.num_adults,
            b.num_children,
            b.total_price,
            b.arrival_date,
            b.departure_date,
            b.status,
            p.package_name,
            py.payment_id,
            py.amount as paid_amount,
            py.payment_method,
            py.payment_date,
            py.payment_details,
            CASE 
                WHEN py.payment_id IS NULL THEN 'pending'
                WHEN py.payment_details LIKE '%\"status\":\"verified\"%' THEN 'verified'
                ELSE 'paid'
            END as payment_status
        FROM bookings b 
        LEFT JOIN packages p ON b.package_id = p.package_id
        LEFT JOIN payments py ON b.booking_id = py.booking_id
        ORDER BY b.arrival_date DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
} catch(Exception $e) { // Changed to generic Exception for mysqli errors
    $_SESSION['error'] = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Bookings - Staff Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { display: flex; background-color: #f0f2f5; }
    .sidebar {
            width: 250px; 
            background-color: #6f74c6; 
            color: white; 
            padding: 40px 20px; 
      height: 100vh;
      position: fixed;
    }
    .sidebar h2 {
            font-size: 48px; 
            margin-bottom: 40px; 
            display: flex;
            align-items: center;
            gap: 10px;
    }
    .sidebar a {
            display: flex; 
            align-items: center;
            gap: 10px;
            color: white; 
            font-weight: bold; 
            font-size: 26px; 
      text-decoration: none;
            margin-bottom: 25px; 
            padding: 12px 20px;
            border-radius: 12px;
            transition: all 0.3s ease;
    }
    .sidebar a:hover {
            background-color: #343795;
            transform: translateX(10px);
    }
        .sidebar a.active { 
            background-color: #343795;
        }
        .main { 
            flex-grow: 1; 
            padding: 30px;
            margin-left: 250px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-top: 20px;
            overflow-x: auto;
        }
        table {
      width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            white-space: nowrap;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            vertical-align: center;
        }
        th {
            background-color: #6f74c6;
            color: white;
            position: sticky;
            top: 0;
    }
        tr:hover {
            background-color: #f5f5f5;
        }
        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
            text-transform: capitalize;
        }
        .status-pending { 
            background-color: #ffeaa7; 
            color: #8B7355; 
        }
        .status-paid { 
            background-color: #55efc4; 
            color: #006400; 
        }
        .status-unpaid { 
            background-color: #fab1a0; 
            color: #8B0000; 
        }
        .total-price {
            font-weight: bold;
            color: #2ecc71;
            font-size: 1.1em;
    }
        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
            white-space: nowrap;
            justify-content: flex-start;
        }
        .btn, .verify-btn {
            padding: 8px 16px;
            border-radius: 20px;
      text-decoration: none;
            color: white;
            font-size: 0.9em;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: bold;
            height: 36px;
            line-height: 20px;
        }
        .btn i, .verify-btn i {
            font-size: 0.9em;
        }
        .btn:hover:not(:disabled), .verify-btn:hover:not(:disabled) {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        .btn-edit { 
            background-color: #3498db; 
        }
        .btn-delete { 
            background-color: #e74c3c; 
        }
        .verify-btn {
            background-color: #27ae60;
        }
        .verify-btn:disabled {
            background-color: #bdc3c7;
            cursor: not-allowed;
            opacity: 0.7;
            transform: none;
        }
        .verify-btn:not(:disabled):hover {
            background-color: #219a52;
        }
        .search-box {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .search-box input {
            padding: 10px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 1em;
        }
        .filter-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .filter-box select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 1em;
        }
        .customer-info {
            font-size: 0.9em;
            color: #666;
        }
        .package-info {
            font-size: 0.9em;
            color: #2d3436;
        }
        .price {
            font-weight: bold;
            color: #2ecc71;
        }
        .payment-status {
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.9em;
      font-weight: bold;
            text-transform: capitalize;
            display: inline-block;
            margin-bottom: 10px;
        }
        .status-pending { 
            background-color: #ffeaa7; 
            color: #8B7355; 
        }
        .status-paid { 
            background-color: #74b9ff; 
            color: #00008B; 
        }
        .status-verified { 
            background-color: #55efc4; 
            color: #006400; 
        }
        .payment-info {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }
        .payment-info i {
            width: 16px;
            margin-right: 5px;
        }
        .receipt-link {
            color: #2980b9;
            text-decoration: none;
            font-size: 0.9em;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .receipt-link:hover {
            text-decoration: underline;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 1em;
            font-weight: 600;
            border-radius: 16px;
            padding: 7px 18px;
            margin-top: 6px;
            box-shadow: 0 2px 8px rgba(80,80,160,0.08);
            background: linear-gradient(90deg, #f8fafc 60%, #e0e7ff 100%);
            color: #444;
            letter-spacing: 0.5px;
            transition: background 0.2s, color 0.2s;
        }
        .status-badge.status-complete {
            background: linear-gradient(90deg, #e0ffe7 60%, #d4fcf7 100%);
            color: #219150;
            border: 1.5px solid #27ae60;
        }
        .status-badge.status-paid {
            background: linear-gradient(90deg, #e0f7fa 60%, #e0e7ff 100%);
            color: #00796b;
            border: 1.5px solid #00bfae;
        }
        .status-badge.status-pending {
            background: linear-gradient(90deg, #fffbe0 60%, #ffeaa7 100%);
            color: #bfa600;
            border: 1.5px solid #f39c12;
        }
        .status-badge.status-not\ complete {
            background: linear-gradient(90deg, #ffe0e0 60%, #ffe7e7 100%);
            color: #c0392b;
            border: 1.5px solid #e74c3c;
        }
  </style>
</head>
<body>
  <div class="sidebar">
        <h2><i class="fas fa-user-shield"></i> Staff</h2>
        <a href="staff_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="customer_booking_staff.php" class="active"><i class="fas fa-file-invoice-dollar"></i> Payment Verification</a>
        <a href="package_detail_staff.php"><i class="fas fa-box"></i> Package Details</a>
        <a href="timetable_staff.php"><i class="fas fa-clock"></i> Timetable</a>
        <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>

    <div class="main">
        <h1><i class="fas fa-file-invoice-dollar"></i> Payment Verification</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="search-box">
            <input type="text" id="searchInput" onkeyup="searchBookings()" placeholder="Search by customer name, email, or booking ID...">
        </div>

        <div class="filter-box">
            <select id="statusFilter" onchange="filterBookings()">
                <option value="">All Payment Status</option>
                <option value="Pending Payment">Pending Payment</option>
                <option value="Payment Received">Payment Received</option>
                <option value="Payment Confirmed">Payment Confirmed</option>
            </select>
        </div>

        <div class="table-container">
            <table id="bookingsTable">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer Details</th>
                        <th>Package & Amount</th>
                        <th>Booking Date</th>
                        <th>Payment Status</th>
                        <th style="text-align:center;">Current Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bookings)): ?>
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($booking['booking_id']); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong><br>
                            <span class="customer-info">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($booking['customer_email']); ?><br>
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($booking['customer_phone']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="package-info">
                                <strong><?php echo htmlspecialchars($booking['package_name']); ?></strong><br>
                                <span class="total-price">
                                    Total: RM <?php echo number_format($booking['total_price'], 2); ?>
                                </span>
                            </span>
                        </td>
                        <td>
                            <?php 
                                $arrival = new DateTime($booking['arrival_date']);
                                echo $arrival->format('d M Y'); 
                            ?>
                        </td>
                        <td style="vertical-align:top; min-width:270px;">
                            <span class="payment-status status-<?php echo htmlspecialchars($booking['payment_status']); ?>">
                                <?php 
                                    switch($booking['payment_status']) {
                                        case 'verified':
                                            echo '<i class="fas fa-check-circle"></i> Payment Confirmed';
                                            break;
                                        case 'paid':
                                            echo '<i class="fas fa-money-bill-wave"></i> Payment Received';
                                            break;
                                        default:
                                            echo '<i class="fas fa-hourglass-half"></i> Pending Payment';
                                    }
                                ?>
                            </span>
                            <?php 
                                $paid = floatval($booking['paid_amount']);
                                $total = floatval($booking['total_price']);
                                $outstanding = $total - $paid;
                            ?>
                            <div class="payment-info">
                                <i class="fas fa-money-bill"></i> Paid: RM <?php echo number_format($paid, 2); ?> / RM <?php echo number_format($total, 2); ?><br>
                                <?php if ($outstanding > 0): ?>
                                    <span style="color:#c0392b;font-weight:bold;">Outstanding: RM <?php echo number_format($outstanding, 2); ?></span>
                                <?php else: ?>
                                    <span style="color:#27ae60;font-weight:bold;">Fully Paid</span>
                                <?php endif; ?>
                                <?php 
                                if ($booking['payment_status'] === 'verified' && $booking['payment_details']) {
                                    $details = json_decode($booking['payment_details'], true);
                                    if (isset($details['verified_date'])) {
                                        echo '<br><i class="fas fa-check-circle"></i> Confirmed on ' . 
                                             (new DateTime($details['verified_date']))->format('d M Y H:i');
                                    }
                                }
                                ?>
                            </div>
                            <div class="actions" style="margin-top:12px; background: #f8f9ff; border-radius: 18px; padding: 10px 10px 8px 10px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                <?php 
                                    $can_confirm = ($booking['payment_status'] === 'paid');
                                    $disabled = $can_confirm ? '' : 'disabled';
                                ?>
                                <form method="post" style="display: flex; align-items:center; gap:10px; width:100%;" onsubmit="return checkStatusSelected(this)">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                    <input type="hidden" name="payment_id" value="<?php echo $booking['payment_id']; ?>">
                                    <select name="payment_status_note" required class="status-dropdown" style="border-radius:16px; padding:7px 16px; font-size:1em; background: #fff; border: 1.5px solid #d1d5db; min-width: 150px; <?php echo $disabled ? 'opacity:0.6;pointer-events:none;' : '' ?>">
                                        <option value="">Select Status</option>
                                        <option value="not complete">Payment Not Complete</option>
                                        <option value="complete">Payment Complete</option>
                                    </select>
                                    <button type="submit" name="verify_payment" class="verify-btn" style="border-radius:16px; font-size:1em; padding:7px 18px; <?php echo $disabled ? 'background:#bdc3c7;cursor:not-allowed;opacity:0.7;' : '' ?>" <?php echo $disabled ? 'disabled' : ''; ?>>
                                        <i class="fas fa-check"></i> Confirm Payment
                                    </button>
                                </form>
                            </div>
                        </td>
                        <td style="text-align:center; vertical-align:middle;">
                            <span class="status-badge status-<?php echo htmlspecialchars($booking['status']); ?>">
                                <?php 
                                    switch($booking['status']) {
                                        case 'complete':
                                            echo '<i class="fas fa-check-circle"></i> Complete';
                                            break;
                                        case 'paid':
                                            echo '<i class="fas fa-money-bill-wave"></i> Paid';
                                            break;
                                        case 'not complete':
                                            echo '<i class="fas fa-exclamation-circle"></i> Not Complete';
                                            break;
                                        case 'pending':
                                        default:
                                            echo '<i class="fas fa-hourglass-half"></i> Pending';
                                    }
                                ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No bookings found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function searchBookings() {
        var input = document.getElementById("searchInput");
        var filter = input.value.toLowerCase();
        var statusFilter = document.getElementById("statusFilter").value;
        var table = document.getElementById("bookingsTable");
        var tr = table.getElementsByTagName("tr");

        for (var i = 1; i < tr.length; i++) {
            var td = tr[i].getElementsByTagName("td");
            var found = false;
            
            if (td.length) {
                var textToSearch = td[0].textContent + ' ' + // Booking ID
                                 td[1].textContent + ' ' + // Customer Details
                                 td[2].textContent; // Package Details
                
                var statusElement = td[4].querySelector('.payment-status');
                var status = statusElement ? statusElement.textContent : '';
                
                var matchesSearch = textToSearch.toLowerCase().indexOf(filter) > -1;
                var matchesStatus = statusFilter === '' || status.includes(statusFilter);
                
                found = matchesSearch && matchesStatus;
            }
            
            tr[i].style.display = found ? "" : "none";
        }
    }

    function filterBookings() {
        searchBookings();
    }

    // Enable the Confirm Payment button only when a status is selected
    function checkStatusSelected(form) {
        var select = form.querySelector('.status-dropdown');
        var btn = form.querySelector('button[type="submit"]');
        if (!select.value) {
            btn.disabled = true;
            select.focus();
            return false;
        }
        btn.disabled = false;
        return true;
    }
    // Attach event listeners to all dropdowns
    window.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.status-dropdown').forEach(function(select) {
            var btn = select.form.querySelector('button[type="submit"]');
            if (select.disabled || btn.disabled) return;
            select.addEventListener('change', function() {
                btn.disabled = !select.value;
            });
            // Initial state
            btn.disabled = !select.value;
        });
    });
    </script>
</body>
</html>
