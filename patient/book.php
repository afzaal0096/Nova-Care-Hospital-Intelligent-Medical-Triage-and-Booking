<?php
require '../config/db.php';

// --- LOAD PHPMAILER ---
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// LOGIN CHECK
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }

$user_id = $_SESSION['user_id'];

// --- FETCH USER DETAILS ---
$u_res = $conn->query("SELECT email, name FROM users WHERE id='$user_id'");
$user_data = $u_res->fetch_assoc();
$user_email = $user_data['email'];
$user_name = $user_data['name'];

// --- NOTIFICATIONS LOGIC ---
$sql_notif = "SELECT appointments.*, doctors.name as doc_name 
              FROM appointments 
              JOIN doctors ON appointments.doctor_id = doctors.id
              WHERE user_id = '$user_id' AND (status = 'approved' OR status = 'cancelled') 
              ORDER BY id DESC LIMIT 5";
$res_notif = $conn->query($sql_notif);
$count_notif = ($res_notif) ? $res_notif->num_rows : 0;

$msg = "";
$booked_slots = [];
$doctor_id = "";
$date = "";

// --- AI PRE-SELECTION LOGIC (PERSISTENT) ---
// We check POST first (if form submitted), then GET (if coming from AI page)
$pre_selected_spec = $_REQUEST['spec'] ?? ''; 
$ai_reason = $_REQUEST['reason'] ?? ''; 
$ai_diagnosis = $_POST['ai_diagnosis'] ?? $ai_reason; // To save in DB

// --- DYNAMIC TIME SETTINGS ---
$doc_start_time = "09:00"; 
$doc_end_time = "17:00";   

// 1. CHECK AVAILABILITY & FETCH DOCTOR TIMING
if (isset($_POST['check_availability']) || isset($_POST['confirm_booking'])) {
    $doctor_id = $_POST['doctor_id'];
    $date = $_POST['date'];
    
    // Fetch Booked Slots
    $sql = "SELECT appointment_time FROM appointments WHERE doctor_id = '$doctor_id' AND appointment_date = '$date' AND status != 'cancelled'";
    $result = $conn->query($sql);
    while($row = $result->fetch_assoc()) { $booked_slots[] = date('H:i', strtotime($row['appointment_time'])); }

    // Fetch Doctor's Actual Availability
    $doc_query = $conn->query("SELECT availability FROM doctors WHERE id = '$doctor_id'");
    if($doc_query->num_rows > 0) {
        $doc_data = $doc_query->fetch_assoc();
        $avail_string = $doc_data['availability']; 
        $parts = explode('-', $avail_string);
        if (count($parts) == 2) {
            $start_ts = strtotime(trim($parts[0]));
            $end_ts = strtotime(trim($parts[1]));
            if ($start_ts && $end_ts) {
                $doc_start_time = date("H:i", $start_ts);
                $doc_end_time = date("H:i", $end_ts);
            }
        }
    }
}

// 2. CONFIRM BOOKING
if (isset($_POST['confirm_booking'])) {
    if(!empty($_POST['time_slot'])) {
        $time = $_POST['time_slot']; 
        $diagnosis_to_save = !empty($_POST['ai_diagnosis']) ? $_POST['ai_diagnosis'] : NULL;
        
        // File Upload Logic
        $report_path = NULL;
        if(isset($_FILES['patient_report']) && $_FILES['patient_report']['error'] == 0) {
            $target_dir = "../uploads/";
            if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
            $file_name = time() . "_" . basename($_FILES["patient_report"]["name"]);
            $target_file = $target_dir . $file_name;
            if(move_uploaded_file($_FILES["patient_report"]["tmp_name"], $target_file)) { $report_path = "uploads/" . $file_name; }
        }

        // Insert into DB (Added ai_diagnosis column)
        $stmt = $conn->prepare("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, status, patient_report, ai_diagnosis) VALUES (?, ?, ?, ?, 'pending', ?, ?)");
        $stmt->bind_param("iissss", $user_id, $doctor_id, $date, $time, $report_path, $diagnosis_to_save);

        if ($stmt->execute()) {
            $msg = "<div class='alert alert-success border-0 shadow-sm'><i class='fas fa-check-circle me-2'></i> Appointment Booked! Confirmation email sent.</div>";
            $booked_slots[] = $time; 

            // --- SEND EMAIL NOTIFICATION ---
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'muhammadafzaalhameed78@gmail.com'; 
                $mail->Password   = 'egmadazhxavbxtvl';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                $mail->setFrom('muhammadafzaalhameed78@gmail.com', 'Nova Care Hospital');
                $mail->addAddress($user_email, $user_name);

                $mail->isHTML(true);
                $mail->Subject = 'Appointment Received - Nova Care';
                
                $diagnosis_html = $diagnosis_to_save ? "<p><strong>AI Detected Issue:</strong> <span style='color:red;'>$diagnosis_to_save</span></p>" : "";

                $mail->Body    = "
                    <div style='font-family:Arial; padding:20px; background:#f4f7f6;'>
                        <div style='background:#fff; padding:20px; border-radius:10px; max-width:500px; margin:0 auto;'>
                            <h2 style='color:#3a7bd5;'>Appointment Received</h2>
                            <p>Hello <b>$user_name</b>,</p>
                            <p>We have received your request for <b>$date</b> at <b>$time</b>.</p>
                            $diagnosis_html
                            <p>Status: <b style='color:orange;'>Pending Approval</b></p>
                        </div>
                    </div>";

                $mail->send();
            } catch (Exception $e) { } 

        } else {
            $msg = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    } else {
        $msg = "<div class='alert alert-warning'>Please select a time slot!</div>";
    }
}

$doctors = $conn->query("SELECT * FROM doctors");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Book Appointment - Nova Care</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; color: #444; }
        .navbar { background: linear-gradient(to right, #3a7bd5, #3a6073); box-shadow: 0 4px 12px rgba(58, 123, 213, 0.2); }
        .navbar-brand { font-weight: 700; color: #fff !important; letter-spacing: 1px; }
        .nav-link { color: rgba(255,255,255,0.9) !important; font-size: 13px; text-transform: uppercase; }
        .navbar .dropdown-toggle { color: #3a7bd5 !important; } 
        .page-header { background: linear-gradient(rgba(58, 123, 213, 0.8), rgba(58, 96, 115, 0.8)), url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1500&q=80'); background-size: cover; color: white; padding: 60px 0; text-align: center; }
        .booking-card { background: #fff; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-top: -40px; border-top: 5px solid #3a7bd5; z-index: 10; position: relative; }
        .booking-header { padding: 30px; background: #fff; border-bottom: 1px solid #f0f0f0; text-align: center; }
        .slot-label { width: 110px; margin: 8px; border-radius: 8px; padding: 12px; font-size: 13px; border: 2px solid #e0e0e0; background: white; color: #333; transition: 0.2s; cursor: pointer; display: inline-block; }
        .slot-radio:checked + .slot-label { background-color: #3a7bd5; color: white; border-color: #3a7bd5; }
        .slot-radio:disabled + .slot-label { background-color: #f8f9fa; color: #dc3545; opacity: 0.6; cursor: not-allowed; }
        .slot-radio { display: none; }
        .upload-box { border: 2px dashed #ccc; padding: 20px; border-radius: 10px; text-align: center; background: #fafafa; margin-bottom: 25px; }
        footer { background: linear-gradient(to right, #3a7bd5, #3a6073); color: white; padding: 60px 0 20px 0; margin-top: 80px; }
        footer a { color: rgba(255,255,255,0.8); text-decoration: none; }
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
                    <li class="nav-item"><a class="nav-link" href="predict_disease.php" style="font-weight: 700;">PREDICT DISEASE</a></li>
                    <li class="nav-item"><a class="nav-link" href="article_about.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="article_tech.php">Technology</a></li>
                    <li class="nav-item"><a class="nav-link" href="article_care.php">Patient Care</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
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
        <h1 class="fw-bold">Book Appointment</h1>
        <p class="lead opacity-90">Schedule a visit with our top specialists</p>
    </div>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="booking-card">
                    <div class="booking-header">
                        <h3 class="fw-bold text-dark mb-1">Select Date & Doctor</h3>
                        <?php if(!empty($ai_reason)): ?>
                            <div class="alert alert-info small mt-3 mb-0 border-0 shadow-sm" style="background-color: #e3f2fd; color: #0c5460;">
                                <i class="fas fa-robot me-2"></i> <strong>AI Suggestion:</strong> Detected <strong><?php echo htmlspecialchars($ai_reason); ?></strong>. We recommend a <strong><?php echo htmlspecialchars($pre_selected_spec); ?></strong>.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-4">
                        <?php echo $msg; ?>
                        
                        <form method="POST">
                            <input type="hidden" name="ai_diagnosis" value="<?php echo htmlspecialchars($ai_diagnosis); ?>">
                            
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold text-muted">Select Doctor</label>
                                    <select name="doctor_id" class="form-select" required>
                                        <option value="">Choose a Specialist...</option>
                                        <?php 
                                        $doctors->data_seek(0);
                                        while($row = $doctors->fetch_assoc()) {
                                            $sel = ($row['id'] == $doctor_id) ? 'selected' : '';
                                            
                                            // Auto-select logic
                                            if (empty($sel) && empty($doctor_id) && !empty($pre_selected_spec) && $row['specialization'] == $pre_selected_spec) {
                                                $sel = 'selected';
                                            }

                                            echo "<option value='".$row['id']."' $sel>".$row['name']." (".$row['specialization'].")</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold text-muted">Select Date</label>
                                    <input type="date" name="date" class="form-control" value="<?php echo $date; ?>" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" name="check_availability" class="btn btn-primary w-100 h-100" style="background: #3a7bd5; border:none;">Check</button>
                                </div>
                            </div>
                        </form>

                        <hr class="my-4 opacity-10">

                        <?php if ($date && $doctor_id) { ?>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
                                <input type="hidden" name="date" value="<?php echo $date; ?>">
                                <input type="hidden" name="ai_diagnosis" value="<?php echo htmlspecialchars($ai_diagnosis); ?>">

                                <div class="text-center mb-4">
                                    <h5 class="mb-3 fw-bold">
                                        1. Select Time Slot <br>
                                        <small class="text-muted small fw-normal">(Availability: <?php echo date("h:i A", strtotime($doc_start_time)) . " - " . date("h:i A", strtotime($doc_end_time)); ?>)</small>
                                    </h5>
                                    <div class="d-flex flex-wrap justify-content-center">
                                        <?php
                                        $start = strtotime($doc_start_time);
                                        $end = strtotime($doc_end_time);
                                        $slots_found = false;

                                        while ($start < $end) {
                                            $slot = date('H:i', $start);
                                            $display = date('h:i A', $start);
                                            $is_booked = in_array($slot, $booked_slots);
                                            $disabled = $is_booked ? 'disabled' : '';
                                            $id = "slot_" . str_replace(':', '', $slot);
                                            
                                            echo "<input type='radio' name='time_slot' value='$slot' id='$id' class='slot-radio' $disabled required>";
                                            echo "<label for='$id' class='slot-label'>";
                                            echo ($is_booked) ? "<i class='fas fa-times-circle mb-1'></i><br>Booked" : "<i class='far fa-clock mb-1'></i><br>$display";
                                            echo "</label>";
                                            
                                            $start = strtotime('+30 minutes', $start);
                                            $slots_found = true;
                                        }
                                        
                                        if (!$slots_found) {
                                            echo "<p class='text-danger'>No slots available based on doctor's timing.</p>";
                                        }
                                        ?>
                                    </div>
                                </div>

                                <div class="upload-box">
                                    <h6 class="fw-bold text-primary mb-2"><i class="fas fa-cloud-upload-alt me-2"></i>2. Upload Reports (Optional)</h6>
                                    <input type="file" name="patient_report" class="form-control w-75 mx-auto">
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" name="confirm_booking" class="btn btn-success btn-lg rounded-pill px-5 shadow">
                                        <i class="fas fa-calendar-check me-2"></i> Confirm Booking
                                    </button>
                                </div>
                            </form>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4"><h5 class="fw-bold mb-3">NOVA CARE</h5><p class="small opacity-75">Providing world-class healthcare services.</p></div>
                <div class="col-md-4 mb-4"><h5 class="fw-bold mb-3">Quick Links</h5><ul class="list-unstyled small opacity-75"><li><a href="../index.php">Home</a></li><li><a href="article_about.php">About Us</a></li><li><a href="book.php">Book Appointment</a></li><li><a href="contact.php">Contact Us</a></li></ul></div>
                <div class="col-md-4"><h5 class="fw-bold mb-3">Contact Info</h5><p class="small opacity-75 mb-2">123 Blue Area, Islamabad</p><p class="small opacity-75">info@novacare.com</p></div>
            </div>
            <hr class="my-4" style="opacity: 0.2;"><p class="mb-0 small opacity-50 text-center">&copy; 2025 Nova Care Hospital.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>