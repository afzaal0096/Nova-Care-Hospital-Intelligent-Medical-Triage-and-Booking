<?php
$conn = new mysqli('localhost', 'root', '', 'doctor_app');

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Ye line Boht Zaroori hai (Iske baghair login yaad nahi rehta)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>