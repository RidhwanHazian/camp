<?php
require_once 'check_admin_auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Customer Feedback - Admin Panel</title>
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
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th, td {
      padding: 12px 15px;
      border: 1px solid #ccc;
      text-align: left;
    }

    th {
      background-color: #f4f4f4;
    }

    td small {
      display: block;
      color: #666;
      font-size: 0.85em;
      margin-top: 4px;
    }

    tr:hover {
      background-color: #f9f9f9;
    }

    td.rating-emoji {
      font-size: 1.8rem;
      text-align: center;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <h2>Admin</h2>
  <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="manage_bookings.php"><i class="fas fa-calendar-check"></i> Manage Bookings</a>
  <a href="manage_campsites.php"><i class="fas fa-campground"></i> Manage Campsites</a>
  <a href="manage_staff.php"><i class="fas fa-users"></i> Manage Staff</a>
  <a href="customer_feedback.php" class="active"><i class="fas fa-comments"></i> Feedback Customer</a>
  <a href="customer_payment.php"><i class="fas fa-credit-card"></i> Customer Payment</a>
  <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
  <h1>Customer Feedback</h1>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Customer Name</th>
        <th>Package Chosen</th>
        <th>Feedback</th>
        <th>Rating</th>
        <th>Media</th>
      </tr>
    </thead>
    <tbody>
      <?php
      include 'db_connection.php';

      // Debug: Print any MySQL errors
      mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

      try {
        // Fetch all feedback with booking and package details
        $sql = "SELECT f.*, b.full_name, b.package_id, p.package_name, p.description
                FROM feedback f 
                LEFT JOIN bookings b ON f.booking_id = b.booking_id
                LEFT JOIN packages p ON b.package_id = p.package_id
                ORDER BY f.feedback_id DESC";

        $result = mysqli_query($conn, $sql);
        
        if (!$result) {
          throw new Exception("Query failed: " . mysqli_error($conn));
        }

        if (mysqli_num_rows($result) > 0) {
          $counter = 1;
          while ($row = mysqli_fetch_assoc($result)) {
            // Debug: Print row data
            // echo "<pre>"; print_r($row); echo "</pre>";
            
            $ratingEmoji = match((int)$row['rating']) {
              5 => "😍",
              4 => "😊",
              3 => "😐",
              2 => "😕",
              1 => "😡",
              default => "❓"
            };

            $packageName = $row['package_name'] ?? 'Unknown Package';
            if (!empty($row['description'])) {
              $packageName .= "<br><small>" . htmlspecialchars($row['description']) . "</small>";
            }

            echo "<tr>
                    <td>{$counter}</td>
                    <td>" . htmlspecialchars($row['full_name'] ?? 'Anonymous') . "</td>
                    <td>{$packageName}</td>
                    <td>" . htmlspecialchars($row['comment']) . "</td>
                    <td class='rating-emoji' title='Rating: {$row['rating']}'>{$ratingEmoji}</td>
                    <td>";

            // Check if photo exists and display it
            if (!empty($row['photo_path'])) {
              $photoPath = htmlspecialchars($row['photo_path']);
              echo "<a href='{$photoPath}' target='_blank' title='View Photo'>🖼️</a> ";
            }

            // Check if video exists and display it
            if (!empty($row['video_path'])) {
              $videoPath = htmlspecialchars($row['video_path']);
              echo "<a href='{$videoPath}' target='_blank' title='View Video'>🎥</a>";
            }

            echo "</td></tr>";
            $counter++;
          }
        } else {
          echo "<tr><td colspan='6'>No feedback found.</td></tr>";
        }
      } catch (Exception $e) {
        echo "<tr><td colspan='6'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
      }
      ?>
    </tbody>
  </table>
</div>

</body>
</html>