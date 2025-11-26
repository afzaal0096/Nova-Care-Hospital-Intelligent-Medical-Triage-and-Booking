<?php
require '../config/db.php';

// Check Admin Login (Agar admin nahi hai to login page par bhej do)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// 1. Basic Ginti (Top Cards ke liye)
$doc_count = $conn->query("SELECT id FROM doctors")->num_rows;
$patient_count = $conn->query("SELECT id FROM users WHERE role='patient'")->num_rows;
$pending_count = $conn->query("SELECT id FROM appointments WHERE status='pending'")->num_rows;
$total_app = $conn->query("SELECT id FROM appointments")->num_rows;

// 2. Recent Activity List (Neeche wali table ke liye - aakhri 5 appointments)
$sql_recent = "SELECT appointments.*, users.name AS p_name, doctors.name AS d_name 
               FROM appointments 
               JOIN users ON appointments.user_id = users.id 
               JOIN doctors ON appointments.doctor_id = doctors.id 
               ORDER BY id DESC LIMIT 5";
$recent_list = $conn->query($sql_recent);

// --- 3. CHARTS DATA (Naya Feature: Graphs ke liye data nikalna) ---

// Chart 1: Appointments Status (Kitne Approved/Pending hain)
$status_query = $conn->query("SELECT status, COUNT(*) as count FROM appointments GROUP BY status");
$status_labels = [];
$status_data = [];
while($row = $status_query->fetch_assoc()) {
    $status_labels[] = ucfirst($row['status']); // Naam (Approved, Pending)
    $status_data[] = $row['count']; // Ginti (5, 2)
}

// Chart 2: Doctors by Specialization (Kis department mein kitne doctor hain)
$spec_query = $conn->query("SELECT specialization, COUNT(*) as count FROM doctors GROUP BY specialization");
$spec_labels = [];
$spec_data = [];
while($row = $spec_query->fetch_assoc()) {
    $spec_labels[] = $row['specialization']; 
    $spec_data[] = $row['count']; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard - Analytics</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; color: #444; }
        
        /* NAVBAR STYLE */
        .navbar { background: linear-gradient(to right, #3a7bd5, #3a6073); box-shadow: 0 4px 12px rgba(58, 123, 213, 0.2); }
        .navbar-brand { font-weight: 700; letter-spacing: 1px; color: #fff !important; }
        .nav-link { color: rgba(255,255,255,0.9) !important; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
        .nav-link:hover { color: #fff !important; transform: translateY(-1px); }

        /* UPAR WALE CARDS KA STYLE */
        .stat-card {
            background: #fff; border-radius: 10px; padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03); border: none;
            border-top: 4px solid #3a7bd5; transition: transform 0.3s ease;
            height: 100%; cursor: pointer;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); }
        .stat-icon { width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #fff; }
        
        /* Colors define kiye hain */
        .bg-blue { background: linear-gradient(135deg, #3a7bd5, #3a6073); }
        .bg-green { background: linear-gradient(135deg, #11998e, #38ef7d); }
        .bg-orange { background: linear-gradient(135deg, #fc4a1a, #f7b733); }
        .bg-purple { background: linear-gradient(135deg, #8E2DE2, #4A00E0); }
        .card-link { text-decoration: none; color: inherit; display: block; height: 100%; }

        /* TABLE KA STYLE */
        .table-card { background: #fff; border-radius: 10px; padding: 20px; border-top: 4px solid #3a6073; box-shadow: 0 5px 15px rgba(0,0,0,0.03); }
        .table thead th { background: #f8f9fa; color: #666; font-weight: 600; border: none; padding: 15px; }
        .status-badge { padding: 5px 12px; border-radius: 30px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .bg-approved { background: #d1e7dd; color: #0f5132; }
        .bg-pending { background: #fff3cd; color: #856404; }
        .bg-cancelled { background: #f8d7da; color: #842029; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-stethoscope me-2"></i>NOVA CARE</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="doctors.php">Doctors</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php">Appointments</a></li>
                    <li class="nav-item ms-2"><a href="../logout.php" class="btn btn-outline-light btn-sm rounded-pill px-4">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="fw-bold text-dark">Admin Dashboard</h3>
                <p class="text-muted">Overview of hospital activities.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <a href="doctors.php" class="card-link">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><h6 class="text-muted mb-1">Doctors</h6><h2 class="fw-bold mb-0"><?php echo $doc_count; ?></h2></div>
                            <div class="stat-icon bg-blue"><i class="fas fa-user-md"></i></div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card" style="border-color: #11998e;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h6 class="text-muted mb-1">Patients</h6><h2 class="fw-bold mb-0"><?php echo $patient_count; ?></h2></div>
                        <div class="stat-icon bg-green"><i class="fas fa-users"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <a href="appointments.php?status=pending" class="card-link">
                    <div class="stat-card" style="border-color: #fc4a1a;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><h6 class="text-muted mb-1">Pending</h6><h2 class="fw-bold mb-0"><?php echo $pending_count; ?></h2></div>
                            <div class="stat-icon bg-orange"><i class="fas fa-clock"></i></div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6">
                <a href="appointments.php" class="card-link">
                    <div class="stat-card" style="border-color: #8E2DE2;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><h6 class="text-muted mb-1">Bookings</h6><h2 class="fw-bold mb-0"><?php echo $total_app; ?></h2></div>
                            <div class="stat-icon bg-purple"><i class="fas fa-calendar-check"></i></div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100 p-3" style="border-radius: 10px;">
                    <h5 class="fw-bold text-muted mb-3 small text-uppercase">Appointments Status</h5>
                    <div style="height: 250px; display: flex; justify-content: center;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100 p-3" style="border-radius: 10px;">
                    <h5 class="fw-bold text-muted mb-3 small text-uppercase">Doctors by Department</h5>
                    <div style="height: 250px;">
                        <canvas id="specChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-12">
                <div class="table-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Recent Activity</h5>
                        <a href="appointments.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Patient</th><th>Doctor</th><th>Date</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php while($row = $recent_list->fetch_assoc()) { 
                                    $bg = match($row['status']) { 'approved'=>'bg-approved', 'cancelled'=>'bg-cancelled', default=>'bg-pending' };
                                ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $row['p_name']; ?></td>
                                    <td class="text-primary"><?php echo $row['d_name']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['appointment_date'])); ?></td>
                                    <td><span class="status-badge <?php echo $bg; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // --- CHART 1 CONFIGURATION (Gool wala chart) ---
        const ctx1 = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx1, {
            type: 'doughnut', // Chart ki kism
            data: {
                labels: <?php echo json_encode($status_labels); ?>, // Labels yahan aayenge
                datasets: [{
                    data: <?php echo json_encode($status_data); ?>, // Data yahan aayega
                    backgroundColor: ['#ffc107', '#198754', '#dc3545', '#3a7bd5'], // Rang
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // --- CHART 2 CONFIGURATION (Lambe bars wala chart) ---
        const ctx2 = document.getElementById('specChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar', // Chart ki kism
            data: {
                labels: <?php echo json_encode($spec_labels); ?>,
                datasets: [{
                    label: 'Doctors',
                    data: <?php echo json_encode($spec_data); ?>,
                    backgroundColor: '#3a7bd5',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>