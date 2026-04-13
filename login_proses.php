<?php
require_once 'koneksi.php';

if (isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Cari user di database
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $query = $conn->query($sql);
    
    if ($query->num_rows > 0) {
        $user = $query->fetch_assoc();
        
        // Verifikasi password (menggunakan password_verify jika dipassword_hash saat register)
        if (password_verify($password, $user['password'])) {
            
            // SIMPAN DATA KE SESSION (Penyelesaian Error Undefined Array Key)
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['role']         = $user['role'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap']; // WAJIB ADA
            $_SESSION['foto_profil']  = $user['foto_profil'];
            
            // Lempar ke Dashboard
            header("Location: index.php");
            exit;
        } else {
            echo "<script>alert('Password salah!'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('Email tidak ditemukan!'); window.location.href='login.php';</script>";
    }
} else {
    header("Location: login.php");
}
?>