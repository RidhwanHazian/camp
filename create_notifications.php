<?php
require_once 'notifications_function.php';

// Example functions to create notifications for different events

function createBookingConfirmationNotification($user_id, $booking_id) {
    $title = "Booking Confirmed";
    $message = "Your booking #$booking_id has been successfully confirmed.";
    $type = "success";
    $icon = "✓";
    
    addNotification($user_id, $title, $message, $type, $icon);
}

function createPaymentNotification($user_id, $amount) {
    $title = "Payment Received - RM$amount";
    $message = "Payment was received successfully.";
    $type = "payment";
    $icon = "💰";
    
    addNotification($user_id, $title, $message, $type, $icon);
}

function createCampReminderNotification($user_id, $booking_id, $camp_date) {
    $title = "Your camp date is tomorrow!";
    $message = "Reminder: Pack your bags and be ready!";
    $type = "reminder";
    $icon = "👍";
    
    addNotification($user_id, $title, $message, $type, $icon);
}

// Example usage:
/*
// When booking is confirmed
createBookingConfirmationNotification($user_id, $booking_id);

// When payment is received
createPaymentNotification($user_id, $amount);

// One day before camp date
createCampReminderNotification($user_id, $booking_id, $camp_date);
*/
?>