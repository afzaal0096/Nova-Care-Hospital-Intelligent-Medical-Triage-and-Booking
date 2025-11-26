<?php
require 'config/db.php';

// PHPMailer Files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (isset($_GET['email'])) {
    $email = $_GET['email'];
    
    // --- OTP UPDATE LOGIC ---
    $otp = rand(100000, 999999); // Naya OTP
    $otp_expiry = date("Y-m-d H:i:s", strtotime("+1 minute")); // Naya 1 Minute ka Time

    // Database update karein (OTP + Time)
    $conn->query("UPDATE users SET otp='$otp', otp_expiry='$otp_expiry' WHERE email='$email'");
    // ------------------------

    // Send Email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'muhammadafzaalhameed78@gmail.com'; 
        $mail->Password   = 'egmadazhxavbxtvl'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('muhammadafzaalhameed78@gmail.com', 'Nova Care Hospital');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Resend OTP - Nova Care';
        $mail->Body    = "<h3>Your New OTP is: <b>$otp</b></h3><p>This code is valid for 1 minute only.</p>";

        $mail->send();
        
        // Wapis Verify page par bhej do
        header("Location: verify_otp.php?email=$email&resend=1");
        exit();

    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    header("Location: login.php");
}
?>