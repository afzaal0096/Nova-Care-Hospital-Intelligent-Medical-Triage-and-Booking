<?php
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); // Updated link (as discussed previously)
    exit();
}

$user_id = $_SESSION['user_id'];

// --- NOTIFICATION LOGIC (Previously Added) ---
$sql_notif = "SELECT appointments.*, doctors.name as doc_name 
              FROM appointments 
              JOIN doctors ON appointments.doctor_id = doctors.id
              WHERE user_id = '$user_id' AND (status = 'approved' OR status = 'cancelled') 
              ORDER BY id DESC LIMIT 5";
$res_notif = $conn->query($sql_notif);
$count_notif = ($res_notif) ? $res_notif->num_rows : 0;
// ----------------------------------------

// Location details
$address = "Plot No. 11, 12, Central Commercial - C DHA Phase 5, Islamabad";

// Google Map Embed URL
$map_embed_url = "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3323.003920953685!2d73.1601007152063!3d33.60677518073576!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x38dfea5538e155e9%3A0xb35a0f12c222629b!2sDHA%20Phase%205%20Islamabad!5e0!3m2!1sen!2s!4v1620800000000!5m2!1sen!2s";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Contact Us - Nova Care</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; color: #444; overflow-x: hidden; }
        
        /* NAVBAR */
        .navbar { background: linear-gradient(to right, #3a7bd5, #3a6073); box-shadow: 0 4px 12px rgba(58, 123, 213, 0.2); }
        .navbar-brand { font-weight: 700; letter-spacing: 1px; color: #fff !important; }
        .nav-link { color: rgba(255,255,255,0.9) !important; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-right: 10px; }
        .nav-link:hover { color: #fff !important; transform: translateY(-1px); }
        .nav-link.active { color: #fff !important; border-bottom: 2px solid rgba(255,255,255,0.5); }
        
        /* FIX: Username Text Color (Set to Blue) */
        .navbar .dropdown-toggle { color: #3a7bd5 !important; } 

        /* HEADER - BLUE BANNER */
        .contact-header {
            /* --- IMAGE FIX: Added Image and Overlay (Matching other patient pages) --- */
            background: 
                linear-gradient(rgba(58, 123, 213, 0.8), rgba(58, 96, 115, 0.8)), 
                url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1500&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            text-align: center;
            margin-top: -1px; /* Remove any gap */
        }
        .contact-header h1 { font-size: 3rem; letter-spacing: 1px; }
        .contact-header p { font-size: 1.2rem; opacity: 0.9; font-weight: 300; }
        
        /* MAIN CONTENT CONTAINER */
        .main-container {
            background-color: #fff; /* White background for content area */
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.05);
            padding: 40px;
            margin-top: -50px; /* Overlap with header */
            position: relative;
            z-index: 10;
        }

        /* INFO CARDS */
        .info-card { background: white; padding: 30px; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.05); height: 100%; transition: 0.3s; border-top: 4px solid #3a7bd5; }
        .info-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .icon-circle { width: 60px; height: 60px; background: #e3f2fd; color: #3a7bd5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; font-size: 24px; }
        
        /* FORM & MAP CARDS */
        .content-card { background: white; padding: 30px; border-radius: 15px; height: 100%; }
        .content-card-title { font-weight: 700; color: #3a7bd5; margin-bottom: 25px; }
        
        .map-container { border-radius: 15px; overflow: hidden; height: 350px; border: 1px solid #eee; margin-top: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }

        /* FORM INPUTS */
        .form-control {
            height: 50px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            background-color: #f9fbfc;
            font-size: 14px;
            padding-left: 15px;
        }
        .form-control:focus { border-color: #3a7bd5; background-color: #fff; box-shadow: 0 0 0 4px rgba(58, 123, 213, 0.1); }
        textarea.form-control { height: auto; padding-top: 15px; }

        .btn-send {
            background: linear-gradient(to right, #3a7bd5, #3a6073);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 14px;
            width: 100%;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(58, 123, 213, 0.3);
        }
        .btn-send:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(58, 123, 213, 0.4); color: white; }

        /* LIST GROUP FOR HOURS */
        .list-group-item { border: none; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
        .list-group-item:last-child { border-bottom: none; }

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

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-heartbeat me-2"></i>NOVA CARE</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#patientNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="patientNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="article_about.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="article_tech.php">Technology</a></li>
                    <li class="nav-item"><a class="nav-link" href="article_care.php">Patient Care</a></li>
                    <li class="nav-item"><a class="nav-link active" href="contact.php">Contact</a></li>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    
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

    <div class="contact-header">
        <div class="container">
            <h1 class="fw-bold mb-2">Get In Touch</h1>
            <p class="lead">We are here to help you 24/7. Feel free to contact us!</p>
        </div>
    </div>

    <div class="container main-container mb-5">
        
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="info-card">
                    <div class="icon-circle"><i class="fas fa-map-marker-alt"></i></div>
                    <h5 class="mt-3 fw-bold">Our Location</h5>
                    <p class="text-muted small mb-0"><?php echo $address; ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <div class="icon-circle"><i class="fas fa-phone"></i></div>
                    <h5 class="mt-3 fw-bold">Emergency Call</h5>
                    <h4 class="text-primary fw-bold mb-1">+92 300 1234567</h4>
                    <p class="text-muted small mb-0">Available 24 Hours a Day</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <div class="icon-circle"><i class="fas fa-envelope"></i></div>
                    <h5 class="mt-3 fw-bold">Email Us</h5>
                    <p class="text-muted small mb-0">info@novacare.com<br>support@novacare.com</p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <div class="col-lg-5">
                <div class="content-card">
                    <h4 class="content-card-title"><i class="far fa-clock me-2"></i>Opening Hours</h4>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Monday - Saturday</span>
                            <span class="fw-bold text-dark">9:00 AM - 9:00 PM</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Sunday</span>
                            <span class="fw-bold text-danger">Emergency Only</span>
                        </li>
                    </ul>

                    <h4 class="content-card-title mt-5"><i class="fas fa-map-marked-alt me-2"></i>Find Us on Map</h4>
                    <div class="map-container">
                        <iframe src="<?php echo $map_embed_url; ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="content-card">
                    <div class="d-flex align-items-center mb-4">
                        <h3 class="fw-bold mb-0 text-dark"><i class="far fa-paper-plane me-2 text-primary"></i>Send Message</h3>
                    </div>
                    <form>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small fw-bold">Your Name</label>
                                <input type="text" class="form-control" placeholder="John Doe">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small fw-bold">Email Address</label>
                                <input type="email" class="form-control" placeholder="name@example.com">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Subject</label>
                            <input type="text" class="form-control" placeholder="Inquiry Subject">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Message</label>
                            <textarea class="form-control" rows="6" placeholder="How can we help you?"></textarea>
                        </div>
                        <button class="btn btn-send">Send Message <i class="fas fa-arrow-right ms-2"></i></button>
                    </form>
                </div>
            </div>

        </div>
    </div>

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
                        <li><a href="../index.php"><i class="fas fa-chevron-right me-2 small"></i>Home</a></li>
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
            <p class="mb-0 small opacity-50 text-center">© 2025 Nova Care Hospital. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>