<?php
require_once 'header.php';
require_once 'sidebar.php';

// Pastikan hanya Petani yang bisa mengakses
if ($_SESSION['role'] !== 'Petani') {
    echo "<script>alert('Akses ditolak!'); window.location.href='index.php';</script>";
    exit;
}

$petani_id = $_SESSION['user_id'];
$pesan_notif = '';

// Update Status Pesanan (Fitur Utama Petani)
if (isset($_POST['update_status'])) {
    $pesanan_id = (int)$_POST['pesanan_id'];
    $status_baru = $conn->real_escape_string($_POST['status_pesanan']);
    
    $update = $conn->query("UPDATE pesanan SET status_pesanan = '$status_baru' WHERE id = $pesanan_id");
    if ($update) {
        $pesan_notif = '<div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 text-sm font-medium">Status pesanan berhasil diperbarui!</div>';
    }
}

// Query untuk mengambil pesanan yang mengandung produk milik petani ini
$sql = "SELECT DISTINCT p.id, p.total_harga, p.status_pesanan, p.status_pembayaran, p.created_at, p.alamat_tujuan, u.nama_lengkap as nama_pembeli
        FROM pesanan p
        JOIN detail_pesanan dp ON p.id = dp.pesanan_id
        JOIN produk pr ON dp.produk_id = pr.id
        JOIN users u ON p.pembeli_id = u.id
        WHERE pr.petani_id = $petani_id
        ORDER BY p.created_at DESC";

$query_pesanan = $conn->query($sql);
?>

<main class="flex-1 flex flex-col h-full overflow-hidden bg-cream">
    <div class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="max-w-6xl mx-auto">
            
            <div class="mb-8">
                <h2 class="text-3xl font-serif font-bold text-soil">Pesanan Masuk</h2>
                <p class="opacity-70 mt-1 text-sm">Kelola pesanan produk pasca-panen dari pelanggan Anda.</p>
            </div>

            <?= $pesan_notif ?>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-soil text-xs uppercase font-bold">
                        <tr>
                            <th class="p-4 border-b">ID / Tanggal</th>
                            <th class="p-4 border-b">Pembeli</th>
                            <th class="p-4 border-b">Status Bayar</th>
                            <th class="p-4 border-b">Status Pesanan</th>
                            <th class="p-4 border-b text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php if($query_pesanan->num_rows > 0): ?>
                            <?php while($row = $query_pesanan->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 border-b">
                                        <span class="font-bold text-soil">#<?= $row['id'] ?></span><br>
                                        <span class="text-xs text-gray-400"><?= date('d/m/Y', strtotime($row['created_at'])) ?></span>
                                    </td>
                                    <td class="p-4 border-b">
                                        <span class="font-medium text-soil"><?= htmlspecialchars($row['nama_pembeli']) ?></span>
                                    </td>
                                    <td class="p-4 border-b">
                                        <span class="px-2 py-1 rounded text-[10px] font-bold <?= $row['status_pembayaran'] == 'Sudah Dibayar' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                                            <?= strtoupper($row['status_pembayaran']) ?>
                                        </span>
                                    </td>
                                    <td class="p-4 border-b">
                                        <form action="" method="POST" class="flex items-center gap-2">
                                            <input type="hidden" name="pesanan_id" value="<?= $row['id'] ?>">
                                            <select name="status_pesanan" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 text-xs rounded p-1 focus:ring-leaf outline-none">
                                                <option value="Menunggu Konfirmasi" <?= $row['status_pesanan'] == 'Menunggu Konfirmasi' ? 'selected' : '' ?>>Menunggu</option>
                                                <option value="Diproses" <?= $row['status_pesanan'] == 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                                                <option value="Dikirim" <?= $row['status_pesanan'] == 'Dikirim' ? 'selected' : '' ?>>Dikirim</option>
                                                <option value="Selesai" <?= $row['status_pesanan'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td class="p-4 border-b text-center">
                                        <a href="detail_pesanan_masuk.php?id=<?= $row['id'] ?>" class="text-leaf font-bold hover:underline">Detail</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="p-12 text-center text-gray-400">Belum ada pesanan masuk.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>