<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
checkAdminSession();

// Handle POST form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = intval($_POST['staff_id']);
    $facility_id = intval($_POST['facility_id']);

    if (!empty($staff_id) && !empty($facility_id)) {
        $check_sql = "SELECT * FROM staff_facilities WHERE staff_id = ? AND facility_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $staff_id, $facility_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            $insert_sql = "INSERT INTO staff_facilities (staff_id, facility_id) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ii", $staff_id, $facility_id);
            $insert_stmt->execute();
            $_SESSION['success'] = "Facility assigned successfully.";
        } else {
            $_SESSION['error'] = "This facility is already assigned to the selected staff.";
        }
    } else {
        $_SESSION['error'] = "Please fill in all required fields.";
    }

    // ✅ Set a flag that form was submitted
    $_SESSION['form_submitted'] = true;

    header("Location: assign_facilities.php");
    exit();
}

// Fetch staff list
$staff_result = $conn->query("SELECT staff_id, staff_name FROM staff");
$staff_list = [];
while ($row = $staff_result->fetch_assoc()) {
    $staff_list[] = $row;
}

// Fetch facility list
$facilities_result = $conn->query("SELECT facility_id, facility_name FROM facilities");
$facilities_list = [];
while ($row = $facilities_result->fetch_assoc()) {
    $facilities_list[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Assign Facilities - TasikBiruCamps</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #f5f7fa;
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 500px;
      margin: 60px auto;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.08);
      padding: 2.5rem 2rem 2rem;
    }
    h1 {
      text-align: center;
      color: #34495e;
      margin-bottom: 2rem;
      font-size: 2rem;
      font-weight: 600;
    }
    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #2c3e50;
    }
    select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-bottom: 1.5rem;
      font-size: 1rem;
    }
    button {
      width: 100%;
      padding: 12px;
      background-color: #27ae60;
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #219150;
    }
    .back-link {
      display: block;
      text-align: center;
      margin-top: 1.5rem;
      color: #2980b9;
      text-decoration: none;
      font-weight: 600;
    }
    .back-link:hover {
      text-decoration: underline;
    }
    .msg {
      text-align: center;
      font-weight: 600;
      margin-bottom: 1rem;
      color: #c0392b;
    }
    .msg.success {
      color: #27ae60;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Assign Facilities</h1>

    <?php if (isset($_SESSION['form_submitted'])): ?>
      <?php if (isset($_SESSION['error'])): ?>
        <div class="msg"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
      <?php elseif (isset($_SESSION['success'])): ?>
        <div class="msg success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
      <?php endif; ?>
      <?php unset($_SESSION['form_submitted']); ?>
    <?php endif; ?>


    <form method="post">
      <label for="staff_id">Select Staff:</label>
      <select id="staff_id" name="staff_id" required>
        <option value="">-- Select Staff --</option>
        <?php foreach ($staff_list as $staff): ?>
          <option value="<?= htmlspecialchars($staff['staff_id']) ?>"><?= htmlspecialchars($staff['staff_name']) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="facility_id">Select Facility:</label>
      <select id="facility_id" name="facility_id" required>
        <option value="">-- Select Facility --</option>
        <?php foreach ($facilities_list as $facility): ?>
          <option value="<?= htmlspecialchars($facility['facility_id']) ?>"><?= htmlspecialchars($facility['facility_name']) ?></option>
        <?php endforeach; ?>
      </select>

      <button type="submit">Assign Facility</button>
    </form>

    <a href="manage_staff.php" class="back-link">&larr; Back to Manage Staff</a>
  </div>
</body>
</html>
