<?php
include 'db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Campsites - TasikBiruCamps</title>
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
      padding: 2rem 1rem;  
      width: calc(100% - 250px);
    }

    h1 {
      font-size: 2.2rem;
      font-weight: 600;
      margin-bottom: 2rem;
      color: #34495e;
      text-align: center;
    }

    .dashboard-cards 
    {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
      margin-bottom: 2rem;
    }

    .table-container 
    {
      background: #fff;
      padding: 1.5rem;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      margin-top: 2rem;
      overflow-x: auto; /* Adds scroll on small screens */
      }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 15px;
      min-width: 600px; /* Prevents table from shrinking too small */
    }

    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #e0e0e0;
    }

    th {
      background-color: #f8f9fa;
      color: #2c3e50;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 13px;
      letter-spacing: 0.5px;
    }

    tr:hover {
      background-color: #f1f1f1;
      transition: background 0.2s ease-in-out;
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

    .add-button {
      margin-bottom: 1rem;
      display: inline-block;
      padding: 10px 20px;
      background-color: #27ae60;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      font-weight: bold;
    }

    .add-button:hover {
      background-color: #219150;
    }
  </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin</h2>
    <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="manage_bookings.php" ><i class="fas fa-calendar-check"></i> Manage Bookings</a>
    <a href="manage_campsites.php" class="active"><i class="fas fa-campground"></i> Manage Campsites</a>
    <a href="manage_staff.php"><i class="fas fa-users"></i> Manage Staff</a>
    <a href="manage_feedback.php"><i class="fas fa-comments"></i> Feedback Customer</a>
    <a href="customer_payment.php"><i class="fas fa-credit-card"></i> Customer Payment</a>
    <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </div>

<div class="content">
  <h1>Manage Campsites</h1>

  <a class="add-button" href="add_campsite.php">+ Add New Campsite</a>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Bil</th>
          <th>Campsite Name</th>
          <th>Package Name</th>
          <th>Description</th>
          <th>Duration</th>
          <th>Activities</th>
          <th>Location</th>
          <th>Price (RM)</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $count = 1;
      $campsite_result = mysqli_query($conn, "SELECT * FROM campsites ORDER BY camp_id ASC");
      while ($camp = mysqli_fetch_assoc($campsite_result)) {
          $camp_id = $camp['camp_id'];
          $package_sql = "
              SELECT p.*, pp.adult_price, pp.child_price
              FROM campsite_packages cp
              JOIN packages p ON cp.package_id = p.package_id
              LEFT JOIN package_prices pp ON p.package_id = pp.package_id
              WHERE cp.camp_id = $camp_id
          ";
          $package_result = mysqli_query($conn, $package_sql);

          if (mysqli_num_rows($package_result) > 0) {
              while ($package = mysqli_fetch_assoc($package_result)) {
                  $package_id = $package['package_id'];
                  $activity_sql = "
                      SELECT a.activity_name
                      FROM package_activities pa
                      JOIN activities a ON pa.activity_id = a.activity_id
                      WHERE pa.package_id = $package_id
                  ";
                  $activity_result = mysqli_query($conn, $activity_sql);
                  $activities = [];
                  while ($act = mysqli_fetch_assoc($activity_result)) {
                      $activities[] = $act['activity_name'];
                  }
                  echo "<tr>
                      <td>{$count}</td>
                      <td>" . htmlspecialchars($camp['camp_name']) . "</td>
                      <td>" . htmlspecialchars($package['package_name']) . "</td>
                      <td>" . htmlspecialchars($package['description']) . "</td>
                      <td>" . htmlspecialchars($package['duration']) . "</td>
                      <td>" . htmlspecialchars(implode(', ', $activities)) . "</td>
                      <td>" . htmlspecialchars($camp['camp_location']) . "</td>
                      <td>Adult: RM " . number_format($package['adult_price'], 2) . "<br>Child: RM " . number_format($package['child_price'], 2) . "</td>
                      <td class='action-buttons'>
                          <a href='edit_campsite.php?id=" . $camp['camp_id'] . "'>Edit</a>
                          <a href='delete_campsite.php?id=" . $camp['camp_id'] . "' onclick='return confirm(\"Are you sure you want to delete this campsite?\");'>Delete</a>
                      </td>
                  </tr>";
                  $count++;
              }
          } else {
              // If no packages, show the campsite row with empty package/activity/price
              echo "<tr>
                  <td>{$count}</td>
                  <td>" . htmlspecialchars($camp['camp_name']) . "</td>
                  <td colspan='7' style='text-align:center;'>No packages assigned</td>
              </tr>";
              $count++;
          }
      }
      ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function confirmCampsiteDelete(campId) {
    if (confirm('Are you sure you want to delete this campsite? This will also remove all associated packages and activities.')) {
        // Create and submit form
        var form = document.createElement('form');
        form.setAttribute('method', 'POST');
        form.setAttribute('action', 'delete_campsite.php');
        
        var input = document.createElement('input');
        input.setAttribute('type', 'hidden');
        input.setAttribute('name', 'id');
        input.setAttribute('value', campId);
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

</body>
</html>