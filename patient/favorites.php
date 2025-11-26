<?php
require '../config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }

$user_id = $_SESSION['user_id'];

// --- NOTIFICATIONS ---
$sql_notif = "SELECT appointments.*, doctors.name as doc_name 
              FROM appointments 
              JOIN doctors ON appointments.doctor_id = doctors.id
              WHERE user_id = '$user_id' AND (status = 'approved' OR status = 'cancelled') 
              ORDER BY id DESC LIMIT 5";
$res_notif = $conn->query($sql_notif);
$count_notif = ($res_notif) ? $res_notif->num_rows : 0;

// --- REMOVE FAVORITE LOGIC ---
if (isset($_GET['remove'])) {
    $doc_id = $_GET['remove'];
    $conn->query("DELETE FROM favorites WHERE user_id='$user_id' AND doctor_id='$doc_id'");
    header("Location: favorites.php");
}

// --- FETCH FAVORITES ---
$sql = "SELECT doctors.* FROM favorites 
        JOIN doctors ON favorites.doctor_id = doctors.id 
        WHERE favorites.user_id = '$user_id'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Favorites - Nova Care</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; color: #444; }
        .navbar { background: linear-gradient(to right, #3a7bd5, #3a6073); box-shadow: 0 4px 12px rgba(58, 123, 213, 0.2); }
        .navbar-brand { font-weight: 700; letter-spacing: 1px; color: #fff !important; }
        .nav-link { color: rgba(255,255,255,0.9) !important; font-size: 13px; text-transform: uppercase; margin-right: 10px; }
        .nav-link:hover { color: #fff !important; }
        .navbar .dropdown-toggle { color: #3a7bd5 !important; }
        .page-header { background: linear-gradient(rgba(58, 123, 213, 0.8), rgba(58, 96, 115, 0.8)), url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1500&q=80'); background-size: cover; color: white; padding: 60px 0; text-align: center; margin-bottom: 40px; }
        
        .doc-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s; height: 100%; text-align: center; border-top: 4px solid #3a7bd5; position: relative; }
        .doc-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .doc-img { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; margin-top: 30px; border: 5px solid #e3f2fd; }
        .doc-body { padding: 20px; }
        
        .remove-btn { position: absolute; top: 15px; right: 15px; width: 35px; height: 35px; border-radius: 50%; background: #fff0f0; color: #dc3545; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.3s; }
        .remove-btn:hover { background: #dc3545; color: white; }
        
        footer { background: linear-gradient(to right, #3a7bd5, #3a6073); color: white; padding: 60px 0 20px 0; text-align: left; margin-top: 80px; }
        footer a { color: rgba(255,255,255,0.8); text-decoration: none; }
        .footer-icon { width: 35px; height: 35px; background: rgba(255,255,255,0.2); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 10px; color: white; }
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
                    <li class="nav-item"><a class="nav-link" href="article_about.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="article_tech.php">Technology</a></li>
                    <li class="nav-item"><a class="nav-link" href="article_care.php">Patient Care</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
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
                            <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                            <li><a class="dropdown-item" href="my_appointments.php">My Appointments</a></li>
                            <li><a class="dropdown-item" href="favorites.php"><i class="fas fa-heart me-2 text-danger"></i>Favorites</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <h1 class="fw-bold">My Favorites</h1>
        <p class="lead opacity-75">Your saved doctors list</p>
    </div>

    <div class="container mb-5">
        <div class="row g-4 justify-content-center">
            <?php 
            if ($result->num_rows > 0) {
                while($doc = $result->fetch_assoc()) {
                    $img_src = "https://i.pravatar.cc/150?img=" . ($doc['id'] + 50);
                    if (!empty($doc['image']) && file_exists("../" . $doc['image'])) { $img_src = "../" . $doc['image']; }
            ?>
            <div class="col-md-6 col-lg-3">
                <div class="doc-card h-100">
                    <a href="favorites.php?remove=<?php echo $doc['id']; ?>" class="remove-btn" title="Remove from Favorites"><i class="fas fa-times"></i></a>
                    <img src="<?php echo $img_src; ?>" class="doc-img" alt="Doctor">
                    <div class="doc-body">
                        <h5 class="fw-bold mb-1"><?php echo $doc['name']; ?></h5>
                        <p class="text-primary fw-bold small mb-1"><?php echo $doc['specialization']; ?></p>
                        <p class="text-muted small"><i class="far fa-clock me-1"></i> <?php echo $doc['availability']; ?></p>
                        <a href="doctor_profile.php?id=<?php echo $doc['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-4 mt-2">View Profile</a>
                    </div>
                </div>
            </div>
            <?php 
                }
            } else { echo "<div class='col-12 text-center text-muted py-5'><h4>No favorite doctors yet.</h4><a href='../index.php' class='btn btn-outline-primary rounded-pill mt-3'>Browse Doctors</a></div>"; }
            ?>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4"><h5 class="fw-bold mb-3">NOVA CARE</h5><p class="small opacity-75">Providing world-class healthcare services.</p></div>
                <div class="col-md-4 mb-4"><h5 class="fw-bold mb-3">Quick Links</h5><ul class="list-unstyled small opacity-75"><li><a href="../index.php">Home</a></li><li><a href="article_about.php">About Us</a></li><li><a href="book.php">Book Appointment</a></li><li><a href="contact.php">Contact Us</a></li></ul></div>
                <div class="col-md-4"><h5 class="fw-bold mb-3">Contact Info</h5><p class="small opacity-75 mb-2">123 Blue Area, Islamabad</p><p class="small opacity-75">+92 300 1234567</p><p class="small opacity-75">info@novacare.com</p></div>
            </div>
            <hr class="my-4" style="opacity: 0.2;"><p class="mb-0 small opacity-50 text-center">&copy; 2025 Nova Care Hospital.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>