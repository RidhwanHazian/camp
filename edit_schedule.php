<?php
session_start();
require_once 'db_connection.php';

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get booking_id from URL
if (!isset($_GET['id'])) {
    header("Location: manage_staff.php");
    exit();
}
$booking_id = intval($_GET['id']);

// Fetch booking info
$stmt = $conn->prepare("SELECT b.*, s.staff_name FROM bookings b LEFT JOIN staff s ON b.staff_id = s.staff_id WHERE b.booking_id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    echo "Booking not found.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = $_POST['staff_id'];
    $update = $conn->prepare("UPDATE bookings SET staff_id = ? WHERE booking_id = ?");
    $update->bind_param("ii", $staff_id, $booking_id);
    if ($update->execute()) {
        header("Location: manage_staff.php?success=1");
        exit();
    } else {
        $error = "Failed to update staff assignment.";
    }
}

// Fetch all staff for dropdown
$staffResult = $conn->query("SELECT staff_id, staff_name FROM staff");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Staff Assignment</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f4f4; }
        .container { max-width: 500px; margin: 60px auto; background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);}
        h2 { color: #2c3e50; text-align: center; margin-bottom: 1.5rem; }
        label { display: block; margin-top: 1rem; margin-bottom: 0.5rem; font-weight: 600; }
        select, button { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; }
        button { background: #27ae60; color: white; font-weight: bold; margin-top: 1.5rem; border: none; cursor: pointer; }
        button:hover { background: #219150; }
        .back-link { display: block; text-align: center; margin-top: 1rem; color: #2980b9; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Staff Assignment</h2>
    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    <form method="POST">
        <label for="staff_id">Assign Staff</label>
        <select id="staff_id" name="staff_id" required>
            <option value="" disabled>Select a staff member</option>
            <?php
            if ($staffResult && $staffResult->num_rows > 0) {
                while ($row = $staffResult->fetch_assoc()) {
                    $selected = ($booking['staff_id'] == $row['staff_id']) ? 'selected' : '';
                    echo "<option value='" . $row['staff_id'] . "' $selected>" . htmlspecialchars($row['staff_name']) . "</option>";
                }
            } else {
                echo "<option disabled>No staff available</option>";
            }
            ?>
        </select>
        <button type="submit">Update Assignment</button>
    </form>
    <a href="manage_staff.php" class="back-link">← Back to Staff List</a>
</div>
</body>
</html>
