<?php
include 'session_check.php';
checkStaffSession();

include 'db_connection.php';

$staff_id = $_SESSION['staff_id'];
$staff_name = $_SESSION['full_name'] ?? 'Staff'; // Use full_name for consistency
$profile_pic = 'default_profile.png'; // Default image

// Fetch staff profile picture
$stmt = $conn->prepare("SELECT profile_pic FROM staff WHERE staff_id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

if ($staff && $staff['profile_pic']) {
    $profile_pic = $staff['profile_pic'];
}

// Fetch total reservations
$total_reservations = 0;
$reservations_query = "SELECT COUNT(*) as total FROM bookings WHERE status != 'cancelled'";
$result = mysqli_query($conn, $reservations_query);
if ($result) {
    $total_reservations = mysqli_fetch_assoc($result)['total'];
}

// Fetch ongoing camps (bookings with status 'confirmed' and current date is between check-in and check-out)
$ongoing_camps = 0;
$ongoing_query = "SELECT COUNT(*) as total FROM bookings 
                 WHERE status = 'confirmed' 
                 AND CURDATE() BETWEEN arrival_date AND departure_date";
$result = mysqli_query($conn, $ongoing_query);
if ($result) {
    $ongoing_camps = mysqli_fetch_assoc($result)['total'];
}


// Fetch recent bookings (last 3 bookings with 'confirmed' status)
$recent_bookings = [];
$recent_query = "SELECT b.booking_id, b.full_name as customer_name, p.package_name, b.arrival_date, b.departure_date, b.status 
                FROM bookings b 
                LEFT JOIN packages p ON b.package_id = p.package_id 
                WHERE b.status = 'confirmed'
                ORDER BY b.booking_id DESC 
                LIMIT 3";
$result = mysqli_query($conn, $recent_query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recent_bookings[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
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
        .header {
            background-color: white; 
            padding: 20px; 
            border-radius: 10px;
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .profile-info { 
            display: flex; 
            align-items: center; 
            gap: 15px; 
        }
        .profile-pic {
            width: 60px; 
            height: 60px; 
            background-color: #6f74c6; 
            border-radius: 50%; 
            display: flex;
            justify-content: center; 
            align-items: center; 
            font-size: 30px; 
            color: white;
            overflow: hidden; /* To ensure image fits within circular boundary */
        }
        .profile-pic img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .metrics { 
            display: flex; 
            gap: 20px; 
            margin-bottom: 30px; 
        }
        .metric-box {
            flex: 1; 
            padding: 20px; 
            border-radius: 10px; 
            text-align: center;
            font-size: 20px; 
            font-weight: bold;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .metric-box:hover {
            transform: translateY(-5px);
        }
        .yellow { border-left: 5px solid #ffeaa7; }
        .red { border-left: 5px solid #fab1a0; }
        .blue { border-left: 5px solid #81ecec; }
        .bookings {
            background-color: white; 
            padding: 20px; 
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .booking-entry {
            background-color: #f8f9fa; 
            margin: 10px 0; 
            padding: 15px; 
            border-radius: 10px;
            transition: transform 0.3s ease;
        }
        .booking-entry:hover {
            transform: translateX(5px);
        }
        .edit-profile {
            background-color: #6f74c6; 
            color: white; 
            padding: 8px 16px;
            text-decoration: none; 
            border-radius: 6px; 
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s ease;
        }
        .edit-profile:hover {
            background-color: #343795;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2><i class="fas fa-user-shield"></i> Staff</h2>
        <a href="staff_dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
        <a href="customer_booking_staff.php"><i class="fas fa-calendar-check"></i> Customer Booking</a>
        <a href="package_detail_staff.php"><i class="fas fa-box"></i> Package Details</a>
        <a href="timetable_staff.php"><i class="fas fa-clock"></i> Timetable</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>

    <div class="main">
        <h1>Dashboard</h1>
        <div class="header">
            <div class="profile-info">
                <div class="profile-pic">
                    <?php if ($profile_pic && file_exists($profile_pic)): ?>
                        <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <div>
                    <strong>Welcome back, <?php echo htmlspecialchars($staff_name); ?> ! 🌿</strong>
                    <p>Here's your overview for today.</p>
                </div>
            </div>
            <a href="edit_profile_staff.php" class="edit-profile">
                <i class="fas fa-user-edit"></i> Edit Profile
            </a>
        </div>

        <div class="metrics">
            <div class="metric-box yellow">
                <?php echo $total_reservations; ?><br>Total Reservations
            </div>
            <div class="metric-box red">
                <?php echo $ongoing_camps; ?><br>Ongoing Camps
            </div>
            <div class="metric-box blue">
                <?php echo $available_lots; ?><br>Available Camp Lots
            </div>
        </div>

        <div class="bookings">
            <h2>Recent Bookings</h2>
            <?php if (empty($recent_bookings)): ?>
                <p>No recent bookings found.</p>
            <?php else: ?>
                <?php foreach ($recent_bookings as $booking): ?>
                    <div class="booking-entry">
                        <strong>Booking ID:</strong> <?php echo htmlspecialchars($booking['booking_id']); ?><br>
                        <strong>Customer:</strong> <?php echo htmlspecialchars($booking['customer_name']); ?><br>
                        <strong>Package:</strong> <?php echo htmlspecialchars($booking['package_name']); ?><br>
                        <strong>Check-in:</strong> <?php echo date('Y-m-d', strtotime($booking['arrival_date'])); ?><br>
                        <strong>Check-out:</strong> <?php echo date('Y-m-d', strtotime($booking['departure_date'])); ?><br>
                        <strong>Status:</strong> 
                        <span style="color: <?php 
                            echo $booking['status'] === 'confirmed' ? '#27ae60' : 
                                ($booking['status'] === 'pending' ? '#f39c12' : 
                                ($booking['status'] === 'cancelled' ? '#e74c3c' : '#3498db')); 
                        ?>">
                            <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
