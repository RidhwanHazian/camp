<?php
  session_start();                    // Start session after enabling error reporting
  include 'db_connection.php';
  include 'session_check.php';        // Load session check functions
  checkAdminSession(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Bookings - Admin Panel</title>
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

    .search-bar {
      margin-bottom: 1rem;
    }

    .search-bar input {
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      outline: none;
    }

    .search-bar button {
      padding: 8px 15px;
      background: #27ae60;
      color: white;
      border: none;
      cursor: pointer;
      font-weight: bold;
      border-radius: 4px;
    }

    .search-bar button:hover {
      background: #219150;
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
  <a href="manage_bookings.php" class="active"><i class="fas fa-calendar-check"></i> Manage Bookings</a>
  <a href="manage_campsites.php"><i class="fas fa-campground"></i> Manage Packages</a>
  <a href="manage_staff.php"><i class="fas fa-users"></i> Manage Staff</a>
  <a href="manage_feedback.php"><i class="fas fa-comments"></i> Feedback Customer</a>
  <a href="customer_payment.php"><i class="fas fa-credit-card"></i> Customer Payment</a>
  <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">
    <i class="fas fa-sign-out-alt"></i> Logout
  </a>
</div>

<div class="content">
  <h1>Manage Bookings</h1>

  <div class="search-bar">
    <form method="get" action="manage_bookings.php">
      <input type="text" name="search" placeholder="Search by customer name..." />
      <button type="submit">Search</button>
    </form>
  </div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Customer Name</th>
          <th>Package</th>
          <th>Telephone</th>
          <th>Date</th>
          <th>Duration</th>
          <th>Payment</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        include 'db_connection.php';

        $searchKeyword = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

        $sql = "
            SELECT b.booking_id, b.full_name, b.phone_no, b.arrival_date, b.status,
                  pk.package_name, pk.duration
            FROM bookings b
            JOIN packages pk ON b.package_id = pk.package_id
        ";

        if (!empty($searchKeyword)) {
            $sql .= " WHERE LOWER(b.full_name) LIKE '%" . mysqli_real_escape_string($conn, $searchKeyword) . "%'";
        }

        $sql .= " ORDER BY b.arrival_date DESC";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $counter = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                    <td>{$counter}</td>
                    <td>" . htmlspecialchars($row['full_name']) . "</td>
                    <td>" . htmlspecialchars($row['package_name']) . "</td>
                    <td>" . htmlspecialchars($row['phone_no']) . "</td>
                    <td>" . htmlspecialchars($row['arrival_date']) . "</td>
                    <td>" . htmlspecialchars($row['duration']) . "</td>
                    <td>" . htmlspecialchars($row['status']) . "</td>
                    <td class='action-buttons'>
                        <a href='edit_booking.php?id=" . $row['booking_id'] . "'>Edit</a>
                        <a href='javascript:void(0)' onclick='confirmDelete(" . $row['booking_id'] . ")' class='delete-btn'>Delete</a>
                    </td>
                </tr>";
                $counter++;
            }
        } else {
            echo "<tr><td colspan='8' style='text-align:center;'>No bookings found.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function confirmDelete(bookingId) {
    if (confirm('Are you sure you want to delete this booking? This action cannot be undone.')) {
        // Create form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete_booking_staff.php';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = bookingId;
        
        const confirmedInput = document.createElement('input');
        confirmedInput.type = 'hidden';
        confirmedInput.name = 'confirmed';
        confirmedInput.value = 'yes';
        
        form.appendChild(idInput);
        form.appendChild(confirmedInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

</body>
</html>