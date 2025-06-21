<?php
session_start();                    // Start session after enabling error reporting
include 'db_connection.php';
include 'session_check.php';        // Load session check functions
checkAdminSession(); 

$schedule_query = "SELECT 
    s.staff_name,
    t.task_id,
    t.task_date,
    t.task_shiftTime as shift_time,
    t.task_location as location,
    t.task_activity as task_description
FROM task_assignment t 
JOIN staff s ON t.staff_id = s.staff_id
ORDER BY t.task_date DESC";
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

    .section-buttons button {
      padding: 10px 20px;
      margin: 0 10px;
      background-color: #3498db;
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .section-buttons button:hover {
      background-color: #2980b9;
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
  <a href="manage_feedback.php"><i class="fas fa-comments"></i> Feedback Customer</a>
  <a href="customer_payment.php"><i class="fas fa-credit-card"></i> Customer Payment</a>
  <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">
    <i class="fas fa-sign-out-alt"></i> Logout
  </a>
</div>

<div class="content">
  <h1>Manage Staff</h1>
  <div class="section-buttons" style="text-align: center; margin-bottom: 2rem;">
    <button onclick="scrollToSection('staff-section')">Staff List</button>
    <button onclick="scrollToSection('schedule-section')">Staff Schedule</button>
    <button onclick="scrollToSection('facility-section')">Assigned Facilities</button>
  </div>

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
          <th>Date</th>
          <th>Shift Time</th>
          <th>Location</th>
          <th>Task Description</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php
      if ($schedule_result && $schedule_result->num_rows > 0) {
        while ($row = $schedule_result->fetch_assoc()) {
          echo "<tr>
            <td>" . htmlspecialchars($row['staff_name']) . "</td>
            <td>" . htmlspecialchars($row['task_date']) . "</td>
            <td>" . htmlspecialchars($row['shift_time']) . "</td>
            <td>" . htmlspecialchars($row['location']) . "</td>
            <td>" . htmlspecialchars($row['task_description']) . "</td>
            <td class='action-buttons'>
              <a href='edit_schedule.php?id=" . $row['task_id'] . "'>Edit</a>
              <a href='delete_schedule.php?id=" . $row['task_id'] . "' onclick='return confirm(\"Are you sure you want to delete this schedule?\");'>Delete</a>
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
        $assignment_sql = "SELECT sf.staff_id, s.staff_name, f.facility_name
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
                  </tr>";
          }
        } else {
          echo "<tr><td colspan='3' style='text-align:center; color:#aaa;'>No facility assignments yet.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  function scrollToSection(id) {
    const section = document.getElementById(id);
    if (section) {
      section.scrollIntoView({ behavior: 'smooth' });
    }
  }
</script>

</body>
</html>