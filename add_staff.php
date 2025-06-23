<?php
include 'session_check.php';
checkAdminSession(); // Only admins can add staff
include 'db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add New Staff - TasikBiruCamps</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
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

    input[type="text"],
    input[type="email"],
    input[type="tel"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
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

    .sidebar {
      width: 200px;
      background-color: #333;
      color: #fff;
      padding: 1rem;
    }

    .sidebar h2 {
      margin-bottom: 1rem;
    }

    .sidebar a {
      display: block;
      color: #fff;
      text-decoration: none;
      padding: 0.5rem 0;
    }

    .sidebar a:hover {
      text-decoration: underline;
    }

    .sidebar a.active {
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <h2>Admin</h2>
  <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
  <a href="manage_bookings.php"><i class="fas fa-calendar-check"></i> Manage Bookings</a>
  <a href="manage_campsites.php"><i class="fas fa-campground"></i> Manage Campsites</a>
  <a href="manage_staff.php" class="active"><i class="fas fa-users"></i> Manage Staff</a>
  <a href="manage_feedback.php"><i class="fas fa-comments"></i> Feedback Customer</a>
  <a href="customer_payment.php"><i class="fas fa-credit-card"></i> Customer Payment</a>
  <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="container">
  <h1>Add New Staff</h1>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
  <?php endif; ?>

  <form action="process_add_staff.php" method="post">
    <label for="staff_name">Full Name</label>
    <input type="text" id="staff_name" name="staff_name" required>

    <label for="staff_username">Username</label>
    <input type="text" id="staff_username" name="staff_username" required>

    <label for="staff_email">Email</label>
    <input type="email" id="staff_email" name="staff_email" required>

    <label for="staff_notel">Phone</label>
    <input type="tel" id="staff_notel" name="staff_notel" required>

    <label for="staff_password">Password</label>
    <input type="password" id="staff_password" name="staff_password" required>

    <button type="submit">Add Staff</button>
  </form>

  <a href="manage_staff.php" class="back-link">← Back to Staff List</a>
</div>

</body>
</html>
