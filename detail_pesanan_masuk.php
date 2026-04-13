<?php
require_once 'header.php';
require_once 'sidebar.php';

if ($_SESSION['role'] !== 'Petani') {
    echo "<script>alert('Akses ditolak!'); window.location.href='index.php';</script>";
    exit;
}

$petani_id = $_SESSION['user_id'];
$pesanan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil info umum pesanan & pembeli
$sql_info = "SELECT p.*, u.nama_lengkap, u.email 
             FROM pesanan p 
             JOIN users u ON p.pembeli_id = u.id 
             WHERE p.id = $pesanan_id";
$info = $conn->query($sql_info)->fetch_assoc();

if (!$info) {
    echo "<script>alert('Pesanan tidak ditemukan!'); window.location.href='pesanan_masuk.php';</script>";
    exit;
}

// Ambil produk milik petani ini yang ada dalam pesanan tersebut
$sql_item = "SELECT dp.*, pr.nama_produk, pr.satuan 
             FROM detail_pesanan dp
             JOIN produk pr ON dp.produk_id = pr.id
             WHERE dp.pesanan_id = $pesanan_id AND pr.petani_id = $petani_id";
$items = $conn->query($sql_item);
?>

<main class="flex-1 flex flex-col h-full overflow-hidden bg-cream">
    <div class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="max-w-4xl mx-auto">
            <div class="mb-6">
                <a href="pesanan_masuk.php" class="text-leaf text-sm font-semibold">&larr; Kembali ke Daftar</a>
                <h2 class="text-2xl font-serif font-bold text-soil mt-2">Detail Pesanan #<?= $pesanan_id ?></h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2 space-y-4">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-soil mb-4 border-b pb-2">Daftar Produk yang Dipesan</h3>
                        <div class="space-y-4">
                            <?php while($item = $items->fetch_assoc()): ?>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-semibold text-soil"><?= htmlspecialchars($item['nama_produk']) ?></p>
                                        <p class="text-xs text-gray-500"><?= $item['jumlah'] ?> <?= htmlspecialchars($item['satuan']) ?> x Rp <?= number_format($item['harga_saat_beli'],0,',','.') ?></p>
                                    </div>
                                    <p class="font-bold text-leaf">Rp <?= number_format($item['jumlah'] * $item['harga_saat_beli'],0,',','.') ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-soil mb-3 text-sm">Informasi Pembeli</h3>
                        <p class="text-sm font-medium"><?= htmlspecialchars($info['nama_lengkap']) ?></p>
                        <p class="text-xs text-gray-500 mb-4"><?= htmlspecialchars($info['email']) ?></p>
                        <h3 class="font-bold text-soil mb-2 text-sm border-t pt-3">Alamat Pengiriman</h3>
                        <p class="text-xs text-gray-600 leading-relaxed"><?= nl2br(htmlspecialchars($info['alamat_tujuan'])) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once 'footer.php'; ?>