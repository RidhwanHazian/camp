<?php
include 'session_check.php';
checkStaffSession();

require_once 'db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Timetable - Staff Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { display: flex; }
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
            min-height: 100vh;
            background: #fff;
            box-shadow: 0 4px 24px rgba(80,80,160,0.07);
            border-radius: 0 0 16px 16px;
        }
        .styled-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 0.9em;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
            border-radius: 5px;
            overflow: hidden;
        }
        .styled-table thead tr {
            background-color: #6f74c6;
            color: #ffffff;
            text-align: left;
        }
        .styled-table th,
        .styled-table td {
            padding: 12px 15px;
        }
        .styled-table tbody tr {
            border-bottom: 1px solid #dddddd;
        }
        .styled-table tbody tr:nth-of-type(even) {
            background-color: #f3f3f3;
        }
        .styled-table tbody tr:last-of-type {
            border-bottom: 2px solid #6f74c6;
        }
        .facility-list { list-style: none; padding: 0; margin: 0; }
        .facility-item {
            display: flex;
            align-items: center;
            background: #fff;
            border-radius: 30px;
            box-shadow: 0 1px 4px rgba(80,80,160,0.07);
            margin-bottom: 12px;
            padding: 10px 20px 10px 14px;
            min-width: 220px;
            max-width: 350px;
            gap: 12px;
        }
        .facility-icon {
            width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;
            background: #e0e7ff; color: #343795; border-radius: 50%; font-size: 1.2em;
        }
        .facility-name { font-weight: 500; color: #333; flex: 1; }
        .facility-btn {
            padding: 4px 16px;
            background: #27ae60;
            color: #fff;
            border: none;
            border-radius: 16px;
            font-size: 0.98em;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .facility-btn:hover { background: #219150; }
        .facility-status {
            color: #27ae60;
            font-weight: bold;
            background: #eafaf1;
            border-radius: 16px;
            padding: 4px 16px;
            font-size: 0.98em;
        }
        .schedule-table {
            margin: 0;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            box-shadow: none;
        }
        .schedule-table thead tr {
            background: linear-gradient(90deg, #6f74c6 60%, #a5a8f7 100%);
            color: #fff;
            font-size: 1.05em;
            letter-spacing: 1px;
        }
        .schedule-table th, .schedule-table td {
            padding: 14px 18px;
            text-align: left;
        }
        .schedule-table tbody tr {
            border-bottom: 1px solid #e0e7ff;
            background: #f8f9ff;
            transition: background 0.2s;
        }
        .schedule-table tbody tr:nth-of-type(even) {
            background: #f3f3f3;
        }
        .schedule-table tbody tr:hover {
            background: #e0e7ff;
        }
        .schedule-table tbody tr:last-of-type {
            border-bottom: 2px solid #6f74c6;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2><i class="fas fa-user-shield"></i> Staff</h2>
        <a href="staff_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="customer_booking_staff.php"><i class="fas fa-calendar-check"></i> Payment Verification</a>
        <a href="package_detail_staff.php"><i class="fas fa-box"></i> Package Details</a>
        <a href="timetable_staff.php" class="active"><i class="fas fa-clock"></i> Timetable</a>
        <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>

    <div class="main">
        <h1>Work Timetable</h1>
        <p style="margin-bottom: 22px; color: #444; font-size: 1.08em;">Check your assigned facilities and schedule below.</p>

        <?php
            $staff_id = $_SESSION['staff_id'];
        // Fetch assigned facilities for this staff
        $facility_stmt = $conn->prepare("SELECT f.facility_id, f.facility_name, sf.status FROM staff_facilities sf JOIN facilities f ON sf.facility_id = f.facility_id WHERE sf.staff_id = ?");
        $facility_stmt->bind_param("i", $staff_id);
        $facility_stmt->execute();
        $facility_result = $facility_stmt->get_result();
        $facilities = [];
        while ($row = $facility_result->fetch_assoc()) {
            $facilities[] = $row;
        }
        ?>
        <div style="margin-bottom: 25px; padding: 18px; background: #f3f3f3; border-radius: 12px; box-shadow: 0 2px 8px rgba(80,80,160,0.06);">
            <h2 style="margin-bottom: 16px; color: #343795; font-size: 1.2em; letter-spacing: 1px;">Assigned Facilities</h2>
            <?php if (count($facilities) > 0): ?>
                <ul class="facility-list">
                    <?php foreach ($facilities as $facility): ?>
                        <li class="facility-item">
                            <span class="facility-icon">
                                <?php
                                // Simple icon logic (add more as needed)
                                $icon = '🏢';
                                if (stripos($facility['facility_name'], 'electric') !== false) $icon = '🔌';
                                elseif (stripos($facility['facility_name'], 'hall') !== false) $icon = '🏛️';
                                elseif (stripos($facility['facility_name'], 'water') !== false) $icon = '💧';
                                elseif (stripos($facility['facility_name'], 'toilet') !== false) $icon = '🚻';
                                echo $icon;
                                ?>
                            </span>
                            <span class="facility-name"><?= htmlspecialchars($facility['facility_name']) ?></span>
                            <?php if ($facility['status'] === 'pending'): ?>
                                <form method="post" action="mark_facility_done.php" style="margin:0;">
                                    <input type="hidden" name="facility_id" value="<?= $facility['facility_id'] ?>">
                                    <button type="submit" class="facility-btn">Mark as Done</button>
                                </form>
                            <?php else: ?>
                                <span class="facility-status">Completed</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <span style="color: #888;">No facilities assigned.</span>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 18px;">
            <h2 style="margin-bottom: 10px; color: #343795; font-size: 1.2em; letter-spacing: 1px;">Work Schedule</h2>
        </div>

        <div style="background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(80,80,160,0.10); padding: 24px 18px 18px 18px; margin-bottom: 20px;">
            <table class="styled-table schedule-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Date</th>
                        <th>Package</th>
                        <th>Total Adult</th>
                        <th>Total Kids</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $stmt = $conn->prepare("SELECT b.booking_id, b.arrival_date, b.departure_date, b.num_adults, b.num_children, p.package_name FROM bookings b LEFT JOIN packages p ON b.package_id = p.package_id WHERE b.staff_id = ? ORDER BY b.arrival_date ASC");
                        $stmt->bind_param("i", $staff_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                    if ($result->num_rows === 0) {
                        echo '<tr><td colspan="5" style="text-align:center;color:#888;">No schedule assigned yet.</td></tr>';
                    } else {
                        while ($row = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['booking_id']) . '</td>';
                            echo '<td>' . date('M d, Y', strtotime($row['arrival_date'])) . ' - ' . date('M d, Y', strtotime($row['departure_date'])) . '</td>';
                            echo '<td>' . htmlspecialchars($row['package_name']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['num_adults']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['num_children']) . '</td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
