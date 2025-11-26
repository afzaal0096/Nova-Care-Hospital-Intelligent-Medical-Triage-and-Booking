<?php
require '../config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }

if (!isset($_GET['id'])) { die("Invalid Request"); }
$doc_id = $_GET['id'];

// Fetch Doctor
$sql = "SELECT * FROM doctors WHERE id = '$doc_id'";
$result = $conn->query($sql);
if($result->num_rows == 0) { die("Doctor not found"); }
$doc = $result->fetch_assoc();

// Image Logic
$img_src = "https://i.pravatar.cc/300?img=" . ($doc_id + 10);
if (!empty($doc['image']) && file_exists("../" . $doc['image'])) {
    $img_src = "../" . $doc['image'];
}

// --- FETCH REVIEWS & RATINGS ---
// 1. Average Rating
$sql_avg = "SELECT AVG(rating) as avg_rat, COUNT(*) as total_rev FROM reviews WHERE doctor_id = '$doc_id'";
$res_avg = $conn->query($sql_avg)->fetch_assoc();
$avg_rating = round($res_avg['avg_rat'], 1);
$total_reviews = $res_avg['total_rev'];

// 2. Fetch All Reviews
$sql_reviews = "SELECT reviews.*, users.name as patient_name 
                FROM reviews 
                JOIN users ON reviews.user_id = users.id 
                WHERE doctor_id = '$doc_id' 
                ORDER BY created_at DESC";
$res_reviews = $conn->query($sql_reviews);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $doc['name']; ?> - Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; color: #444; }
        .navbar { background: linear-gradient(to right, #3a7bd5, #3a6073); box-shadow: 0 4px 12px rgba(58, 123, 213, 0.2); }
        .navbar-brand { font-weight: 700; color: #fff !important; letter-spacing: 1px; }
        
        .profile-header {
            background: linear-gradient(rgba(58, 123, 213, 0.8), rgba(58, 96, 115, 0.8)), url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1500&q=80');
            background-size: cover; height: 300px; position: relative; margin-bottom: 80px;
        }
        .profile-container { margin-top: -140px; position: relative; z-index: 2; }
        .profile-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-top: 5px solid #3a7bd5; padding: 40px; }
        .profile-img { width: 160px; height: 160px; border-radius: 50%; border: 6px solid white; object-fit: cover; box-shadow: 0 5px 15px rgba(0,0,0,0.15); position: absolute; top: -80px; left: 50%; transform: translateX(-50%); background: white; }
        
        .info-card { background: #f8f9fa; border-radius: 10px; padding: 20px; border-left: 4px solid #3a7bd5; height: 100%; }
        
        /* REVIEW SECTION STYLES */
        .review-card { border-bottom: 1px solid #eee; padding: 15px 0; }
        .review-card:last-child { border-bottom: none; }
        .star-yellow { color: #ffc107; }
        .star-gray { color: #e0e0e0; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php"><i class="fas fa-heartbeat me-2"></i>NOVA CARE</a>
            <a href="../index.php" class="btn btn-outline-light btn-sm rounded-pill px-4">Back to Home</a>
        </div>
    </nav>

    <div class="profile-header"></div>

    <div class="container profile-container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="profile-card text-center">
                    
                    <img src="<?php echo $img_src; ?>" class="profile-img" alt="Doctor">
                    
                    <h1 class="mt-5 mb-1"><?php echo $doc['name']; ?></h1>
                    <span class="badge bg-light text-primary border border-primary px-3 py-2 fs-6 rounded-pill">
                        <?php echo $doc['specialization']; ?>
                    </span>

                    <div class="mt-3">
                        <?php if($total_reviews > 0) { ?>
                            <span class="badge bg-warning text-dark fs-6 rounded-pill">
                                <i class="fas fa-star me-1"></i> <?php echo $avg_rating; ?> / 5.0
                            </span>
                            <small class="text-muted ms-2">(<?php echo $total_reviews; ?> Reviews)</small>
                        <?php } else { ?>
                            <small class="text-muted"><i class="far fa-star me-1"></i> No reviews yet</small>
                        <?php } ?>
                    </div>

                    <div class="row text-start g-4 my-4">
                        <div class="col-md-4"><div class="info-card"><small class="text-uppercase text-muted fw-bold">Availability</small><h6 class="mb-0 mt-1 fw-bold"><?php echo $doc['availability']; ?></h6></div></div>
                        <div class="col-md-4"><div class="info-card"><small class="text-uppercase text-muted fw-bold">Experience</small><h6 class="mb-0 mt-1 fw-bold"><?php echo $doc['experience']; ?></h6></div></div>
                        <div class="col-md-4"><div class="info-card"><small class="text-uppercase text-muted fw-bold">Location</small><h6 class="mb-0 mt-1 fw-bold"><?php echo $doc['location']; ?></h6></div></div>
                    </div>

                    <div class="text-start px-md-4 mb-5">
                        <h5>About <?php echo $doc['name']; ?></h5>
                        <p style="line-height: 1.8; color: #555;"><?php echo nl2br($doc['bio']); ?></p>
                    </div>

                    <a href="book.php" class="btn btn-primary btn-lg rounded-pill px-5 mb-5 shadow-sm">
                        <i class="fas fa-calendar-check me-2"></i> Book Appointment Now
                    </a>

                    <hr>

                    <div class="text-start mt-5">
                        <h4 class="fw-bold mb-4"><i class="fas fa-comments me-2 text-primary"></i>Patient Reviews</h4>
                        
                        <?php if ($res_reviews->num_rows > 0) { ?>
                            <?php while($rev = $res_reviews->fetch_assoc()) { ?>
                                <div class="review-card">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="fw-bold mb-1"><?php echo $rev['patient_name']; ?></h6>
                                        <small class="text-muted"><?php echo date('d M Y', strtotime($rev['created_at'])); ?></small>
                                    </div>
                                    <div class="mb-2">
                                        <?php 
                                        for($i=1; $i<=5; $i++) {
                                            echo ($i <= $rev['rating']) ? '<i class="fas fa-star star-yellow small"></i>' : '<i class="fas fa-star star-gray small"></i>';
                                        } 
                                        ?>
                                    </div>
                                    <p class="text-muted small mb-0">"<?php echo $rev['comment']; ?>"</p>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="text-center py-4 bg-light rounded">
                                <p class="text-muted mb-0">No reviews yet. Be the first to rate this doctor!</p>
                            </div>
                        <?php } ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

</body>
</html>