<?php
session_start();
require_once 'session_check.php';
include 'db_connection.php';
checkStaffSession();

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

        // ✅ Only send email if marked "complete"
        if ($new_status === 'complete') {
            require_once 'send_email.php';

            $stmt = $conn->prepare("
                SELECT 
                    b.full_name, 
                    b.email, 
                    b.total_price,
                    b.num_adults,
                    b.num_children,
                    b.arrival_date,
                    b.departure_date,
                    p.package_name,
                    py.payment_method,
                    py.payment_date
                FROM bookings b
                LEFT JOIN packages p ON b.package_id = p.package_id
                LEFT JOIN payments py ON b.booking_id = py.booking_id
                WHERE b.booking_id = ?
            ");

            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $customer = $result->fetch_assoc();

            if ($customer) {
                $stayDuration = date("d M Y", strtotime($customer['arrival_date'])) . ' - ' . date("d M Y", strtotime($customer['departure_date']));
                $guestSummary = $customer['num_adults'] . ' Adults';
                if ($customer['num_children'] > 0) {
                    $guestSummary .= ', ' . $customer['num_children'] . ' Children';
                }

                $paymentDateFormatted = $customer['payment_date'] 
                    ? date("d M Y, h:i A", strtotime($customer['payment_date']))
                    : 'N/A';

                $paymentMethod = $customer['payment_method'] ?: 'N/A';

                sendPaymentReceipt(
                    $customer['email'],
                    $customer['full_name'],
                    $booking_id,
                    $customer['total_price'],
                    $customer['package_name'] ?? 'N/A',
                    $stayDuration,
                    $guestSummary,
                    $paymentMethod,
                    $paymentDateFormatted
                );
            }
        }

        $_SESSION['success'] = "Payment has been confirmed successfully!";
        header("Location: customer_booking_staff.php");
        exit();
    } catch(Exception $e) {
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
                WHEN b.status = 'confirmed' AND b.total_price <= py.amount THEN 'complete'
                WHEN b.total_price > py.amount THEN 'pending'
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
    <!-- Add Google Fonts: Inter for modern look -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', Arial, sans-serif; }
        body { display: flex; background-color: #f0f2f5; }
    .sidebar {
            width: 250px; 
            background-color: #6f74c6; 
            color: white; 
            padding: 40px 20px; 
      height: 100vh;
      position: fixed;
            transition: width 0.3s;
    }
    .sidebar h2 {
            font-size: 48px; 
            margin-bottom: 40px; 
            display: flex;
            align-items: center;
            gap: 10px;
            transition: font-size 0.3s, margin-bottom 0.3s;
    }
    .sidebar a {
            display: flex; 
            align-items: center;
            gap: 10px;
            color: white; 
            font-weight: 500;
            font-size: 18px;
            letter-spacing: 0.5px;
      text-decoration: none;
            margin-bottom: 18px;
            padding: 10px 18px 10px 18px;
            border-radius: 8px 20px 20px 8px;
            border-left: 4px solid transparent;
            transition: all 0.2s;
    }
        .sidebar a.active { 
            background-color: #343795;
            border-left: 4px solid #ffd700;
            color: #ffd700;
        }
        .sidebar a:hover {
            background-color: #4b4fae;
            border-left: 4px solid #ffd700;
            color: #ffd700;
            transform: none;
        }
        .sidebar a i { font-size: 22px; }
        @media (max-width: 1200px) {
            .sidebar { width: 180px; padding: 30px 10px; }
            .sidebar h2 { font-size: 26px; margin-bottom: 18px; }
            .sidebar a { font-size: 15px; padding: 8px 12px 8px 12px; margin-bottom: 12px; }
        }
        @media (max-width: 900px) {
            .sidebar { width: 70px; padding: 16px 4px; }
            .sidebar h2 { font-size: 0; margin-bottom: 0; }
            .sidebar a { font-size: 0; padding: 10px 8px; justify-content: center; margin-bottom: 0; }
            .sidebar a i { font-size: 22px; }
        }
        @media (max-width: 600px) {
            body { flex-direction: column; }
            .sidebar {
                position: static; width: 100vw; height: auto; display: flex; flex-direction: row;
                justify-content: space-around; align-items: center; padding: 6px 0; z-index: 10;
            }
            .sidebar h2 { display: none; }
            .sidebar a { font-size: 0; padding: 10px 8px; margin-bottom: 0; border-radius: 50%; width: 44px; height: 44px; justify-content: center; }
            .sidebar a i { font-size: 22px; }
        }
        .main { 
            flex-grow: 1; 
            padding: 10px;
            margin-left: 250px;
            max-width: 100vw;
            transition: margin-left 0.3s, padding 0.3s;
        }
        h1, h2 {
            font-size: 1.05rem;
            margin-bottom: 8px;
        }
        .search-bar, .status-filter {
            font-size: 0.85rem;
            padding: 0.2rem 0.5rem;
            border-radius: 16px;
            border: 1px solid #e0e7ff;
            margin-bottom: 0.4rem;
        }
        .status-filter { margin-left: 0.2rem; }
        .table-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 0.3rem 0.2rem;
            margin-top: 0.3rem;
            overflow-x: auto;
            max-width: 100vw;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            min-width: 700px;
        }
        th, td {
            padding: 0.2rem 0.2rem;
            text-align: left;
        }
        th {
            background: #6f74c6;
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
        }
        tr:not(:last-child) { border-bottom: 1px solid #e0e7ff; }
        .booking-row {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 4px rgba(80,80,160,0.04);
            margin-bottom: 0.2rem;
            padding: 0.2rem 0.1rem;
            font-size: 0.85rem;
        }
        .booking-row strong { font-size: 0.95rem; }
        .status-btn, .status-badge {
            font-size: 0.8rem;
            padding: 0.1rem 0.5rem;
            border-radius: 16px;
            border: none;
            font-weight: 600;
            margin-right: 0.2rem;
        }
        .alert {
            padding: 4px;
            margin-bottom: 4px;
            border-radius: 6px;
            font-size: 0.85rem;
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
        .status-badge.status-not-complete {
        background: linear-gradient(90deg, #ffe0e0 60%, #ffe7e7 100%);
        color: #c0392b;
        border: 1.5px solid #e74c3c;
    }

    .status-partial { 
        background-color: #ffeaa7; 
        color: #8B7355; 
    }
    .status-badge.status-partial {
        background: linear-gradient(90deg, #fffbe0 60%, #ffeaa7 100%);
        color: #bfa600;
        border: 1.5px solid #f39c12;
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
                <option value="pending">Pending Payment</option>
                <option value="paid">Payment Received</option>
                <option value="complete">Payment Confirmed</option>
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
                    <?php $clean_status = trim(strtolower($booking['status'])); ?>
                    <tr data-status="<?php echo htmlspecialchars($clean_status); ?>">
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
                                    $can_confirm = ($booking['status'] === 'paid');
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
                            <span class="status-badge status-<?php echo htmlspecialchars(str_replace(' ', '-', $clean_status)); ?>">
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
                var rowStatus = tr[i].getAttribute('data-status');
                var matchesSearch = textToSearch.toLowerCase().indexOf(filter) > -1;
                var matchesStatus = statusFilter === '' || rowStatus === statusFilter;
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