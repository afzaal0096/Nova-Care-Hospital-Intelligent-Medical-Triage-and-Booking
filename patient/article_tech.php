<?php
require '../config/db.php';

// FIX: Changed redirection from index.php to login.php
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
        .nav-link.active { color: #fff !important; border-bottom: 2px solid rgba(255,255,255,0.5); }
        
        /* FIX: Dropdown Toggle Text Color (Ensure Blue on White button) */
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
                    <li class="nav-item"><a class="nav-link" href="predict_disease.php" style="font-weight: 700;">PREDICT DISEASE</a></li> <li class="nav-item"><a class="nav-link" href="article_about.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link active" href="article_tech.php">Technology</a></li> 
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
                    <span class="badge bg-primary mb-2">Technology</span>
                    <h1 class="mb-4">Advanced Medical Technology</h1>
                    <p class="text-muted small mb-4"><i class="far fa-calendar me-2"></i> Published: Nov 2025 &nbsp; | &nbsp; <i class="far fa-user me-2"></i> By Nova Care Team</p>
                    
                    <hr>

                    <p>At Nova Care, innovation is at the heart of our operations. We invest heavily in the latest medical technology to ensure our patients receive the most accurate diagnostics and effective, least-invasive treatments possible.</p>

                    <h4>Robotic Surgery Unit</h4>
                    <p>Our state-of-the-art Robotic Surgery unit allows our surgeons to perform complex procedures with unprecedented precision, leading to smaller incisions, reduced blood loss, faster recovery times, and less pain for the patient.</p>

                    <div class="highlight-box">
                        <h5 class="fw-bold text-primary">AI-Assisted Diagnostics</h5>
                        <p class="mb-0">We use Artificial Intelligence in our radiology and pathology departments to scan images and lab results, helping our doctors catch critical signs and diseases at the earliest stages.</p>
                    </div>

                    <h4>Modern Imaging Technology</h4>
                    <p>We boast a fully equipped imaging department including 3-Tesla MRI scanners, low-dose CT Scanners, and 4D Ultrasound machines. These tools provide crystal-clear images, which are vital for pinpointing health issues accurately.</p>

                    <ul class="list-unstyled my-4">
                        <li class="mb-2"><i class="fas fa-microscope text-success me-2"></i> **Molecular Labs:** Advanced genetic and pathological testing.</li>
                        <li class="mb-2"><i class="fas fa-robot text-success me-2"></i> **Minimally Invasive:** Focus on fast recovery and minimal scarring.</li>
                        <li class="mb-2"><i class="fas fa-wifi text-success me-2"></i> **Telemedicine Ready:** Connect with specialists globally for second opinions.</li>
                    </ul>

                    <p>Our commitment to technology ensures that you are receiving care that meets international standards, right here at Nova Care.</p>

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