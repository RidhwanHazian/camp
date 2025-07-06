<?php
  session_start();                    
  include 'db_connection.php';
  include 'session_check.php';        
  checkAdminSession(); 
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
      text-align: center;
    }

    .table-container {
      background: #fff;
      padding: 1.5rem;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      overflow-x: auto;
      margin-top: 1.5rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 15px;
      min-width: 600px;
    }

    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #e0e0e0;
      vertical-align: top;
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

    td.rating-emoji {
      text-align: center !important;
      vertical-align: middle;
    }

    .star-rating {
      display: flex;
      font-size: 1rem;
      justify-content: center;
    }

    .star-rating .fa-star {
      margin: 0 2px;
      color: #ccc;
    }

    .star-rating .fa-star.filled {
      color: #f1c40f;
    }

    .feedback-media img, .feedback-media video {
      max-width: 100px;
      max-height: 100px;
      border-radius: 6px;
      display: block;
      margin-bottom: 5px;
    }

    .feedback-media .no-media {
      color: #888;
      font-style: italic;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <h2>Admin</h2>
  <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="manage_bookings.php"><i class="fas fa-calendar-check"></i> Manage Bookings</a>
  <a href="manage_campsites.php"><i class="fas fa-campground"></i> Manage Packages</a>
  <a href="manage_staff.php"><i class="fas fa-users"></i> Manage Staff</a>
  <a href="manage_feedback.php" class="active"><i class="fas fa-comments"></i> Feedback Customer</a>
  <a href="customer_payment.php"><i class="fas fa-credit-card"></i> Customer Payment</a>
  <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">
    <i class="fas fa-sign-out-alt"></i> Logout
  </a>
</div>

<div class="content">
  <h1>Customer Feedback</h1>
  <div class="table-container">
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
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
          $sql = "SELECT f.*, b.full_name, b.package_id, p.package_name, p.description
                  FROM feedback f 
                  LEFT JOIN bookings b ON f.booking_id = b.booking_id
                  LEFT JOIN packages p ON b.package_id = p.package_id
                  ORDER BY f.rating DESC";

          $result = mysqli_query($conn, $sql);

          if (mysqli_num_rows($result) > 0) {
            $counter = 1;
            while ($row = mysqli_fetch_assoc($result)) {
              $stars = '<div class="star-rating">';
              for ($i = 1; $i <= 5; $i++) {
                $filled = $i <= $row['rating'] ? 'filled' : '';
                $stars .= "<i class='fas fa-star $filled'></i>";
              }
              $stars .= '</div>';

              $packageName = $row['package_name'] ?? 'Unknown Package';
              if (!empty($row['description'])) {
                $packageName .= "<br><small>" . htmlspecialchars($row['description']) . "</small>";
              }

              echo "<tr>
                      <td>{$counter}</td>
                      <td>" . htmlspecialchars($row['full_name'] ?? 'Anonymous') . "</td>
                      <td>{$packageName}</td>
                      <td>" . htmlspecialchars($row['comment']) . "</td>
                      <td class='rating-emoji' title='Rating: {$row['rating']}'>{$stars}</td>
                      <td class='feedback-media'>";

              $hasMedia = false;

              if (!empty($row['photo_path'])) {
                echo "<img src='" . htmlspecialchars($row['photo_path']) . "' alt='Photo'>";
                $hasMedia = true;
              }

              if (!empty($row['video_path'])) {
                echo "<video src='" . htmlspecialchars($row['video_path']) . "' controls></video>";
                $hasMedia = true;
              }

              if (!$hasMedia) {
                echo "<span class='no-media'>No media attached</span>";
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
</div>

</body>
</html>
