<?php
session_start();
require_once 'koneksi.php';

// ==========================
// CEK LOGIN
// ==========================
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ==========================
// SESSION TIMEOUT (30 MENIT)
// ==========================
$timeout = 1800;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: login.php?expired=1");
    exit;
}

// ==========================
// CEK STATUS PETANI
// ==========================
if ($_SESSION['role'] == 'Petani') {
    $cek = $conn->query("SELECT status_verifikasi FROM users WHERE id=".$_SESSION['user_id']);
    $user = $cek->fetch_assoc();

    if ($user['status_verifikasi'] == 'Menunggu') {
        echo "<script>alert('Akun Anda masih menunggu verifikasi admin!');</script>";
    }

    if ($user['status_verifikasi'] == 'Ditolak') {
        echo "<script>alert('Akun Anda ditolak!'); window.location='logout.php';</script>";
    }
}

// update aktivitas
$_SESSION['last_activity'] = time();

// ==========================
// DATA USER
// ==========================
$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$user_nama = $_SESSION['nama'] ?? 'User';

// ==========================
// LABEL ROLE
// ==========================
if ($user_role == 'Admin') {
    $label_role = "Admin Panel";
    $icon_role  = "👑";
} elseif ($user_role == 'Petani') {
    $label_role = "Dashboard Petani";
    $icon_role  = "🌾";
} else {
    $label_role = "Dashboard Pembeli";
    $icon_role  = "🛒";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — AgriConnect</title>

<script src="https://cdn.tailwindcss.com"></script>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<script>
tailwind.config = {
    theme: {
        extend: {
            colors: { 
                soil: '#2C1A0E', 
                cream: '#FAF5E9', 
                leaf: '#2D5A27', 
                sprout: '#6FA85A',
                chalk: '#FFFFFF'
            },
            fontFamily: { 
                sans: ['DM Sans', 'sans-serif'], 
                serif: ['Playfair Display', 'serif'] 
            }
        }
    }
}
</script>
</head>

<body class="flex h-screen bg-cream font-sans">

<!-- ========================== -->
<!-- OVERLAY -->
<!-- ========================== -->
<div id="overlay"
class="fixed inset-0 bg-black/40 z-40 hidden md:hidden"></div>

<!-- ========================== -->
<!-- HEADER MOBILE -->
<!-- ========================== -->
<header class="md:hidden bg-white shadow-sm px-4 py-3 flex justify-between items-center fixed w-full top-0 left-0 z-50">

    <!-- LEFT -->
    <div class="flex items-center gap-3">

        <!-- BUTTON SIDEBAR -->
        <button id="btnSidebar" class="text-xl text-gray-700">
            ☰
        </button>

        <!-- LOGO -->
        <div class="flex items-center gap-2">
            <img src="uploads/logo.png" class="h-8">

            <div class="flex flex-col leading-tight">
                <span class="font-serif font-bold text-leaf text-sm">
                    AgriConnect
                </span>
                <span class="text-[10px] text-gray-400">
                    <?= $icon_role ?> <?= $label_role ?>
                </span>
            </div>
        </div>

    </div>

    <!-- RIGHT -->
    <div class="flex items-center gap-2">

        <!-- Nama -->
        <span class="text-xs text-gray-600 max-w-[80px] truncate">
            <?= htmlspecialchars($user_nama) ?>
        </span>

        <!-- Logout -->
        <a href="logout.php" 
           class="bg-red-500 text-white px-2 py-2 rounded-lg text-xs hover:bg-red-600 transition">
            ⎋
        </a>

    </div>

</header>

<!-- Spacer -->
<div class="h-14 md:hidden"></div>

<!-- ========================== -->
<!-- SCRIPT SIDEBAR -->
<!-- ========================== -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    const btn = document.getElementById("btnSidebar");
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("overlay");

    function bukaSidebar() {
        if (sidebar) sidebar.classList.remove("-translate-x-full");
        if (overlay) overlay.classList.remove("hidden");
    }

    function tutupSidebar() {
        if (sidebar) sidebar.classList.add("-translate-x-full");
        if (overlay) overlay.classList.add("hidden");
    }

    if (btn) {
        btn.addEventListener("click", bukaSidebar);
    }

    if (overlay) {
        overlay.addEventListener("click", tutupSidebar);
    }

});
</script>