<?php
require 'config/db.php';

// PHPMailer Load
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    
    // Check if Email Exists
    $check = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $otp = rand(100000, 999999);
        
        // Save OTP to DB
        $conn->query("UPDATE users SET otp='$otp' WHERE email='$email'");

        // Send Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            
            // =========== APNI DETAILS DALEIN ===========
            $mail->Username   = 'muhammadafzaalhameed78@gmail.com'; 
            $mail->Password   = 'egmadazhxavbxtvl'; 
            // ===========================================

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('muhammadafzaalhameed78@gmail.com', 'Nova Care Hospital');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Reset Password OTP - Nova Care';
            $mail->Body    = "<h3>Your Password Reset Code is: <b>$otp</b></h3>";

            $mail->send();
            header("Location: reset_code.php?email=$email");
            exit();

        } catch (Exception $e) {
            $msg = "<div class='alert alert-danger'>Email error: {$mail->ErrorInfo}</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>Email not found in our system!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password - Nova Care</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body, html { height: 100%; font-family: 'Poppins', sans-serif; background-color: #fff; }
        
        /* LEFT SIDE IMAGE (Same as Login) */
        .bg-image {
            background: radial-gradient(circle at 50% 0%, rgba(255, 255, 255, 0.3) 0%, rgba(58, 123, 213, 0.1) 40%, transparent 80%),
                linear-gradient(to bottom, rgba(16, 78, 139, 0.6), rgba(58, 123, 213, 0.6)),
                url('https://images.unsplash.com/photo-1538108149393-fbbd81895907?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80');
            background-size: cover; background-position: center; position: relative; min-height: 100vh;
        }
        .image-text { position: absolute; bottom: 50px; left: 50px; color: white; z-index: 2; width: 85%; }
        .image-text h1 { font-size: 3.5rem; font-weight: 700; margin-bottom: 15px; line-height: 1.1; text-shadow: 2px 2px 10px rgba(0,0,0,0.2); }
        .image-text p { font-size: 1.1rem; font-weight: 300; opacity: 0.95; margin-top: 15px; border-left: 4px solid #fff; padding-left: 15px; }
        .top-logo { position: absolute; top: 40px; left: 50px; font-size: 2rem; color: white; opacity: 0.9; }

        /* RIGHT SIDE FORM */
        .login-section { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #fff; }
        .form-container { width: 100%; max-width: 400px; padding: 30px; }
        .form-control { border-radius: 8px; padding: 12px; background-color: #f8f9fa; border: 1px solid #eee; }
        .form-control:focus { background-color: #fff; border-color: #3a7bd5; box-shadow: none; }
        
        .btn-primary { 
            background: linear-gradient(to right, #3a7bd5, #3a6073); 
            border: none; height: 50px; border-radius: 8px; 
            font-weight: 600; letter-spacing: 0.5px; transition: 0.3s; 
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(58, 123, 213, 0.3); }

        @media (max-width: 768px) { .bg-image { display: none; } }
    </style>
</head>
<body>

    <div class="container-fluid p-0">
        <div class="row g-0">
            
            <div class="col-lg-7 col-md-6 d-none d-md-block bg-image">
                <div class="top-logo"><i class="fas fa-stethoscope"></i></div>
                <div class="image-text">
                    <h1>Recovery Starts Here</h1>
                    <p>Reset your password securely and get back to your health dashboard.</p>
                </div>
            </div>

            <div class="col-lg-5 col-md-6 login-section">
                <div class="form-container">
                    <div class="text-center mb-5">
                        <h3 class="fw-bold" style="color: #3a7bd5;">Forgot Password?</h3>
                        <p class="text-muted">Enter your email to receive a reset code.</p>
                    </div>

                    <?php echo $msg; ?>

                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 btn-login">Send Reset Code <i class="fas fa-paper-plane ms-2"></i></button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted small">Remember your password? <a href="login.php" class="fw-bold text-decoration-none" style="color: #3a7bd5;">Login Here</a></p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>