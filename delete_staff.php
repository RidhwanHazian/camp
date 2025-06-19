<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
checkAdminSession();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid staff ID provided.";
    header('Location: manage_staff.php');
    exit();
}

$staff_id = intval($_GET['id']);

try {
    // Start transaction
    $conn->begin_transaction();

    // Delete from staff_facilities
    $stmt1 = $conn->prepare("DELETE FROM staff_facilities WHERE staff_id = ?");
    if ($stmt1) {
        $stmt1->bind_param("i", $staff_id);
        $stmt1->execute();
        $stmt1->close();
    }

    // Delete from task_assignment
    $stmt2 = $conn->prepare("DELETE FROM task_assignment WHERE staff_id = ?");
    if ($stmt2) {
        $stmt2->bind_param("i", $staff_id);
        $stmt2->execute();
        $stmt2->close();
    }

    // Unassign from bookings (set NULL instead of deleting booking)
    $stmt3 = $conn->prepare("UPDATE bookings SET staff_id = NULL WHERE staff_id = ?");
    if ($stmt3) {
        $stmt3->bind_param("i", $staff_id);
        $stmt3->execute();
        $stmt3->close();
    }

    // Finally delete from staff
    $stmt4 = $conn->prepare("DELETE FROM staff WHERE staff_id = ?");
    if ($stmt4) {
        $stmt4->bind_param("i", $staff_id);
        $stmt4->execute();

        if ($stmt4->affected_rows > 0) {
            $conn->commit();
            $_SESSION['success'] = "Staff deleted successfully!";
        } else {
            $conn->rollback();
            $_SESSION['error'] = "Staff not found or could not be deleted.";
        }

        $stmt4->close();
    } else {
        throw new Exception("Failed to prepare staff delete statement.");
    }

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error deleting staff: " . $e->getMessage();
}

header("Location: manage_staff.php");
exit();
?>
