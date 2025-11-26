<?php
require '../config/db.php';

// FIX: Changed redirection to login.php
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }

$user_id = $_SESSION['user_id'];

// --- ADDED NOTIFICATION LOGIC ---
$sql_notif = "SELECT appointments.*, doctors.name as doc_name 
              FROM appointments 
              JOIN doctors ON appointments.doctor_id = doctors.id
              WHERE user_id = '$user_id' AND (status = 'approved' OR status = 'cancelled') 
              ORDER BY id DESC LIMIT 5";
$res_notif = $conn->query($sql_notif);
$count_notif = ($res_notif) ? $res_notif->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>About Nova Care - Full Article</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; color: #444; }
        
        /* NAVBAR (MATCHING HOME PAGE) */
        .navbar { background: linear-gradient(to right, #3a7bd5, #3a6073); box-shadow: 0 4px 12px rgba(58, 123, 213, 0.2); }
        .navbar-brand { font-weight: 700; color: #fff !important; letter-spacing: 1px; }
        .nav-link { color: rgba(255,255,255,0.9) !important; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-right: 10px; }
        .nav-link:hover { color: #fff !important; transform: translateY(-1px); }
        /* Active Link Styling */
        .nav-link.active { color: #fff !important; border-bottom: 2px solid rgba(255,255,255,0.5); }
        
        /* FIX: Dropdown Toggle Text Color */
        .navbar .dropdown-toggle { color: #3a7bd5 !important; }
        
        /* ARTICLE CONTAINER */
        .article-container {
            background: #fff;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
            margin-top: -50px; /* Overlap effect */
            position: relative;
            border-top: 5px solid #3a7bd5;
        }
        
        /* HEADER IMAGE */
        .article-header {
            height: 300px;
            background: url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1500&q=80');
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .overlay { background: rgba(0,0,0,0.5); height: 100%; width: 100%; }
        
        h1 { font-weight: 700; color: #3a7bd5; }
        p { line-height: 1.8; margin-bottom: 20px; text-align: justify; }
        .highlight-box { background: #e3f2fd; padding: 20px; border-left: 5px solid #3a7bd5; margin: 30px 0; border-radius: 5px; }

        /* FOOTER STYLING */
        footer { background: linear-gradient(to right, #3a7bd5, #3a6073); color: white; padding: 60px 0 20px 0; text-align: left; margin-top: 50px; }
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
            <a class="navbar-brand" href="../index.php"><i class="fas fa-heartbeat me-2"></i>NOVA CARE</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#patientNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="patientNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="predict_disease.php" style="font-weight: 700;">PREDICT DISEASE</a></li> <li class="nav-item"><a class="nav-link active" href="article_about.php">About Us</a></li> 
                    <li class="nav-item"><a class="nav-link" href="article_tech.php">Technology</a></li>
                    <li class="nav-item"><a class="nav-link" href="article_care.php">Patient Care</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
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
                        <a class="nav-link dropdown-toggle bg-white text-primary rounded-pill px-3 py-1" href="#" data-bs-toggle="dropdown"><i class="fas fa-user-circle me-1"></i> <?php echo $_SESSION['name']; ?></a>
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

    <div class="article-header">
        <div class="overlay"></div>
    </div>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="article-container">
                    <span class="badge bg-primary mb-2">About Us</span>
                    <h1 class="mb-4">Leading the Way in Medical Excellence</h1>
                    <p class="text-muted small mb-4"><i class="far fa-calendar me-2"></i> Published: Nov 2025 &nbsp; | &nbsp; <i class="far fa-user me-2"></i> By Dr. Sarah Khan</p>
                    
                    <hr>

                    <p>Nova Care Hospital has been a trusted name in healthcare for over 20 years. Founded with the mission to provide world-class medical services to the community, we have grown from a small clinic to a multi-specialty hospital equipped with state-of-the-art technology.</p>

                    <p>Our journey began in 2005 when a group of dedicated doctors decided to create a medical facility that prioritizes patient care above all else. Today, Nova Care stands as a beacon of hope and healing, offering comprehensive healthcare solutions under one roof.</p>

                    <div class="highlight-box">
                        <h5 class="fw-bold text-primary">Our Mission</h5>
                        <p class="mb-0">To improve the health and well-being of the communities we serve by providing compassionate, high-quality, and affordable healthcare services.</p>
                    </div>

                    <h4>Why Choose Nova Care?</h4>
                    <p>We believe that healthcare is not just about treating diseases but about caring for people. Our team of 50+ specialized doctors works round the clock to ensure that every patient receives personalized attention. From routine check-ups to complex surgeries, we are equipped to handle it all.</p>

                    <ul class="list-unstyled my-4">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> <strong>Expert Team:</strong> Highly qualified specialists from around the globe.</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> <strong>Advanced Technology:</strong> Latest MRI, CT Scan, and Robotic Surgery equipment.</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> <strong>Patient Centric:</strong> We treat patients like family, ensuring comfort and dignity.</li>
                    </ul>

                    <p>At Nova Care, we are constantly evolving. We regularly update our facilities and train our staff to keep up with the latest advancements in medical science. Your trust is our biggest asset, and we strive every day to uphold it.</p>

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
                        <li><a href="predict_disease.php"><i class="fas fa-chevron-right me-2 small"></i>Predict Disease</a></li> <li><a href="article_about.php"><i class="fas fa-chevron-right me-2 small"></i>About Us</a></li>
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