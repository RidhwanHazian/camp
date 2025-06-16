<?php
include 'db_connection.php';

if (!isset($_GET['id'])) {
    header('Location: manage_campsites.php');
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$error = '';

// --- Fetch existing data ---
// Fetch campsite details
$campsite_sql = "SELECT camp_name, camp_location FROM campsites WHERE camp_id = '$id'";
$campsite_result = mysqli_query($conn, $campsite_sql);

if (mysqli_num_rows($campsite_result) == 0) {
    header('Location: manage_campsites.php');
    exit();
}
$campsite_data = mysqli_fetch_assoc($campsite_result);

// Fetch the *first* package linked to this campsite (assuming one primary package for this edit page)
$linked_package_id = null;
$package_link_sql = "SELECT package_id FROM campsite_packages WHERE camp_id = '$id' LIMIT 1";
$package_link_result = mysqli_query($conn, $package_link_sql);
if ($package_link_row = mysqli_fetch_assoc($package_link_result)) {
    $linked_package_id = $package_link_row['package_id'];
}

$package_data = [
    'package_name' => '',
    'description' => '',
    'duration' => '',
];
$pricing_data = [
    'adult_price' => 0.00,
    'child_price' => 0.00,
];
$current_activities = [];

if ($linked_package_id) {
    // Fetch package details
    $package_sql = "SELECT package_name, description, duration FROM packages WHERE package_id = '$linked_package_id'";
    $package_result = mysqli_query($conn, $package_sql);
    $package_data = mysqli_fetch_assoc($package_result);

    // Fetch pricing details
    $pricing_sql = "SELECT adult_price, child_price FROM package_prices WHERE package_id = '$linked_package_id'";
    $pricing_result = mysqli_query($conn, $pricing_sql);
    if (mysqli_num_rows($pricing_result) > 0) {
        $pricing_data = mysqli_fetch_assoc($pricing_result);
    }

    // Fetch activities for this package
    $activities_sql = "
        SELECT a.activity_name
        FROM package_activities pa
        JOIN activities a ON pa.activity_id = a.activity_id
        WHERE pa.package_id = '$linked_package_id'
    ";
    $activities_result = mysqli_query($conn, $activities_sql);
    while ($activity_row = mysqli_fetch_assoc($activities_result)) {
        $current_activities[] = $activity_row['activity_name'];
    }
}

// --- Handle form submission ---
if (isset($_POST['submit'])) {
    mysqli_begin_transaction($conn);
    try {
        // Update campsite
        $camp_name = mysqli_real_escape_string($conn, $_POST['camp_name']);
        $camp_location = mysqli_real_escape_string($conn, $_POST['camp_location']);
        $update_camp_sql = "UPDATE campsites SET camp_name = '$camp_name', camp_location = '$camp_location' WHERE camp_id = '$id'";
        mysqli_query($conn, $update_camp_sql);

        // Update or Insert Package
        $package_name = mysqli_real_escape_string($conn, $_POST['package_name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $duration = mysqli_real_escape_string($conn, $_POST['duration']);

        $current_package_id = $linked_package_id;

        if ($current_package_id) {
            $update_package_sql = "UPDATE packages SET package_name = '$package_name', description = '$description', duration = '$duration' WHERE package_id = '$current_package_id'";
            mysqli_query($conn, $update_package_sql);
        } else {
            // If no package was linked initially, create a new one
            $insert_package_sql = "INSERT INTO packages (package_name, description, duration) VALUES ('$package_name', '$description', '$duration')";
            mysqli_query($conn, $insert_package_sql);
            $current_package_id = mysqli_insert_id($conn);
            // Link the new package to the campsite
            mysqli_query($conn, "INSERT INTO campsite_packages (camp_id, package_id) VALUES ('$id', '$current_package_id')");
        }
        
        // Update or Insert Package Prices
        $adult_price = mysqli_real_escape_string($conn, $_POST['adult_price']);
        $child_price = mysqli_real_escape_string($conn, $_POST['child_price']);
        
        $check_pricing_sql = "SELECT price_id FROM package_prices WHERE package_id = '$current_package_id'";
        $check_pricing_result = mysqli_query($conn, $check_pricing_sql);

        if (mysqli_num_rows($check_pricing_result) > 0) {
            // Update existing pricing
            $update_pricing_sql = "UPDATE package_prices SET adult_price = '$adult_price', child_price = '$child_price' WHERE package_id = '$current_package_id'";
            mysqli_query($conn, $update_pricing_sql);
        } else {
            // Insert new pricing
            $insert_pricing_sql = "INSERT INTO package_prices (package_id, adult_price, child_price) VALUES ('$current_package_id', '$adult_price', '$child_price')";
            mysqli_query($conn, $insert_pricing_sql);
        }

        // Handle Activities: Delete old links and re-insert new ones
        if ($current_package_id) {
            mysqli_query($conn, "DELETE FROM package_activities WHERE package_id = '$current_package_id'");
        }

        if (isset($_POST['activities']) && is_array($_POST['activities'])) {
            foreach ($_POST['activities'] as $activity_name) {
                $act_name = mysqli_real_escape_string($conn, $activity_name); // Assuming direct activity name input
                
                // Check if activity exists in 'activities' table
                $result_act = mysqli_query($conn, "SELECT activity_id FROM activities WHERE activity_name='$act_name'");
                if (mysqli_num_rows($result_act) > 0) {
                    $activity_row = mysqli_fetch_assoc($result_act);
                    $activity_id = $activity_row['activity_id'];
                } else {
                    // Insert new activity into 'activities' table
                    mysqli_query($conn, "INSERT INTO activities (activity_name) VALUES ('$act_name')");
                    $activity_id = mysqli_insert_id($conn);
                }
                // Link activity to package in 'package_activities' table
                mysqli_query($conn, "INSERT INTO package_activities (package_id, activity_id) VALUES ('$current_package_id', '$activity_id')");
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Campsite & Package</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
    body { background-color: #f5f7fa; padding: 2.5rem 3rem; color: #333; min-height: 100vh; }
    h2 { font-size: 2rem; font-weight: 600; color: #34495e; margin-bottom: 2rem; text-align: center; }
    form { background: #fff; max-width: 700px; margin: 0 auto; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); }
    label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50; font-size: 0.9rem; }
    input[type="text"], input[type="number"] { width: 100%; padding: 10px 12px; margin-bottom: 1.5rem; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem; transition: border-color 0.3s ease; }
    input[type="text"]:focus, input[type="number"]:focus { outline: none; border-color: #2980b9; }
    .price-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .activity-container { margin-bottom: 1.5rem; }
    .activity-row { display: flex; gap: 1rem; margin-bottom: 0.5rem; }
    .activity-row input { flex: 1; }
    .remove-activity { background: #e74c3c; color: white; border: none; padding: 0 1rem; border-radius: 6px; cursor: pointer; }
    .add-activity { background: #3498db; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; margin-bottom: 1.5rem; }
    input[type="submit"] { width: 100%; padding: 12px; background-color: #27ae60; border: none; border-radius: 8px; color: white; font-weight: 700; font-size: 1.1rem; cursor: pointer; transition: background-color 0.3s ease; }
    input[type="submit"]:hover { background-color: #219150; }
    a { display: inline-block; margin-top: 1.5rem; text-align: center; width: 100%; color: #2980b9; font-weight: 600; text-decoration: none; transition: text-decoration 0.3s ease; }
    a:hover { text-decoration: underline; }
    .error { color: #e74c3c; margin-bottom: 1rem; text-align: center; }
  </style>
  <script>
    let activityIndex = 0; // Initialize with 0 as we'll loop existing activities
    document.addEventListener('DOMContentLoaded', function() {
        // Set initial index based on pre-populated activities
        activityIndex = document.querySelectorAll('.activity-row').length;
    });

    function addActivity() {
      const activitiesDiv = document.getElementById('activities');
      const newActivity = document.createElement('div');
      newActivity.className = 'activity-row';
      newActivity.innerHTML = `
        <input type="text" name="activities[]" placeholder="Activity Name" required>
        <button type="button" class="remove-activity" onclick="removeActivity(this)">Remove</button>
      `;
      activitiesDiv.appendChild(newActivity);
      activityIndex++;
    }
    function removeActivity(btn) {
      if (document.querySelectorAll('.activity-row').length > 1) {
        btn.parentElement.remove();
      } else {
        alert('You must have at least one activity.');
      }
    }
  </script>
</head>
<body>
  <h2>Edit Campsite & Package</h2>
  <?php if (isset($error)): ?>
    <div class="error"><?php echo $error; ?></div>
  <?php endif; ?>
  <form method="post">
    <label for="camp_name">Campsite Name:</label>
    <input type="text" id="camp_name" name="camp_name" required value="<?php echo htmlspecialchars($campsite_data['camp_name'] ?? ''); ?>" />

    <label for="camp_location">Campsite Location:</label>
    <input type="text" id="camp_location" name="camp_location" required value="<?php echo htmlspecialchars($campsite_data['camp_location'] ?? ''); ?>" />

    <hr style="margin: 2rem 0; border: none; border-top: 1px solid #eee;">

    <h3>Package Details</h3>
    <label for="package_name">Package Name:</label>
    <input type="text" id="package_name" name="package_name" required value="<?php echo htmlspecialchars($package_data['package_name'] ?? ''); ?>" />

    <label for="description">Package Description:</label>
    <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($package_data['description'] ?? ''); ?>" />

    <label for="duration">Package Duration:</label>
    <input type="text" id="duration" name="duration" value="<?php echo htmlspecialchars($package_data['duration'] ?? ''); ?>" />

    <div class="price-inputs">
      <div>
        <label for="adult_price">Adult Price (RM):</label>
        <input type="number" step="0.01" id="adult_price" name="adult_price" required value="<?php echo htmlspecialchars($pricing_data['adult_price'] ?? '0.00'); ?>" />
      </div>
      <div>
        <label for="child_price">Child Price (RM):</label>
        <input type="number" step="0.01" id="child_price" name="child_price" required value="<?php echo htmlspecialchars($pricing_data['child_price'] ?? '0.00'); ?>" />
      </div>
    </div>

    <div class="activity-container">
      <label>Activities:</label>
      <div id="activities">
        <?php if (!empty($current_activities)): ?>
            <?php foreach ($current_activities as $activity_name): ?>
                <div class="activity-row">
                    <input type="text" name="activities[]" placeholder="Activity Name" value="<?php echo htmlspecialchars($activity_name); ?>" required>
                    <button type="button" class="remove-activity" onclick="removeActivity(this)">Remove</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="activity-row">
                <input type="text" name="activities[]" placeholder="Activity Name" required>
                <button type="button" class="remove-activity" onclick="removeActivity(this)">Remove</button>
            </div>
        <?php endif; ?>
      </div>
      <button type="button" class="add-activity" onclick="addActivity()">+ Add Another Activity</button>
    </div>

    <input type="submit" name="submit" value="Update Campsite & Package" />
  </form>

  <a href="manage_campsites.php">← Back to Campsite Management</a>
</body>
</html>
