<?php
//add session_start(); here if not working
function checkAdminSession() {
    if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
}

function checkStaffSession() {
    if (!isset($_SESSION['staff_id']) || $_SESSION['role'] !== 'staff') {
        header("Location: login.php");
        exit();
    }
}

function checkCustomerSession() {
    if (!isset($_SESSION['customer_id']) || $_SESSION['role'] !== 'customer') {
        header("Location: login.php");
        exit();
    }
}

function checkAdminOrStaffSession() {
    if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
        header("Location: login.php");
        exit();
    }
}
?> 