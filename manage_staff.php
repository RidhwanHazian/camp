<?php
$conn = new mysqli("localhost", "root", "", "camp");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$schedule_query = "SELECT 
    s.staff_name, 
    b.arrival_date, 
    b.departure_date, b.full_name AS customer_name, b.status, b.booking_id FROM bookings b JOIN staff s ON b.staff_id = s.staff_id ORDER BY b.arrival_date DESC";
$schedule_result = $conn->query($schedule_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Staff - TasikBiruCamps</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background-color: #f5f7fa;
      display: flex;
      min-height: 100vh;
      color: #333;
    }

    .sidebar {
      width: 250px;
      background: #2c3e50;
      color: #ecf0f1;
      height: 100vh;
      position: fixed;
      display: flex;
      flex-direction: column;
      padding: 2rem 1rem;
      box-shadow: 2px 0 8px rgba(0,0,0,0.1);
    }

    .sidebar h2 {
      font-weight: 600;
      font-size: 1.8rem;
      text-align: center;
      margin-bottom: 2rem;
    }

    .sidebar a {
      padding: 12px 20px;
      margin: 0.5rem 0;
      color: #bdc3c7;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 500;
      display: flex;
      align-items: center;
      transition: background 0.3s, color 0.3s;
    }

    .sidebar a i {
      margin-right: 12px;
      font-size: 1rem;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background-color: #2980b9;
      color: white;
    }

    .content {
      margin-left: 250px;
      padding: 2.5rem 3rem;
      width: calc(100% - 250px);
    }

    h1 {
      font-size: 2.2rem;
      font-weight: 600;
      margin-bottom: 2rem;
      color: #34495e;
      text-align: center; 
    }


    .section-header {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: 1rem;
      flex-wrap: wrap;
    }


    .search-box {
      display: flex;
      align-items: center;
    }

    .search-box input[type="text"] {
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      outline: none;
    }

    .add-button {
      padding: 10px 20px;
      background-color: #27ae60;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      font-weight: bold;
      white-space: nowrap;
    }

    .add-button:hover {
      background-color: #219150;
    }

    .table-container {
      margin-top: 1rem; 
      background: #fff;
      padding: 1.5rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      margin-bottom: 3rem;
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 15px;
    }

    th, td {
      padding: 14px 18px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #ecf0f1;
      color: #333;
    }

    tr:hover {
      background-color: #f1f1f1;
    }

    .action-buttons a {
      margin-right: 10px;
      color: #2980b9;
      text-decoration: none;
      font-weight: 600;
    }

    .action-buttons a:hover {
      text-decoration: underline;
    }

    html {
      scroll-behavior: smooth;
    }

    .sidebar .section-label {
      padding-left: 10px;
      font-size: 0.75rem;
      color: #95a5a6;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-top: 1rem;
      margin-bottom: 0.3rem;
    }

    .sidebar .sub-link {
      font-size: 0.85rem;
      padding-left: 30px;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <h2>Admin</h2>
  <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="manage_bookings.php"><i class="fas fa-calendar-check"></i> Manage Bookings</a>
  <a href="manage_campsites.php"><i class="fas fa-campground"></i> Manage Packages</a>
  <a href="manage_staff.php" class="active"><i class="fas fa-users"></i> Manage Staff</a>

  <div style="margin: 0.1rem 0; border-top: 1px solid #7f8c8d;"></div>
  <div class="section-label">Manage staff sidebar</div>
  <a href="#staff-section" class="sub-link"><i class="fas fa-user"></i> Staff List</a>
  <a href="#schedule-section" class="sub-link"><i class="fas fa-clock"></i> Staff Schedule</a>
  <a href="#facility-section" class="sub-link"><i class="fas fa-building"></i> Facilities</a>

  <div style="margin: 0.1rem 0; border-top: 1px solid #7f8c8d;"></div>
  <a href="manage_feedback.php"><i class="fas fa-comments"></i> Feedback Customer</a>
  <a href="customer_payment.php"><i class="fas fa-credit-card"></i> Customer Payment</a>
  <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">
    <i class="fas fa-sign-out-alt"></i> Logout
  </a>
</div>

<div class="content">
  <h1>Manage Staff</h1>
  

  <!-- Staff Section -->
  <div id="staff-section" class="section-header">
    <div class="search-box">
      <input type="text" placeholder="Search staff...">
    </div>
    <a class="add-button" href="add_staff.php">+ Add New Staff</a>
  </div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Staff Name</th>
          <th>Username</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $staff_sql = "SELECT * FROM staff";
        $staff_result = $conn->query($staff_sql);
        $num = 1;

        if ($staff_result->num_rows > 0) {
          while ($row = $staff_result->fetch_assoc()) {
            echo "<tr>
              <td>{$num}</td>
              <td>{$row['staff_name']}</td>
              <td>{$row['staff_username']}</td>
              <td>{$row['staff_email']}</td>
              <td>{$row['staff_notel']}</td>
              <td class='action-buttons'>
                <a href='edit_staff.php?id={$row['staff_id']}'>Edit</a>
                <a href='delete_staff.php?id={$row['staff_id']}' onclick='return confirm(\"Are you sure you want to delete this staff?\");'>Delete</a>
              </td>
            </tr>";
            $num++;
          }
        } else {
          echo "<tr><td colspan='6'>No staff found.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <!-- Schedule Section -->
  <div id="schedule-section" class="section-header">
    <div class="search-box">
      <input type="text" placeholder="Search schedule...">
    </div>
    <a class="add-button" href="add_schedule.php">+ Add Schedule</a>
  </div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Staff Name</th>
          <th>Date Start</th>
          <th>Date End</th>
          <th>Customer</th>
          <th>Payment Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php
      if ($schedule_result && $schedule_result->num_rows > 0) {
        while ($row = $schedule_result->fetch_assoc()) {
          echo "<tr>
            <td>" . htmlspecialchars($row['staff_name']) . "</td>
            <td>" . htmlspecialchars($row['arrival_date']) . "</td>
            <td>" . htmlspecialchars($row['departure_date']) . "</td>
            <td>" . htmlspecialchars($row['customer_name']) . "</td>
            <td>" . htmlspecialchars($row['status']) . "</td>
            <td class='action-buttons'>
              <a href='edit_schedule.php?id=" . $row['booking_id'] . "'>Edit</a>
              <a href='delete_schedule.php?id=" . $row['booking_id'] . "' onclick='return confirm(\"Are you sure you want to delete this schedule?\");'>Delete</a>
            </td>
          </tr>";
        }
      } else {
        echo "<tr><td colspan='6'>No schedule found.</td></tr>";
      }
      ?>
      </tbody>
    </table>
  </div>

  <!-- Assign Facilities Section -->
  <div id="facility-section" class="section-header">
    <div class="search-box">
      <input type="text" placeholder="Search facility assignments...">
    </div>
    <a class="add-button" href="assign_facilities.php">+ Assign Facilities</a>
  </div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Staff Name</th>
          <th>Facility Name</th>
          <th>Actions</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $assignment_sql = "SELECT sf.staff_id, s.staff_name, f.facility_name, sf.status
                          FROM staff_facilities sf
                          JOIN staff s ON sf.staff_id = s.staff_id
                          JOIN facilities f ON sf.facility_id = f.facility_id";
        $assignment_result = $conn->query($assignment_sql);

        if ($assignment_result && $assignment_result->num_rows > 0) {
          while ($row = $assignment_result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['staff_name']) . "</td>
                    <td>" . htmlspecialchars($row['facility_name']) . "</td>
                    <td class='action-buttons'>
                      <a href='edit_assign_facilities.php?staff_id=" . $row['staff_id'] . "&facility_name=" . urlencode($row['facility_name']) . "'>Edit</a>
                      <a href='delete_assign_facilities.php?staff_id=" . $row['staff_id'] . "&facility_name=" . urlencode($row['facility_name']) . "' onclick='return confirm(\"Are you sure you want to delete this assignment?\");'>Delete</a>
                    </td>
                    <td>" . ($row['status'] === 'done' ? "<span style='color:#27ae60;font-weight:bold;'>Complete</span>" : "<span style='color:#c0392b;font-weight:bold;'>Pending</span>") . "</td>\n                </tr>";
          }
        } else {
          echo "<tr><td colspan='4' style='text-align:center; color:#aaa;'>No facility assignments yet.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
