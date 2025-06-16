<?php
// Start the session
session_start();

// Store the message before destroying the session
$message = "You have been successfully logged out.";

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Start a new session for the message
session_start();
$_SESSION['success_message'] = $message;

// Redirect to login page
header("Location: login.php");
exit();
?>