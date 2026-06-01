<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Global variable to store error information
$mail = null;

function sendOTP($toEmail, $otp)
{
    global $mail;

    try {
        // Create PHPMailer instance
        $mail = new PHPMailer(true);

        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // Your Gmail address
        $mail->Username = 'kodarikumar978@gmail.com';

        // Your Gmail App Password (without spaces)
        $mail->Password = 'fetvkgjtobnkgyrn';

        // Encryption and Port
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Sender and Recipient
        $mail->setFrom(
            'kodarikumar978@gmail.com',
            'TraceBack LFIS'
        );

        $mail->addAddress($toEmail);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = 'TraceBack Password Reset OTP';

        $mail->Body =
            '<h2>Password Reset OTP</h2>' .
            '<p>Your OTP is:</p>' .
            '<h1 style="color:#2563eb;">' . $otp . '</h1>' .
            '<p>This OTP is valid for 10 minutes.</p>';

        // Plain text fallback
        $mail->AltBody =
            'Your OTP is: ' . $otp .
            '. This OTP is valid for 10 minutes.';

        // Send Email
        $mail->send();

        return true;

    } catch (Exception $e) {
        // Save the real PHPMailer error
        if ($mail) {
            $mail->ErrorInfo = $e->getMessage();
        }

        return false;
    }
}
?>