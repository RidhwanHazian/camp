<?php
include 'session_check.php';
checkStaffSession();

include 'db_connection.php';

$staff_id = $_SESSION['staff_id']; // Use staff_id from session
$staff_name = '';
$staff_username = ''; // Add username variable
$profile_pic = 'default_profile.png'; // Default image

// Fetch current staff data
$stmt = $conn->prepare("SELECT staff_name, staff_username, profile_pic FROM staff WHERE staff_id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

if ($staff) {
    $staff_name = $staff['staff_name'];
    $staff_username = $staff['staff_username']; // Get username
    if ($staff['profile_pic']) {
        $profile_pic = $staff['profile_pic'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_staff_name = trim($_POST['staff_name']);
    $new_staff_username = trim($_POST['staff_username']); // Get new username
    $new_password = $_POST['staff_password']; // New password field

    $update_fields = [];
    $bind_params = '';
    $bind_values = [];

    // Update staff name if changed
    if ($new_staff_name !== $staff_name) {
        $update_fields[] = 'staff_name = ?';
        $bind_params .= 's';
        $bind_values[] = $new_staff_name;
        $_SESSION['full_name'] = $new_staff_name; // Update session full name for consistency
    }

    // Update username if changed
    if ($new_staff_username !== $staff_username) {
        // Check if username already exists
        $check_stmt = $conn->prepare("SELECT staff_id FROM staff WHERE staff_username = ? AND staff_id != ?");
        $check_stmt->bind_param("si", $new_staff_username, $staff_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $_SESSION['error'] = "Username already exists. Please choose a different one.";
            header("Location: edit_profile_staff.php");
            exit();
        }
        
        $update_fields[] = 'staff_username = ?';
        $bind_params .= 's';
        $bind_values[] = $new_staff_username;
        $_SESSION['username'] = $new_staff_username; // Update session username
    }

    // Handle password update if provided
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_fields[] = 'staff_password = ?';
        $bind_params .= 's';
        $bind_values[] = $hashed_password;
    }

    // Handle file upload for profile picture
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $new_filename = 'staff_' . $staff_id . '_' . time() . '.' . $ext;
        $upload_dir = 'uploads/profile/';
        $upload_path = $upload_dir . $new_filename;

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
            $update_fields[] = 'profile_pic = ?';
            $bind_params .= 's';
            $bind_values[] = $upload_path;
            $profile_pic = $upload_path; // Update current profile pic path
        } else {
            $_SESSION['error'] = "Failed to upload profile picture.";
        }
    }

    if (!empty($update_fields)) {
        $sql = "UPDATE staff SET " . implode(', ', $update_fields) . " WHERE staff_id = ?";
        $bind_params .= 'i'; // Add type for staff_id
        $bind_values[] = $staff_id; // Add staff_id to values

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($bind_params, ...$bind_values);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Profile updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating profile: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['success'] = "No changes to save.";
    }
    header("Location: edit_profile_staff.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Staff Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        body { display: flex; background-color: #f0f2f5; font-family: Arial, sans-serif; }
        .sidebar {
            width: 250px; background-color: #6f74c6; color: white;
            padding: 40px 20px; height: 100vh; position: fixed;
        }
        .sidebar h2 { font-size: 48px; margin-bottom: 40px; display: flex; align-items: center; gap: 10px; }
        .sidebar a {
            display: flex; align-items: center; gap: 10px; color: white; font-weight: bold;
            font-size: 26px; text-decoration: none; margin-bottom: 25px; padding: 12px 20px;
            border-radius: 12px; transition: all 0.3s ease;
        }
        .sidebar a:hover, .sidebar a.active { background-color: #343795; }
        .main { flex-grow: 1; padding: 30px; margin-left: 250px; }
        .profile-edit-card {
            max-width: 400px; margin: 40px auto; background: #fff; border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08); padding: 2rem 2.5rem 2.5rem 2.5rem; text-align: center;
        }
        .profile-pic {
            width: 120px; height: 120px; border-radius: 50%; object-fit: cover;
            border: 3px solid #6f74c6; margin-bottom: 1rem; background: #eee;
        }
        .form-group { margin-bottom: 1.5rem; text-align: left; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; color: #6f74c6; }
        input[type='text'], input[type='password'], input[type='file'] {
            width: 100%; padding: 0.7rem; border: 1px solid #ccc; border-radius: 8px; font-size: 1rem;
        }
        button {
            background: #6f74c6; color: #fff; border: none; padding: 0.8rem 2rem; border-radius: 8px;
            font-size: 1.1rem; font-weight: bold; cursor: pointer; margin-top: 1rem; transition: background 0.2s;
        }
        button:hover { background: #343795; }
        .success { color: #27ae60; margin-bottom: 1rem; }
        .error { color: #e74c3c; margin-bottom: 1rem; }
        .back-link {
            display: inline-block; margin-top: 1.5rem; color: #6f74c6; text-decoration: none; font-weight: bold;
        }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2><i class="fas fa-user-shield"></i> Staff</h2>
        <a href="staff_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="customer_booking_staff.php"><i class="fas fa-calendar-check"></i> Customer Booking</a>
        <a href="package_detail_staff.php"><i class="fas fa-box"></i> Package Details</a>
        <a href="timetable_staff.php"><i class="fas fa-clock"></i> Timetable</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>
    <div class="main">
        <div class="profile-edit-card">
            <h2>Edit Profile</h2>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <div class="profile-pic">
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                </div>
                <div class="form-group">
                    <label for="profile_pic">Change Picture:</label>
                    <input type="file" name="profile_pic" id="profile_pic" accept="image/*">
                </div>
                <div class="form-group">
                    <label for="staff_name">Full Name:</label>
                    <input type="text" name="staff_name" id="staff_name" value="<?php echo htmlspecialchars($staff_name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="staff_username">Username:</label>
                    <input type="text" name="staff_username" id="staff_username" value="<?php echo htmlspecialchars($staff_username); ?>" required>
                </div>
                <div class="form-group">
                    <label for="staff_password">New Password (leave blank to keep current):</label>
                    <input type="password" name="staff_password" id="staff_password">
                </div>
                <button type="submit">Save Changes</button>
            </form>
            <a href="staff_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
        </div>
    </div>
</body>
</html>