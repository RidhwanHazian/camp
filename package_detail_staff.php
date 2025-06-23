<?php
// package_detail_staff.php
include 'session_check.php';
checkStaffSession();
include 'db_connection.php';

// Handle per-date slot update
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_slot'])) {
    $package_id = intval($_POST['package_id']);
    $slot_date = $_POST['slot_date'];
    $slot_limit = intval($_POST['slot_limit']);

    // Check if entry exists
    $stmt = $conn->prepare("SELECT * FROM package_slots WHERE package_id = ? AND slot_date = ?");
    $stmt->bind_param("is", $package_id, $slot_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update
        $update = $conn->prepare("UPDATE package_slots SET slot_limit = ? WHERE package_id = ? AND slot_date = ?");
        $update->bind_param("iis", $slot_limit, $package_id, $slot_date);
        $update->execute();
    } else {
        // Insert
        $insert = $conn->prepare("INSERT INTO package_slots (package_id, slot_date, slot_limit) VALUES (?, ?, ?)");
        $insert->bind_param("isi", $package_id, $slot_date, $slot_limit);
        $insert->execute();
    }
    $success = true;
}

// Fetch all (package, date) combinations with bookings
$sql = "SELECT 
            b.package_id,
            p.package_name,
            b.arrival_date,
            COALESCE(ps.slot_limit, 0) AS slot_limit,
            COUNT(b.booking_id) AS booking_count
        FROM bookings b
        JOIN packages p ON b.package_id = p.package_id
        LEFT JOIN package_slots ps 
            ON b.package_id = ps.package_id AND b.arrival_date = ps.slot_date
        WHERE b.arrival_date >= CURDATE()
        GROUP BY b.package_id, p.package_name, b.arrival_date, ps.slot_limit
        ORDER BY p.package_name ASC, b.arrival_date ASC";
$result = $conn->query($sql);
$package_dates = [];
while ($row = $result->fetch_assoc()) {
    $package_dates[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Package Details - Staff</title>
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
        h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 2px 12px rgba(80,80,160,0.08);
            border-radius: 12px;
            overflow: hidden;
        }
        thead {
            background: #6f74c6;
            color: #fff;
        }
        thead th {
            background: #6f74c6;
            color: #fff;
        }
        th:first-child {
            border-radius: 16px 0 0 0;
        }
        th:last-child {
            border-radius: 0 16px 0 0;
        }
        th, td {
            padding: 16px 12px;
            text-align: left;
        }
        th {
            font-size: 1.1rem;
        }
        tr {
            background: #f7f8fa;
        }
        tr:nth-child(even) {
            background: #ecebff;
        }
        form {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        input[type="number"] {
            width: 70px;
            padding: 6px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button[type="submit"] {
            background: #6f74c6;
            color: #fff;
            border: none;
            padding: 7px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }
        button[type="submit"]:hover {
            background: #343795;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 18px;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2><i class="fas fa-user-shield"></i> Staff</h2>
        <a href="staff_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="customer_booking_staff.php"><i class="fas fa-calendar-check"></i> Payment Verification</a>
        <a href="package_detail_staff.php" class="active"><i class="fas fa-box"></i> Package Details</a>
        <a href="timetable_staff.php"><i class="fas fa-clock"></i> Timetable</a>
        <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
    <div class="main">
        <h1>Manage Package Slots Per Date</h1>
        <?php if ($success): ?>
            <div class="success-message">Slot limit updated successfully!</div>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th style="color: #fff; background: #6f74c6;">Package Name</th>
                    <th>Date</th>
                    <th>Slot Limit</th>
                    <th>Booked</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($package_dates as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['package_name']) ?></td>
                    <td><?= htmlspecialchars($row['arrival_date']) ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="number" name="slot_limit" value="<?= (int)$row['slot_limit'] ?>" min="1" required>
                            <input type="hidden" name="package_id" value="<?= $row['package_id'] ?>">
                            <input type="hidden" name="slot_date" value="<?= $row['arrival_date'] ?>">
                    </td>
                    <td><?= $row['booking_count'] ?></td>
                    <td>
                        <?= ($row['slot_limit'] > 0 && $row['booking_count'] >= $row['slot_limit']) ? 'Full' : 'Available' ?>
                    </td>
                    <td>
                            <button type="submit" name="update_slot">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
