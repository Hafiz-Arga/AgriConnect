<?php
require_once 'header.php';
require_once 'sidebar.php';

// Pastikan hanya Pembeli yang bisa mengakses
if ($_SESSION['role'] !== 'Pembeli') {
    echo "<script>alert('Akses ditolak!'); window.location.href='index.php';</script>";
    exit;
}

$pembeli_id = $_SESSION['user_id'];
$pesan_notif = '';

// Ambil data keranjang saat ini
$sql_keranjang = "SELECT k.id as keranjang_id, k.jumlah, k.produk_id, 
                         p.nama_produk, p.harga, p.satuan, p.stok, p.petani_id
                  FROM keranjang k 
                  JOIN produk p ON k.produk_id = p.id 
                  WHERE k.pembeli_id = $pembeli_id";
$query_keranjang = $conn->query($sql_keranjang);

// Jika keranjang kosong, kembalikan ke halaman belanja
if ($query_keranjang->num_rows == 0) {
    echo "<script>alert('Keranjang Anda kosong!'); window.location.href='belanja.php';</script>";
    exit;
}

$total_belanja = 0;
$items = [];
while ($row = $query_keranjang->fetch_assoc()) {
    $total_belanja += ($row['harga'] * $row['jumlah']);
    $items[] = $row;
}

// Ambil alamat default pembeli jika ada
$query_user = $conn->query("SELECT alamat_pengiriman FROM users WHERE id = $pembeli_id");
$data_user = $query_user->fetch_assoc();
$alamat_default = $data_user['alamat_pengiriman'] ?? '';

// Proses Checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proses_checkout'])) {
    $alamat_tujuan = $conn->real_escape_string($_POST['alamat_tujuan']);
    $metode_pembayaran = $conn->real_escape_string($_POST['metode_pembayaran']);
    
    // Generate Kode VA Dummy jika metode transfer bank
    $kode_va = ($metode_pembayaran == 'Transfer Bank') ? 'VA' . rand(10000000, 99999999) : '';
    
    // Gunakan Transaction untuk memastikan semua query berhasil atau gagal bersamaan
    $conn->begin_transaction();
    
    try {
        // 1. Insert ke tabel pesanan
        $sql_pesanan = "INSERT INTO pesanan (pembeli_id, total_harga, alamat_tujuan, metode_pembayaran, kode_va, status_pembayaran, status_pesanan) 
                        VALUES ('$pembeli_id', '$total_belanja', '$alamat_tujuan', '$metode_pembayaran', '$kode_va', 'Belum Bayar', 'Menunggu Konfirmasi')";
        $conn->query($sql_pesanan);
        $pesanan_id = $conn->insert_id;

        // 2. Loop setiap item di keranjang
        foreach ($items as $item) {
            $produk_id = $item['produk_id'];
            $jumlah = $item['jumlah'];
            $harga_saat_beli = $item['harga'];
            
            // Insert ke tabel detail_pesanan
            $sql_detail = "INSERT INTO detail_pesanan (pesanan_id, produk_id, jumlah, harga_saat_beli) 
                           VALUES ('$pesanan_id', '$produk_id', '$jumlah', '$harga_saat_beli')";
            $conn->query($sql_detail);
            
            // Kurangi stok di tabel produk
            $sql_stok = "UPDATE produk SET stok = stok - $jumlah WHERE id = $produk_id";
            $conn->query($sql_stok);
        }

        // 3. Kosongkan keranjang pembeli ini
        $conn->query("DELETE FROM keranjang WHERE pembeli_id = $pembeli_id");

        // Commit transaksi (simpan permanen)
        $conn->commit();
        
        // Redirect ke halaman sukses / riwayat pesanan (nanti kita buat)
        echo "<script>alert('Pesanan berhasil dibuat!'); window.location.href='riwayat_pesanan.php';</script>";
        exit;
        
    } catch (Exception $e) {
        $conn->rollback(); // Batalkan semua query jika ada error
        $pesan_notif = '<div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6 text-sm font-medium">Terjadi kesalahan sistem: ' . $e->getMessage() . '</div>';
    }
}
?>

<main class="flex-1 flex flex-col h-full overflow-hidden bg-cream">
    <header class="md:hidden bg-white shadow-sm p-4 flex justify-between items-center z-10">
        <h1 class="text-xl font-serif text-leaf font-bold">🌱 TaniDirect</h1>
    </header>

    <div class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="max-w-4xl mx-auto">
            
            <div class="mb-8">
                <a href="keranjang.php" class="text-leaf text-sm font-semibold hover:underline mb-2 inline-block">&larr; Kembali ke Keranjang</a>
                <h2 class="text-3xl font-serif font-bold text-soil">Checkout Pesanan</h2>
                <p class="opacity-70 mt-1 text-sm">Lengkapi detail pengiriman dan pembayaran Anda.</p>
            </div>

            <?= $pesan_notif ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-6">
                    <form action="" method="POST" id="formCheckout">
                        
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-6">
                            <h3 class="font-bold text-soil text-lg mb-4 flex items-center gap-2">📍 Alamat Pengiriman</h3>
                            <textarea name="alamat_tujuan" rows="4" required placeholder="Tuliskan alamat lengkap (Jalan, RT/RW, Desa/Kelurahan, Kecamatan, Kota/Kabupaten, Kode Pos)..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-1 focus:ring-leaf focus:border-leaf outline-none text-sm"><?= htmlspecialchars($alamat_default) ?></textarea>
                            <p class="text-xs text-gray-500 mt-2">Pastikan alamat terisi lengkap agar petani mudah dalam mengirimkan pesanan.</p>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                            <h3 class="font-bold text-soil text-lg mb-4 flex items-center gap-2">💳 Metode Pembayaran</h3>
                            
                            <div class="space-y-3">
                                <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition border-leaf bg-mist">
                                    <input type="radio" name="metode_pembayaran" value="Transfer Bank" checked class="w-4 h-4 text-leaf focus:ring-leaf">
                                    <div class="ml-3">
                                        <span class="block text-sm font-bold text-soil">Transfer Bank (Virtual Account)</span>
                                        <span class="block text-xs text-gray-500">Mendukung BCA, Mandiri, BNI, BRI.</span>
                                    </div>
                                </label>
                                
                                <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                    <input type="radio" name="metode_pembayaran" value="COD" class="w-4 h-4 text-leaf focus:ring-leaf">
                                    <div class="ml-3">
                                        <span class="block text-sm font-bold text-soil">Cash on Delivery (COD)</span>
                                        <span class="block text-xs text-gray-500">Bayar di tempat saat pesanan tiba.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                    </form>
                </div>

                <div class="w-full">
                    <div class="bg-white rounded-2xl shadow-sm border border-wheat p-6 sticky top-6">
                        <h3 class="font-bold text-soil text-lg mb-4 pb-3 border-b border-gray-100">Ringkasan Pesanan</h3>
                        
                        <div class="space-y-4 mb-4">
                            <?php foreach($items as $item): ?>
                                <div class="flex justify-between items-start text-sm">
                                    <div class="flex-1 pr-4">
                                        <p class="font-semibold text-soil line-clamp-1"><?= htmlspecialchars($item['nama_produk']) ?></p>
                                        <p class="text-xs text-gray-500"><?= $item['jumlah'] ?> <?= htmlspecialchars($item['satuan']) ?> x Rp <?= number_format($item['harga'],0,',','.') ?></p>
                                    </div>
                                    <span class="font-bold text-soil">Rp <?= number_format($item['harga'] * $item['jumlah'],0,',','.') ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="pt-4 border-t border-gray-100 mb-6 space-y-2">
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <span>Subtotal</span>
                                <span>Rp <?= number_format($total_belanja,0,',','.') ?></span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <span>Biaya Layanan</span>
                                <span>Gratis</span>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-200 mb-6 flex justify-between items-center">
                            <span class="font-bold text-soil text-lg">Total Bayar</span>
                            <span class="font-bold text-leaf text-xl">Rp <?= number_format($total_belanja,0,',','.') ?></span>
                        </div>

                        <button type="submit" form="formCheckout" name="proses_checkout" class="block w-full text-center px-4 py-3 bg-leaf text-white font-bold rounded-lg hover:bg-sprout transition shadow-sm text-lg">
                            Buat Pesanan
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>