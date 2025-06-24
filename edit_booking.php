<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
checkAdminSession();

if (!isset($_GET['id'])) {
    header("Location: manage_bookings.php");
    exit();
}

$booking_id = intval($_GET['id']);

// Fetch booking
$stmt = $conn->prepare("
    SELECT 
        b.*, 
        p.package_name, 
        py.payment_id, 
        py.amount AS paid_amount, 
        py.payment_method, 
        py.payment_date 
    FROM bookings b
    LEFT JOIN packages p ON b.package_id = p.package_id
    LEFT JOIN payments py ON b.booking_id = py.booking_id
    WHERE b.booking_id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    echo "Booking not found.";
    exit();
}

// Fetch price
$price_stmt = $conn->prepare("SELECT adult_price, child_price FROM package_prices WHERE package_id = ?");
$price_stmt->bind_param("i", $booking['package_id']);
$price_stmt->execute();
$prices = $price_stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $arrival = new DateTime($_POST['arrival_date']);
    $departure = new DateTime($_POST['departure_date']);
    $num_days = $arrival->diff($departure)->days + 1;

    $total_price = ($_POST['num_adults'] * $prices['adult_price'] + $_POST['num_children'] * $prices['child_price']) * $num_days;

    $update = $conn->prepare("UPDATE bookings SET full_name=?, email=?, phone_no=?, num_adults=?, num_children=?, arrival_date=?, departure_date=?, total_price=? WHERE booking_id=?");
    $update->bind_param("sssiissdi",
        $_POST['full_name'],
        $_POST['email'],
        $_POST['phone_no'],
        $_POST['num_adults'],
        $_POST['num_children'],
        $_POST['arrival_date'],
        $_POST['departure_date'],
        $total_price,
        $booking_id
    );

    if ($update->execute()) {
        header("Location: manage_bookings.php?success=1");
        exit();
    } else {
        $error = "Failed to update booking.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Booking</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f4f4; }
        .container { max-width: 700px; margin: 50px auto; background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 0 12px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; text-align: center; margin-bottom: 1.5rem; }
        h3 { color: #34495e; margin-bottom: 1rem; }
        label { display: block; margin-top: 1rem; margin-bottom: 0.5rem; font-weight: 600; }
        input, button { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; }
        button { background: #27ae60; color: white; font-weight: bold; margin-top: 1.5rem; border: none; cursor: pointer; }
        button:hover { background: #219150; }
        .back-link { display: block; text-align: center; margin-top: 1rem; color: #2980b9; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .info-box { background: #f9f9f9; border-left: 4px solid #3498db; padding: 1rem; margin-bottom: 1.5rem; border-radius: 6px; }
        .info-box p { margin: 0.3rem 0; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.9rem; color: white; }
        .bg-success { background: #2ecc71; }
        .bg-danger { background: #e74c3c; }
        .bg-primary { background: #3498db; }
        .bg-warning { background: #f39c12; }
        .error { color: red; text-align: center; margin-bottom: 1rem; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Booking #<?= htmlspecialchars($booking_id) ?></h2>

    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>

    <div class="info-box">
        <h3>Package Information</h3>
        <p><strong>Package:</strong> <?= htmlspecialchars($booking['package_name']) ?></p>
        <p><strong>Total Price:</strong> RM <?= number_format($booking['total_price'], 2) ?></p>
        <p><strong>Status:</strong> 
            <span class="badge bg-<?= 
                $booking['status'] === 'complete' ? 'success' :
                ($booking['status'] === 'confirmed' ? 'primary' :
                ($booking['status'] === 'paid' ? 'warning' : 'danger')) ?>">
                <?= ucfirst(htmlspecialchars($booking['status'])) ?>
            </span>
        </p>
        <?php if ($booking['payment_id']): ?>
            <p><strong>Payment Amount:</strong> RM <?= number_format($booking['paid_amount'], 2) ?></p>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars($booking['payment_method']) ?></p>
            <p><strong>Payment Date:</strong> <?= htmlspecialchars($booking['payment_date']) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($booking['status'] === 'complete'): ?>
        <div class="info-box" style="border-left-color: #e67e22;">
            <strong>This booking is marked as complete and cannot be edited.</strong>
        </div>
    <?php else: ?>
        <form method="POST">
            <label for="full_name">Full Name</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($booking['full_name']) ?>" required>

            <label for="email">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($booking['email']) ?>" required>

            <label for="phone_no">Phone Number</label>
            <input type="text" name="phone_no" value="<?= htmlspecialchars($booking['phone_no']) ?>" required>

            <label for="num_adults">Number of Adults</label>
            <input type="number" name="num_adults" min="1" value="<?= htmlspecialchars($booking['num_adults']) ?>" required>

            <label for="num_children">Number of Children</label>
            <input type="number" name="num_children" min="0" value="<?= htmlspecialchars($booking['num_children']) ?>" required>

            <label for="arrival_date">Arrival Date</label>
            <input type="date" name="arrival_date" value="<?= htmlspecialchars($booking['arrival_date']) ?>" required>

            <label for="departure_date">Departure Date</label>
            <input type="date" name="departure_date" value="<?= htmlspecialchars($booking['departure_date']) ?>" required>

            <button type="submit">Update Booking</button>
        </form>
    <?php endif; ?>

    <a href="manage_bookings.php" class="back-link">← Back to Bookings</a>
</div>
</body>
</html>
