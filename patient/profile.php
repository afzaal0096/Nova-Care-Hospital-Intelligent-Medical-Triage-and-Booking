<?php
require '../config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); } // ← Yahan login.php kar dein

$user_id = $_SESSION['user_id'];
$msg = "";

// UPDATE PROFILE LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    
    $sql = "UPDATE users SET name='$name', phone='$phone', dob='$dob', gender='$gender' WHERE id='$user_id'";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['name'] = $name;
        $msg = "<div class='alert alert-success border-0 shadow-sm'><i class='fas fa-check-circle me-2'></i> Profile Updated!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Error updating profile.</div>";
    }
}

$user = $conn->query("SELECT * FROM users WHERE id='$user_id'")->fetch_assoc();

// NOTIFICATIONS
$sql_notif = "SELECT appointments.*, doctors.name as doc_name FROM appointments JOIN doctors ON appointments.doctor_id = doctors.id WHERE user_id = '$user_id' AND (status = 'approved' OR status = 'cancelled') ORDER BY id DESC LIMIT 5";
$res_notif = $conn->query($sql_notif);
$count_notif = ($res_notif) ? $res_notif->num_rows : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Profile - Nova Care</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; color: #444; }
        
        /* NAVBAR */
        .navbar { background: linear-gradient(to right, #3a7bd5, #3a6073); box-shadow: 0 4px 12px rgba(58, 123, 213, 0.2); }
        .navbar-brand { font-weight: 700; color: #fff !important; letter-spacing: 1px; }
        .nav-link { color: rgba(255,255,255,0.9) !important; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-right: 10px; }
        .nav-link:hover { color: #fff !important; transform: translateY(-1px); }
        .nav-link.active { color: #fff !important; border-bottom: 2px solid rgba(255,255,255,0.5); }
        
        /* FIX: Dropdown Toggle Text Color */
        .navbar .dropdown-toggle { color: #fff !important; } 

        /* HEADER WITH IMAGE (Updated) */
        .page-header {
            /* Online Image Link - Hamesha Chalegi */
            background: linear-gradient(rgba(58, 123, 213, 0.8), rgba(58, 96, 115, 0.8)), url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1500&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            text-align: center;
            height: 250px;
            position: relative;
        }

        /* PROFILE CARD */
        .profile-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-top: -80px; /* Overlap effect */
            border-top: 5px solid #3a7bd5;
            position: relative;
            z-index: 10;
        }
        .card-header-custom {
            padding: 25px;
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
        }
        .form-control, .form-select {
            height: 50px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            background-color: #f9fbfc;
            font-size: 14px;
            padding-left: 15px;
        }
        .form-control:focus, .form-select:focus { border-color: #3a7bd5; box-shadow: 0 0 0 4px rgba(58, 123, 213, 0.1); background-color: #fff; }
        
        .btn-update {
            background: linear-gradient(to right, #3a7bd5, #3a6073);
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 50px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 14px;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(58, 123, 213, 0.3);
        }
        .btn-update:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(58, 123, 213, 0.4); color: white; }

        /* FOOTER */
        footer { background: linear-gradient(to right, #3a7bd5, #3a6073); color: white; padding: 60px 0 20px 0; text-align: left; margin-top: 80px; }
        footer h5 { font-weight: 700; margin-bottom: 20px; color: #fff; text-transform: uppercase; letter-spacing: 1px; }
        footer ul li { margin-bottom: 10px; }
        footer a { color: rgba(255,255,255,0.8); text-decoration: none; transition: 0.3s; }
        footer a:hover { color: #fff; padding-left: 5px; }
        footer p.small { color: rgba(255,255,255,0.6); }
        .footer-icon { width: 35px; height: 35px; background: rgba(255,255,255,0.2); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 10px; transition: 0.3s; color: white; }
        .footer-icon:hover { background: white; color: #3a7bd5; transform: translateY(-3px); }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-heartbeat me-2"></i>NOVA CARE</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#patientNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="patientNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="article_about.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="article_tech.php">Technology</a></li>
                    <li class="nav-item"><a class="nav-link" href="article_care.php">Patient Care</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    <!-- NOTIFICATION BELL -->
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link" href="#" data-bs-toggle="dropdown"><i class="fas fa-bell"></i><?php if($count_notif > 0) echo '<span class="badge bg-danger rounded-circle position-absolute top-0 start-100 translate-middle p-1 border border-light rounded-circle"><span class="visually-hidden">New alerts</span></span>'; ?></a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><h6 class="dropdown-header">Updates</h6></li>
                            <?php 
                            if ($count_notif > 0) {
                                while($row = $res_notif->fetch_assoc()) {
                                    echo "<li><a class='dropdown-item small' href='#'>Dr. ".$row['doc_name']." - ".ucfirst($row['status'])."</a></li>";
                                }
                            } else { echo "<li><span class='dropdown-item text-muted small'>No new notifications</span></li>"; }
                            ?>
                        </ul>
                    </li>

                    <!-- USER PROFILE -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle bg-white text-primary rounded-pill px-3 py-1" href="#" data-bs-toggle="dropdown" style="color: #3a7bd5 !important;">
                            <i class="fas fa-user-circle me-1"></i> <?php echo $_SESSION['name']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                            <li><a class="dropdown-item" href="my_appointments.php">My Appointments</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HEADER -->
    <div class="page-header">
        <h1 class="fw-bold">My Profile</h1>
        <p class="lead opacity-90">Update your personal information</p>
    </div>

    <!-- PROFILE FORM -->
    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="profile-card">
                    <div class="card-header-custom">
                        <h4 class="mb-0 fw-bold text-primary"><i class="fas fa-user-edit me-2"></i>Edit Profile</h4>
                    </div>
                    
                    <div class="p-4">
                        <?php echo $msg; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold text-muted">Full Name</label>
                                    <input type="text" class="form-control" name="name" value="<?php echo $user['name']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold text-muted">Email (Read Only)</label>
                                    <input type="email" class="form-control bg-light" value="<?php echo $user['email']; ?>" readonly>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold text-muted">Phone Number</label>
                                    <input type="text" name="phone" class="form-control" value="<?php echo $user['phone']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold text-muted">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" value="<?php echo $user['dob']; ?>" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">Gender</label>
                                <select name="gender" class="form-select" required>
                                    <option value="Male" <?php if($user['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                                    <option value="Female" <?php if($user['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                                    <option value="Other" <?php if($user['gender'] == 'Other') echo 'selected'; ?>>Other</option>
                                </select>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-update">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">NOVA CARE</h5>
                    <p class="small opacity-75">Providing world-class healthcare services with compassion and excellence since 2005.</p>
                    <div class="mt-3">
                        <a href="#" class="footer-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="footer-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="footer-icon"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">Quick Links</h5>
                    <ul class="list-unstyled small opacity-75">
                        <li><a href="index.php"><i class="fas fa-chevron-right me-2 small"></i>Home</a></li>
                        <li><a href="article_about.php"><i class="fas fa-chevron-right me-2 small"></i>About Us</a></li>
                        <li><a href="book.php"><i class="fas fa-chevron-right me-2 small"></i>Book Appointment</a></li>
                        <li><a href="contact.php"><i class="fas fa-chevron-right me-2 small"></i>Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="fw-bold mb-3">Contact Info</h5>
                    <p class="small opacity-75 mb-2"><i class="fas fa-map-marker-alt me-2"></i> 123 Blue Area, Islamabad</p>
                    <p class="small opacity-75 mb-2"><i class="fas fa-phone me-2"></i> +92 300 1234567</p>
                    <p class="small opacity-75"><i class="fas fa-envelope me-2"></i> info@novacare.com</p>
                </div>
            </div>
            <hr class="my-4" style="opacity: 0.2;">
            <p class="mb-0 small opacity-50 text-center">&copy; 2025 Nova Care Hospital. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>