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
            width: 250px; background-color: #6f74c6; color: white; padding: 40px 20px; height: 100vh;
        }
        .sidebar h2 { font-size: 48px; margin-bottom: 40px; }
        .sidebar a {
            display: block; color: white; font-weight: bold; font-size: 26px; text-decoration: none;
            margin-bottom: 25px; padding-left: 10px;
        }
        .sidebar a.active { background-color: #343795; padding: 12px 20px; border-radius: 12px; }
        .main { flex-grow: 1; padding: 30px; }
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
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Staff</h2>
        <a href="staff_dashboard.php">Dashboard</a>
        <a href="customer_booking_staff.php">Customer Booking</a>
        <a href="package_detail_staff.php">Package Details</a>
        <a href="timetable_staff.php" class="active">Timetable</a>
        <a href="logout.php">Log Out</a>
    </div>

    <div class="main">
        <h1>Work Timetable</h1>
        <p>This is your work schedule. Please refer to the following timetable.</p>

        <table class="styled-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Shift</th>
                    <th>Location</th>
                    <th>Assigned Task</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $staff_id = $_SESSION['staff_id'];
                    $stmt = $conn->prepare("
                        SELECT 
                            t.task_date as date,
                            t.task_shiftTime as shift,
                            t.task_location as location,
                            t.task_activity as task
                        FROM task_assignment t 
                        WHERE t.staff_id = ?
                        ORDER BY t.task_date ASC
                    ");
                    $stmt->bind_param("i", $staff_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['shift']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['task']) . "</td>";
                        echo "</tr>";
                    }
                } catch(Exception $e) { // Changed to generic Exception for mysqli errors
                    echo "<tr><td colspan='4'>Error: " . $e->getMessage() . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>