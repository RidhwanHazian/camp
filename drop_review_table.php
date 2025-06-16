<?php
require_once 'confg.php';

try {
    $sql = "DROP TABLE IF EXISTS review";
    $conn->exec($sql);
    echo "Review table dropped successfully";
} catch(PDOException $e) {
    echo "Error dropping table: " . $e->getMessage();
}
?> 