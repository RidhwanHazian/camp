<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
checkAdminSession();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid staff ID provided.";
    header('Location: manage_staff.php');
    exit();
}

$id = intval($_GET['id']);

try {
    $sql = "SELECT * FROM staff WHERE staff_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result->fetch_assoc();

    if (!$staff) {
        $_SESSION['error'] = "Staff not found.";
        header('Location: manage_staff.php');
        exit();
    }
    $stmt->close();
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header('Location: manage_staff.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Staff</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f5f7fa;
      margin: 0;
      padding: 2rem;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .container {
      background: white;
      padding: 2rem 3rem;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      width: 400px;
      max-width: 90vw;
    }

    h1 {
      font-weight: 600;
      font-size: 1.8rem;
      margin-bottom: 1.5rem;
      color: #34495e;
      text-align: center;
    }

    form label {
      display: block;
      margin-bottom: 0.3rem;
      font-weight: 500;
      color: #333;
      margin-top: 1rem;
    }

    form input[type="text"],
    form input[type="email"],
    form input[type="tel"],
    form input[type="password"] {
      width: 100%;
      padding: 10px 12px;
      border: 1.8px solid #ccc;
      border-radius: 6px;
      font-size: 1rem;
      transition: border-color 0.3s ease;
    }

    form input[type="text"]:focus,
    form input[type="email"]:focus,
    form input[type="tel"]:focus,
    form input[type="password"]:focus {
      border-color: #2980b9;
      outline: none;
    }

    button[type="submit"] {
      margin-top: 2rem;
      width: 100%;
      background-color: #27ae60;
      color: white;
      padding: 12px;
      font-size: 1.1rem;
      font-weight: 600;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    button[type="submit"]:hover {
      background-color: #219150;
    }

    .back-link {
      display: block;
      margin-top: 1.5rem;
      text-align: center;
      color: #2980b9;
      text-decoration: none;
      font-weight: 500;
    }

    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="container">
  <h1>Edit Staff</h1>

  <form action="process_edit_staff.php" method="post">
    <input type="hidden" name="staff_id" value="<?= htmlspecialchars($staff['staff_id']) ?>">
    
    <label for="staff_name">Name:</label>
    <input type="text" id="staff_name" name="staff_name" value="<?= htmlspecialchars($staff['staff_name']) ?>" required>
    
    <label for="staff_username">Username:</label>
    <input type="text" id="staff_username" name="staff_username" value="<?= htmlspecialchars($staff['staff_username']) ?>" required>
    
    <label for="staff_email">Email:</label>
    <input type="email" id="staff_email" name="staff_email" value="<?= htmlspecialchars($staff['staff_email']) ?>" required>
    
    <label for="staff_notel">Phone:</label>
    <input type="tel" id="staff_notel" name="staff_notel" value="<?= htmlspecialchars($staff['staff_notel']) ?>" required>
    
    <label for="staff_password">New Password: (leave blank to keep current password)</label>
    <input type="password" id="staff_password" name="staff_password">
    
    <button type="submit">Update Staff</button>
  </form>
  
  <a class="back-link" href="manage_staff.php">← Back to Manage Staff</a>
</div>

</body>
</html>
