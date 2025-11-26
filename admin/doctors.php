<?php
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// --- DELETE DOCTOR LOGIC ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM doctors WHERE id = $id");
    header("Location: doctors.php");
}

// --- ADD DOCTOR LOGIC ---
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $specialization = $_POST['specialization'];
    $availability = $_POST['availability'];
    $experience = $_POST['experience'];
    $location = $_POST['location'];
    $bio = $_POST['bio'];
    
    // Image Upload Logic
    $image_path = "uploads/default.png";
    
    if(isset($_FILES['doc_image']) && $_FILES['doc_image']['error'] == 0) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        $file_ext = pathinfo($_FILES['doc_image']['name'], PATHINFO_EXTENSION);
        $new_name = time() . "_" . rand(1000, 9999) . "." . $file_ext;
        $target_file = $target_dir . $new_name;
        if(move_uploaded_file($_FILES['doc_image']['tmp_name'], $target_file)) {
            $image_path = "uploads/" . $new_name;
        } else {
            $msg = "<div class='alert alert-warning'>Image Upload Failed!</div>";
        }
    }

    $sql = "INSERT INTO doctors (name, specialization, availability, experience, location, bio, image) 
            VALUES ('$name', '$specialization', '$availability', '$experience', '$location', '$bio', '$image_path')";
    
    if ($conn->query($sql) === TRUE) {
        $msg = "<div class='alert alert-success border-0 shadow-sm'><i class='fas fa-check-circle me-2'></i> Doctor Added Successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// Fetch All Doctors
$doctors = $conn->query("SELECT * FROM doctors ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Doctors - Nova Care</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; color: #444; }
        .navbar { background: linear-gradient(to right, #3a7bd5, #3a6073); box-shadow: 0 4px 12px rgba(58, 123, 213, 0.2); }
        .navbar-brand { font-weight: 700; color: #fff !important; letter-spacing: 1px; }
        .nav-link { color: rgba(255,255,255,0.9) !important; font-size: 13px; text-transform: uppercase; }
        .nav-link:hover { color: #fff !important; }

        .card-custom { background: #fff; border-radius: 15px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; border-top: 4px solid #3a7bd5; }
        .card-header-custom { background: #fff; padding: 20px; border-bottom: 1px solid #f0f0f0; }
        
        .form-control, .form-select { border-radius: 8px; padding: 10px; border: 1px solid #e0e0e0; background-color: #f9fbfc; font-size: 14px; }
        .form-control:focus { border-color: #3a7bd5; box-shadow: 0 0 0 4px rgba(58, 123, 213, 0.1); background-color: #fff; }
        
        .btn-add { background: linear-gradient(to right, #3a7bd5, #3a6073); color: white; border: none; padding: 10px; border-radius: 50px; font-weight: 600; width: 100%; transition: 0.3s; }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(58, 123, 213, 0.3); color: white; }

        .table thead th { background: #f8f9fa; color: #666; font-weight: 600; border: none; padding: 15px; font-size: 13px; text-transform: uppercase; }
        .table tbody td { vertical-align: middle; padding: 15px; border-bottom: 1px solid #f4f6f9; }
        
        .doc-img-small { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #e3f2fd; margin-right: 15px; }
        .doc-icon { width: 45px; height: 45px; background-color: #e3f2fd; color: #3a7bd5; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-right: 15px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-stethoscope me-2"></i>NOVA CARE</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="doctors.php">Doctors</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php">Appointments</a></li>
                    <li class="nav-item ms-2"><a href="../logout.php" class="btn btn-outline-light btn-sm rounded-pill px-4">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="row mb-4"><div class="col-12"><h3 class="fw-bold text-dark">Manage Medical Staff</h3><p class="text-muted">Complete profile management for doctors.</p></div></div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card card-custom h-100">
                    <div class="card-header-custom"><h5 class="mb-0 fw-bold text-primary"><i class="fas fa-user-plus me-2"></i>Add New Doctor</h5></div>
                    <div class="card-body p-4">
                        <?php echo $msg; ?>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3 text-center"><label class="form-label small fw-bold text-muted d-block">Doctor's Photo</label><input type="file" name="doc_image" class="form-control" accept="image/*"></div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label small fw-bold text-muted">Name</label><input type="text" name="name" class="form-control" required placeholder="Dr. Name"></div>
                                <div class="col-md-6 mb-3"><label class="form-label small fw-bold text-muted">Specialization</label>
                                    <select name="specialization" class="form-select" required>
                                        <option value="">Select...</option><option value="Cardiologist">Cardiologist</option><option value="Dermatologist">Dermatologist</option><option value="Neurologist">Neurologist</option><option value="Orthopedic">Orthopedic</option><option value="Dentist">Dentist</option><option value="General Physician">General Physician</option><option value="Pediatrician">Pediatrician</option><option value="Child">Child Specialist</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label small fw-bold text-muted">Availability</label><input type="text" name="availability" class="form-control" required placeholder="e.g. 9am - 5pm"></div>
                                <div class="col-md-6 mb-3"><label class="form-label small fw-bold text-muted">Experience</label><input type="text" name="experience" class="form-control" required placeholder="e.g. 10+ Years"></div>
                            </div>
                            <div class="mb-3"><label class="form-label small fw-bold text-muted">Location/Room</label><input type="text" name="location" class="form-control" required placeholder="e.g. Room 304"></div>
                            <div class="mb-4"><label class="form-label small fw-bold text-muted">About Doctor</label><textarea name="bio" class="form-control" rows="3" required placeholder="Short bio..."></textarea></div>
                            <button type="submit" class="btn btn-add">Add Doctor Profile</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card card-custom">
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">Doctor Directory</h5>
                        <span class="badge bg-primary rounded-pill px-3"><?php echo $doctors->num_rows; ?> Doctors</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead><tr><th>Doctor</th><th>Details</th><th class="text-end">Action</th></tr></thead>
                            <tbody>
                                <?php 
                                if ($doctors->num_rows > 0) {
                                    while($row = $doctors->fetch_assoc()) { 
                                        $img_src = (!empty($row['image']) && file_exists("../" . $row['image'])) ? "../" . $row['image'] : null;
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if($img_src) { ?><img src="<?php echo $img_src; ?>" class="doc-img-small" alt="Doc"><?php } else { ?><div class="doc-icon"><i class="fas fa-user-md"></i></div><?php } ?>
                                            <div><div class="fw-bold text-dark"><?php echo $row['name']; ?></div><span class="badge bg-light text-primary border border-primary"><?php echo $row['specialization']; ?></span></div>
                                        </div>
                                    </td>
                                    <td><small class="d-block text-muted"><i class="far fa-clock me-1"></i> <?php echo $row['availability']; ?></small><small class="d-block text-muted"><i class="fas fa-star me-1 text-warning"></i> <?php echo $row['experience']; ?></small></td>
                                    
                                    <td class="text-end">
                                        <a href="edit_doctor.php?id=<?php echo $row['id']; ?>" class="btn btn-light text-primary btn-sm rounded-circle shadow-sm me-1" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="doctors.php?delete=<?php echo $row['id']; ?>" class="btn btn-light text-danger btn-sm rounded-circle shadow-sm" onclick="return confirm('Delete this profile?');" title="Delete"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                                <?php } } else { echo "<tr><td colspan='3' class='text-center py-5 text-muted'>No doctors found.</td></tr>"; } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>