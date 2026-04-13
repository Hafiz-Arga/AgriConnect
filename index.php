<?php
require_once 'header.php';
require_once 'sidebar.php';

// Proteksi login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

?>

<main class="flex-1 flex flex-col h-full overflow-hidden bg-cream pt-10 md:pt-0">
<div class="flex-1 overflow-y-auto p-6 md:p-8">
<div class="max-w-6xl mx-auto">

<?php if ($role === 'Admin'): ?>

    <!-- =========================
         DASHBOARD ADMIN (STATISTIK)
    ========================== -->

    <?php
    $total_pembeli = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='Pembeli'")->fetch_assoc()['total'];
    $total_petani  = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='Petani'")->fetch_assoc()['total'];
    $total_produk  = $conn->query("SELECT COUNT(*) as total FROM produk")->fetch_assoc()['total'];

    // Aman kalau belum ada tabel transaksi
    $total_gmv = 0;
    ?>

    <h2 class="text-2xl font-bold mb-6 text-gray-700">
        Dashboard Statistik
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

        <div class="bg-white p-5 rounded-xl shadow">
            <p class="text-gray-500 text-sm">Total Pembeli</p>
            <h3 class="text-2xl font-bold text-green-600"><?= $total_pembeli ?></h3>
        </div>

        <div class="bg-white p-5 rounded-xl shadow">
            <p class="text-gray-500 text-sm">Total Petani</p>
            <h3 class="text-2xl font-bold text-green-600"><?= $total_petani ?></h3>
        </div>

        <div class="bg-white p-5 rounded-xl shadow">
            <p class="text-gray-500 text-sm">Total Transaksi (GMV)</p>
            <h3 class="text-2xl font-bold text-blue-600">Rp <?= number_format($total_gmv) ?></h3>
        </div>

        <div class="bg-white p-5 rounded-xl shadow">
            <p class="text-gray-500 text-sm">Total Produk</p>
            <h3 class="text-2xl font-bold text-purple-600"><?= $total_produk ?></h3>
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-5 rounded-xl shadow">
            <h3 class="font-bold text-green-700 mb-3">Statistik User</h3>
            <p>Pembeli: <?= $total_pembeli ?></p>
            <p>Petani: <?= $total_petani ?></p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow">
            <h3 class="font-bold text-blue-700 mb-3">Statistik Produk</h3>
            <p>Total Produk: <?= $total_produk ?></p>
        </div>
    </div>

<?php else: ?>

    <!-- =========================
         DASHBOARD PETANI / PEMBELI (ASLI)
    ========================== -->

    <?php
    $stat1 = 0; $stat2 = 0; $stat3 = 0;

    if ($role === 'Petani') {

        $res1 = $conn->query("SELECT COUNT(*) as total FROM produk WHERE petani_id = $user_id");
        $stat1 = $res1->fetch_assoc()['total'];

        $res2 = $conn->query("SELECT COUNT(DISTINCT p.id) as total 
                              FROM pesanan p 
                              JOIN detail_pesanan dp ON p.id = dp.pesanan_id 
                              JOIN produk pr ON dp.produk_id = pr.id 
                              WHERE pr.petani_id = $user_id AND p.status_pesanan != 'Selesai'");
        $stat2 = $res2->fetch_assoc()['total'];

        $res3 = $conn->query("SELECT COUNT(*) as total FROM lahan WHERE petani_id = $user_id");
        $stat3 = $res3->fetch_assoc()['total'];

        $label1 = "Produk Aktif"; 
        $label2 = "Pesanan Berjalan"; 
        $label3 = "Total Lahan";

    } else {

        $res1 = $conn->query("SELECT COUNT(*) as total FROM pesanan WHERE pembeli_id = $user_id AND status_pesanan != 'Selesai'");
        $stat1 = $res1->fetch_assoc()['total'];

        $res2 = $conn->query("SELECT SUM(jumlah) as total FROM keranjang WHERE pembeli_id = $user_id");
        $stat2 = $res2->fetch_assoc()['total'] ?? 0;

        $res3 = $conn->query("SELECT COUNT(*) as total FROM pengajuan_minat WHERE pembeli_id = $user_id");
        $stat3 = $res3->fetch_assoc()['total'];

        $label1 = "Pesanan Saya"; 
        $label2 = "Isi Keranjang"; 
        $label3 = "Minat Lahan";
    }
    ?>

    <div class="mb-8">
        <h2 class="text-3xl font-serif font-bold text-soil">
            Selamat Datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Pengguna') ?>!
        </h2>
        <p class="opacity-70 mt-1">Berikut adalah ringkasan aktivitas Anda di AgriConnect hari ini.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-6 rounded-2xl shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 bg-green-50 text-leaf rounded-full flex items-center justify-center text-xl">📦</div>
            <div>
                <p class="text-xs text-gray-400"><?= $label1 ?></p>
                <h3 class="text-2xl font-bold"><?= $stat1 ?></h3>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-full flex items-center justify-center text-xl">🛒</div>
            <div>
                <p class="text-xs text-gray-400"><?= $label2 ?></p>
                <h3 class="text-2xl font-bold"><?= $stat2 ?></h3>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-xl">🗺️</div>
            <div>
                <p class="text-xs text-gray-400"><?= $label3 ?></p>
                <h3 class="text-2xl font-bold"><?= $stat3 ?></h3>
            </div>
        </div>
    </div>

<?php endif; ?>

</div>
</div>
</main>

<?php require_once 'footer.php'; ?>