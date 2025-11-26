<?php
require '../config/db.php';

// LOGIN CHECK
if (!isset($_SESSION['user_id'])) { 
    header("Location: ../login.php"); 
    exit(); 
}

$user_id = $_SESSION['user_id'];

// --- 1. ADD TO FAVORITES LOGIC (NEW) ---
if (isset($_GET['add_fav'])) {
    $fav_doc_id = $_GET['add_fav'];
    // Check if already favorite
    $check = $conn->query("SELECT id FROM favorites WHERE user_id='$user_id' AND doctor_id='$fav_doc_id'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO favorites (user_id, doctor_id) VALUES ('$user_id', '$fav_doc_id')");
    }
    // Redirect back to avoid re-submission
    header("Location: index.php");
    exit();
}

// --- 2. NOTIFICATIONS ---
$sql_notif = "SELECT appointments.*, doctors.name as doc_name 
              FROM appointments 
              JOIN doctors ON appointments.doctor_id = doctors.id
              WHERE user_id = '$user_id' AND (status = 'approved' OR status = 'cancelled') 
              ORDER BY id DESC LIMIT 5";
$res_notif = $conn->query($sql_notif);
$count_notif = ($res_notif) ? $res_notif->num_rows : 0;

// --- 3. SEARCH & DOCTORS LIST ---
$search_query = "";
$search_spec = "";
$is_searching = false;

$sql_docs = "SELECT * FROM doctors WHERE 1=1";

if (isset($_GET['btn_search'])) {
    $is_searching = true;
    if (!empty($_GET['search_name'])) {
        $name = $_GET['search_name'];
        $sql_docs .= " AND name LIKE '%$name%'";
        $search_query = $name;
    }
    if (!empty($_GET['search_spec'])) {
        $spec = $_GET['search_spec'];
        $sql_docs .= " AND specialization = '$spec'";
        $search_spec = $spec;
    }
}

$res_docs = $conn->query($sql_docs);
$doctors_list = [];
if ($res_docs->num_rows > 0) {
    while($d = $res_docs->fetch_assoc()) {
        $doctors_list[] = $d;
    }
}
$doctor_chunks = array_chunk($doctors_list, 4);

// FETCH FAVORITES LIST (To show red heart)
$fav_ids = [];
$fav_res = $conn->query("SELECT doctor_id FROM favorites WHERE user_id='$user_id'");
while($r = $fav_res->fetch_assoc()) { $fav_ids[] = $r['doctor_id']; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Home - Nova Care</title>
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

        /* HERO SECTION */
        .hero-section {
            background: linear-gradient(rgba(58, 123, 213, 0.8), rgba(58, 96, 115, 0.8)), url('https://images.unsplash.com/photo-1538108149393-fbbd81895907?ixlib=rb-4.0.3&auto=format&fit=crop&w=1500&q=80');
            background-size: cover; background-position: center; color: white;
            padding: 100px 0 120px 0; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .hero-title { font-size: 3rem; font-weight: 700; margin-bottom: 10px; text-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .hero-text { font-size: 1.1rem; opacity: 0.95; margin-bottom: 40px; max-width: 700px; margin-left: auto; margin-right: auto; }
        
        /* SEARCH BOX */
        .search-box {
            background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); padding: 20px;
            border-radius: 15px; border: 1px solid rgba(255,255,255,0.2); max-width: 800px; margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .search-input, .search-select { height: 55px; border-radius: 8px; border: none; font-size: 15px; }
        .btn-search { height: 55px; background: #3a6073; color: white; font-weight: 700; border-radius: 8px; width: 100%; transition: 0.3s; border: 2px solid white; }
        .btn-search:hover { background: white; color: #3a7bd5; }

        /* SLIDER & CARDS */
        .slider-container { background: #fff; padding: 30px 0; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; white-space: nowrap; border-bottom: 1px solid #eee; }
        .slider-track { display: inline-block; animation: scroll 20s linear infinite; }
        .specialty-badge { display: inline-block; padding: 12px 30px; margin: 0 15px; background: #e3f2fd; color: #3a7bd5; font-weight: 600; border-radius: 50px; font-size: 16px; border: 1px solid #3a7bd5; box-shadow: 0 3px 6px rgba(0,0,0,0.05); }
        @keyframes scroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }

        .section-title { font-weight: 700; color: #3a7bd5; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        
        .doc-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s; height: 100%; text-align: center; border-top: 4px solid #3a7bd5; position: relative; }
        .doc-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        
        /* HEART ICON STYLE (This was missing) */
        .fav-btn {
            position: absolute; top: 15px; right: 15px;
            width: 35px; height: 35px; border-radius: 50%;
            background: white; box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            display: flex; align-items: center; justify-content: center;
            color: #ccc; transition: 0.3s; text-decoration: none; font-size: 18px;
            z-index: 10;
        }
        .fav-btn:hover { transform: scale(1.1); color: #ff4757; }
        .fav-btn.active { color: #ff4757; }

        .doc-img { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; margin-top: 30px; border: 5px solid #e3f2fd; }
        .doc-body { padding: 20px; }
        
        .doctor-carousel-btn { width: 45px; height: 45px; background-color: #3a7bd5; border-radius: 50%; top: 50%; transform: translateY(-50%); opacity: 1; }
        .doctor-carousel-btn:hover { background-color: #3a6073; }

        /* OTHER SECTIONS */
        .article-img { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; height: 300px; object-fit: cover; }
        .stats-strip { background: #3a7bd5; color: white; padding: 60px 0; margin-bottom: 0; }
        .stat-number { font-size: 2.5rem; font-weight: 700; margin-bottom: 0; }
        
        .testimonial-card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); border-left: 5px solid #3a7bd5; margin: 20px; }
        .testimonial-img { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; border: 3px solid #e3f2fd; }

        /* FAQ & FORM */
        .accordion-button:not(.collapsed) { background-color: #e3f2fd; color: #3a7bd5; }
        .accordion-button:focus { box-shadow: none; border-color: rgba(0,0,0,.125); }
        .form-card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); border-top: 4px solid #3a7bd5; margin-top: 30px; }
        .form-card .form-control { background-color: #f8f9fa; border: 1px solid #eee; height: 50px; padding: 10px 15px; }
        .btn-send { background: #3a7bd5; color: white; border: none; padding: 12px 30px; border-radius: 50px; font-weight: 600; width: 100%; transition: 0.3s; }
        .btn-send:hover { background: #2c5aa0; box-shadow: 0 5px 15px rgba(58, 123, 213, 0.3); }

        footer { background: linear-gradient(to right, #3a7bd5, #3a6073); color: white; padding: 60px 0 20px 0; text-align: left; }
        footer a { color: rgba(255,255,255,0.8); text-decoration: none; transition: 0.3s; }
        footer a:hover { color: #fff; padding-left: 5px; }
        .footer-icon { width: 35px; height: 35px; background: rgba(255,255,255,0.2); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 10px; color: white; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-heartbeat me-2"></i>NOVA CARE</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#patientNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="patientNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    
                    <li class="nav-item"><a class="nav-link" href="patient/predict_disease.php" style="font-weight: 700;">PREDICT DISEASE</a></li>
                    
                    <li class="nav-item"><a class="nav-link" href="patient/article_about.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="patient/article_tech.php">Technology</a></li>
                    <li class="nav-item"><a class="nav-link" href="patient/article_care.php">Patient Care</a></li>
                    <li class="nav-item"><a class="nav-link" href="patient/contact.php">Contact</a></li>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    
                    <?php if($user_id) { ?>
                        <li class="nav-item dropdown me-3">
                            <a class="nav-link" href="#" data-bs-toggle="dropdown"><i class="fas fa-bell"></i><?php if($count_notif > 0) echo '<span class="badge bg-danger rounded-circle position-absolute top-0 start-100 translate-middle p-1 border border-light"></span>'; ?></a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><h6 class="dropdown-header">Updates</h6></li>
                                <?php if ($count_notif > 0) { while($row = $res_notif->fetch_assoc()) { echo "<li><a class='dropdown-item small' href='#'>Dr. ".$row['doc_name']." - ".ucfirst($row['status'])."</a></li>"; } } else { echo "<li><span class='dropdown-item text-muted small'>No new notifications</span></li>"; } ?>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle bg-white text-primary rounded-pill px-3 py-1" href="#" data-bs-toggle="dropdown" style="color: #3a7bd5 !important;">
                                <i class="fas fa-user-circle me-1"></i> <?php echo $_SESSION['name']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><a class="dropdown-item" href="patient/profile.php">My Profile</a></li>
                                <li><a class="dropdown-item" href="patient/my_appointments.php">My Appointments</a></li>
                                <li><a class="dropdown-item" href="patient/favorites.php"><i class="fas fa-heart me-2 text-danger"></i>Favorites</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php } else { ?>
                        <li class="nav-item"><a href="login.php" class="nav-link">Login</a></li>
                        <li class="nav-item ms-2"><a href="register.php" class="btn btn-light rounded-pill px-4 text-primary fw-bold">Register</a></li>
                    <?php } ?>

                </ul>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container">
            <h1 class="hero-title">Find Your Doctor</h1>
            <p class="hero-text">Search for the best specialists, view profiles, and book appointments instantly.</p>
            
            <div class="search-box">
                <form action="index.php" method="GET">
                    <div class="row g-2">
                        <div class="col-md-5"><input type="text" name="search_name" class="form-control search-input" placeholder="Doctor Name (e.g. Dr. Ali)" value="<?php echo htmlspecialchars($search_query); ?>"></div>
                        <div class="col-md-4">
                            <select name="search_spec" class="form-select search-select">
                                <option value="">Select Specialization</option>
                                <option value="Cardiologist" <?php if($search_spec == 'Cardiologist') echo 'selected'; ?>>Cardiologist</option>
                                <option value="Dermatologist" <?php if($search_spec == 'Dermatologist') echo 'selected'; ?>>Dermatologist</option>
                                <option value="Neurologist" <?php if($search_spec == 'Neurologist') echo 'selected'; ?>>Neurologist</option>
                                <option value="Orthopedic" <?php if($search_spec == 'Orthopedic') echo 'selected'; ?>>Orthopedic</option>
                                <option value="Dentist" <?php if($search_spec == 'Dentist') echo 'selected'; ?>>Dentist</option>
                                <option value="Pediatrician" <?php if($search_spec == 'Pediatrician') echo 'selected'; ?>>Pediatrician</option>
                            </select>
                        </div>
                        <div class="col-md-3"><button type="submit" class="btn btn-search"><i class="fas fa-search me-2"></i> Search</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="slider-container">
        <div class="slider-track">
            <span class="specialty-badge"><i class="fas fa-heartbeat me-2"></i>Cardiology</span>
            <span class="specialty-badge"><i class="fas fa-brain me-2"></i>Neurology</span>
            <span class="specialty-badge"><i class="fas fa-bone me-2"></i>Orthopedics</span>
            <span class="specialty-badge"><i class="fas fa-baby me-2"></i>Pediatrics</span>
            <span class="specialty-badge"><i class="fas fa-allergies me-2"></i>Dermatology</span>
            <span class="specialty-badge"><i class="fas fa-ribbon me-2"></i>Oncology</span>
            <span class="specialty-badge"><i class="fas fa-female me-2"></i>Gynecology</span>
            <span class="specialty-badge"><i class="fas fa-notes-medical me-2"></i>Urology</span>
            <span class="specialty-badge"><i class="fas fa-heartbeat me-2"></i>Cardiology</span>
            <span class="specialty-badge"><i class="fas fa-brain me-2"></i>Neurology</span>
        </div>
    </div>

    <div class="container py-5 my-5">
        <div class="text-center mb-5">
            <h5 class="section-title">Our Specialists</h5>
            <?php if($is_searching) { ?>
                <h2 class="fw-bold">Search Results</h2>
                <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill px-4 mt-2">Clear Search</a>
            <?php } else { ?>
                <h2 class="fw-bold">Meet Our Top Doctors</h2>
            <?php } ?>
        </div>
        
        <?php if($is_searching) { ?>
            <div class="row g-4">
                <?php 
                if (!empty($doctors_list)) {
                    foreach ($doctors_list as $doc) {
                        $img_src = "https://i.pravatar.cc/150?img=" . ($doc['id'] + 50);
                        if (!empty($doc['image']) && file_exists("patient/" . $doc['image'])) { $img_src = "patient/" . $doc['image']; } 
                        
                        // Favorite Logic
                        $is_fav = in_array($doc['id'], $fav_ids) ? 'active' : '';
                        $fav_link = ($user_id) ? "index.php?add_fav=".$doc['id'] : "login.php";
                ?>
                <div class="col-md-6 col-lg-3">
                    <div class="doc-card h-100">
                        <a href="<?php echo $fav_link; ?>" class="fav-btn <?php echo $is_fav; ?>" title="Add to Favorites"><i class="fas fa-heart"></i></a>
                        
                        <img src="<?php echo $img_src; ?>" class="doc-img" alt="Doctor">
                        <div class="doc-body">
                            <h5 class="fw-bold mb-1"><?php echo $doc['name']; ?></h5>
                            <p class="text-primary fw-bold small mb-1"><?php echo $doc['specialization']; ?></p>
                            <p class="text-muted small"><i class="far fa-clock me-1"></i> <?php echo $doc['availability']; ?></p>
                            <a href="patient/doctor_profile.php?id=<?php echo $doc['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-4 mt-2">View Profile</a>
                        </div>
                    </div>
                </div>
                <?php } 
                } else { echo "<div class='col-12 text-center text-muted py-5'><h4>No doctors found.</h4></div>"; }
                ?>
            </div>

        <?php } else { ?>
            <div id="doctorCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php 
                    if (!empty($doctor_chunks)) {
                        foreach ($doctor_chunks as $index => $chunk) {
                            $active = ($index == 0) ? 'active' : '';
                    ?>
                    <div class="carousel-item <?php echo $active; ?>">
                        <div class="row g-4">
                            <?php foreach ($chunk as $doc) { 
                                $img_src = "https://i.pravatar.cc/150?img=" . ($doc['id'] + 50);
                                if (!empty($doc['image']) && file_exists("patient/" . $doc['image'])) { $img_src = "patient/" . $doc['image']; } 
                                
                                // Favorite Logic
                                $is_fav = in_array($doc['id'], $fav_ids) ? 'active' : '';
                                $fav_link = ($user_id) ? "index.php?add_fav=".$doc['id'] : "login.php";
                            ?>
                            <div class="col-md-6 col-lg-3">
                                <div class="doc-card h-100">
                                    <a href="<?php echo $fav_link; ?>" class="fav-btn <?php echo $is_fav; ?>" title="Add to Favorites"><i class="fas fa-heart"></i></a>
                                    
                                    <img src="<?php echo $img_src; ?>" class="doc-img" alt="Doctor">
                                    <div class="doc-body">
                                        <h5 class="fw-bold mb-1"><?php echo $doc['name']; ?></h5>
                                        <p class="text-primary fw-bold small mb-1"><?php echo $doc['specialization']; ?></p>
                                        <p class="text-muted small"><i class="far fa-clock me-1"></i> <?php echo $doc['availability']; ?></p>
                                        <a href="patient/doctor_profile.php?id=<?php echo $doc['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-4 mt-2">View Profile</a>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } } else { echo "<div class='text-center text-muted'>No doctors available.</div>"; } ?>
                </div>
                
                <?php if (count($doctor_chunks) > 1) { ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#doctorCarousel" data-bs-slide="prev" style="width: 5%;"><span class="carousel-control-prev-icon doctor-carousel-btn" aria-hidden="true"></span></button>
                <button class="carousel-control-next" type="button" data-bs-target="#doctorCarousel" data-bs-slide="next" style="width: 5%;"><span class="carousel-control-next-icon doctor-carousel-btn" aria-hidden="true"></span></button>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <div class="container py-5 my-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="https://images.unsplash.com/photo-1551076805-e1869033e561?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" class="article-img" alt="Doctors Team">
            </div>
            <div class="col-lg-6 ps-lg-5">
                <h5 class="section-title">About Us</h5>
                <h2 class="fw-bold mb-3">Leading Medical Excellence</h2>
                <p class="text-muted">Nova Care Hospital has been a trusted name in healthcare for over 20 years.</p>
                <a href="patient/article_about.php" class="btn btn-outline-primary rounded-pill px-4 mt-3">Read More</a>
            </div>
        </div>
    </div>

    <div class="py-5" style="background-color: #e9ecef;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 pe-lg-5 order-2 order-lg-1">
                    <h5 class="section-title" style="color: #11998e;">Patient Care</h5>
                    <h2 class="fw-bold mb-3">Your Comfort is Our Priority</h2>
                    <p class="text-muted">We believe in treating patients with compassion and dignity.</p>
                    <a href="patient/article_care.php" class="btn btn-outline-success rounded-pill px-4 mt-3" style="border-color: #11998e; color: #11998e;">Read More</a>
                </div>
                <div class="col-lg-6 order-1 order-lg-2 mb-4 mb-lg-0">
                    <img src="https://images.unsplash.com/photo-1538108149393-fbbd81895907?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" class="article-img" alt="Patient Care">
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5 my-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" class="article-img" alt="Technology">
            </div>
            <div class="col-lg-6 ps-lg-5">
                <h5 class="section-title" style="color: #6f42c1;">Technology</h5>
                <h2 class="fw-bold mb-3">Advanced Medical Technology</h2>
                <p class="text-muted">We utilize the latest medical equipment to ensure accurate diagnosis.</p>
                <a href="patient/article_tech.php" class="btn btn-outline-primary rounded-pill px-4 mt-3" style="border-color: #6f42c1; color: #6f42c1;">Read More</a>
            </div>
        </div>
    </div>

    <div class="stats-strip">
        <div class="container text-center">
            <div class="row">
                <div class="col-md-3 mb-4 mb-md-0"><h2 class="stat-number">20+</h2><p class="mb-0 opacity-75">Years Experience</p></div>
                <div class="col-md-3 mb-4 mb-md-0"><h2 class="stat-number">500+</h2><p class="mb-0 opacity-75">Doctors & Staff</p></div>
                <div class="col-md-3 mb-4 mb-md-0"><h2 class="stat-number">10k+</h2><p class="mb-0 opacity-75">Happy Patients</p></div>
                <div class="col-md-3"><h2 class="stat-number">24/7</h2><p class="mb-0 opacity-75">Emergency Service</p></div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5"><h5 class="section-title">FAQ</h5><h2 class="fw-bold">Frequently Asked Questions</h2></div>
                <div class="accordion mb-5" id="faqAccordion">
                    <div class="accordion-item border-0 mb-3 shadow-sm"><h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">How can I book an appointment?</button></h2><div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted">Click "Book Appointment" button.</div></div></div>
                    <div class="accordion-item border-0 mb-3 shadow-sm"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">Do you accept insurance?</button></h2><div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted">Yes, major plans accepted.</div></div></div>
                    <div class="accordion-item border-0 mb-3 shadow-sm"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">Emergency service?</button></h2><div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted">24/7 Available.</div></div></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="py-5" style="background-color: #e9ecef;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="form-card">
                        <div class="text-center mb-4"><h2 class="fw-bold mb-0">Send Us a Message</h2><p class="text-muted small">We're here to help you 24/7.</p></div>
                        <form>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label small fw-bold text-muted">Your Name</label><input type="text" class="form-control" placeholder="John Doe"></div>
                                <div class="col-md-6 mb-3"><label class="form-label small fw-bold text-muted">Email Address</label><input type="email" class="form-control" placeholder="name@example.com"></div>
                            </div>
                            <div class="mb-3"><label class="form-label small fw-bold text-muted">Subject</label><input type="text" class="form-control" placeholder="Inquiry Subject"></div>
                            <div class="mb-3"><label class="form-label small fw-bold text-muted">Message</label><textarea class="form-control" rows="4" placeholder="How can we help you?"></textarea></div>
                            <button class="btn btn-send w-100">Send Message <i class="fas fa-paper-plane ms-2"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">NOVA CARE</h5>
                    <p class="small opacity-75">Providing world-class healthcare services since 2005.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">Quick Links</h5>
                    <ul class="list-unstyled small opacity-75">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="patient/predict_disease.php">Predict Disease</a></li> <li><a href="patient/article_about.php">About Us</a></li>
                        <li><a href="patient/book.php">Book Appointment</a></li>
                        <li><a href="patient/contact.php">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="fw-bold mb-3">Contact Info</h5>
                    <p class="small opacity-75 mb-2">123 Blue Area, Islamabad</p>
                    <p class="small opacity-75">+92 300 1234567</p>
                    <p class="small opacity-75">info@novacare.com</p>
                </div>
            </div>
            <hr class="my-4" style="opacity: 0.2;">
            <p class="mb-0 small opacity-50 text-center">&copy; 2025 Nova Care Hospital.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>