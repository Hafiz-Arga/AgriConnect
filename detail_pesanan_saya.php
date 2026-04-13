<?php
require_once 'header.php';
require_once 'sidebar.php';

if ($_SESSION['role'] !== 'Pembeli') {
    echo "<script>alert('Akses ditolak!'); window.location.href='index.php';</script>";
    exit;
}

$pembeli_id = $_SESSION['user_id'];
$pesanan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql_info = "SELECT * FROM pesanan WHERE id = $pesanan_id AND pembeli_id = $pembeli_id";
$info = $conn->query($sql_info)->fetch_assoc();

if (!$info) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='riwayat_pesanan.php';</script>";
    exit;
}

$sql_items = "SELECT dp.*, pr.nama_produk, pr.satuan, u.nama_lengkap as nama_petani 
              FROM detail_pesanan dp
              JOIN produk pr ON dp.produk_id = pr.id
              JOIN users u ON pr.petani_id = u.id
              WHERE dp.pesanan_id = $pesanan_id";
$items = $conn->query($sql_items);
?>

<main class="flex-1 flex flex-col h-full overflow-hidden bg-cream">
    <div class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="max-w-3xl mx-auto">
            <div class="mb-6 flex justify-between items-end">
                <h2 class="text-2xl font-serif font-bold text-soil">Rincian Belanja #<?= $pesanan_id ?></h2>
                <a href="riwayat_pesanan.php" class="text-leaf text-sm font-semibold hover:underline">Kembali</a>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50 bg-gray-50/50">
                    <p class="text-xs text-gray-500 uppercase font-bold">Status Pesanan</p>
                    <p class="text-lg font-bold text-leaf"><?= $info['status_pesanan'] ?></p>
                </div>
                
                <div class="p-6 space-y-6">
                    <?php while($item = $items->fetch_assoc()): ?>
                        <div class="flex justify-between items-start border-b border-gray-50 pb-4 last:border-0">
                            <div>
                                <p class="font-bold text-soil"><?= htmlspecialchars($item['nama_produk']) ?></p>
                                <p class="text-xs text-gray-400">Petani: <?= htmlspecialchars($item['nama_petani']) ?></p>
                                <p class="text-sm mt-1"><?= $item['jumlah'] ?> <?= htmlspecialchars($item['satuan']) ?> x Rp <?= number_format($item['harga_saat_beli'],0,',','.') ?></p>
                            </div>
                            <p class="font-bold text-soil text-right">Rp <?= number_format($item['jumlah'] * $item['harga_saat_beli'],0,',','.') ?></p>
                        </div>
                    <?php endwhile; ?>

                    <div class="pt-4 flex justify-between items-center">
                        <p class="text-lg font-bold text-soil">Total Pembayaran</p>
                        <p class="text-2xl font-bold text-leaf">Rp <?= number_format($info['total_harga'],0,',','.') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once 'footer.php'; ?>