<?php
include 'db_connection.php';
include 'session_check.php';
checkAdminSession();

if (!isset($_GET['staff_id']) || !isset($_GET['facility_name'])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: manage_staff.php");
    exit();
}

$staff_id = intval($_GET['staff_id']);
$facility_name = urldecode($_GET['facility_name']);

// Fetch existing facility ID
$facility_query = $conn->prepare("SELECT facility_id FROM facilities WHERE facility_name = ?");
$facility_query->bind_param("s", $facility_name);
$facility_query->execute();
$facility_result = $facility_query->get_result();
$old_facility = $facility_result->fetch_assoc();
$old_facility_id = $old_facility['facility_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_facility_id = intval($_POST['facility_id']);

    if ($new_facility_id > 0) {
        // Update assignment
        $update = $conn->prepare("UPDATE staff_facilities SET facility_id = ? WHERE staff_id = ? AND facility_id = ?");
        $update->bind_param("iii", $new_facility_id, $staff_id, $old_facility_id);
        $update->execute();

        $_SESSION['success'] = "Facility updated successfully.";
    } else {
        $_SESSION['error'] = "Please select a facility.";
    }

    header("Location: manage_staff.php");
    exit();
}

// Get staff name
$staff_result = $conn->query("SELECT staff_name FROM staff WHERE staff_id = $staff_id");
$staff_name = $staff_result->fetch_assoc()['staff_name'] ?? '';

// Get all facility options
$facility_result = $conn->query("SELECT facility_id, facility_name FROM facilities");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Assigned Facility</title>
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
  </style>
</head>
<body>
  <div class="container">
    <h1>Edit Facility for <?= htmlspecialchars($staff_name) ?></h1>

    <form method="post">
      <label for="facility_id">Select New Facility:</label>
      <select name="facility_id" id="facility_id" required>
        <option value="">-- Select Facility --</option>
        <?php while ($facility = $facility_result->fetch_assoc()): ?>
          <option value="<?= $facility['facility_id'] ?>" <?= ($facility['facility_id'] == $old_facility_id) ? 'selected' : '' ?>>
            <?= htmlspecialchars($facility['facility_name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
      <button type="submit">Update Assignment</button>
    </form>

    <a href="manage_staff.php" class="back-link">&larr; Back to Manage Staff</a>
  </div>
</body>
</html>
