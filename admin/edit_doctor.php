<?php
require '../config/db.php';

// Check Admin Login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Check ID
if (!isset($_GET['id'])) {
    header("Location: doctors.php");
    exit();
}

$id = $_GET['id'];
$msg = "";

// --- UPDATE LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $specialization = $_POST['specialization'];
    $availability = $_POST['availability'];
    $experience = $_POST['experience'];
    $location = $_POST['location'];
    $bio = $_POST['bio'];
    
    // Image Logic (Agar nayi image upload ho toh purani replace karo)
    $image_query = "";
    if(isset($_FILES['doc_image']) && $_FILES['doc_image']['error'] == 0) {
        $target_dir = "../uploads/";
        $file_ext = pathinfo($_FILES['doc_image']['name'], PATHINFO_EXTENSION);
        $new_name = time() . "_" . rand(1000, 9999) . "." . $file_ext;
        $target_file = $target_dir . $new_name;

        if(move_uploaded_file($_FILES['doc_image']['tmp_name'], $target_file)) {
            $path = "uploads/" . $new_name;
            $image_query = ", image='$path'";
        }
    }

    $sql = "UPDATE doctors SET 
            name='$name', specialization='$specialization', availability='$availability', 
            experience='$experience', location='$location', bio='$bio' $image_query 
            WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: doctors.php"); // Update ke baad wapis bhej do
        exit();
    } else {
        $msg = "<div class='alert alert-danger'>Error updating record: " . $conn->error . "</div>";
    }
}

// Fetch Doctor Data
$result = $conn->query("SELECT * FROM doctors WHERE id = '$id'");
if($result->num_rows == 0) { die("Doctor not found"); }
$doc = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Doctor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; }
        .card-custom { border: none; border-top: 4px solid #3a7bd5; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .btn-update { background: #3a7bd5; color: white; border-radius: 50px; padding: 10px 30px; border: none; }
        .btn-update:hover { background: #2c5aa0; color: white; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-custom p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0">Edit Doctor Details</h4>
                        <a href="doctors.php" class="btn btn-outline-secondary btn-sm rounded-pill">Back</a>
                    </div>
                    
                    <?php echo $msg; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo $doc['name']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Specialization</label>
                                <select name="specialization" class="form-select" required>
                                    <option value="<?php echo $doc['specialization']; ?>" selected><?php echo $doc['specialization']; ?> (Current)</option>
                                    <option value="Cardiologist">Cardiologist</option>
                                    <option value="Dermatologist">Dermatologist</option>
                                    <option value="Neurologist">Neurologist</option>
                                    <option value="Orthopedic">Orthopedic</option>
                                    <option value="Dentist">Dentist</option>
                                    <option value="General Physician">General Physician</option>
                                    <option value="Pediatrician">Pediatrician</option>
                                    <option value="Child">Child Specialist</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Availability</label>
                                <input type="text" name="availability" class="form-control" value="<?php echo $doc['availability']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Experience</label>
                                <input type="text" name="experience" class="form-control" value="<?php echo $doc['experience']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Location</label>
                            <input type="text" name="location" class="form-control" value="<?php echo $doc['location']; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Bio</label>
                            <textarea name="bio" class="form-control" rows="3" required><?php echo $doc['bio']; ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Update Photo (Optional)</label>
                            <input type="file" name="doc_image" class="form-control">
                            <small class="text-muted">Leave empty to keep current photo.</small>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-update fw-bold">Update Doctor</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>