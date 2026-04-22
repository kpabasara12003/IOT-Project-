<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'vendor/autoload.php';  

$email_config = [
    'host'       => 'smtp.gmail.com',
    'username'   => 'kusumindapabasara@gmail.com',     
    'password'   => 'dhut ayuu ucir jmxr',        
    'port'       => 587,
    'encryption' => PHPMailer::ENCRYPTION_STARTTLS, 
    'from_email' => 'kusumindapabasara@gmail.com',
    'from_name'  => 'LBMS'
];

function sendVerificationCode($email, $code, $student_name) {
    global $email_config;
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $email_config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $email_config['username'];
        $mail->Password   = $email_config['password'];
        $mail->SMTPSecure = $email_config['encryption'];
        $mail->Port       = $email_config['port'];

        $mail->setFrom($email_config['from_email'], $email_config['from_name']);
        $mail->addAddress($email, $student_name);

        $mail->isHTML(true);
        $mail->Subject = 'Your Library Login Verification Code';
        $mail->Body = "
        <html>
        <body style='font-family: Arial;'>
            <h2>Library System Verification</h2>
            <p>Hello <strong>$student_name</strong>,</p>
            <p>Your login code is: <span style='font-size:28px; font-weight:bold;'>$code</span></p>
            <p>This code expires in <strong>10 minutes</strong>.</p>
            <p>If you did not request this, ignore this email.</p>
        </body>
        </html>";
        $mail->AltBody = "Your verification code is: $code. Valid for 10 minutes.";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email error: " . $mail->ErrorInfo);
        return false;
    }
}
?>