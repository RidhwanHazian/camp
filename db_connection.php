<?php
$servername = "localhost";
$username = "root";
$password = ""; // If your MySQL has no password
$dbname = "camp";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
