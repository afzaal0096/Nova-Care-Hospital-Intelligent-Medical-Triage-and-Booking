<?php
require 'config/db.php';

// Check if email is present in URL
if (!isset($_GET['email'])) { 
    header("Location: register.php"); 
    exit(); 
}

$email = $_GET['email'];
$msg = "";

// Agar Resend karke wapis aya hai to success message dikhao
if (isset($_GET['resend'])) {
    $msg = "<div class='alert alert-success text-center'>✅ New OTP has been sent. Valid for 1 minute.</div>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp_entered = $_POST['otp'];
    
    // User ka data fetch karein (OTP aur Expiry Time)
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_time = date("Y-m-d H:i:s");

        // Check 1: Kya OTP match kar raha hai?
        if ($row['otp'] == $otp_entered) {
            
            // Check 2: Kya Time abhi bacha hai? (Expiry Time > Current Time)
            if ($row['otp_expiry'] >= $current_time) {
                // SUCCESS: Account Verified!
                $conn->query("UPDATE users SET is_verified=1, otp=NULL, otp_expiry=NULL WHERE email='$email'");
                echo "<script>alert('✅ Account Verified Successfully! You can now Login.'); window.location.href='login.php';</script>";
                exit();
            } else {
                // FAILURE: OTP Expired
                $msg = "<div class='alert alert-warning text-center'>⚠️ OTP Expired! Please click 'Resend OTP' to get a new code.</div>";
            }
            
        } else {
            // FAILURE: Wrong OTP
            $msg = "<div class='alert alert-danger text-center'>❌ Invalid OTP! Please try again.</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger text-center'>❌ Email not found in system.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Verify Account - Nova Care</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { border: none; border-top: 5px solid #3a7bd5; border-radius: 15px; max-width: 400px; width: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .form-control { font-weight: 700; letter-spacing: 5px; text-align: center; font-size: 24px; height: 60px; border-radius: 10px; border: 2px solid #eee; }
        .form-control:focus { border-color: #3a7bd5; box-shadow: none; background-color: #f9fbfc; }
        .btn-verify { background: linear-gradient(to right, #3a7bd5, #3a6073); color: white; font-weight: 600; border: none; padding: 12px; border-radius: 50px; transition: 0.3s; }
        .btn-verify:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(58, 123, 213, 0.3); }
        
        /* Timer Style */
        .timer-text { font-size: 14px; color: #666; margin-top: 15px; }
        .resend-link { text-decoration: none; color: #3a7bd5; font-weight: bold; display: none; cursor: pointer; transition: 0.2s; }
        .resend-link:hover { text-decoration: underline; color: #2c5aa0; }
    </style>
</head>
<body>
    
    <div class="card p-4 mx-3">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-dark mb-2">Verify OTP</h3>
            <p class="text-muted small">We've sent a verification code to<br><b><?php echo htmlspecialchars($email); ?></b></p>
        </div>
        
        <?php echo $msg; ?>
        
        <form method="POST">
            <div class="mb-4">
                <input type="text" name="otp" class="form-control" placeholder="000000" maxlength="6" required autocomplete="off">
            </div>
            <button type="submit" class="btn btn-verify w-100">VERIFY ACCOUNT</button>
        </form>
        
        <div class="text-center">
            <p class="timer-text" id="countdown-box">
                Resend code in <span id="timer" class="fw-bold text-dark">60</span>s
            </p>
            
            <a href="resend_otp.php?email=<?php echo $email; ?>" id="resend-btn" class="resend-link mt-3 d-inline-block">
                <i class="fas fa-redo me-1"></i> Resend OTP
            </a>
        </div>

        <div class="mt-4 pt-3 border-top text-center">
            <small class="text-muted" style="font-size: 12px;">
                Didn't receive the email? Check your <b>Spam</b> folder.
            </small>
        </div>
    </div>

    <script>
        // 60 Seconds Countdown Logic
        let timeLeft = 60; 
        const timerElement = document.getElementById('timer');
        const countdownBox = document.getElementById('countdown-box');
        const resendBtn = document.getElementById('resend-btn');

        const countdown = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(countdown);
                countdownBox.style.display = "none"; // Hide timer text
                resendBtn.style.display = "inline-block"; // Show Resend button
            } else {
                timerElement.innerText = timeLeft;
                timeLeft--;
            }
        }, 1000);
    </script>

</body>
</html>