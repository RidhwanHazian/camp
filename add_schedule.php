<?php
include 'session_check.php';
checkAdminSession();

require_once 'db_connection.php'; // Connect to your DB

// Fetch staff list
$staffResult = $conn->query("SELECT staff_id, staff_name FROM staff");

// Fetch booking details for the dropdown
$bookingDetailsResult = $conn->query("
    SELECT b.booking_id, b.full_name, p.package_name
    FROM bookings b
    LEFT JOIN packages p ON b.package_id = p.package_id
    ORDER BY b.booking_id DESC
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Schedule - TasikBiruCamps</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background-color: #f4f4f4;
    }

    .container {
      max-width: 600px;
      margin: 60px auto;
      background-color: #fff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    h1 {
      color: #2c3e50;
      text-align: center;
      margin-bottom: 1.5rem;
    }

    label {
      display: block;
      margin-top: 1rem;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    input, select, textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    textarea {
      resize: vertical;
      min-height: 80px;
    }

    button {
      margin-top: 1.5rem;
      width: 100%;
      padding: 10px;
      background-color: #27ae60;
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
    }

    button:hover {
      background-color: #219150;
    }

    .back-link {
      display: block;
      text-align: center;
      margin-top: 1rem;
      color: #2980b9;
      text-decoration: none;
    }

    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<?php
if (isset($_GET['success'])) {
    echo "<script>alert('Schedule added successfully!');</script>";
}
if (isset($_GET['error'])) {
    echo "<script>alert('Failed to add schedule.');</script>";
}
?>

<div class="container">
  <h1>Add Staff Schedule</h1>

  <form method="POST" action="process_add_schedule.php">
    <label for="staff_id">Staff Name</label>
    <select id="staff_id" name="staff_id" required>
      <option value="" disabled selected>Select a staff member</option>
      <?php
      if ($staffResult && $staffResult->num_rows > 0) {
          while ($row = $staffResult->fetch_assoc()) {
              echo "<option value='" . $row['staff_id'] . "'>" . htmlspecialchars($row['staff_name']) . "</option>";
          }
      } else {
          echo "<option disabled>No staff available</option>";
      }
      ?>
    </select>

    <label for="booking_id">Assign to Booking</label>
    <select id="booking_id" name="booking_id" required>
      <option value="" disabled selected>Select a booking</option>
      <?php
      if ($bookingDetailsResult && $bookingDetailsResult->num_rows > 0) {
          while ($row = $bookingDetailsResult->fetch_assoc()) {
              $display_text = "Booking ID: " . htmlspecialchars($row['booking_id']) . ", Customer: " . htmlspecialchars($row['full_name']) . ", Package: " . htmlspecialchars($row['package_name']);
              echo "<option value=\"" . htmlspecialchars($row['booking_id']) . "\">" . $display_text . "</option>";
          }
      } else {
          echo "<option disabled>No bookings available</option>";
      }
      ?>
    </select>

    <button type="submit">Add Schedule</button>
  </form>

  <a href="manage_staff.php" class="back-link">← Back to Staff List</a>
</div>

</body>
</html>
