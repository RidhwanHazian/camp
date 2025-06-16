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

$id = intval($_GET['id']);
error_log("Attempting to delete campsite ID: " . $id);

try {
    // First delete from campsite_packages
    $delete_campsite_packages = $conn->prepare("DELETE FROM campsite_packages WHERE camp_id = ?");
    $delete_campsite_packages->bind_param("i", $id);
    $delete_campsite_packages->execute();
    error_log("Deleted from campsite_packages: " . $delete_campsite_packages->affected_rows . " rows");
    $delete_campsite_packages->close();

    // Then delete the campsite
    $delete_campsite = $conn->prepare("DELETE FROM campsites WHERE camp_id = ?");
    $delete_campsite->bind_param("i", $id);
    $delete_campsite->execute();
    error_log("Deleted from campsites: " . $delete_campsite->affected_rows . " rows");
    
    if ($delete_campsite->affected_rows > 0) {
        $_SESSION['success'] = "Campsite deleted successfully!";
    } else {
        $_SESSION['error'] = "Campsite not found or could not be deleted.";
    }
    
    $delete_campsite->close();

} catch (Exception $e) {
    error_log("Error in delete_campsite.php: " . $e->getMessage());
    $_SESSION['error'] = "Error deleting campsite: " . $e->getMessage();
}

header("Location: manage_campsites.php");
exit();
?>