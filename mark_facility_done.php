<?php
include 'session_check.php';
checkStaffSession();
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['facility_id'])) {
    $staff_id = $_SESSION['staff_id'];
    $facility_id = intval($_POST['facility_id']);
    $stmt = $conn->prepare("UPDATE staff_facilities SET status = 'done' WHERE staff_id = ? AND facility_id = ?");
    $stmt->bind_param("ii", $staff_id, $facility_id);
    $stmt->execute();
}
header('Location: timetable_staff.php');
exit(); 