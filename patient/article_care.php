<?php
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- NOTIFICATIONS LOGIC (ADDED) ---
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
    <title>Patient Care - Nova Care</title>
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
        
        /* DROPDOWN FIX */
        .navbar .dropdown-toggle { color: #fff !important; } 
        .dropdown-menu { border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }

        /* ARTICLE CONTENT */
        .article-container {
            background: #fff;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
            margin-top: -50px;
            position: relative;
            border-top: 5px solid #3a7bd5; 
        }
        .article-header {
            height: 300px;
            background: url('https://images.unsplash.com/photo-1538108149393-fbbd81895907?ixlib=rb-4.0.3&auto=format&fit=crop&w=1500&q=80');
            background-size: cover;
            background-position: center;
        }
        h1 { font-weight: 700; color: #3a7bd5; }
        p { line-height: 1.8; text-align: justify; }
        
        .badge-primary-custom { background-color: #3a7bd5; }

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
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="predict_disease.php" style="font-weight: 700;">PREDICT DISEASE</a></li> <li class="nav-item"><a class="nav-link" href="article_about.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="article_tech.php">Technology</a></li>
                    <li class="nav-item"><a class="nav-link active" href="article_care.php">Patient Care</a></li> <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
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

    <div class="article-header"></div>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="article-container">
                    <span class="badge badge-primary-custom mb-2">Patient Care</span>
                    <h1 class="mb-4">Your Comfort is Our Priority</h1>
                    <p class="text-muted small mb-4">Published: Nov 2025 | Nova Care Team</p>
                    <hr>

                    <p>At Nova Care, we understand that visiting a hospital can be stressful. That is why we have designed our patient care protocols to ensure maximum comfort, safety, and peace of mind for you and your loved ones.</p>

                    <h4>Compassionate Nursing</h4>
                    <p>Our nursing staff is the backbone of our hospital. Trained in both medical care and emotional support, they are available 24/7 to attend to your needs. Whether it is post-operative care or routine monitoring, our nurses treat every patient with kindness and respect.</p>

                    <h4>Private & Comfortable Rooms</h4>
                    <p>We offer a range of accommodation options, from general wards to luxury private suites. All our rooms are equipped with:</p>
                    <ul>
                        <li>Adjustable electric beds for maximum comfort.</li>
                        <li>Private bathrooms with hygiene kits.</li>
                        <li>Free Wi-Fi and Television for entertainment.</li>
                        <li>Sofa beds for attendants.</li>
                    </ul>

                    <h4>Hygienic & Healthy Meals</h4>
                    <p>Nutrition plays a vital role in recovery. Our in-house nutritionists design personalized meal plans for every patient based on their medical condition. Our kitchen adheres to strict hygiene standards to ensure safe and healthy food delivery.</p>

                    <p>We are committed to making your stay as comfortable as possible. If you have any special requirements, our Patient Relationship Officers are always available to assist you.</p>
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
                        <li><a href="index.php"><i class="fas fa-chevron-right me-2 small"></i>Home</a></li>
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