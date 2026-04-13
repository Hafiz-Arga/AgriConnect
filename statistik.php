<?php
require_once 'header.php';
require_once 'sidebar.php';

// CEK ADMIN
if ($_SESSION['role'] !== 'Admin') {
    echo "<script>alert('Akses ditolak!'); window.location.href='index.php';</script>";
    exit;
}

// ======================
// QUERY DATA STATISTIK
// ======================

// Total user pembeli
$pembeli = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='Pembeli'")->fetch_assoc()['total'];

// Total user petani
$petani = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='Petani'")->fetch_assoc()['total'];

// Total transaksi (GMV)
$gmv = $conn->query("SELECT SUM(total_harga) as total FROM transaksi")->fetch_assoc()['total'] ?? 0;

// Total lahan
$total_lahan = $conn->query("SELECT COUNT(*) as total FROM lahan")->fetch_assoc()['total'];

// Total lahan diminati
$lahan_diminati = $conn->query("SELECT COUNT(DISTINCT lahan_id) as total FROM pengajuan_minat")->fetch_assoc()['total'];

// Total produk
$total_produk = $conn->query("SELECT COUNT(*) as total FROM produk")->fetch_assoc()['total'];

?>

<main class="flex-1 p-6 bg-cream-100 min-h-screen">

    <h2 class="text-2xl font-bold text-gray-700 mb-6">
        Dashboard Statistik
    </h2>

    <!-- GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- USER -->
        <div class="bg-white p-5 rounded-2xl shadow">
            <h3 class="text-gray-500 text-sm">Total Pembeli</h3>
            <p class="text-3xl font-bold text-green-600"><?= $pembeli ?></p>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow">
            <h3 class="text-gray-500 text-sm">Total Petani</h3>
            <p class="text-3xl font-bold text-green-700"><?= $petani ?></p>
        </div>

        <!-- GMV -->
        <div class="bg-white p-5 rounded-2xl shadow">
            <h3 class="text-gray-500 text-sm">Total Transaksi (GMV)</h3>
            <p class="text-2xl font-bold text-blue-600">
                Rp <?= number_format($gmv,0,',','.') ?>
            </p>
        </div>

        <!-- PRODUK -->
        <div class="bg-white p-5 rounded-2xl shadow">
            <h3 class="text-gray-500 text-sm">Total Produk</h3>
            <p class="text-3xl font-bold text-purple-600"><?= $total_produk ?></p>
        </div>

    </div>

    <!-- SECTION 2 -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

        <!-- LAHAN -->
        <div class="bg-white p-6 rounded-2xl shadow">
            <h3 class="text-lg font-semibold mb-3 text-green-700">
                Statistik Lahan
            </h3>

            <div class="flex justify-between mb-2">
                <span>Total Lahan</span>
                <span class="font-bold"><?= $total_lahan ?></span>
            </div>

            <div class="flex justify-between">
                <span>Lahan Diminati</span>
                <span class="font-bold text-green-600"><?= $lahan_diminati ?></span>
            </div>
        </div>

        <!-- USER COMPOSITION -->
        <div class="bg-white p-6 rounded-2xl shadow">
            <h3 class="text-lg font-semibold mb-3 text-blue-700">
                Komposisi User
            </h3>

            <div class="flex justify-between mb-2">
                <span>Pembeli</span>
                <span class="font-bold"><?= $pembeli ?></span>
            </div>

            <div class="flex justify-between">
                <span>Petani</span>
                <span class="font-bold"><?= $petani ?></span>
            </div>
        </div>

    </div>

</main>

<?php require_once 'footer.php'; ?>