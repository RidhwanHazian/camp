<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

try {
    // Get all booked dates from the database
    $stmt = $conn->prepare("
        SELECT DISTINCT date_range.date
        FROM bookings b
        CROSS JOIN (
            SELECT DATE_ADD(b2.arrival_date, INTERVAL n.num DAY) as date
            FROM bookings b2
            CROSS JOIN (
                SELECT a.N + b.N * 10 as num
                FROM (SELECT 0 as N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a
                CROSS JOIN (SELECT 0 as N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b
            ) n
            WHERE DATE_ADD(b2.arrival_date, INTERVAL n.num DAY) <= b2.departure_date
        ) date_range
        WHERE date_range.date >= CURDATE()
        AND b.status != 'cancelled'
    ");
    $stmt->execute();
    
    $bookedDates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(array_values(array_unique($bookedDates)));
} catch(PDOException $e) {
    error_log("Error in get_booked_dates.php: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to fetch booked dates']);
}
?>
