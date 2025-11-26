<?php
require 'config/db.php';

// Check if email is in URL, otherwise redirect to login
if (!isset($_GET['email'])) { header("Location: login.php"); exit(); }
$email = $_GET['email'];
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = $_POST['otp'];
    
    // Check if OTP matches in database
    $check = $conn->query("SELECT * FROM users WHERE email='$email' AND otp='$otp'");

    if ($check->num_rows > 0) {
        // OTP Match! Save email in session and redirect to password change page
        $_SESSION['reset_email'] = $email;
        header("Location: new_password.php");
        exit();
    } else {
        $msg = "<div class='alert alert-danger'>Invalid OTP! Please try again.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Verify Code - Nova Care</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body, html { height: 100%; font-family: 'Poppins', sans-serif; background-color: #fff; }
        
        /* LEFT SIDE IMAGE */
        .bg-image {
            background: radial-gradient(circle at 50% 0%, rgba(255, 255, 255, 0.3) 0%, rgba(58, 123, 213, 0.1) 40%, transparent 80%),
                linear-gradient(to bottom, rgba(16, 78, 139, 0.6), rgba(58, 123, 213, 0.6)),
                url('https://images.unsplash.com/photo-1538108149393-fbbd81895907?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80');
            background-size: cover; background-position: center; position: relative; min-height: 100vh;
        }
        .image-text { position: absolute; bottom: 50px; left: 50px; color: white; z-index: 2; width: 85%; }
        .image-text h1 { font-size: 3.5rem; font-weight: 700; margin-bottom: 15px; }
        .top-logo { position: absolute; top: 40px; left: 50px; font-size: 2rem; color: white; opacity: 0.9; }

        /* RIGHT SIDE FORM */
        .login-section { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #fff; }
        .form-container { width: 100%; max-width: 400px; padding: 30px; }
        .form-control { 
            border-radius: 8px; padding: 12px; background-color: #f8f9fa; border: 1px solid #eee; 
            font-size: 24px; text-align: center; letter-spacing: 5px; font-weight: 700;
        }
        .form-control:focus { background-color: #fff; border-color: #3a7bd5; box-shadow: none; }
        
        .btn-primary { 
            background: linear-gradient(to right, #3a7bd5, #3a6073); border: none; height: 50px; 
            border-radius: 8px; font-weight: 600; letter-spacing: 0.5px; transition: 0.3s; 
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
                <div class="image-text"><h1>Security Check</h1><p>Please verify your identity to continue.</p></div>
            </div>
            <div class="col-lg-5 col-md-6 login-section">
                <div class="form-container">
                    <div class="text-center mb-5">
                        <h3 class="fw-bold" style="color: #3a7bd5;">Enter OTP</h3>
                        <p class="text-muted">We sent a code to <?php echo htmlspecialchars($email); ?></p>
                    </div>
                    <?php echo $msg; ?>
                    <form method="POST">
                        <div class="mb-4">
                            <input type="text" name="otp" class="form-control" placeholder="XXXXXX" maxlength="6" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Verify & Proceed <i class="fas fa-arrow-right ms-2"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>