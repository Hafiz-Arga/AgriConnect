<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah class mysqli ada (mencegah Fatal Error jika php.ini bermasalah)
if (!class_exists('mysqli')) {
    die("Error: Ekstensi MySQLi belum aktif di PHP Anda. Silakan aktifkan di php.ini dan restart Apache.");
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "agriconnect";

$conn = @new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>