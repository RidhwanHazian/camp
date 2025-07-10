<?php
  session_start();                    // Start session after enabling error reporting
  include 'db_connection.php';
  include 'session_check.php';        // Load session check functions
  checkAdminSession(); 

  $startDate = $_GET['start_date'] ?? null;
  $endDate = $_GET['end_date'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <title>Customer Payment - Admin Panel</title>
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

    .dashboard-cards 
    {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
      margin-bottom: 2rem;
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
  </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin</h2>
    <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="manage_bookings.php"><i class="fas fa-calendar-check"></i> Manage Bookings</a>
    <a href="manage_campsites.php"><i class="fas fa-campground"></i> Manage Packages</a>
    <a href="manage_staff.php"><i class="fas fa-users"></i> Manage Staff</a>
    <a href="manage_feedback.php"><i class="fas fa-comments"></i> Feedback Customer</a>
    <a href="customer_payment.php" class="active"><i class="fas fa-credit-card"></i> Customer Payment</a>
    <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<div class="content">
  <h1>Customer Payments</h1>

  <!-- Button to print PDF report for this month -->
  <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
    <form method="get" action="customer_payment.php" style="display: flex; align-items: center; gap: 10px;">
      <input type="date" name="start_date" id="start_date"
            value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : '' ?>"
            style="padding: 8px; border-radius: 6px; border: 1px solid #ccc;" required>

      <span>to</span>

      <input type="date" name="end_date" id="end_date"
            value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : '' ?>"
            style="padding: 8px; border-radius: 6px; border: 1px solid #ccc;" required>

      <button type="submit"
              style="padding: 8px 14px; background-color: #2980b9; color: white; border: none; border-radius: 6px;">
        Filter
      </button>
    </form>

    <?php if (isset($_GET['start_date']) && isset($_GET['end_date'])): ?>
      <a href="customer_payment.php"
        style="padding: 8px 14px; background-color: #c0392b; color: white; border-radius: 6px; text-decoration: none;">
        Clear Filter
      </a>

      <form method="get" action="generate_payment_report.php" style="display: inline;">
        <input type="hidden" name="start_date" value="<?= htmlspecialchars($_GET['start_date']) ?>">
        <input type="hidden" name="end_date" value="<?= htmlspecialchars($_GET['end_date']) ?>">
        <button type="submit"
                style="padding: 8px 14px; background-color: #27ae60; color: white; border-radius: 6px; border: none;">
          <i class="fas fa-file-pdf"></i> Print PDF
        </button>
      </form>
    <?php endif; ?>
  </div>

  <?php if ($startDate && $endDate): ?>
    <p>Showing results from <strong><?= htmlspecialchars($startDate) ?></strong> to <strong><?= htmlspecialchars($endDate) ?></strong></p>
  <?php endif; ?>

  <div class="table-container">
    <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Customer Name</th>
        <th>Package Chosen</th>
        <th>Payment Date</th>
        <th>Total Cost (RM)</th>
        <th>Phone Number</th>
      </tr>
    </thead>
      <tbody>
        <?php
        if ($startDate && $endDate) {
          $sql = "
            SELECT 
              b.full_name,
              b.phone_no,
              pk.package_name,
              p.payment_date,
              p.amount
            FROM payments p
            JOIN bookings b ON p.booking_id = b.booking_id
            JOIN packages pk ON b.package_id = pk.package_id
            WHERE DATE(p.payment_date) BETWEEN '$startDate' AND '$endDate'
            ORDER BY p.payment_date DESC
          ";
        } else {
          $sql = "
            SELECT 
              b.full_name,
              b.phone_no,
              pk.package_name,
              p.payment_date,
              p.amount
            FROM payments p
            JOIN bookings b ON p.booking_id = b.booking_id
            JOIN packages pk ON b.package_id = pk.package_id
            ORDER BY p.payment_date DESC
          ";
        }

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $count = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                    <td>{$count}</td>
                    <td>" . htmlspecialchars($row['full_name']) . "</td>
                    <td>" . htmlspecialchars($row['package_name']) . "</td>
                    <td>" . date('Y-m-d', strtotime($row['payment_date'])) . "</td>
                    <td>" . number_format($row['amount'], 2) . "</td>
                    <td>" . htmlspecialchars($row['phone_no']) . "</td>
                </tr>";
                $count++;
            }
        } else {
            echo "<tr><td colspan='6'>No customer payments found.</td></tr>";
        }
        mysqli_close($conn);
        ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>