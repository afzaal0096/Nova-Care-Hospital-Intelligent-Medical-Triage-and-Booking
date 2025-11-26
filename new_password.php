<?php
require 'config/db.php';

// Check if session has verified email
if (!isset($_SESSION['reset_email'])) { header("Location: login.php"); exit(); }
$email = $_SESSION['reset_email'];
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password']; // Doosra password field

    // --- VALIDATION CHECKS ---
    if ($pass !== $confirm_pass) {
        $msg = "<div class='alert alert-danger'>❌ Passwords do not match!</div>";
    } elseif (strlen($pass) < 6) {
        $msg = "<div class='alert alert-warning'>⚠️ Password must be at least 6 characters.</div>";
    } else {
        // Sab theek hai, password update karo
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        
        $conn->query("UPDATE users SET password='$hashed', otp=NULL WHERE email='$email'");
        
        // Session clear karo aur login par bhejo
        unset($_SESSION['reset_email']);
        
        echo "<script>alert('✅ Password Changed Successfully! Please Login.'); window.location.href='login.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>New Password - Nova Care</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body, html { height: 100%; font-family: 'Poppins', sans-serif; background-color: #fff; }
        
        .bg-image {
            background: radial-gradient(circle at 50% 0%, rgba(255, 255, 255, 0.3) 0%, rgba(58, 123, 213, 0.1) 40%, transparent 80%),
                linear-gradient(to bottom, rgba(16, 78, 139, 0.6), rgba(58, 123, 213, 0.6)),
                url('https://images.unsplash.com/photo-1538108149393-fbbd81895907?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80');
            background-size: cover; background-position: center; position: relative; min-height: 100vh;
        }
        .image-text { position: absolute; bottom: 50px; left: 50px; color: white; z-index: 2; width: 85%; }
        .image-text h1 { font-size: 3.5rem; font-weight: 700; margin-bottom: 15px; }
        .top-logo { position: absolute; top: 40px; left: 50px; font-size: 2rem; color: white; opacity: 0.9; }

        .login-section { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #fff; }
        .form-container { width: 100%; max-width: 400px; padding: 30px; }
        .form-control { border-radius: 8px; padding: 12px; background-color: #f8f9fa; border: 1px solid #eee; }
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
                <div class="image-text"><h1>New Start</h1><p>Create a strong password to secure your account.</p></div>
            </div>

            <div class="col-lg-5 col-md-6 login-section">
                <div class="form-container">
                    <div class="text-center mb-5">
                        <h3 class="fw-bold" style="color: #3a7bd5;">Reset Password</h3>
                        <p class="text-muted">Enter your new password below.</p>
                    </div>

                    <?php echo $msg; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="******" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="******" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>