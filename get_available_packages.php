<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

$arrival_date = isset($_GET['arrival_date']) ? $_GET['arrival_date'] : date('Y-m-d');

// This query gets all packages and LEFT JOINs to find their slot info for the selected date.
// A package without a slot defined for a day will have a NULL slot_limit.
$sql = "
    SELECT
        p.package_id,
        ps.slot_limit,
        (SELECT COUNT(*) FROM bookings b WHERE b.package_id = p.package_id AND b.arrival_date = ?) as booked_slots
    FROM
        packages p
    LEFT JOIN
        package_slots ps ON p.package_id = ps.package_id AND ps.slot_date = ?
";

$stmt = $conn->prepare($sql);
// Note: mysqli_prepare requires the connection object. Assuming $conn is available.
if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit();
}
$stmt->bind_param("ss", $arrival_date, $arrival_date);
$stmt->execute();
$result = $stmt->get_result();

$availability_data = [];
while ($row = $result->fetch_assoc()) {
    $is_available = true;
    // A package is unavailable only if a slot limit is defined AND the number of booked slots has reached the limit.
    // If no slot limit is defined (slot_limit is NULL), the package is available by default.
    if ($row['slot_limit'] !== null && $row['booked_slots'] >= $row['slot_limit']) {
        $is_available = false;
    }
    $availability_data[$row['package_id']] = ['is_available' => $is_available];
}

echo json_encode($availability_data);

$stmt->close();
$conn->close();
?> 
