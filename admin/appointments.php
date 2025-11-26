<?php
require '../config/db.php';

// LOAD PHPMAILER
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../index.php"); exit(); }

// --- FUNCTION TO SEND EMAIL ---
function sendStatusEmail($email, $name, $status, $extra_msg="", $slip_data=null) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // =========== APNI DETAILS DALEIN ===========
        $mail->Username   = 'muhammadafzaalhameed78@gmail.com'; 
        $mail->Password   = 'egmadazhxavbxtvl'; 
        // ===========================================

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('muhammadafzaalhameed78@gmail.com', 'Nova Care Hospital');
        $mail->addAddress($email, $name);

        $color = ($status == 'approved') ? 'green' : 'red';
        $status_text = strtoupper($status);

        // Slip Link
        $slip_html = "";
        if ($status == 'approved' && $slip_data) {
            $link = "http://localhost/doctor_app/patient/print_slip.php?id=" . $slip_data['id'];
            $slip_html = "<br><br><a href='$link' style='background-color:#3a7bd5;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🖨 Download Slip</a>";
        }

        $mail->isHTML(true);
        $mail->Subject = 'Appointment Update - Nova Care';
        $mail->Body    = "
            <div style='font-family:Arial; padding:20px; background:#f4f7f6;'>
                <div style='background:#fff; padding:20px; border-radius:10px; max-width:500px; margin:0 auto;'>
                    <h2 style='color:#3a7bd5;'>Appointment Status</h2>
                    <p>Hello <b>$name</b>,</p>
                    <p>Your appointment status has been updated to: <b style='color:$color;'>$status_text</b></p>
                    <p>$extra_msg</p>
                    $slip_html
                </div>
            </div>";
        $mail->send();
    } catch (Exception $e) { }
}

// --- HANDLE PRESCRIPTION UPLOAD ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_prescription'])) {
    $app_id = $_POST['app_id'];
    
    if(isset($_FILES['prescription']) && $_FILES['prescription']['error'] == 0) {
        $target_dir = "../uploads/";
        $file_name = "rx_" . time() . "_" . basename($_FILES["prescription"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if(move_uploaded_file($_FILES["prescription"]["tmp_name"], $target_file)) {
            $path = "uploads/" . $file_name;
            
            // Update DB
            $conn->query("UPDATE appointments SET doctor_prescription = '$path', status = 'approved' WHERE id = '$app_id'");
            
            // Fetch Info for Email
            $sql = "SELECT u.email, u.name, d.name as doc_name, a.appointment_date, a.appointment_time 
                    FROM appointments a 
                    JOIN users u ON a.user_id = u.id 
                    JOIN doctors d ON a.doctor_id = d.id 
                    WHERE a.id='$app_id'";
            $res = $conn->query($sql);
            $row = $res->fetch_assoc();
            
            $slip_data = [
                'id' => $app_id,
                'doc_name' => $row['doc_name'],
                'date' => date('d M, Y', strtotime($row['appointment_date'])),
                'time' => date('h:i A', strtotime($row['appointment_time']))
            ];

            // Send Email
            sendStatusEmail($row['email'], $row['name'], 'approved', 'A prescription has been uploaded by your doctor.', $slip_data);
        }
    }
    header("Location: appointments.php");
}

// --- HANDLE STATUS CHANGE (Approve/Cancel) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    $conn->query("UPDATE appointments SET status = '$action' WHERE id = $id");

    // Fetch Info
    $sql = "SELECT u.email, u.name, d.name as doc_name, a.appointment_date, a.appointment_time 
            FROM appointments a 
            JOIN users u ON a.user_id = u.id 
            JOIN doctors d ON a.doctor_id = d.id 
            WHERE a.id='$id'";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    
    $msg = ($action == 'approved') ? "Your appointment is confirmed." : "Please contact support for details.";
    $slip_data = ($action == 'approved') ? ['id' => $id, 'doc_name' => $row['doc_name'], 'date' => $row['appointment_date'], 'time' => $row['appointment_time']] : null;

    sendStatusEmail($row['email'], $row['name'], $action, $msg, $slip_data);
    header("Location: appointments.php");
}

// SEARCH & FILTER
$search = $_GET['search'] ?? ''; 
$status = $_GET['status'] ?? ''; 
$date = $_GET['date'] ?? '';

$sql = "SELECT a.*, u.name as p_name, d.name as d_name 
        FROM appointments a 
        JOIN users u ON a.user_id = u.id 
        JOIN doctors d ON a.doctor_id = d.id 
        WHERE 1=1";

if ($search) $sql .= " AND (u.name LIKE '%$search%' OR d.name LIKE '%$search%' OR a.id LIKE '%$search%')";
if ($status) $sql .= " AND a.status = '$status'";
if ($date) $sql .= " AND a.appointment_date = '$date'";
$sql .= " ORDER BY a.appointment_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Appointments Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; color: #444; }
        .navbar { background: linear-gradient(to right, #3a7bd5, #3a6073); box-shadow: 0 4px 12px rgba(58, 123, 213, 0.2); }
        .navbar-brand { font-weight: 700; color: #fff !important; }
        .nav-link { color: rgba(255,255,255,0.9) !important; text-transform: uppercase; font-size: 14px; }
        .filter-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); margin-bottom: 20px; border-left: 4px solid #3a7bd5; }
        .table-card { background: #fff; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); border-top: 4px solid #3a6073; overflow: hidden; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .bg-approved { background: #d1e7dd; color: #0f5132; } .bg-pending { background: #fff3cd; color: #856404; } .bg-cancelled { background: #f8d7da; color: #842029; }
        .btn-icon { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 12px; }
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
                    <li class="nav-item"><a class="nav-link" href="doctors.php">Doctors</a></li>
                    <li class="nav-item"><a class="nav-link active" href="appointments.php">Appointments</a></li>
                    <li class="nav-item ms-2"><a href="../logout.php" class="btn btn-outline-light btn-sm rounded-pill px-4">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div><h3 class="fw-bold mb-0">Appointment Manager</h3><p class="text-muted small mb-0">Manage patient bookings & prescriptions</p></div>
            
            <a href="export.php" class="btn btn-success rounded-pill px-4 shadow-sm"><i class="fas fa-file-excel me-2"></i>Export Report</a>
        </div>
        
        <div class="filter-card">
            <form method="GET" class="row g-3">
                <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Search Patient/Doctor/ID..." value="<?php echo $search; ?>"></div>
                <div class="col-md-3"><select name="status" class="form-select"><option value="">All Statuses</option><option value="pending">Pending</option><option value="approved">Approved</option></select></div>
                <div class="col-md-3"><input type="date" name="date" class="form-control" value="<?php echo $date; ?>"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100 rounded-pill">Filter</button></div>
            </form>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Reports & Rx</th> <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()) { 
                            $bg = match($row['status']) { 'approved'=>'bg-approved', 'cancelled'=>'bg-cancelled', default=>'bg-pending' }; 
                        ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td class="fw-bold"><?php echo $row['p_name']; ?></td>
                            <td class="text-primary"><?php echo $row['d_name']; ?></td>
                            <td><?php echo date('M d', strtotime($row['appointment_date'])); ?><br><small class="text-muted"><?php echo date('h:i A', strtotime($row['appointment_time'])); ?></small></td>
                            <td>
                                <?php if(!empty($row['patient_report'])) { ?>
                                    <a href="../<?php echo $row['patient_report']; ?>" target="_blank" class="badge bg-info text-decoration-none"><i class="fas fa-file-alt me-1"></i> Report</a>
                                <?php } else { echo '<span class="text-muted small">-</span>'; } ?>
                                
                                <?php if(!empty($row['doctor_prescription'])) { ?>
                                    <br><a href="../<?php echo $row['doctor_prescription']; ?>" target="_blank" class="badge bg-success text-decoration-none mt-1"><i class="fas fa-prescription me-1"></i> Rx Given</a>
                                <?php } ?>
                            </td>
                            <td><span class="status-badge <?php echo $bg; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                            <td class="text-end">
                                <?php if($row['status'] == 'pending' || $row['status'] == 'approved') { ?>
                                    <button class="btn btn-primary btn-icon" title="Upload Prescription" onclick="openPrescriptionModal('<?php echo $row['id']; ?>', '<?php echo $row['p_name']; ?>')"><i class="fas fa-file-medical"></i></button>
                                    
                                    <?php if($row['status'] == 'pending') { ?>
                                        <a href="appointments.php?id=<?php echo $row['id']; ?>&action=approved" class="btn btn-success btn-icon" title="Approve"><i class="fas fa-check"></i></a>
                                        <a href="appointments.php?id=<?php echo $row['id']; ?>&action=cancelled" class="btn btn-danger btn-icon" title="Cancel"><i class="fas fa-times"></i></a>
                                    <?php } ?>
                                <?php } else { echo '<i class="fas fa-lock text-muted opacity-25"></i>'; } ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white"><h5 class="modal-title">Upload Prescription</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4"><input type="hidden" name="app_id" id="modal_app_id"><p>Uploading for: <strong id="modal_p_name" class="text-primary"></strong></p><div class="mb-3"><input type="file" name="prescription" class="form-control" required></div></div>
                    <div class="modal-footer border-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button><button type="submit" name="upload_prescription" class="btn btn-primary">Upload & Approve</button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openPrescriptionModal(id, name) {
            document.getElementById('modal_app_id').value = id;
            document.getElementById('modal_p_name').innerText = name;
            new bootstrap.Modal(document.getElementById('rxModal')).show();
        }
    </script>
</body>
</html>