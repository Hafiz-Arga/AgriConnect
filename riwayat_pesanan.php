<?php
require_once 'header.php';
require_once 'sidebar.php';

// Pastikan hanya Pembeli yang bisa mengakses
if ($_SESSION['role'] !== 'Pembeli') {
    echo "<script>alert('Akses ditolak!'); window.location.href='index.php';</script>";
    exit;
}

$pembeli_id = $_SESSION['user_id'];

// Ambil semua pesanan milik pembeli ini
$sql = "SELECT * FROM pesanan WHERE pembeli_id = $pembeli_id ORDER BY created_at DESC";
$query_pesanan = $conn->query($sql);
?>

<main class="flex-1 flex flex-col h-full overflow-hidden bg-cream">
    <header class="md:hidden bg-white shadow-sm p-4 flex justify-between items-center z-10">
        <h1 class="text-xl font-serif text-leaf font-bold">🌱 TaniDirect</h1>
    </header>

    <div class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="max-w-5xl mx-auto">
            
            <div class="mb-8">
                <h2 class="text-3xl font-serif font-bold text-soil">Riwayat Pesanan</h2>
                <p class="opacity-70 mt-1 text-sm">Pantau status pembayaran dan pengiriman hasil panen Anda.</p>
            </div>

            <?php if($query_pesanan->num_rows > 0): ?>
                <div class="space-y-6">
                    <?php while($row = $query_pesanan->fetch_assoc()): 
                        // Warna Label Status
                        $bg_bayar = $row['status_pembayaran'] == 'Sudah Dibayar' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700';
                        $bg_status = 'bg-blue-100 text-blue-700';
                        if($row['status_pesanan'] == 'Selesai') $bg_status = 'bg-green-100 text-green-700';
                        if($row['status_pesanan'] == 'Menunggu Konfirmasi') $bg_status = 'bg-gray-100 text-gray-700';
                    ?>
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-4 md:p-6 border-b border-gray-50 flex flex-wrap justify-between items-center gap-4">
                                <div>
                                    <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">ID Pesanan: #<?= $row['id'] ?></p>
                                    <p class="text-sm text-gray-500"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></p>
                                </div>
                                <div class="flex gap-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold <?= $bg_bayar ?>"><?= $row['status_pembayaran'] ?></span>
                                    <span class="px-3 py-1 rounded-full text-xs font-bold <?= $bg_status ?>"><?= $row['status_pesanan'] ?></span>
                                </div>
                            </div>

                            <div class="p-4 md:p-6 flex flex-col md:flex-row gap-6">
                                <div class="flex-1">
                                    <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Detail Pengiriman</h4>
                                    <p class="text-sm text-soil leading-relaxed mb-4"><?= nl2br(htmlspecialchars($row['alamat_tujuan'])) ?></p>
                                    
                                    <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">Metode Pembayaran</h4>
                                    <p class="text-sm text-soil font-semibold"><?= $row['metode_pembayaran'] ?></p>
                                    
                                    <?php if($row['status_pembayaran'] == 'Belum Bayar' && $row['metode_pembayaran'] == 'Transfer Bank'): ?>
                                        <div class="mt-3 p-3 bg-mist border border-leaf/20 rounded-lg">
                                            <p class="text-xs text-leaf font-bold uppercase">Nomor Virtual Account:</p>
                                            <p class="text-lg font-mono font-bold text-soil"><?= $row['kode_va'] ?></p>
                                            <p class="text-[10px] text-gray-500 mt-1">*Silakan transfer sesuai total tagihan agar pesanan diproses petani.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="w-full md:w-64 border-t md:border-t-0 md:border-l border-gray-100 pt-4 md:pt-0 md:pl-6 flex flex-col justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500">Total Tagihan:</p>
                                        <p class="text-2xl font-bold text-leaf">Rp <?= number_format($row['total_harga'],0,',','.') ?></p>
                                    </div>
                                    
                                    <div class="mt-6 space-y-2">
                                        <a href="detail_pesanan_saya.php?id=<?= $row['id'] ?>" class="block w-full text-center py-2 border border-leaf text-leaf text-sm font-bold rounded-lg hover:bg-leaf hover:text-white transition">
                                            Lihat Produk
                                        </a>
                                        
                                        <?php if($row['status_pesanan'] == 'Dikirim'): ?>
                                            <button class="w-full py-2 bg-green-600 text-white text-sm font-bold rounded-lg hover:bg-green-700 transition shadow-sm">
                                                Konfirmasi Selesai
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="bg-white p-12 rounded-2xl text-center border border-gray-100 shadow-sm mt-6">
                    <div class="text-6xl mb-4 opacity-30">📑</div>
                    <h3 class="text-xl font-bold text-soil mb-2">Belum Ada Pesanan</h3>
                    <p class="text-gray-500 mb-6">Anda belum pernah melakukan transaksi produk pasca-panen.</p>
                    <a href="belanja.php" class="inline-block px-6 py-3 bg-leaf text-white font-semibold rounded-lg hover:bg-sprout transition">
                        Belanja Sekarang
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>