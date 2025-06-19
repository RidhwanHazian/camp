<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
checkAdminSession();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage_campsites.php');
    exit();
}

$package_id = intval($_GET['id']);
$error = '';
$success = '';

// Fetch package info
$package_sql = "SELECT * FROM packages WHERE package_id = $package_id";
$package_result = mysqli_query($conn, $package_sql);
$package = mysqli_fetch_assoc($package_result);

// Fetch price
$price_result = mysqli_query($conn, "SELECT * FROM package_prices WHERE package_id = $package_id");
$price = mysqli_fetch_assoc($price_result);

// Fetch all activities
$all_activities_result = mysqli_query($conn, "SELECT * FROM activities");
$activity_options = [];
while ($row = mysqli_fetch_assoc($all_activities_result)) {
    $activity_options[] = $row;
}

// Fetch selected activities
$current_activities_result = mysqli_query($conn, "
    SELECT a.activity_id, a.activity_name
    FROM package_activities pa
    JOIN activities a ON pa.activity_id = a.activity_id
    WHERE pa.package_id = $package_id
");
$current_activities = [];
while ($act = mysqli_fetch_assoc($current_activities_result)) {
    $current_activities[] = $act;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $duration = mysqli_real_escape_string($conn, $_POST['duration']);
    $adult_price = floatval($_POST['adult_price']);
    $child_price = floatval($_POST['child_price']);
    $activities = $_POST['activities'] ?? [];

    mysqli_begin_transaction($conn);
    try {
        // Update package
        mysqli_query($conn, "UPDATE packages SET description='$description', duration='$duration' WHERE package_id=$package_id");

        // Update or insert price
        $existing = mysqli_query($conn, "SELECT price_id FROM package_prices WHERE package_id=$package_id");
        if (mysqli_num_rows($existing) > 0) {
            mysqli_query($conn, "UPDATE package_prices SET adult_price=$adult_price, child_price=$child_price WHERE package_id=$package_id");
        } else {
            mysqli_query($conn, "INSERT INTO package_prices (package_id, adult_price, child_price) VALUES ($package_id, $adult_price, $child_price)");
        }

        // Update activities
        mysqli_query($conn, "DELETE FROM package_activities WHERE package_id=$package_id");
        foreach ($activities as $activity_id) {
            $act_id = intval($activity_id);
            mysqli_query($conn, "INSERT INTO package_activities (package_id, activity_id) VALUES ($package_id, $act_id)");
        }

        mysqli_commit($conn);
        $success = "Package updated successfully!";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Update failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Package</title>
    <style>
        body { font-family: Poppins, sans-serif; padding: 2rem; background: #f5f5f5; }
        form { background: #fff; padding: 2rem; max-width: 700px; margin: auto; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
        h2 { text-align: center; margin-bottom: 1.5rem; }
        label { display: block; margin-top: 1rem; font-weight: 600; }
        input[type=text], input[type=number], select {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; margin-top: 5px;
        }
        .activity-row { display: flex; gap: 1rem; margin-bottom: 0.5rem; }
        .activity-row select { flex: 1; }
        button { background: #27ae60; color: white; padding: 10px 20px; border: none; border-radius: 6px; margin-top: 1.5rem; cursor: pointer; }
        button:hover { background: #219150; }
        .add-btn { background: #2980b9; margin-top: 1rem; }
        .msg { text-align: center; font-weight: bold; margin-top: 1rem; }
        .msg.success { color: green; }
        .msg.error { color: red; }
        .back-link { display: block; text-align: center; margin-top: 1.5rem; color: #2980b9; text-decoration: none; font-weight: 600; }
        .back-link:hover { text-decoration: underline; }
    </style>
    <script>
        function addActivityRow() {
            const container = document.getElementById('activity-container');
            const row = document.createElement('div');
            row.className = 'activity-row';
            row.innerHTML = `
                <select name="activities[]">
                    <?= implode('', array_map(function($a) {
                        return "<option value='{$a['activity_id']}'>" . htmlspecialchars($a['activity_name']) . "</option>";
                    }, $activity_options)); ?>
                </select>
                <button type="button" onclick="this.parentElement.remove()" style="background:#e74c3c;color:white;">Remove</button>
            `;
            container.appendChild(row);
        }
    </script>
</head>
<body>

<h2>Edit Package: <?= htmlspecialchars($package['package_name']) ?></h2>
<?php if ($error): ?><div class="msg error"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="msg success"><?= $success ?></div><?php endif; ?>

<form method="POST">
    <label>Description</label>
    <input type="text" name="description" value="<?= htmlspecialchars($package['description'] ?? '') ?>" required>

    <label>Duration</label>
    <input type="text" name="duration" value="<?= htmlspecialchars($package['duration'] ?? '') ?>" required>

    <label>Adult Price (RM)</label>
    <input type="number" name="adult_price" step="0.01" value="<?= $price['adult_price'] ?? 0 ?>" required>

    <label>Child Price (RM)</label>
    <input type="number" name="child_price" step="0.01" value="<?= $price['child_price'] ?? 0 ?>" required>

    <label>Activities</label>
    <div id="activity-container">
        <?php foreach ($current_activities as $act): ?>
            <div class="activity-row">
                <select name="activities[]">
                    <?php foreach ($activity_options as $opt): ?>
                        <option value="<?= $opt['activity_id'] ?>" <?= $opt['activity_id'] == $act['activity_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($opt['activity_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" onclick="this.parentElement.remove()" style="background:#e74c3c;color:white;">Remove</button>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="add-btn" onclick="addActivityRow()">+ Add Activity</button>

    <br>
    <button type="submit" name="update">Update Package</button>
</form>
<a href="manage_campsites.php" class="back-link">← Back to Package Management</a>

</body>
</html>
