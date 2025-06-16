<?php
require_once 'db_connection.php';

// Read the SQL file
$sql = file_get_contents('setup_database.sql');

// Execute multi query
if (mysqli_multi_query($conn, $sql)) {
    echo "Database tables created successfully!";
} else {
    echo "Error creating database tables: " . mysqli_error($conn);
}

$conn->close();
?> 