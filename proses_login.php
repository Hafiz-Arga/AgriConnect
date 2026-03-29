<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password']; 
    $role = $_POST['role'];
    
    if (!empty($username) && !empty($password)) {
        
        // 1. SET SESSION & WAKTU LOGIN
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        $_SESSION['login_time'] = time(); // Simpan detik saat user klik tombol login
        
        // 2. SET COOKIE (Berlaku 1 Menit / 60 Detik)
        if (isset($_POST['remember'])) {
            // Waktu saat ini + 60 detik
            setcookie("remember_username", $username, time() + 60, "/");
        } else {
            // Jika tidak dicentang, hapus cookie
            setcookie("remember_username", "", time() - 3600, "/");
        }
        
        header("Location: index.php");
        exit();
    } else {
        echo "<script>alert('Username dan Password harus diisi!'); window.location.href='login.php';</script>";
    }
}
?>