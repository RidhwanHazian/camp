<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
checkAdminSession();

if (!isset($_GET['id'])) {
    header("Location: manage_staff.php");
    exit();
}

$task_id = intval($_GET['id']);

// Fetch current task assignment
$stmt = $conn->prepare("SELECT * FROM task_assignment WHERE task_id = ?");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();

if (!$task) {
    echo "Task not found.";
    exit();
}

// Handle form submission (update staff_id only)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = intval($_POST['staff_id']);

    $update = $conn->prepare("UPDATE task_assignment SET staff_id = ? WHERE task_id = ?");
    $update->bind_param("ii", $staff_id, $task_id);

    if ($update->execute()) {
        header("Location: manage_staff.php?success=1");
        exit();
    } else {
        $error = "Failed to update staff assignment.";
    }
}

// Get staff list
$staffResult = $conn->query("SELECT staff_id, staff_name FROM staff");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Assigned Staff</title>
  <style>
    body { font-family: 'Inter', sans-serif; background: #f4f4f4; }
    .container {
      max-width: 500px;
      margin: 60px auto;
      background: #fff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #2c3e50;
    }
    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }
    select, button {
      width: 100%;
      padding: 10px;
      font-size: 1rem;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-bottom: 1.2rem;
    }
    button {
      background-color: #27ae60;
      color: white;
      font-weight: bold;
      border: none;
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
    .error {
      color: red;
      text-align: center;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
<div class="container">
  <h2>Change Assigned Staff</h2>

  <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>

  <form method="POST">
    <label for="staff_id">Staff Member</label>
    <select name="staff_id" id="staff_id" required>
      <option value="">-- Select Staff --</option>
      <?php while ($row = $staffResult->fetch_assoc()): ?>
        <option value="<?= $row['staff_id'] ?>" <?= ($task['staff_id'] == $row['staff_id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($row['staff_name']) ?>
        </option>
      <?php endwhile; ?>
    </select>
    <button type="submit">Update</button>
  </form>

  <a href="manage_staff.php" class="back-link">&larr; Back to Manage Staff</a>
</div>
</body>
</html>
