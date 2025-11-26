<?php
require 'config/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verify Password
        if (password_verify($password, $row['password'])) {
            
            // --- LOGIN SUCCESSFUL (No OTP Check) ---
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = $row['role'];

            // Redirect based on role
            if ($row['role'] == 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: patient/index.php");
            }
            exit();

        } else {
            $error = "Invalid Password!";
        }
    } else {
        $error = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nova Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body, html { height: 100%; font-family: 'Poppins', sans-serif; background-color: #fff; }
        
        /* Left Side Image */
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

        /* Right Side Form */
        .login-section { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #fff; }
        .form-container { width: 100%; max-width: 400px; padding: 30px; }
        .form-control { border-radius: 8px; padding: 12px; background-color: #f8f9fa; border: 1px solid #eee; }
        .form-control:focus { background-color: #fff; border-color: #3a7bd5; box-shadow: none; }
        
        .btn-login { 
            background: linear-gradient(to right, #3a7bd5, #3a6073); border: none; height: 50px; 
            border-radius: 8px; font-weight: 600; letter-spacing: 0.5px; transition: 0.3s; 
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(58, 123, 213, 0.3); }
        
        @media (max-width: 768px) { .bg-image { display: none; } }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            
            <div class="col-lg-7 col-md-6 d-none d-md-block bg-image">
                <div class="top-logo"><i class="fas fa-stethoscope"></i></div>
                <div class="image-text">
                    <h1>Welcome to NOVA CARE</h1>
                    <p>Your Health, Our Priority.<br>Connect with top specialists instantly.</p>
                </div>
            </div>

            <div class="col-lg-5 col-md-6 login-section">
                <div class="form-container">
                    <div class="text-center mb-5">
                        <h3 class="fw-bold" style="color: #3a7bd5;">Welcome Back!</h3>
                        <p class="text-muted">Please sign in to continue.</p>
                    </div>

                    <?php if($error) { echo "<div class='alert alert-danger text-center p-2 mb-3 small'>$error</div>"; } ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember">
                                <label class="form-check-label text-muted small" for="remember">Remember me</label>
                            </div>
                            <a href="forgot_password.php" class="text-decoration-none small fw-bold" style="color: #3a7bd5;">Forgot Password?</a>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-login">LOGIN <i class="fas fa-arrow-right ms-2"></i></button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted small">Don't have an account? <a href="register.php" class="fw-bold text-decoration-none" style="color: #3a7bd5;">Create Account</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>