<?php
session_start(); // Start the session to use $_SESSION messages
include 'db_connection.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Log the POST data
error_log("POST data: " . print_r($_POST, true));

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid campsite ID provided.";
    header('Location: manage_campsites.php');
    exit();
}

$package_id = intval($_GET['id']);

try {
    $conn->begin_transaction();

    // Get photo filename before deleting
    $photo_query = $conn->prepare("SELECT photo FROM packages WHERE package_id = ?");
    $photo_query->bind_param("i", $package_id);
    $photo_query->execute();
    $photo_result = $photo_query->get_result();
    $photo = '';
    if ($photo_result->num_rows > 0) {
        $photo = $photo_result->fetch_assoc()['photo'];
    }
    $photo_query->close();

    // Delete price record
    $price_stmt = $conn->prepare("DELETE FROM package_prices WHERE package_id = ?");
    $price_stmt->bind_param("i", $package_id);
    $price_stmt->execute();
    $price_stmt->close();

    // Delete package
    $pkg_stmt = $conn->prepare("DELETE FROM packages WHERE package_id = ?");
    $pkg_stmt->bind_param("i", $package_id);
    $pkg_stmt->execute();
    $pkg_stmt->close();

    // Delete the photo file from Assets folder
    if (!empty($photo)) {
        $photo_path = 'Assets/' . $photo;
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }
    }

    $conn->commit();
    $_SESSION['success'] = "Package deleted successfully!";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error deleting package: " . $e->getMessage();
}

header("Location: manage_campsites.php");
exit();
?>
