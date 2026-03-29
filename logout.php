<?php
// Mulai session untuk bisa mengakses session yang sedang aktif
session_start();

// Hapus semua variabel session
session_unset();

// Hancurkan session sepenuhnya dari server
session_destroy();

// Hapus cookie (Opsional, tapi biasanya cookie 'remember me' dibiarkan agar form login tetap terisi)
// Jika ingin menghapus cookie remember me juga saat logout, hilangkan tanda komentar (//) di bawah ini:
// setcookie("remember_username", "", time() - 3600, "/");

// Arahkan kembali ke halaman login
header("Location: login.php");
exit();
?>