<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: login.php");
  exit();
}

if (isset($_GET['task_id'])) {
  $task_id = $_GET['task_id'];

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_name = $_POST['task_name'];
    $task_date = $_POST['task_date'];
    $task_shiftTime = $_POST['task_shiftTime'];
    $task_location = $_POST['task_location'];
    $task_activity = $_POST['task_activity'];

    $stmt = $conn->prepare("UPDATE task_assignment SET task_name=?, task_date=?, task_shiftTime=?, task_location=?, task_activity=? WHERE task_id=?");
    $stmt->bind_param("sssssi", $task_name, $task_date, $task_shiftTime, $task_location, $task_activity, $task_id);
    $stmt->execute();
    header("Location: manage_staff.php");
    exit();
  }

  $stmt = $conn->prepare("SELECT * FROM task_assignment WHERE task_id=?");
  $stmt->bind_param("i", $task_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $task = $result->fetch_assoc();
} else {
  header("Location: manage_staff.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Schedule - TasikBiruCamps</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    /* Reuse same CSS from add_schedule.php */
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Edit Schedule</h2>
    <form method="POST">
      <input type="text" name="task_name" value="<?= htmlspecialchars($task['task_name']) ?>" required />
      <input type="date" name="task_date" value="<?= $task['task_date'] ?>" required />
      <input type="text" name="task_shiftTime" value="<?= $task['task_shiftTime'] ?>" required />
      <input type="text" name="task_location" value="<?= htmlspecialchars($task['task_location']) ?>" required />
      <textarea name="task_activity" required><?= htmlspecialchars($task['task_activity']) ?></textarea>
      <button type="submit">Update Schedule</button>
    </form>
    <a href="manage_staff.php">← Back to Manage Staff</a>
  </div>
</body>
</html>
