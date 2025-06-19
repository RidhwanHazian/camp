<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
checkAdminSession();

if (!isset($_GET['id'])) {
    header('Location: manage_campsites.php');
    exit();
}

$camp_id = mysqli_real_escape_string($conn, $_GET['id']);
$error = '';

// Fetch campsite details
$campsite_sql = "SELECT camp_name, camp_location FROM campsites WHERE camp_id = '$camp_id'";
$campsite_result = mysqli_query($conn, $campsite_sql);

if (mysqli_num_rows($campsite_result) == 0) {
    header('Location: manage_campsites.php');
    exit();
}
$campsite_data = mysqli_fetch_assoc($campsite_result);

// Fetch linked package
$linked_package_sql = "
    SELECT cp.package_id, p.package_name, p.description, p.duration, cp.slot_available,
           pp.adult_price, pp.child_price
    FROM campsite_packages cp
    JOIN packages p ON cp.package_id = p.package_id
    LEFT JOIN package_prices pp ON p.package_id = pp.package_id
    WHERE cp.camp_id = '$camp_id'
    LIMIT 1
";
$linked_package_result = mysqli_query($conn, $linked_package_sql);
$package_data = mysqli_fetch_assoc($linked_package_result);
$package_id = $package_data['package_id'] ?? null;

// Fetch current activities
$current_activities = [];
if ($package_id) {
    $activities_sql = "
        SELECT a.activity_name
        FROM package_activities pa
        JOIN activities a ON pa.activity_id = a.activity_id
        WHERE pa.package_id = '$package_id'
    ";
    $activities_result = mysqli_query($conn, $activities_sql);
    while ($row = mysqli_fetch_assoc($activities_result)) {
        $current_activities[] = $row['activity_name'];
    }
}

// --- Form Submission ---
if (isset($_POST['submit'])) {
    mysqli_begin_transaction($conn);
    try {
        $camp_location = mysqli_real_escape_string($conn, $_POST['camp_location']);
        $slot_available = intval($_POST['slot_available']);
        $adult_price = floatval($_POST['adult_price']);
        $child_price = floatval($_POST['child_price']);

        // Update location
        mysqli_query($conn, "UPDATE campsites SET camp_location = '$camp_location' WHERE camp_id = '$camp_id'");

        // Update slot
        mysqli_query($conn, "UPDATE campsite_packages SET slot_available = '$slot_available' WHERE camp_id = '$camp_id' AND package_id = '$package_id'");

        // Update or insert price
        $check_price = mysqli_query($conn, "SELECT price_id FROM package_prices WHERE package_id = '$package_id'");
        if (mysqli_num_rows($check_price) > 0) {
            mysqli_query($conn, "UPDATE package_prices SET adult_price = '$adult_price', child_price = '$child_price' WHERE package_id = '$package_id'");
        } else {
            mysqli_query($conn, "INSERT INTO package_prices (package_id, adult_price, child_price) VALUES ('$package_id', '$adult_price', '$child_price')");
        }

        // Update activities
        mysqli_query($conn, "DELETE FROM package_activities WHERE package_id = '$package_id'");
        if (isset($_POST['activities'])) {
            foreach ($_POST['activities'] as $activity) {
                $act = mysqli_real_escape_string($conn, $activity);
                $check_act = mysqli_query($conn, "SELECT activity_id FROM activities WHERE activity_name = '$act'");
                if (mysqli_num_rows($check_act) > 0) {
                    $act_row = mysqli_fetch_assoc($check_act);
                    $act_id = $act_row['activity_id'];
                } else {
                    mysqli_query($conn, "INSERT INTO activities (activity_name) VALUES ('$act')");
                    $act_id = mysqli_insert_id($conn);
                }
                mysqli_query($conn, "INSERT INTO package_activities (package_id, activity_id) VALUES ('$package_id', '$act_id')");
            }
        }

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
  <title>Edit Campsite</title>
  <style>
    body { font-family: Poppins, sans-serif; padding: 2rem; background: #f5f5f5; }
    h2 { text-align: center; margin-bottom: 1rem; }
    form { background: #fff; padding: 2rem; border-radius: 10px; max-width: 600px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    label { font-weight: 600; margin-top: 1rem; display: block; }
    input[type=text], input[type=number] {
        width: 100%; padding: 0.6rem; margin-top: 0.3rem; border-radius: 6px; border: 1px solid #ccc;
    }
    input[readonly] { background-color: #eee; }
    .activity-row { display: flex; margin-bottom: 0.5rem; gap: 0.5rem; }
    .activity-row input { flex: 1; }
    button.remove-activity, .add-activity {
        padding: 0.4rem 0.8rem; background: #e74c3c; color: white; border: none; border-radius: 6px; cursor: pointer;
    }
    .add-activity { background: #3498db; margin-top: 1rem; }
    input[type=submit] {
        background: #27ae60; color: white; padding: 0.8rem 1.2rem; border: none;
        border-radius: 6px; cursor: pointer; margin-top: 1.5rem; width: 100%;
        font-size: 1rem;
    }
    .error { color: red; margin-top: 1rem; text-align: center; }
    .back-button {
      display: inline-block;
      margin-top: 1.5rem;
      text-align: center;
      width: 100%;
      color: #2980b9;
      font-weight: 600;
      text-decoration: none;
      font-family: 'Poppins', sans-serif;
      transition: text-decoration 0.3s ease;
    }

    .back-button:hover {
      text-decoration: underline;
    }

  </style>
  <script>
    function addActivity() {
      const container = document.getElementById('activities');
      const row = document.createElement('div');
      row.classList.add('activity-row');
      row.innerHTML = `
        <input type="text" name="activities[]" required placeholder="Activity Name">
        <button type="button" class="remove-activity" onclick="this.parentElement.remove()">Remove</button>
      `;
      container.appendChild(row);
    }
  </script>
</head>
<body>
<h2>Edit Campsite & Package Info</h2>
<?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
<form method="post">
  <label>Campsite Name</label>
  <input type="text" readonly value="<?= htmlspecialchars($campsite_data['camp_name']) ?>">

  <label>Campsite Location</label>
  <input type="text" name="camp_location" value="<?= htmlspecialchars($campsite_data['camp_location']) ?>" required>

  <label>Package Name</label>
  <input type="text" readonly value="<?= htmlspecialchars($package_data['package_name'] ?? '') ?>">

  <label>Package Description</label>
  <input type="text" readonly value="<?= htmlspecialchars($package_data['description'] ?? '') ?>">

  <label>Package Duration</label>
  <input type="text" readonly value="<?= htmlspecialchars($package_data['duration'] ?? '') ?>">

  <label>Slot Available</label>
  <input type="number" name="slot_available" value="<?= htmlspecialchars($package_data['slot_available'] ?? '0') ?>" min="1" required>

  <label>Adult Price (RM)</label>
  <input type="number" name="adult_price" step="0.01" value="<?= htmlspecialchars($package_data['adult_price'] ?? '0.00') ?>" required>

  <label>Child Price (RM)</label>
  <input type="number" name="child_price" step="0.01" value="<?= htmlspecialchars($package_data['child_price'] ?? '0.00') ?>" required>

  <label>Activities</label>
  <div id="activities">
    <?php foreach ($current_activities as $act): ?>
      <div class="activity-row">
        <input type="text" name="activities[]" value="<?= htmlspecialchars($act) ?>" required>
        <button type="button" class="remove-activity" onclick="this.parentElement.remove()">Remove</button>
      </div>
    <?php endforeach; ?>
  </div>
  <button type="button" class="add-activity" onclick="addActivity()">+ Add Activity</button>

  <input type="submit" name="submit" value="Update">
</form>
<a href="manage_campsites.php" class="back-button">← Back to Campsite Management</a>

</body>
</html>
