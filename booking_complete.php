<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
checkCustomerSession();

// Check if booking data is posted, otherwise redirect
if (!isset($_POST['package']) || empty($_POST['package'])) {
    header("Location: makeBooking.php");
    exit();
}

// Retrieve booking data from POST request
$package_id = $_POST['package'];
$arrive_date_str = $_POST['arriveDate'];
$depart_date_str = $_POST['departDate'];
$adults = (int)$_POST['adults'];
$children = (int)$_POST['children'];

// Fetch package details from DB
$package_sql = "
    SELECT 
        p.package_id, p.package_name, p.description, p.duration, p.photo, p.activity,
        pp.adult_price, pp.child_price 
    FROM packages p
    JOIN package_prices pp ON p.package_id = pp.package_id
    WHERE p.package_id = ?
";
$stmt = $conn->prepare($package_sql);
$stmt->bind_param("i", $package_id);
$stmt->execute();
$package_result = $stmt->get_result();
$package = $package_result->fetch_assoc();

if (!$package) {
    // Handle error, maybe redirect back with a message
    die("Package not found.");
}

// Fetch user details from DB
$user_id = $_SESSION['customer_id'];
$user_sql = "SELECT full_name, email, phone_no FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($user_sql);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user = $user_result->fetch_assoc();


// Calculate price (logic copied from makeBooking.php)
$date1 = new DateTime($arrive_date_str);
$date2 = new DateTime($depart_date_str);
$day_count = $date2->diff($date1)->days + 1;
$total_cost = ($package['adult_price'] * $adults + $package['child_price'] * $children) * $day_count;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your Booking - TasikBiruCamps</title>
    <style>
        body {
            background-image: url('backgroundcamp.jpg');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #4A4A4A;
            margin: 0;
            padding: 20px 0; /* Add some padding for better spacing */
        }
        .back-link {
            text-decoration: none;
            font-size: 1em;
            font-weight: bold;
            color: #6D5B4B;
            padding: 8px 15px;
            background-color: rgba(255, 255, 255, 0.6);
            border-radius: 10px;
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.1);
            display: inline-block; /* Changed for better placement */
            margin-top: 20px; /* Space from the list above */
        }
        .back-link:hover {
            background-color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            grid-template-rows: auto auto;
            gap: 30px;
            grid-template-areas:
                "package summary"
                "details summary";
        }
        .package-display { 
            grid-area: package; 
            background-color: #D8D8D8; 
            border-radius: 20px; 
            padding: 40px;
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }
        .package-image-large {
            width: 250px;
            height: 250px;
            object-fit: cover;
            border-radius: 15px;
            flex-shrink: 0;
        }
        .package-display-details h1 {
            margin-top: 0;
        }
        .package-display-details ul {
            padding-left: 20px;
            margin-top: 5px;
        }
        .booking-summary { grid-area: summary; background-color: #8E7A68; border-radius: 20px; padding: 30px; color: white; height: fit-content; }
        .details-form { grid-area: details; background-color: #DBCAB9; border-radius: 20px; padding: 30px; }

        /* Booking Summary Styling */
        .booking-summary h2, .booking-summary h3 { margin: 0; }
        .booking-summary .date-guest-info { font-size: 1.1em; line-height: 1.6; margin-bottom: 20px; }
        .booking-summary .options { background-color: rgba(0,0,0,0.1); border-radius: 10px; padding: 20px; }
        .booking-summary .options p { margin: 0 0 10px 0; }
        .booking-summary .options .total { font-weight: bold; font-size: 1.2em; margin-top: 15px; }

        /* Details Form Styling */
        .details-form h2 { font-size: 1.8em; margin-top: 0; color: #6D5B4B; }
        .details-form .form-group { margin-bottom: 20px; }
        .details-form label { display: block; margin-bottom: 5px; font-weight: bold; }
        .details-form input[type="text"], .details-form input[type="email"] {
            width: 80%;
            padding: 12px;
            border: none;
            border-bottom: 2px solid #8E7A68;
            background-color: transparent;
            font-size: 1.1em;
        }
        .details-form input:focus { outline: none; border-bottom-color: #6D5B4B; }
        .book-button {
            background-color: transparent;
            border: 2px solid #6D5B4B;
            color: #6D5B4B;
            padding: 10px 40px;
            font-size: 1.2em;
            font-weight: bold;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .book-button:hover { background-color: #6D5B4B; color: white; }
        .success-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0; top: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
        }
        .success-modal.active {
            display: flex;
        }
        .success-modal-content {
            background: #fff;
            color: #6D5B4B;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            text-align: center;
            font-size: 1.3em;
            min-width: 320px;
        }
        .success-modal-content .icon {
            font-size: 2.5em;
            margin-bottom: 10px;
            color: #8E7A68;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="success-modal active" id="successModal">
        <div class="success-modal-content">
            <div class="icon">&#10004;</div>
            <div>Booking successful!<br>You can view your booking in <b>My Bookings</b>.</div>
        </div>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = 'my_bookings.php';
        }, 2200);
    </script>
    <?php endif; ?>

    <div class="container">
        
        <div class="package-display">
            <img src="<?php echo htmlspecialchars($package['photo']); ?>" alt="<?php echo htmlspecialchars($package['package_name']); ?>" class="package-image-large">
            <div class="package-display-details">
                <h1><?php echo htmlspecialchars($package['package_name']); ?></h1>
                <p><strong>Duration:</strong> <?php echo htmlspecialchars($package['duration']); ?> days</p>
                <p><?php echo htmlspecialchars($package['description']); ?></p>
                
                <strong>Activities included:</strong>
                <ul>
                    <?php 
                    $activities = explode(',', $package['activity']);
                    foreach ($activities as $activity) {
                        echo '<li>' . htmlspecialchars(trim($activity)) . '</li>';
                    }
                    ?>
                </ul>
                <a href="makeBooking.php" class="back-link">&larr; Change your selection</a>
            </div>
        </div>

        <div class="booking-summary">
            <div class="date-guest-info">
                <strong>Dates:</strong> <?php echo $date1->format('D, M j') . ' → ' . $date2->format('D, M j'); ?><br>
                <strong>Guests:</strong> <?php echo $adults; ?> Adult(s), <?php echo $children; ?> Child(ren)<br>
                <strong>Duration:</strong> <?php echo $day_count; ?> Day(s)
            </div>
            <div class="options">
                <p><strong>Options:</strong></p>
                <p>1x - <?php echo htmlspecialchars($package['package_name']); ?></p>
                <p class="total">TOTAL <?php echo 'MYR ' . number_format($total_cost, 2); ?></p>
            </div>
        </div>
        
        <div class="details-form">
            <form action="process_booking.php" method="POST">
                <h2>Your details:</h2>
                
                <!-- Hidden fields to pass all booking data -->
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                <input type="hidden" name="package" value="<?php echo htmlspecialchars($package_id); ?>">
                <input type="hidden" name="arriveDate" value="<?php echo htmlspecialchars($arrive_date_str); ?>">
                <input type="hidden" name="departDate" value="<?php echo htmlspecialchars($depart_date_str); ?>">
                <input type="hidden" name="adults" value="<?php echo htmlspecialchars($adults); ?>">
                <input type="hidden" name="children" value="<?php echo htmlspecialchars($children); ?>">
                
                <div class="form-group">
                    <label for="fullName">Full Name:</label>
                    <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone no:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone_no']); ?>" required>
                </div>
                
                <button type="submit" class="book-button">Book</button>
            </form>
        </div>

    </div>
</body>
</html> 
