<?php
session_start();

// Proteksi 1: Cek apakah session ada
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Proteksi 2: Cek apakah umur session sudah lebih dari 60 detik
if (isset($_SESSION['login_time'])) {
    // (Waktu saat ini) dikurangi (Waktu saat login)
    if ((time() - $_SESSION['login_time']) > 60) {
        
        // Sesi expired (habis), hancurkan session!
        session_unset();
        session_destroy();
        
        // Munculkan alert dan kembalikan ke login
        echo "<script>alert('Waktu Session (60 detik) sudah habis! Anda otomatis ter-logout.'); window.location.href='login.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>AgriConnect - Dashboard Petani</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Styling tambahan khusus untuk header & tombol logout */
        .header-dashboard {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: white;
            border-bottom: 2px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .user-info {
            font-size: 18px;
            font-weight: bold;
        }
        .btn-logout {
            background: #d50000;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-logout:hover {
            background: #ff1744;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="background"></div>

<div class="header-dashboard">
    <div class="user-info">
        👋 Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>! (Role: <?php echo htmlspecialchars($_SESSION['role']); ?>)
    </div>
    <a href="logout.php" class="btn-logout">Keluar / Logout</a>
</div>

<div class="wrapper">
    <div class="card form-card">
        <h2>🌾 Input Data Produksi</h2>
        <form id="formProduksi">
            <input type="hidden" id="editIndex">

            <div class="input-group">
                <label>Jenis Komoditas</label>
                <input type="text" id="komoditas" required>
                <span class="error"></span>
            </div>

            <div class="input-group">
                <label>Estimasi Panen</label>
                <input type="date" id="estimasi" required>
                <span class="error"></span>
            </div>

            <div class="input-group">
                <label>Kapasitas (kg)</label>
                <input type="number" id="kapasitas" required>
                <span class="error"></span>
            </div>

            <div class="input-group">
                <label>Standar Kualitas</label>
                <input type="text" id="kualitas" required>
                <span class="error"></span>
            </div>

            <div class="input-group">
                <label>Harga Diharapkan (Rp)</label>
                <input type="number" id="harga" required>
                <span class="error"></span>
            </div>

            <button type="submit" class="btn-primary">Simpan</button>
        </form>
    </div>

    <div class="card table-card">
        <h2>📊 Marketplace Supplier</h2>
        <table>
            <thead>
                <tr>
                    <th>Komoditas</th>
                    <th>Estimasi</th>
                    <th>Kapasitas</th>
                    <th>Kualitas</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
</div>

<script src="JavaScript.js"></script>
</body>
</html>