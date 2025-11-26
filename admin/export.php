<?php
require '../config/db.php';

// Check Admin Login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Headers taake browser isse file download samjhe
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="appointments_report.csv"');

// File open karein (Output stream)
$output = fopen('php://output', 'w');

// 1. Column Headings (Excel ki pehli line)
fputcsv($output, array('ID', 'Patient Name', 'Phone', 'Doctor Name', 'Department', 'Date', 'Time', 'Status'));

// 2. Database se Data nikalein
$sql = "SELECT appointments.*, users.name AS p_name, users.phone AS p_phone, 
               doctors.name AS d_name, doctors.specialization 
        FROM appointments 
        JOIN users ON appointments.user_id = users.id 
        JOIN doctors ON appointments.doctor_id = doctors.id 
        ORDER BY appointment_date DESC";
$result = $conn->query($sql);

// 3. Loop chalakar Excel mein row add karein
while($row = $result->fetch_assoc()) {
    fputcsv($output, array(
        $row['id'],
        $row['p_name'],
        $row['p_phone'],
        $row['d_name'],
        $row['specialization'],
        $row['appointment_date'],
        date('h:i A', strtotime($row['appointment_time'])),
        ucfirst($row['status'])
    ));
}

fclose($output);
exit();
?>