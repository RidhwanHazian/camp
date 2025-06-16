<?php
// package_detail_staff.php
include 'session_check.php';
checkStaffSession();

include 'db_connection.php'; // Your DB connection file

// Fetch packages
$packages = [];
$sql = "SELECT * FROM packages";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $packages[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Package Details - Staff</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:600,700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: #f7f8fa;
            margin: 0;
        }
        .sidebar {
            width: 260px;
            background: #6c5ce7;
            color: #fff;
            height: 100vh;
            position: fixed;
            left: 0; top: 0;
            display: flex;
            flex-direction: column;
            padding: 32px 0;
        }
        .sidebar h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 40px;
            text-align: center;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            font-size: 1.25rem;
            font-weight: 600;
            padding: 18px 40px;
            display: block;
            border-radius: 8px 0 0 8px;
            margin-bottom: 8px;
            transition: background 0.2s;
        }
        .sidebar a.active, .sidebar a:hover {
            background: #4834d4;
        }
        .main-content {
            margin-left: 260px;
            padding: 40px 60px;
        }
        .main-content h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(80, 80, 160, 0.08);
            padding: 32px;
            margin-bottom: 32px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
        }
        th, td {
            padding: 16px 12px;
            text-align: left;
        }
        th {
            background: #6c5ce7;
            color: #fff;
            font-size: 1.1rem;
        }
        tr {
            background: #f7f8fa;
        }
        tr:nth-child(even) {
            background: #ecebff;
        }
        .btn {
            background: #6c5ce7;
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #4834d4;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.3);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #fff;
            border-radius: 12px;
            padding: 32px 28px;
            min-width: 320px;
            box-shadow: 0 4px 24px rgba(80, 80, 160, 0.12);
            position: relative;
        }
        .modal-content h2 {
            font-size: 1.5rem;
            margin-bottom: 18px;
        }
        .close {
            position: absolute;
            right: 18px;
            top: 12px;
            font-size: 1.5rem;
            color: #6c5ce7;
            cursor: pointer;
        }
        select, .modal-content button {
            font-size: 1rem;
            padding: 8px 12px;
            margin-top: 10px;
            border-radius: 6px;
            border: 1px solid #d1d1e0;
        }
        .modal-content button {
            background: #6c5ce7;
            color: #fff;
            border: none;
            margin-top: 18px;
            font-weight: 600;
            cursor: pointer;
        }
        .modal-content button:hover {
            background: #4834d4;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Staff</h2>
        <a href="staff_dashboard.php">Dashboard</a>
        <a href="customer_booking_staff.php">Customer Booking</a>
        <a href="package_detail_staff.php" class="active">Package Details</a>
        <a href="timetable_staff.php">Timetable</a>
        <a href="logout.php">Log Out</a>
    </div>
    <div class="main-content">
        <h1>Package Details</h1>
        <div class="card">
            <p style="font-size:1.1rem; margin-bottom:18px;">Update available packages for customers</p>
            <table>
                <tr>
                    <th>Package ID</th>
                    <th>Package Name</th>
                    <th>Description</th>
                    <th>Duration</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($packages as $pkg): ?>
                <tr>
                    <td><?= htmlspecialchars($pkg['package_id']) ?></td>
                    <td><?= htmlspecialchars($pkg['package_name']) ?></td>
                    <td><?= htmlspecialchars($pkg['description'] ?? '') ?></td>
                    <td><?= htmlspecialchars($pkg['duration'] ?? '') ?></td>
                    <td>
                        <button class="btn" onclick="openModal('<?= $pkg['package_id'] ?>')">Edit Package</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="statusModal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Edit Package</h2>
            <form id="updateStatusForm" method="post" action="update_package_staff.php">
                <input type="hidden" name="package_id" id="modalPackageId">
                <label for="packageName">Package Name:</label><br>
                <input type="text" name="package_name" id="packageName" required><br>
                <label for="description">Description:</label><br>
                <textarea name="description" id="description" rows="4"></textarea><br>
                <label for="duration">Duration:</label><br>
                <input type="text" name="duration" id="duration"><br>
                <button type="submit">Update Package</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(packageId) {
            document.getElementById('modalPackageId').value = packageId;
            document.getElementById('statusModal').style.display = 'flex';
        }
        function closeModal() {
            document.getElementById('statusModal').style.display = 'none';
        }
        // Close modal on outside click
        window.onclick = function(event) {
            var modal = document.getElementById('statusModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>