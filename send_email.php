<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'session_check.php';
include 'db_connection.php';
checkStaffSession();
require_once 'vendor/autoload.php'; // Composer autoloader

function sendPaymentReceipt($email, $name, $bookingId, $amount, $packageName, $stayDuration, $guestSummary, $paymentMethod, $paymentDate) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'iwghostpride@gmail.com';
        $mail->Password = 'edbucokhnzajivxy'; // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('iwghostpride@gmail.com', 'TasikBiruCamps');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = "Your Payment Receipt - Booking #$bookingId";

        $mail->Body = "
        <div style='font-family:Arial, sans-serif; max-width:600px; margin:0 auto; padding:20px; background-color:#f4f4f4; border-radius:8px;'>
            <div style='background-color:#6f74c6; color:white; padding:20px; border-top-left-radius:8px; border-top-right-radius:8px; text-align:center;'>
                <h2 style='margin:0;'>TasikBiruCamps</h2>
                <p style='margin:5px 0 0;'>Payment Receipt</p>
            </div>

            <div style='background:white; padding:20px; border-bottom-left-radius:8px; border-bottom-right-radius:8px;'>
                <p>Hi <strong>" . htmlspecialchars($name) . "</strong>,</p>

                <p>Thank you for your payment! We're pleased to confirm your booking with the following details:</p>

                <table style='width:100%; border-collapse:collapse; margin:20px 0; font-size:15px;'>
                    <tr><td style='padding:8px;'><strong>Booking ID:</strong></td><td style='padding:8px;'>#$bookingId</td></tr>
                    <tr><td style='padding:8px;'><strong>Customer Name:</strong></td><td style='padding:8px;'>" . htmlspecialchars($name) . "</td></tr>
                    <tr><td style='padding:8px;'><strong>Package:</strong></td><td style='padding:8px;'>" . htmlspecialchars($packageName) . "</td></tr>
                    <tr><td style='padding:8px;'><strong>Stay Duration:</strong></td><td style='padding:8px;'>$stayDuration</td></tr>
                    <tr><td style='padding:8px;'><strong>Number of Guests:</strong></td><td style='padding:8px;'>$guestSummary</td></tr>
                    <tr><td style='padding:8px;'><strong>Payment Method:</strong></td><td style='padding:8px;'>$paymentMethod</td></tr>
                    <tr><td style='padding:8px;'><strong>Payment Date:</strong></td><td style='padding:8px;'>$paymentDate</td></tr>
                    <tr><td style='padding:8px; font-weight:bold;'><strong>Total Amount Paid:</strong></td><td style='padding:8px; font-weight:bold;'>RM " . number_format($amount, 2) . "</td></tr>
                </table>

                <p style='margin-top:20px;'>Thank you again for choosing <strong>TasikBiruCamps</strong>! We look forward to your visit.</p>
                <p style='margin-top:30px; font-size:0.9em; color:#666;'>This is an automated receipt email. Please do not reply.</p>
            </div>

            <div style='text-align:center; font-size:12px; color:#555; margin-top:10px;'>
                TasikBiruCamps, Jalan Tasik Biru, 12345 Kampung Damai, Malaysia<br>
                Phone: 012-3456789 | Email: info@tasikbirucamps.com
            </div>
        </div>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Receipt email error: " . $mail->ErrorInfo);
    }
}
?>
