<?php
require '../config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Treatments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .custom-navbar { background-color: #104e8b; padding: 0; border-bottom: 4px solid #3097d1; }
        .navbar-nav .nav-link { color: #ffffff !important; font-weight: 500; text-transform: uppercase; padding: 20px 15px !important; font-size: 14px; letter-spacing: 0.5px; }
        .navbar-nav .nav-link:hover { background-color: #0d4073; color: #4db8ff !important; }
        .navbar-nav .nav-link.active { background-color: #0d4073; color: #4db8ff !important; border-bottom: 3px solid #4db8ff; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg custom-navbar sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-white text-uppercase me-4" href="index.php"><i class="fas fa-heartbeat me-2"></i>Medical Center</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"><i class="fas fa-bars text-white"></i></button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">HOME</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">ABOUT US</a></li>
                    <li class="nav-item"><a class="nav-link active" href="treatments.php">TREATMENTS</a></li>
                    <li class="nav-item"><a class="nav-link" href="book.php">BOOK APPOINTMENT</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">CONTACT US</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center mb-5 text-primary text-uppercase fw-bold">Our Treatments</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow border-0 h-100">
                    <img src="https://images.unsplash.com/photo-1629909613654-28e377c37b09" class="card-img-top" height="200" style="object-fit: cover;">
                    <div class="card-body text-center">
                        <h4 class="fw-bold">Cardiology</h4>
                        <p class="text-muted small">Heart surgery, ECG, and consultation.</p>
                        <a href="book.php" class="btn btn-outline-primary btn-sm rounded-pill px-4">Book Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card shadow border-0 h-100">
                    <img src="https://images.unsplash.com/photo-1606811841689-23dfddce3e95" class="card-img-top" height="200" style="object-fit: cover;">
                    <div class="card-body text-center">
                        <h4 class="fw-bold">Dental Care</h4>
                        <p class="text-muted small">Teeth cleaning, whitening, and implants.</p>
                        <a href="book.php" class="btn btn-outline-primary btn-sm rounded-pill px-4">Book Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card shadow border-0 h-100">
                    <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118" class="card-img-top" height="200" style="object-fit: cover;">
                    <div class="card-body text-center">
                        <h4 class="fw-bold">Neurology</h4>
                        <p class="text-muted small">Brain, spine, and nerve treatments.</p>
                        <a href="book.php" class="btn btn-outline-primary btn-sm rounded-pill px-4">Book Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>