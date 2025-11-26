<?php
require 'config/db.php';

// PHPMailer Files Load
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$message = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $password = $_POST['password'];

    // Password Validation
    if (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/", $password)) {
        $message = "<div class='alert alert-danger shadow-sm border-0'>⚠️ Password is too weak! (Must contain Capital, Number & Symbol)</div>";
    } else {
        $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check->num_rows > 0) {
            $message = "<div class='alert alert-warning shadow-sm border-0'>⚠️ Email already registered!</div>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // --- OTP LOGIC START ---
            $otp = rand(100000, 999999);
            // Set Expiry time to 1 minute from now
            $otp_expiry = date("Y-m-d H:i:s", strtotime("+1 minute")); 

            // Insert OTP and Expiry into Database
            $sql = "INSERT INTO users (name, email, phone, dob, gender, password, role, is_verified, otp, otp_expiry) 
                    VALUES ('$name', '$email', '$phone', '$dob', '$gender', '$hashed_password', 'patient', 0, '$otp', '$otp_expiry')";
            // --- OTP LOGIC END ---
            
            if ($conn->query($sql) === TRUE) {
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
                    $mail->addAddress($email, $name);

                    $mail->isHTML(true);
                    $mail->Subject = 'Verify Your Account - Nova Care';
                    $mail->Body    = "
                        <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                            <div style='background-color: white; padding: 20px; border-radius: 10px; text-align: center;'>
                                <h2 style='color: #3a7bd5;'>Welcome to Nova Care!</h2>
                                <p>Thank you for registering. Please use the code below to verify your account.</p>
                                <h1 style='letter-spacing: 5px; color: #333;'>$otp</h1>
                                <p style='color: #red;'>This code will expire in 1 minute.</p>
                            </div>
                        </div>";

                    $mail->send();
                    header("Location: verify_otp.php?email=$email");
                    exit();

                } catch (Exception $e) {
                    $message = "<div class='alert alert-danger'>Email sending failed. Error: {$mail->ErrorInfo}</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Create Account - Nova Care</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body, html { height: 100%; font-family: 'Poppins', sans-serif; background-color: #fff; }
        .bg-image {
            background: radial-gradient(circle at 50% 0%, rgba(255, 255, 255, 0.3) 0%, rgba(58, 123, 213, 0.1) 40%, transparent 80%),
                linear-gradient(to bottom, rgba(16, 78, 139, 0.6), rgba(58, 123, 213, 0.6)),
                url('https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80');
            background-size: cover; background-position: center; position: relative; min-height: 100vh;
        }
        .image-text { position: absolute; bottom: 50px; left: 50px; color: white; z-index: 2; width: 85%; }
        .image-text h1 { font-size: 3.5rem; font-weight: 700; margin-bottom: 15px; }
        .register-section { display: flex; align-items: center; justify-content: center; height: 100vh; overflow-y: auto; padding: 40px 20px; }
        .form-container { width: 100%; max-width: 500px; }
        .btn-register { background: linear-gradient(to right, #3a7bd5, #3a6073); color: white; height: 50px; border: none; font-weight: 600; border-radius: 8px; }
        @media (max-width: 768px) { .bg-image { display: none; } .register-section { height: auto; } }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-lg-6 d-none d-lg-block bg-image">
                <div class="image-text">
                    <h1>Join NOVA CARE</h1>
                    <p>Experience world-class healthcare with just a few clicks.</p>
                </div>
            </div>
            <div class="col-lg-6 register-section">
                <div class="form-container">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold" style="color: #3a7bd5;">Create Account</h3>
                        <p class="text-muted small">Fill in your details to register as a patient.</p>
                    </div>
                    <?php echo $message; ?>
                    <form method="POST">
                        <div class="mb-3"><input type="text" name="name" class="form-control p-3" placeholder="Full Name" required></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><input type="email" name="email" class="form-control p-3" placeholder="Email" required></div>
                            <div class="col-md-6 mb-3"><input type="text" name="phone" class="form-control p-3" placeholder="Phone" required></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><input type="date" name="dob" class="form-control p-3" required></div>
                            <div class="col-md-6 mb-3">
                                <select name="gender" class="form-select p-3" required>
                                    <option value="">Gender...</option><option value="Male">Male</option><option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4"><input type="password" name="password" class="form-control p-3" placeholder="Password (Strong)" required></div>
                        <button type="submit" class="btn btn-register w-100">REGISTER NOW</button>
                    </form>
                    <div class="text-center mt-4">
                        <p class="text-muted small">Already have an account? <a href="login.php" class="fw-bold text-decoration-none" style="color: #3a7bd5;">Sign In Here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>