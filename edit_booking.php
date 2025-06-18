<?php
session_start();                    // Start session after enabling error reporting
include 'db_connection.php';
include 'session_check.php';        // Load session check functions
checkAdminSession();
$booking_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$booking_id) {
    $_SESSION['error'] = "No booking ID provided";
    header('Location: customer_booking_staff.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Update booking information
        $stmt = $conn->prepare("
            UPDATE bookings 
            SET full_name = ?,
                email = ?,
                phone_no = ?,
                num_adults = ?,
                num_children = ?,
                arrival_date = ?,
                departure_date = ?
            WHERE booking_id = ?
        ");
        
        $stmt->bind_param("sssiissi", 
            $_POST['full_name'],
            $_POST['email'],
            $_POST['phone_no'],
            $_POST['num_adults'],
            $_POST['num_children'],
            $_POST['arrival_date'],
            $_POST['departure_date'],
            $booking_id
        );

        if ($stmt->execute()) {
            $_SESSION['success'] = "Booking updated successfully!";
        } else {
            throw new Exception("Error updating booking: " . $stmt->error);
        }

        header('Location: customer_booking_staff.php');
        exit();
    } catch(Exception $e) {
        $_SESSION['error'] = "Error updating booking: " . $e->getMessage();
    }
}

// Get booking details
try {
    $stmt = $conn->prepare("
        SELECT 
            b.*,
            p.package_name,
            py.payment_id,
            py.amount as paid_amount,
            py.payment_method,
            py.payment_date,
            CASE 
                WHEN py.payment_id IS NULL THEN 'pending'
                WHEN py.payment_details IS NOT NULL THEN 'verified'
                ELSE 'paid'
            END as payment_status
        FROM bookings b 
        LEFT JOIN packages p ON b.package_id = p.package_id 
        LEFT JOIN payments py ON b.booking_id = py.booking_id
        WHERE b.booking_id = ?
    ");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();

    if (!$booking) {
        throw new Exception("Booking not found");
    }
} catch(Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: customer_booking_staff.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Edit Booking #<?php echo htmlspecialchars($booking_id); ?></h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Package Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Package:</strong> <?php echo htmlspecialchars($booking['package_name']); ?></p>
                <p><strong>Total Price:</strong> RM <?php echo htmlspecialchars($booking['total_price']); ?></p>
                <p>
                    <strong>Payment Status:</strong> 
                    <span class="badge bg-<?php 
                        echo $booking['payment_status'] === 'verified' ? 'success' : 
                            ($booking['payment_status'] === 'paid' ? 'warning' : 'danger'); 
                    ?>">
                        <?php echo ucfirst(htmlspecialchars($booking['payment_status'])); ?>
                    </span>
                </p>
                <?php if ($booking['payment_id']): ?>
                <p><strong>Payment Amount:</strong> RM <?php echo htmlspecialchars($booking['paid_amount']); ?></p>
                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($booking['payment_method']); ?></p>
                <p><strong>Payment Date:</strong> <?php echo htmlspecialchars($booking['payment_date']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <form method="POST" class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Booking Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($booking['full_name']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($booking['email']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone_no" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone_no" name="phone_no" 
                               value="<?php echo htmlspecialchars($booking['phone_no']); ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="num_adults" class="form-label">Number of Adults</label>
                        <input type="number" class="form-control" id="num_adults" name="num_adults" 
                               value="<?php echo htmlspecialchars($booking['num_adults']); ?>" min="1" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="num_children" class="form-label">Number of Children</label>
                        <input type="number" class="form-control" id="num_children" name="num_children" 
                               value="<?php echo htmlspecialchars($booking['num_children']); ?>" min="0" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="arrival_date" class="form-label">Arrival Date</label>
                        <input type="date" class="form-control" id="arrival_date" name="arrival_date" 
                               value="<?php echo htmlspecialchars($booking['arrival_date']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="departure_date" class="form-label">Departure Date</label>
                        <input type="date" class="form-control" id="departure_date" name="departure_date" 
                               value="<?php echo htmlspecialchars($booking['departure_date']); ?>" required>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update Booking</button>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="manage_bookings.php" class="btn btn-secondary">Cancel</a>
                <?php else: ?>
                    <a href="customer_booking_staff.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 