<?php
include 'db_connection.php';

if (!isset($_GET['id'])) {
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

// Fetch price info
$price_result = mysqli_query($conn, "SELECT * FROM package_prices WHERE package_id = $package_id");
$price = mysqli_fetch_assoc($price_result);

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $duration = mysqli_real_escape_string($conn, $_POST['duration']);
    $activities = mysqli_real_escape_string($conn, $_POST['activity']);
    $adult_price = floatval($_POST['adult_price']);
    $child_price = floatval($_POST['child_price']);

    // Handle optional image upload
    $photoFile = $_FILES['photo'];
    $newPhotoName = $package['photo']; // keep existing by default

    if ($photoFile['error'] === 0 && $photoFile['size'] > 0) {
        $check = getimagesize($photoFile['tmp_name']); // Step 1: Validate it's an image
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp']; // Step 2: Allowed extensions

        if ($check !== false) {
            $originalName = pathinfo($photoFile['name'], PATHINFO_FILENAME);
            $ext = strtolower(pathinfo($photoFile['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowedTypes)) {
                $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
                $newPhotoName = strtolower($safeName . '_' . $package_id . '.' . $ext);

                // Delete old photo if exists
                if (!empty($package['photo']) && file_exists('Assets/' . $package['photo'])) {
                    unlink('Assets/' . $package['photo']);
                }

                move_uploaded_file($photoFile['tmp_name'], 'Assets/' . $newPhotoName);
            } else {
                $error = "Invalid image type. Only JPG, PNG, GIF, and WEBP are allowed.";
            }
        } else {
            $error = "Uploaded file is not a valid image.";
        }
    }


    mysqli_begin_transaction($conn);
    try {
        // Update package details
        mysqli_query($conn, "UPDATE packages 
                             SET description='$description', duration='$duration', activity='$activities', photo='$newPhotoName' 
                             WHERE package_id=$package_id");

        // Update or insert price
        $check_price = mysqli_query($conn, "SELECT price_id FROM package_prices WHERE package_id = $package_id");
        if (mysqli_num_rows($check_price) > 0) {
            mysqli_query($conn, "UPDATE package_prices 
                                 SET adult_price=$adult_price, child_price=$child_price 
                                 WHERE package_id=$package_id");
        } else {
            mysqli_query($conn, "INSERT INTO package_prices (package_id, adult_price, child_price) 
                                 VALUES ($package_id, $adult_price, $child_price)");
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
    form { background: #fff; padding: 2rem; max-width: 600px; margin: auto; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
    h2 { text-align: center; margin-bottom: 1.5rem; }
    label { display: block; margin-top: 1rem; font-weight: 600; }
    input[type=text], input[type=number], textarea, input[type=file] {
        width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; margin-top: 5px;
    }
    textarea { resize: vertical; height: 80px; }
    .img-preview { margin-top: 10px; max-width: 200px; display: block; border-radius: 6px; }
    button {
        background: #27ae60; color: white; padding: 10px 20px;
        border: none; border-radius: 6px; margin-top: 1.5rem; cursor: pointer;
    }
    button:hover { background: #219150; }
    .msg { text-align: center; font-weight: bold; margin-top: 1rem; }
    .msg.success { color: green; }
    .msg.error { color: red; }
    .back-link {
        display: block; text-align: center; margin-top: 1.5rem;
        color: #2980b9; text-decoration: none; font-weight: 600;
    }
    .back-link:hover { text-decoration: underline; }
  </style>
</head>
<body>

<h2>Edit Package: <?= htmlspecialchars($package['package_name']) ?></h2>

<?php if ($error): ?><div class="msg error"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="msg success"><?= $success ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
  <label>Description</label>
  <input type="text" name="description" value="<?= htmlspecialchars($package['description']) ?>" required>

  <label>Duration</label>
  <input type="text" name="duration" value="<?= htmlspecialchars($package['duration']) ?>" required>

  <label>Activities <small>(comma separated)</small></label>
  <textarea name="activity" required><?= htmlspecialchars($package['activity']) ?></textarea>

  <label>Adult Price (RM)</label>
  <input type="number" name="adult_price" step="0.01" value="<?= htmlspecialchars($price['adult_price'] ?? '0.00') ?>" required>

  <label>Child Price (RM)</label>
  <input type="number" name="child_price" step="0.01" value="<?= htmlspecialchars($price['child_price'] ?? '0.00') ?>" required>

  <label>Current Photo</label>
  <?php if (!empty($package['photo']) && file_exists('Assets/' . $package['photo'])): ?>
    <img src="Assets/<?= $package['photo'] ?>" class="img-preview" alt="Package Photo">
  <?php else: ?>
    <p style="color: #888;">No image attached</p>
  <?php endif; ?>

  <label>Upload New Photo (optional)</label>
  <input type="file" name="photo" accept="image/*">

  <button type="submit" name="update">Update Package</button>
</form>

<a href="manage_campsites.php" class="back-link">← Back to Package Management</a>

</body>
</html>
