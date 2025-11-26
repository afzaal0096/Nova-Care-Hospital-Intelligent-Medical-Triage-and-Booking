<?php
require '../config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit(); }

if (!isset($_GET['id'])) { die("Invalid Request"); }
$app_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch Appointment Details
$sql = "SELECT appointments.*, 
               doctors.name AS doc_name, doctors.specialization, doctors.availability,
               users.name AS patient_name, users.phone AS patient_phone, users.email AS patient_email
        FROM appointments 
        JOIN doctors ON appointments.doctor_id = doctors.id 
        JOIN users ON appointments.user_id = users.id 
        WHERE appointments.id = '$app_id' AND appointments.user_id = '$user_id'";

$result = $conn->query($sql);
if ($result->num_rows == 0) { die("Appointment not found."); }
$data = $result->fetch_assoc();

// --- QR CODE DATA GENERATION ---
// Ye wo text hai jo scan karne par show hoga
$qr_content = "NOVA CARE HOSPITAL\n";
$qr_content .= "App ID: #".str_pad($app_id, 5, '0', STR_PAD_LEFT)."\n";
$qr_content .= "Patient: ".$data['patient_name']."\n";
$qr_content .= "Doctor: Dr. ".$data['doc_name']."\n";
$qr_content .= "Date: ".date('d M, Y', strtotime($data['appointment_date']))."\n";
$qr_content .= "Time: ".date('h:i A', strtotime($data['appointment_time']))."\n";
$qr_content .= "Status: ".strtoupper($data['status']);

// Google Chart API ya QRServer API use kar ke image link banayenge
// urlencode() zaroori hai taake spaces aur special characters URL mein masla na karein
$qr_image_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qr_content);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Appointment Slip - Nova Care</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; color: #444; }
        
        /* NAVBAR */
        .navbar { background: linear-gradient(to right, #3a7bd5, #3a6073); box-shadow: 0 4px 12px rgba(58, 123, 213, 0.2); }
        .navbar-brand { font-weight: 700; color: #fff !important; letter-spacing: 1px; }
        .nav-link { color: rgba(255,255,255,0.9) !important; font-size: 13px; text-transform: uppercase; }
        .navbar .dropdown-toggle { color: #fff !important; } 

        /* SLIP CONTAINER */
        .slip-container {
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            border: 1px solid #e0e0e0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        
        .slip-header {
            background: linear-gradient(to right, #3a7bd5, #3a6073);
            color: white;
            padding: 30px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .slip-body { padding: 40px; }
        
        .info-group label { display: block; font-size: 12px; text-transform: uppercase; color: #888; font-weight: 600; margin-bottom: 3px; }
        .info-group p { font-size: 16px; font-weight: 500; color: #333; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        
        .status-stamp {
            border: 3px solid #198754;
            color: #198754;
            font-weight: 700;
            text-transform: uppercase;
            padding: 10px 20px;
            font-size: 20px;
            display: inline-block;
            border-radius: 10px;
            transform: rotate(-10deg);
            opacity: 0.8;
        }
        
        .qr-box {
            text-align: center;
            margin-top: 10px;
            border: 1px dashed #ccc;
            padding: 10px;
            display: inline-block;
            border-radius: 10px;
        }
        
        .btn-print { background: #3a7bd5; color: white; border: none; padding: 10px 30px; border-radius: 50px; }
        .btn-print:hover { background: #2c5aa0; color: white; }

        /* FOOTER */
        footer { background: linear-gradient(to right, #3a7bd5, #3a6073); color: white; padding: 60px 0 20px 0; margin-top: 80px; }

        /* --- PRINT SPECIFIC STYLES --- */
        @media print {
            @page { size: auto; margin: 0mm; }
            body { background-color: #fff; margin: 20px; -webkit-print-color-adjust: exact; }
            .navbar, footer, .no-print { display: none !important; }
            
            .slip-container { 
                box-shadow: none; margin: 0; border: 2px solid #3a7bd5; 
                width: 100%; max-width: 100%; 
            }
            .slip-header { 
                -webkit-print-color-adjust: exact; background: #3a7bd5 !important; color: white !important; padding: 20px 30px !important;
            }
            .slip-body { padding: 30px !important; }
            a { text-decoration: none; color: #333; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="../index.php"><i class="fas fa-heartbeat me-2"></i>NOVA CARE</a>
            <div class="ms-auto">
                <a href="my_appointments.php" class="btn btn-outline-light btn-sm rounded-pill">Back</a>
            </div>
        </div>
    </nav>

    <div class="container">
        
        <div class="d-flex justify-content-between align-items-center mt-5 mb-3 no-print" style="max-width: 800px; margin: 0 auto;">
            <a href="my_appointments.php" class="btn btn-outline-secondary rounded-pill"><i class="fas fa-arrow-left me-2"></i>Back</a>
            <button onclick="window.print()" class="btn btn-print shadow-sm"><i class="fas fa-print me-2"></i>Print Slip</button>
        </div>

        <div class="slip-container">
            <div class="slip-header">
                <div>
                    <h2 class="fw-bold mb-0" style="font-size: 24px;"><i class="fas fa-heartbeat me-2"></i>NOVA CARE</h2>
                    <small style="opacity: 0.9;">Medical Center & Hospital</small>
                </div>
                <div class="text-end">
                    <h5 class="mb-0 text-uppercase" style="font-weight: 700;">Appointment Slip</h5>
                    <small>ID: #<?php echo str_pad($app_id, 5, '0', STR_PAD_LEFT); ?></small>
                </div>
            </div>

            <div class="slip-body">
                
                <div class="row mb-4">
                    <div class="col-6">
                        <h6 class="text-primary fw-bold text-uppercase mb-3">Patient Info</h6>
                        <div class="info-group">
                            <label>Full Name</label>
                            <p><?php echo $data['patient_name']; ?></p>
                        </div>
                        <div class="info-group">
                            <label>Contact</label>
                            <p><?php echo $data['patient_phone']; ?></p>
                        </div>
                    </div>
                    
                    <div class="col-6 text-end">
                        <?php if($data['status'] == 'approved') { ?>
                            <div class="status-stamp border-success text-success">APPROVED</div>
                        <?php } elseif($data['status'] == 'cancelled') { ?>
                            <div class="status-stamp border-danger text-danger">CANCELLED</div>
                        <?php } else { ?>
                            <div class="status-stamp border-warning text-warning">PENDING</div>
                        <?php } ?>
                    </div>
                </div>

                <hr class="my-4" style="opacity: 0.1;">

                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-primary fw-bold text-uppercase mb-3">Appointment Details</h6>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-group">
                            <label>Doctor Name</label>
                            <p class="fw-bold fs-5">Dr. <?php echo $data['doc_name']; ?></p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-group">
                            <label>Department</label>
                            <p><?php echo $data['specialization']; ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-group">
                            <label>Date</label>
                            <p class="text-primary fw-bold"><?php echo date('d F, Y', strtotime($data['appointment_date'])); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-group">
                            <label>Time Slot</label>
                            <p class="text-primary fw-bold"><?php echo date('h:i A', strtotime($data['appointment_time'])); ?></p>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center mt-5">
                    <div class="col-8">
                        <div class="alert alert-light border text-start small text-muted mb-0">
                            <i class="fas fa-info-circle me-1"></i> 
                            Please arrive 15 minutes before your scheduled time. <br>
                            Bring this slip along with your original ID for verification.
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="qr-box">
                            <img src="<?php echo $qr_image_url; ?>" alt="QR Code" width="100">
                            <div style="font-size: 10px; margin-top: 5px; color: #888;">Scan for Details</div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4 pt-3 border-top">
                    <small class="text-muted">Generated on <?php echo date('d M Y, h:i A'); ?> | Nova Care Hospital System</small>
                </div>

            </div>
        </div>
    </div>

    <footer class="text-center">
        <div class="container">
            <p class="mb-0 small opacity-50">&copy; 2025 Nova Care Hospital. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>