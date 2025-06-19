<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
checkAdminSession();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid campsite ID provided.";
    header('Location: manage_campsites.php');
    exit();
}

$id = intval($_GET['id']);

try {
    // Begin transaction
    $conn->begin_transaction();

    // Step 1: Get all package_ids linked to this camp
    $package_ids = [];
    $pkg_query = $conn->prepare("SELECT package_id FROM campsite_packages WHERE camp_id = ?");
    $pkg_query->bind_param("i", $id);
    $pkg_query->execute();
    $pkg_result = $pkg_query->get_result();
    while ($row = $pkg_result->fetch_assoc()) {
        $package_ids[] = $row['package_id'];
    }
    $pkg_query->close();

    // Step 2: For each package, check if it is only used by this campsite
    foreach ($package_ids as $package_id) {
        // Check how many campsites use this package
        $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM campsite_packages WHERE package_id = ?");
        $count_stmt->bind_param("i", $package_id);
        $count_stmt->execute();
        $result = $count_stmt->get_result();
        $total = $result->fetch_assoc()['total'];
        $count_stmt->close();

        if ($total == 1) {
            // Delete from package_activities
            $conn->prepare("DELETE FROM package_activities WHERE package_id = ?")
                 ->bind_param("i", $package_id)
                 ->execute();

            // Delete from package_prices
            $conn->prepare("DELETE FROM package_prices WHERE package_id = ?")
                 ->bind_param("i", $package_id)
                 ->execute();

            // Delete from packages
            $conn->prepare("DELETE FROM packages WHERE package_id = ?")
                 ->bind_param("i", $package_id)
                 ->execute();
        }

        // Remove campsite-package link
        $remove_link = $conn->prepare("DELETE FROM campsite_packages WHERE camp_id = ? AND package_id = ?");
        $remove_link->bind_param("ii", $id, $package_id);
        $remove_link->execute();
        $remove_link->close();
    }

    // Step 3: Delete the campsite
    $delete_campsite = $conn->prepare("DELETE FROM campsites WHERE camp_id = ?");
    $delete_campsite->bind_param("i", $id);
    $delete_campsite->execute();

    if ($delete_campsite->affected_rows > 0) {
        $_SESSION['success'] = "Campsite and its related packages deleted successfully!";
    } else {
        $_SESSION['error'] = "Campsite not found or could not be deleted.";
    }

    $delete_campsite->close();
    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
    error_log("Delete error: " . $e->getMessage());
    $_SESSION['error'] = "Error deleting campsite and data: " . $e->getMessage();
}

header("Location: manage_campsites.php");
exit();
?>
