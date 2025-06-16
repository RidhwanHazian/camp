<?php
include 'db_connection.php';

if (isset($_POST['submit'])) {
    // Start transaction
    mysqli_begin_transaction($conn);
    try {
        // Insert campsite
        $camp_name = mysqli_real_escape_string($conn, $_POST['camp_name']);
        $camp_location = mysqli_real_escape_string($conn, $_POST['camp_location']);
        $insert_camp = "INSERT INTO campsites (camp_name, camp_location) VALUES ('$camp_name', '$camp_location')";
        mysqli_query($conn, $insert_camp);
        $camp_id = mysqli_insert_id($conn);

        // Insert package
        $package_name = mysqli_real_escape_string($conn, $_POST['package_name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $duration = mysqli_real_escape_string($conn, $_POST['duration']);
        $insert_package = "INSERT INTO packages (package_name, description, duration) VALUES ('$package_name', '$description', '$duration')";
        mysqli_query($conn, $insert_package);
        $package_id = mysqli_insert_id($conn);

        // Link package to campsite
        mysqli_query($conn, "INSERT INTO campsite_packages (camp_id, package_id) VALUES ($camp_id, $package_id)");

        // Insert package prices
        $adult_price = mysqli_real_escape_string($conn, $_POST['adult_price']);
        $child_price = mysqli_real_escape_string($conn, $_POST['child_price']);
        mysqli_query($conn, "INSERT INTO package_prices (package_id, adult_price, child_price) VALUES ($package_id, $adult_price, $child_price)");

        // Insert activities (no location)
        foreach ($_POST['activities'] as $act_name) {
            $act_name = mysqli_real_escape_string($conn, $act_name);
            $result = mysqli_query($conn, "SELECT activity_id FROM activities WHERE activity_name='$act_name'");
            if ($row = mysqli_fetch_assoc($result)) {
                $activity_id = $row['activity_id'];
            } else {
                mysqli_query($conn, "INSERT INTO activities (activity_name) VALUES ('$act_name')");
                $activity_id = mysqli_insert_id($conn);
            }
            mysqli_query($conn, "INSERT INTO package_activities (package_id, activity_id) VALUES ($package_id, $activity_id)");
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
  <title>Add Campsite - TasikBiruCamps</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background-color: #f5f7fa;
      padding: 2.5rem 3rem;
      color: #333;
      min-height: 100vh;
    }

    h2 {
      font-size: 2rem;
      font-weight: 600;
      color: #34495e;
      margin-bottom: 2rem;
      text-align: center;
    }

    form {
      background: #fff;
      max-width: 700px;
      margin: 0 auto;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #2c3e50;
      font-size: 0.9rem;
    }

    input[type="text"],
    input[type="number"] {
      width: 100%;
      padding: 10px 12px;
      margin-bottom: 1.5rem;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 1rem;
      transition: border-color 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="number"]:focus {
      outline: none;
      border-color: #2980b9;
    }

    .price-inputs {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    .activity-container {
      margin-bottom: 1.5rem;
    }

    .activity-row {
      display: flex;
      gap: 1rem;
      margin-bottom: 0.5rem;
    }

    .activity-row input {
      flex: 1;
    }

    .remove-activity {
      background: #e74c3c;
      color: white;
      border: none;
      padding: 0 1rem;
      border-radius: 6px;
      cursor: pointer;
    }

    .add-activity {
      background: #3498db;
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      cursor: pointer;
      margin-bottom: 1.5rem;
    }

    input[type="submit"] {
      width: 100%;
      padding: 12px;
      background-color: #27ae60;
      border: none;
      border-radius: 8px;
      color: white;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    input[type="submit"]:hover {
      background-color: #219150;
    }

    a {
      display: inline-block;
      margin-top: 1.5rem;
      text-align: center;
      width: 100%;
      color: #2980b9;
      font-weight: 600;
      text-decoration: none;
      transition: text-decoration 0.3s ease;
    }

    a:hover {
      text-decoration: underline;
    }

    .error {
      color: #e74c3c;
      margin-bottom: 1rem;
      text-align: center;
    }
  </style>
  <script>
    let activityIndex = 1;
    function addActivity() {
      const div = document.createElement('div');
      div.className = 'activity-row';
      div.innerHTML = `
        <input type="text" name="activities[]" placeholder="Activity Name" required>
        <button type="button" class="remove-activity" onclick="removeActivity(this)">Remove</button>
      `;
      document.getElementById('activities').appendChild(div);
      activityIndex++;
    }
    function removeActivity(btn) {
      btn.parentElement.remove();
    }
  </script>
</head>
<body>
  <h2>Add New Campsite & Package</h2>
  <?php if (isset($error)): ?>
    <div class="error"><?php echo $error; ?></div>
  <?php endif; ?>
  <form method="post">
    <label for="camp_name">Campsite Name:</label>
    <input type="text" id="camp_name" name="camp_name" required />
    <label for="camp_location">Campsite Location:</label>
    <input type="text" id="camp_location" name="camp_location" required />
    <label for="package_name">Package Name:</label>
    <input type="text" id="package_name" name="package_name" required />
    <label for="description">Package Description:</label>
    <input type="text" id="description" name="description" required />
    <label for="duration">Package Duration:</label>
    <input type="text" id="duration" name="duration" required />
    <div class="price-inputs">
      <div>
        <label for="adult_price">Adult Price (RM):</label>
        <input type="number" step="0.01" id="adult_price" name="adult_price" required />
      </div>
      <div>
        <label for="child_price">Child Price (RM):</label>
        <input type="number" step="0.01" id="child_price" name="child_price" required />
      </div>
    </div>
    <div class="activity-container">
      <label>Activities:</label>
      <div id="activities">
        <div class="activity-row">
          <input type="text" name="activities[]" placeholder="Activity Name" required>
          <button type="button" class="remove-activity" onclick="removeActivity(this)">Remove</button>
        </div>
      </div>
      <button type="button" class="add-activity" onclick="addActivity()">+ Add Another Activity</button>
    </div>
    <input type="submit" name="submit" value="Add Campsite & Package" />
  </form>
  <a href="manage_campsites.php">← Back to Campsite Management</a>
</body>
</html>