<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
checkCustomerSession();

// Get booking ID from URL
$booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : null;

if (!$booking_id) {
    header("Location: my_bookings.php");
    exit();
}

// First check if payment already exists for this booking
$stmt = $conn->prepare("SELECT payment_id FROM payments WHERE booking_id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$existing_payment = $result->fetch_assoc();

// Get booking details
$stmt = $conn->prepare("
    SELECT b.*, p.package_name, p.description, p.duration,
           pp.adult_price as package_price,
           pp.child_price
    FROM bookings b 
    LEFT JOIN packages p ON b.package_id = p.package_id 
    LEFT JOIN package_prices pp ON p.package_id = pp.package_id
    WHERE b.booking_id = ? AND b.user_id = ?
");
$stmt->bind_param("ii", $booking_id, $_SESSION['customer_id']);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    // Either booking doesn't exist, doesn't belong to user
    header("Location: my_bookings.php");
    exit();
}

// Only redirect to payment_success if booking is fully paid/confirmed
if ($booking['status'] == 'paid' || $booking['status'] == 'complete' || $booking['status'] == 'confirmed') {
    header("Location: payment_success.php?booking_id=" . $booking_id);
    exit();
}

// Use the total_price from the booking record instead of recalculating
$total_price = $booking['total_price'];

// Check if this is preview mode (no booking_id)
$preview_mode = !isset($_GET['booking_id']);

if ($preview_mode) {
    // Sample data for preview
    $booking = [
        'package_name' => 'Sample Package',
        'duration' => '2',
        'package_price' => 199.00,
        'child_price' => 99.00,
    ];
    $total_price = 199.00;
}

// Calculate outstanding amount if status is 'not complete' or 'pending'
$outstanding_amount = null;
if ($booking['status'] == 'not complete') {
    $sum_stmt = $conn->prepare("SELECT SUM(amount) as total_paid FROM payments WHERE booking_id = ?");
    $sum_stmt->bind_param("i", $booking_id);
    $sum_stmt->execute();
    $sum_result = $sum_stmt->get_result();
    $total_paid = 0;
    if ($sum_row = $sum_result->fetch_assoc()) {
        $total_paid = floatval($sum_row['total_paid']);
    }
    $outstanding_amount = floatval($booking['total_price']) - $total_paid;
    if ($outstanding_amount < 0) $outstanding_amount = 0;
} else if ($booking['status'] == 'pending') {
    $outstanding_amount = floatval($booking['total_price']);
}

// Only process POST if not in preview mode
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$preview_mode) {
    $payment_method = $_POST['payment_method'];

    // Get payment details based on method
    $payment_details = [];
    if ($payment_method === 'card') {
        $payment_details = [
            'card_name' => $_POST['card_name'],
            'card_number' => substr($_POST['card_number'], -4), // Store only last 4 digits for security
            'expiry' => $_POST['expiry']
        ];
    } else if ($payment_method === 'bank') {
        $payment_details = [
            'bank_name' => $_POST['bank_name'],
            'reference' => $_POST['reference']
        ];
    }

    // Convert payment details to JSON for storage
    $payment_details_json = json_encode($payment_details);

    // Prevent payment if outstanding_amount is zero or less
    if ($outstanding_amount <= 0) {
        $error = "No outstanding amount to pay.";
    } else {
        try {
            // Begin transaction
            $conn->begin_transaction();

            if ($existing_payment) {
                // Update existing payment: add to amount, update date/details
                $stmt = $conn->prepare("
                    UPDATE payments 
                    SET amount = amount + ?, payment_method = ?, payment_details = ?, payment_date = NOW() 
                    WHERE booking_id = ?
                ");
                $stmt->bind_param("dssi", $outstanding_amount, $payment_method, $payment_details_json, $booking_id);
                $stmt->execute();
            } else {
                // Insert new payment record
                $stmt = $conn->prepare("
                    INSERT INTO payments (booking_id, amount, payment_method, payment_details, payment_date) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->bind_param("idss", $booking_id, $outstanding_amount, $payment_method, $payment_details_json);
                $stmt->execute();
            }

            // Update booking status
            $stmt = $conn->prepare("UPDATE bookings SET status = 'paid' WHERE booking_id = ? AND status != 'paid'");
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();

            // Check if booking was actually updated (another safety check)
            if ($stmt->affected_rows === 0) {
                // Booking was already paid or doesn't exist
                $conn->rollback();
                header("Location: my_bookings.php");
                exit();
            }

            $conn->commit();
            
            // Redirect to success page
            header("Location: payment_success.php?booking_id=" . $booking_id);
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Payment processing failed. Please try again. Error: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TasikBiruCamps - Payment</title>
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
            min-height: calc(100vh - 60px);
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .payment-container {
            max-width: 800px;
            width: 100%;
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .payment-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .payment-header h1 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .payment-summary {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
        }

        .summary-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .payment-method {
            padding: 1rem;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: #6c757d;
        }

        .payment-method.selected {
            border-color: #28a745;
            background: #f8fff9;
        }

        .payment-details {
            margin-top: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #495057;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }

        .submit-btn {
            background: #28a745;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .submit-btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .error-message {
            color: #dc3545;
            background: #f8d7da;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .payment-container {
                padding: 1rem;
            }

            .payment-methods {
                grid-template-columns: 1fr;
            }
        }

        .preview-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            text-align: center;
            font-weight: bold;
        }

        button:disabled {
            background-color: #cccccc !important;
            cursor: not-allowed;
        }

        input:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }

        .payment-method input:disabled + label {
            color: #666;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="main-container">
    <div class="payment-container">
        <div class="payment-header">
            <h1>Payment Details</h1>
            <?php if ($preview_mode): ?>
                <div class="preview-notice">
                    <i class="fas fa-info-circle"></i>
                    This is a preview of the payment page. To make an actual payment, please make a booking first.
                </div>
            <?php else: ?>
                <p>Complete your payment to confirm your booking</p>
            <?php endif; ?>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="payment-summary">
            <div class="summary-row">
                <span>Package</span>
                <span><?php echo htmlspecialchars($booking['package_name']); ?></span>
            </div>
            <div class="summary-row">
                <span>Duration</span>
                <span><?php echo $booking['duration']; ?> days</span>
            </div>
            <div class="summary-row">
                <span>Adults</span>
                <span><?php echo $booking['num_adults']; ?> × RM<?php echo number_format($booking['package_price'], 2); ?></span>
            </div>
            <?php if ($booking['num_children'] > 0): ?>
            <div class="summary-row">
                <span>Children</span>
                <span><?php echo $booking['num_children']; ?> × RM<?php echo number_format($booking['child_price'], 2); ?></span>
            </div>
            <?php endif; ?>
            <div class="summary-row">
                <strong>Total Amount</strong>
                <strong>RM<?php echo number_format($total_price, 2); ?></strong>
            </div>
            <?php if ($outstanding_amount !== null && $outstanding_amount < $total_price): ?>
            <div class="summary-row" style="color:#c0392b;font-weight:bold;">
                <span>Outstanding Amount</span>
                <span>RM<?php echo number_format($outstanding_amount, 2); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <form method="POST" id="paymentForm">
            <div class="payment-methods">
                <div class="payment-method" onclick="<?php echo $preview_mode ? '' : 'selectPaymentMethod(\'card\')'; ?>">
                    <input type="radio" name="payment_method" value="card" id="card" <?php echo $preview_mode ? 'disabled' : 'required'; ?>>
                    <label for="card">Credit/Debit Card</label>
                </div>
                <div class="payment-method" onclick="<?php echo $preview_mode ? '' : 'selectPaymentMethod(\'bank\')'; ?>">
                    <input type="radio" name="payment_method" value="bank" id="bank" <?php echo $preview_mode ? 'disabled' : 'required'; ?>>
                    <label for="bank">Bank Transfer</label>
                </div>
            </div>

            <div class="payment-details" id="cardDetails" style="display: none;">
                <div class="form-group">
                    <label for="card_name">Name on Card</label>
                    <input type="text" id="card_name" name="card_name" placeholder="Enter name as shown on card">
                </div>

                <div class="form-group">
                    <label for="card_number">Card Number</label>
                    <input type="text" id="card_number" name="card_number" placeholder="Enter card number" maxlength="16">
                </div>
                <div class="form-group">
                    <label for="expiry">Expiry Date</label>
                    <input type="text" id="expiry" name="expiry" placeholder="MM/YY" maxlength="5">
                </div>
                <div class="form-group">
                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv" name="cvv" placeholder="Enter CVV" maxlength="3">
                </div>
            </div>

            <div class="payment-details" id="bankDetails" style="display: none;">
                <div class="form-group">
                    <label for="bank_name">Bank Name</label>
                    <input type="text" id="bank_name" name="bank_name" placeholder="Enter your bank name">
                </div>
                <div class="form-group">
                    <label for="reference">Reference Number</label>
                    <input type="text" id="reference" name="reference" placeholder="Enter transfer reference number">
                </div>
            </div>

            <button type="submit" class="submit-btn" <?php echo $preview_mode ? 'disabled' : ''; ?>>
                <?php echo $preview_mode ? 'Preview Mode - Cannot Submit' : 'Complete Payment'; ?>
            </button>
        </form>
    </div>
</div>

<script>
function selectPaymentMethod(method) {
    // Update radio button
    document.getElementById(method).checked = true;
    
    // Update visual selection
    document.querySelectorAll('.payment-method').forEach(el => {
        el.classList.remove('selected');
    });
    document.getElementById(method).parentElement.classList.add('selected');
    
    // Show/hide relevant details
    document.getElementById('cardDetails').style.display = method === 'card' ? 'block' : 'none';
    document.getElementById('bankDetails').style.display = method === 'bank' ? 'block' : 'none';
}

document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
    let isValid = true;
    let errorMessage = '';

    if (selectedMethod === 'card') {
        const cardName = document.getElementById('card_name').value;
        const cardNumber = document.getElementById('card_number').value;
        const expiry = document.getElementById('expiry').value;
        const cvv = document.getElementById('cvv').value;

        if (!cardName || !cardNumber || !expiry || !cvv) {
            isValid = false;
            errorMessage = 'Please fill in all card details';
        } else if (cardNumber.length !== 16) {
            isValid = false;
            errorMessage = 'Card number must be 16 digits';
        } else if (!expiry.match(/^\d{2}\/\d{2}$/)) {
            isValid = false;
            errorMessage = 'Expiry date must be in MM/YY format';
        } else if (cvv.length !== 3) {
            isValid = false;
            errorMessage = 'CVV must be 3 digits';
        }
    } else if (selectedMethod === 'bank') {
        const bankName = document.getElementById('bank_name').value;
        const reference = document.getElementById('reference').value;

        if (!bankName || !reference) {
            isValid = false;
            errorMessage = 'Please fill in all bank transfer details';
        }
    }

    if (!isValid) {
        e.preventDefault();
        alert(errorMessage);
    }
});

// Format expiry date input
document.getElementById('expiry').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.slice(0,2) + '/' + value.slice(2);
    }
    e.target.value = value;
});

// Only allow numbers in card number and CVV
document.getElementById('card_number').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
});

document.getElementById('cvv').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
});
</script>

</body>
</html>
