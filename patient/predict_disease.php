<?php
require '../config/db.php';

// --- SESSION CHECK (MODIFIED) ---
// Ab hum redirect nahi kar rahe, bas check kar rahe hain ki user logged in hai ya nahi
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// --- CONNECTION FIX ---
$ML_API_URL = 'http://127.0.0.1:5000/predict_disease'; 

// --- NOTIFICATIONS LOGIC (Only if Logged In) ---
$count_notif = 0;
$res_notif = null;

if ($user_id) {
    $sql_notif = "SELECT appointments.*, doctors.name as doc_name FROM appointments JOIN doctors ON appointments.doctor_id = doctors.id WHERE user_id = '$user_id' AND (status = 'approved' OR status = 'cancelled') ORDER BY id DESC LIMIT 5";
    $res_notif = $conn->query($sql_notif);
    $count_notif = ($res_notif) ? $res_notif->num_rows : 0;
}

// --- SYMPTOM CATEGORIES ---
$symptom_categories = [
    'Group A: Internal & Circulatory' => ['chest pain', 'palpitation', 'high blood', 'low blood', 'tight chest', 'fever high', 'cold severe', 'general weakness', 'gums swollen', 'cavity'],
    'Group B: External & Musculoskeletal' => ['skin rash', 'itching severe', 'acne', 'hair loss', 'eczema', 'psoriasis', 'knee joint', 'fracture pain', 'shoulder injury', 'back pain chronic'],
    'Group C: Head, Nerve & Development' => ['severe head', 'migraine', 'numbness', 'tingling hands', 'seizure', 'memory loss', 'tremor', 'speech slur', 'tooth ache', 'jaw ache'],
    'Group D: Digestive & Trauma' => ['faint', 'vomiting', 'root canal', 'wisdom tooth', 'foot swell', 'wrist pain joint', 'ligament tear', 'bone injury']
];

// --- API CALL LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['symptoms'])) {
    $symptoms = $_POST['symptoms'];
    // Note: Duration/Severity are included in the symptoms string by JS
    
    $ch = curl_init($ML_API_URL);
    $payload = json_encode(['symptoms' => $symptoms]);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $final_predictions = [];

    if ($http_code == 200 && $response) {
        $data = json_decode($response, true);
        if (isset($data['predictions']) && is_array($data['predictions'])) {
            foreach ($data['predictions'] as $prediction) {
                $suggested_spec = $prediction['specialization'];
                
                $docs_res = $conn->query("SELECT name FROM doctors WHERE specialization = '$suggested_spec'");
                $doctors = [];
                while($r = $docs_res->fetch_assoc()) { $doctors[] = $r['name']; }

                $final_predictions[] = [
                    'disease' => $prediction['disease'],
                    'specialization' => $suggested_spec,
                    'matched_symptoms' => $prediction['matched_symptoms'],
                    'doctors' => $doctors
                ];
            }
        }
    }
    
    if ($http_code != 200 && empty($final_predictions)) {
         $final_predictions[] = ['disease' => 'CONNECTION ERROR', 'specialization' => 'Server Down', 'matched_symptoms' => 'Port 5000 unreachable'];
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'results' => $final_predictions]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>AI Prediction - Nova Care</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; color: #444; }
        .navbar { background: linear-gradient(to right, #3a7bd5, #3a6073); box-shadow: 0 4px 12px rgba(58, 123, 213, 0.2); }
        .navbar-brand { font-weight: 700; color: #fff !important; letter-spacing: 1px; }
        .nav-link { color: rgba(255,255,255,0.9) !important; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-right: 10px; }
        .nav-link:hover { color: #fff !important; transform: translateY(-1px); }
        .nav-link.active { color: #fff !important; border-bottom: 2px solid rgba(255,255,255,0.5); }
        .navbar .dropdown-toggle { color: #3a7bd5 !important; }
        
        .page-header { background: linear-gradient(rgba(58, 123, 213, 0.9), rgba(58, 96, 115, 0.9)), url('https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1500&q=80'); background-size: cover; color: white; padding: 60px 0; text-align: center; }
        .card-custom { border-radius: 15px; border-top: 5px solid #3a7bd5; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-top: -40px; }
        .btn-predict { background: #3a7bd5; color: white; padding: 12px 40px; border-radius: 50px; font-weight: 600; }
        .result-card-item { background: #fff; border: 1px solid #eee; padding: 15px; border-radius: 10px; margin-bottom: 10px; border-left: 5px solid #198754; }
        
        footer { background: linear-gradient(to right, #3a7bd5, #3a6073); color: white; padding: 60px 0 20px 0; margin-top: 80px; }
        footer a { color: rgba(255,255,255,0.8); text-decoration: none; }
        
        /* Accordion Fix */
        .accordion-button { font-weight: 600; }
        .accordion-button:not(.collapsed) { background-color: #e3f2fd; color: #3a7bd5; }
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
                    <li class="nav-item"><a class="nav-link active" href="predict_disease.php">Predict Disease</a></li>
                    <li class="nav-item"><a class="nav-link" href="article_about.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="article_tech.php">Technology</a></li>
                    <li class="nav-item"><a class="nav-link" href="article_care.php">Patient Care</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    
                    <?php if($user_id) { ?>
                        <li class="nav-item dropdown me-3">
                            <a class="nav-link" href="#" data-bs-toggle="dropdown"><i class="fas fa-bell"></i><?php if($count_notif > 0) echo '<span class="badge bg-danger rounded-circle position-absolute top-0 start-100 translate-middle p-1 border border-light rounded-circle"></span>'; ?></a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><h6 class="dropdown-header">Updates</h6></li>
                                <?php if ($count_notif > 0) { while($row = $res_notif->fetch_assoc()) { echo "<li><a class='dropdown-item small' href='#'>Dr. ".$row['doc_name']." - ".ucfirst($row['status'])."</a></li>"; } } else { echo "<li><span class='dropdown-item text-muted small'>No new notifications</span></li>"; } ?>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle bg-white text-primary rounded-pill px-3 py-1" href="#" data-bs-toggle="dropdown"><i class="fas fa-user-circle me-1"></i> <?php echo $_SESSION['name']; ?></a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                                <li><a class="dropdown-item" href="my_appointments.php">My Appointments</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="../logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php } else { ?>
                        <li class="nav-item"><a href="../login.php" class="btn btn-outline-light rounded-pill px-4 btn-sm me-2">Login</a></li>
                        <li class="nav-item"><a href="../register.php" class="btn btn-light rounded-pill px-4 btn-sm text-primary fw-bold">Register</a></li>
                    <?php } ?>

                </ul>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <h1 class="fw-bold">AI Health Assistant</h1>
        <p class="lead opacity-75">Analyze symptoms instantly - No login required to check!</p>
    </div>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-custom p-4">
                    
                    <h5 class="fw-bold text-primary mb-3">1. Additional Details (Optional)</h5>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Duration</label>
                            <select id="durationInput" class="form-select form-select-sm">
                                <option value="none">Select...</option>
                                <option value="acute">Less than 7 Days</option>
                                <option value="chronic">More than 30 Days</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Severity</label>
                            <select id="severityInput" class="form-select form-select-sm">
                                <option value="none">Select...</option>
                                <option value="mild">Mild</option>
                                <option value="severe">Severe</option>
                            </select>
                        </div>
                    </div>

                    <h5 class="fw-bold text-primary mb-3">2. Select Symptoms</h5>
                    <div class="accordion accordion-flush" id="symptomsAccordion">
                        <?php foreach($symptom_categories as $cat => $symptoms): $id = md5($cat); ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c-<?php echo $id; ?>">
                                    <i class="fas fa-notes-medical me-2"></i> <?php echo $cat; ?>
                                </button>
                            </h2>
                            <div id="c-<?php echo $id; ?>" class="accordion-collapse collapse" data-bs-parent="#symptomsAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <?php foreach($symptoms as $sym): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input symptom-checkbox" type="checkbox" value="<?php echo $sym; ?>">
                                                <label class="form-check-label"><?php echo ucwords($sym); ?></label>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <button id="predictBtn" class="btn btn-predict shadow">Analyze Symptoms</button>
                    </div>

                    <div id="predictionOutput" class="mt-5 d-none">
                        <h5 class="fw-bold mb-3">Analysis Result:</h5>
                        <div id="resultsContainer"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4"><h5 class="fw-bold mb-3">NOVA CARE</h5><p class="small opacity-75">World-class healthcare services.</p></div>
                <div class="col-md-4 mb-4"><h5 class="fw-bold mb-3">Quick Links</h5><ul class="list-unstyled small opacity-75"><li><a href="../index.php">Home</a></li><li><a href="predict_disease.php">Predict Disease</a></li><li><a href="book.php">Book Appointment</a></li><li><a href="contact.php">Contact</a></li></ul></div>
                <div class="col-md-4"><h5 class="fw-bold mb-3">Contact</h5><p class="small opacity-75">Islamabad, Pakistan<br>info@novacare.com</p></div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.getElementById('predictBtn').addEventListener('click', function() {
            const selected = Array.from(document.querySelectorAll('.symptom-checkbox:checked')).map(c => c.value);
            const duration = document.getElementById('durationInput').value;
            const severity = document.getElementById('severityInput').value;

            if (selected.length === 0) return alert('Please select at least one symptom.');

            let symptomsString = selected.join(', ');
            if (duration !== 'none') symptomsString += `, ${duration}`;
            if (severity !== 'none') symptomsString += `, ${severity}`;

            const btn = this;
            const output = document.getElementById('predictionOutput');
            const container = document.getElementById('resultsContainer');
            
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Analyzing...';
            btn.disabled = true;
            output.classList.add('d-none');

            fetch('predict_disease.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'symptoms=' + encodeURIComponent(symptomsString)
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = 'Analyze Symptoms';
                btn.disabled = false;
                output.classList.remove('d-none');
                container.innerHTML = '';

                if(data.results && data.results.length > 0) {
                    if(data.results[0].disease === 'CONNECTION ERROR') {
                        container.innerHTML = `<div class="alert alert-danger">⚠️ Server Error: Make sure Python ML script is running.</div>`;
                        return;
                    }

                    data.results.forEach(res => {
                        let docs = res.doctors.length ? res.doctors.map(d => `<li>Dr. ${d}</li>`).join('') : '<li class="text-muted">No doctors available currently.</li>';
                        let link = `book.php?spec=${encodeURIComponent(res.specialization)}&reason=${encodeURIComponent(res.disease)}`;
                        
                        container.innerHTML += `
                            <div class="result-card-item">
                                <span class="badge bg-primary mb-2">${res.specialization}</span>
                                <h5 class="text-danger fw-bold">${res.disease}</h5>
                                <p class="small text-muted">Matched: ${res.matched_symptoms}</p>
                                <hr>
                                <h6 class="fw-bold text-primary">Recommended Doctors:</h6>
                                <ul class="small mb-3 list-unstyled">${docs}</ul>
                                <a href="${link}" class="btn btn-success btn-sm rounded-pill px-4">Book Appointment</a>
                            </div>`;
                    });
                } else {
                    container.innerHTML = `<div class="alert alert-warning">No specific condition matched. Please consult a General Physician.</div>`;
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Analyze Symptoms';
                alert('Connection Error');
            });
        });
    </script>
</body>
</html>