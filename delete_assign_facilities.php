<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
checkAdminSession();

if (!isset($_GET['staff_id']) || !isset($_GET['facility_name'])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: manage_staff.php");
    exit();
}

$staff_id = intval($_GET['staff_id']);
$facility_name = urldecode($_GET['facility_name']);

// Get facility_id from name
$facility_query = $conn->prepare("SELECT facility_id FROM facilities WHERE facility_name = ?");
$facility_query->bind_param("s", $facility_name);
$facility_query->execute();
$facility_result = $facility_query->get_result();
$facility = $facility_result->fetch_assoc();
$facility_id = $facility['facility_id'] ?? 0;

if ($facility_id > 0) {
    $delete = $conn->prepare("DELETE FROM staff_facilities WHERE staff_id = ? AND facility_id = ?");
    $delete->bind_param("ii", $staff_id, $facility_id);
    $delete->execute();

    $_SESSION['success'] = "Facility assignment deleted successfully.";
} else {
    $_SESSION['error'] = "Facility not found.";
}

header("Location: manage_staff.php");
exit();
?>
