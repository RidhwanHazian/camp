<?php
include 'db_connection.php';
include 'session_check.php';
checkAdminSession();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    mysqli_begin_transaction($conn);
    try {
        $package_name = mysqli_real_escape_string($conn, $_POST['package_name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $duration = mysqli_real_escape_string($conn, $_POST['duration']);
        $adult_price = floatval($_POST['adult_price']);
        $child_price = floatval($_POST['child_price']);

        // Handle photo upload (stored in Assets/)
        $photo_name = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed_exts)) {
                $safe_name = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($package_name));
                $photo_name = "package_{$safe_name}." . $ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], 'Assets/' . $photo_name);
            } else {
                throw new Exception("Invalid file type. Only JPG, JPEG, PNG, GIF, WEBP allowed.");
            }
        }

        // Collect all activities into a single comma-separated string
        $activities = '';
        if (!empty($_POST['activities'])) {
            $trimmed_activities = array_map('trim', $_POST['activities']);
            $escaped_activities = array_map(fn($a) => mysqli_real_escape_string($conn, $a), $trimmed_activities);
            $activities = implode(', ', $escaped_activities);
        }

        // Insert into packages
        $sql = "INSERT INTO packages (package_name, description, duration, photo, activity)
                VALUES ('$package_name', '$description', '$duration', '$photo_name', '$activities')";
        mysqli_query($conn, $sql);
        $package_id = mysqli_insert_id($conn);

        // Insert prices
        mysqli_query($conn, "INSERT INTO package_prices (package_id, adult_price, child_price)
                             VALUES ($package_id, $adult_price, $child_price)");

        mysqli_commit($conn);
        header("Location: manage_campsites.php");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add New Package</title>
  <style>
    body { font-family: Poppins, sans-serif; padding: 2rem; background: #f5f5f5; }
    form { background: #fff; padding: 2rem; max-width: 600px; margin: auto; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { text-align: center; margin-bottom: 1.5rem; }
    label { display: block; margin-top: 1rem; font-weight: 600; }
    input[type="text"], input[type="number"] {
      width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; margin-top: 5px;
    }
    .activity-row { display: flex; gap: 0.5rem; margin-bottom: 0.5rem; }
    .activity-row input { flex: 1; }
    .activity-row button { background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 5px; }
    .add-activity { margin-top: 1rem; background: #2980b9; color: white; padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer; }
    input[type="submit"] {
      margin-top: 1.5rem; width: 100%; padding: 12px; background: #27ae60; color: white; font-weight: bold; border: none; border-radius: 6px; cursor: pointer;
    }
    .error { color: red; text-align: center; margin-bottom: 1rem; }
    .back-link { text-align: center; display: block; margin-top: 1.5rem; text-decoration: none; color: #2980b9; font-weight: 600; }
    .back-link:hover { text-decoration: underline; }
  </style>
  <script>
    function addActivity() {
      const container = document.getElementById('activities');
      const row = document.createElement('div');
      row.className = 'activity-row';
      row.innerHTML = `
        <input type="text" name="activities[]" placeholder="Activity Name" required>
        <button type="button" onclick="this.parentElement.remove()">Remove</button>
      `;
      container.appendChild(row);
    }
  </script>
</head>
<body>

<h2>Add New Package</h2>
<?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
  <label for="package_name">Package Name</label>
  <input type="text" name="package_name" required>

  <label for="description">Description</label>
  <input type="text" name="description" required>

  <label for="duration">Duration</label>
  <input type="text" name="duration" required>

  <label for="adult_price">Adult Price (RM)</label>
  <input type="number" name="adult_price" step="0.01" required>

  <label for="child_price">Child Price (RM)</label>
  <input type="number" name="child_price" step="0.01" required>
  
  <label for="photo">Upload Photo</label>
  <input type="file" name="photo" accept="image/*">

  <label>Activities</label>
  <div id="activities">
    <div class="activity-row">
      <input type="text" name="activities[]" placeholder="Activity Name" required>
      <button type="button" onclick="this.parentElement.remove()">Remove</button>
    </div>
  </div>

  <button type="button" class="add-activity" onclick="addActivity()">+ Add Activity</button>

  <input type="submit" name="submit" value="Add Package">
</form>

<a href="manage_campsites.php" class="back-link">← Back to Package Management</a>

</body>
</html>
