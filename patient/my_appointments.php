<?php
require '../config/db.php';

// LOGIN CHECK
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

// --- HANDLE REVIEW SUBMISSION ---
if (isset($_POST['submit_review'])) {
    $app_id = $_POST['appointment_id'];
    $doc_id = $_POST['doctor_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    $stmt = $conn->prepare("INSERT INTO reviews (user_id, doctor_id, appointment_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiis", $user_id, $doc_id, $app_id, $rating, $comment);

    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success shadow-sm border-0'>Thank you! Your review has been submitted.</div>";
    } else {
        $msg = "<div class='alert alert-danger shadow-sm border-0'>Error submitting review.</div>";
    }
}

// --- NOTIFICATIONS LOGIC ---
$sql_notif = "SELECT appointments.*, doctors.name as doc_name 
              FROM appointments 
              JOIN doctors ON appointments.doctor_id = doctors.id
              WHERE user_id = '$user_id' AND (status = 'approved' OR status = 'cancelled') 
              ORDER BY id DESC LIMIT 5";
$res_notif = $conn->query($sql_notif);
$count_notif = ($res_notif) ? $res_notif->num_rows : 0;

// --- FETCH APPOINTMENTS ---
$sql = "SELECT appointments.*, doctors.name AS doc_name, doctors.specialization, reviews.id as review_id
        FROM appointments 
        JOIN doctors ON appointments.doctor_id = doctors.id 
        LEFT JOIN reviews ON appointments.id = reviews.appointment_id
        WHERE appointments.user_id = '$user_id' 
        ORDER BY appointment_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Appointments - Nova Care</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; color: #444; }
        .navbar { background: linear-gradient(to right, #3a7bd5, #3a6073); box-shadow: 0 4px 12px rgba(58, 123, 213, 0.2); }
        .navbar-brand { font-weight: 700; letter-spacing: 1px; color: #fff !important; }
        .nav-link { color: rgba(255,255,255,0.9) !important; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-right: 10px; }
        .nav-link:hover { color: #fff !important; transform: translateY(-1px); }
        .nav-link.active { color: #fff !important; border-bottom: 2px solid rgba(255,255,255,0.5); }
        .navbar .dropdown-toggle { color: #3a7bd5 !important; } 
        .dropdown-menu { border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .page-header {
            background: linear-gradient(rgba(58, 123, 213, 0.9), rgba(58, 96, 115, 0.9)), url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1500&q=80');
            background-size: cover; background-position: center; color: white; padding: 60px 0; text-align: center; margin-bottom: 40px;
        }
        .table-card { background: #fff; border-radius: 15px; padding: 0; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; border-top: 5px solid #3a7bd5; }
        .table thead th { background: #f8f9fa; color: #666; font-weight: 600; text-transform: uppercase; font-size: 12px; padding: 15px 25px; border: none; }
        .table tbody td { padding: 20px 25px; vertical-align: middle; border-bottom: 1px solid #f4f6f9; }
        .status-badge { padding: 6px 15px; border-radius: 30px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .bg-approved { background: #d1e7dd; color: #0f5132; }
        .bg-pending { background: #fff3cd; color: #856404; }
        .bg-cancelled { background: #f8d7da; color: #842029; }
        .rating-star { color: #ddd; font-size: 24px; cursor: pointer; transition: 0.2s; }
        .rating-star:hover, .rating-star.active { color: #ffc107; }
        .rating-radio { display: none; }
        footer { background: linear-gradient(to right, #3a7bd5, #3a6073); color: white; padding: 60px 0 20px 0; text-align: left; margin-top: 80px; }
        footer a { color: rgba(255,255,255,0.8); text-decoration: none; transition: 0.3s; }
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
                        <a class="nav-link" href="#" data-bs-toggle="dropdown"><i class="fas fa-bell"></i><?php if($count_notif > 0) echo '<span class="badge bg-danger rounded-circle position-absolute top-0 start-100 translate-middle p-1 border border-light rounded-circle"><span class="visually-hidden">New alerts</span></span>'; ?></a>
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
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <h1 class="fw-bold">My Appointments</h1>
        <p class="lead opacity-75">View and manage your booking history</p>
    </div>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="mb-3"><?php echo $msg; ?></div>
                <div class="d-flex justify-content-end mb-3">
                    <a href="book.php" class="btn btn-primary rounded-pill shadow-sm px-4" style="background: #3a7bd5; border:none;"><i class="fas fa-plus me-2"></i>Book New Appointment</a>
                </div>

                <div class="table-card">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Doctor Info</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) { 
                                        $bg = match($row['status']) { 'approved'=>'bg-approved', 'cancelled'=>'bg-cancelled', default=>'bg-pending' };
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; color: #3a7bd5;"><i class="fas fa-user-md fa-lg"></i></div>
                                            <div>
                                                <div class="fw-bold text-dark">Dr. <?php echo $row['doc_name']; ?></div>
                                                <small class="text-muted"><?php echo $row['specialization']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-dark"><?php echo date('M d, Y', strtotime($row['appointment_date'])); ?></span>
                                            <small class="text-muted"><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></small>
                                        </div>
                                    </td>
                                    <td><span class="status-badge <?php echo $bg; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                    
                                    <td class="text-end">
                                        <?php if($row['status'] == 'approved') { ?>
                                            
                                            <?php if(!empty($row['doctor_prescription'])) { ?>
                                                <a href="../<?php echo $row['doctor_prescription']; ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 mb-1" title="Download Prescription">
                                                    <i class="fas fa-file-prescription me-1"></i> Rx
                                                </a>
                                            <?php } ?>

                                            <a href="print_slip.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 mb-1"><i class="fas fa-print me-1"></i> Slip</a>
                                            
                                            <?php if(!$row['review_id']) { ?>
                                            <button class="btn btn-sm btn-warning text-white rounded-pill px-3 mb-1" data-bs-toggle="modal" data-bs-target="#rateModal" onclick="setRateData('<?php echo $row['id']; ?>', '<?php echo $row['doctor_id']; ?>', '<?php echo $row['doc_name']; ?>')"><i class="fas fa-star me-1"></i> Rate</button>
                                            <?php } else { ?>
                                                <span class="badge bg-light text-warning border border-warning rounded-pill"><i class="fas fa-check"></i> Rated</span>
                                            <?php } ?>

                                        <?php } else { ?>
                                            <span class="text-muted small">-</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php } } else { echo "<tr><td colspan='4' class='text-center py-5 text-muted'><i class='far fa-calendar-times fa-3x mb-3 opacity-50'></i><br>No appointment history found.</td></tr>"; } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Rate Your Experience</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body text-center p-4">
                        <input type="hidden" name="appointment_id" id="modal_app_id">
                        <input type="hidden" name="doctor_id" id="modal_doc_id">
                        <h6 class="text-muted mb-3">How was your appointment with <span id="modal_doc_name" class="fw-bold text-primary"></span>?</h6>
                        <div class="mb-4">
                            <?php for($i=1; $i<=5; $i++) { ?>
                                <label><input type="radio" name="rating" value="<?php echo $i; ?>" class="rating-radio" required><i class="fas fa-star rating-star" onclick="highlightStars(<?php echo $i; ?>)"></i></label>
                            <?php } ?>
                        </div>
                        <div class="form-floating">
                            <textarea class="form-control" name="comment" placeholder="Leave a comment here" style="height: 100px"></textarea>
                            <label>Write your review (Optional)</label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="submit_review" class="btn btn-primary rounded-pill px-5">Submit Review</button>
                    </div>
                </form>
            </div>
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
    <script>
        function setRateData(appId, docId, docName) {
            document.getElementById('modal_app_id').value = appId;
            document.getElementById('modal_doc_id').value = docId;
            document.getElementById('modal_doc_name').innerText = "Dr. " + docName;
        }
        function highlightStars(rating) {
            let stars = document.querySelectorAll('.rating-star');
            stars.forEach((star, index) => {
                if (index < rating) { star.classList.add('active', 'text-warning'); star.style.color = "#ffc107"; } 
                else { star.classList.remove('active', 'text-warning'); star.style.color = "#ddd"; }
            });
        }
    </script>
</body>
</html>