<?php
require_once 'header.php';
require_once 'sidebar.php';

// Pastikan hanya Petani yang bisa mengakses
if ($_SESSION['role'] !== 'Petani') {
    echo "<script>alert('Akses ditolak!'); window.location.href='index.php';</script>";
    exit;
}

$petani_id = $_SESSION['user_id'];
$pesan = '';

// Fitur 2: Logika untuk Menghapus Lahan
if (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['id'])) {
    $id_lahan = (int)$_GET['id'];
    
    // Opsional: Hapus file foto dari folder uploads
    $cek_foto = $conn->query("SELECT foto_lahan FROM lahan WHERE id = $id_lahan AND petani_id = $petani_id")->fetch_assoc();
    if($cek_foto && file_exists('uploads/' . $cek_foto['foto_lahan'])) {
        unlink('uploads/' . $cek_foto['foto_lahan']);
    }

    $hapus = $conn->query("DELETE FROM lahan WHERE id = $id_lahan AND petani_id = $petani_id");
    if ($hapus) {
        $pesan = '<div class="bg-green-100 text-green-700 p-4 rounded-lg mb-4 text-sm font-medium">Lahan berhasil dihapus!</div>';
    }
}

// Fitur 6: Logika untuk Menutup Lahan Secara Manual
if (isset($_GET['action']) && $_GET['action'] == 'tutup' && isset($_GET['id'])) {
    $id_lahan = (int)$_GET['id'];
    $tutup = $conn->query("UPDATE lahan SET status_listing = 'Tutup' WHERE id = $id_lahan AND petani_id = $petani_id");
    if ($tutup) {
        $pesan = '<div class="bg-green-100 text-green-700 p-4 rounded-lg mb-4 text-sm font-medium">Status lahan berhasil ditutup! Pembeli tidak bisa lagi mengajukan minat.</div>';
    }
}

// Fitur 2: Logika Buka Kembali (Opsional/Tambahan)
if (isset($_GET['action']) && $_GET['action'] == 'buka' && isset($_GET['id'])) {
    $id_lahan = (int)$_GET['id'];
    $conn->query("UPDATE lahan SET status_listing = 'Buka' WHERE id = $id_lahan AND petani_id = $petani_id");
    $pesan = '<div class="bg-green-100 text-green-700 p-4 rounded-lg mb-4 text-sm font-medium">Status lahan berhasil dibuka kembali!</div>';
}

// Ambil semua data lahan milik petani yang sedang login
$query_lahan = $conn->query("SELECT * FROM lahan WHERE petani_id = $petani_id ORDER BY created_at DESC");
?>

<main class="flex-1 flex flex-col h-full overflow-hidden bg-cream">
    <header class="md:hidden bg-white shadow-sm p-4 flex justify-between items-center z-10">
        <h1 class="text-xl font-serif text-leaf font-bold">🌱 TaniDirect</h1>
    </header>

    <div class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="max-w-6xl mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between md:items-end mb-6 gap-4">
                <div>
                    <h2 class="text-2xl font-serif font-bold text-soil">Daftar Lahan Saya</h2>
                    <p class="opacity-70 mt-1 text-sm">Kelola listing lahan Pre-panen Anda di sini.</p>
                </div>
                <a href="tambah_lahan.php" class="inline-block px-4 py-2 bg-leaf text-white rounded-lg font-medium hover:bg-sprout transition text-sm text-center">
                    + Tambah Lahan Baru
                </a>
            </div>

            <?= $pesan ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if($query_lahan->num_rows > 0): ?>
                    <?php while($row = $query_lahan->fetch_assoc()): ?>
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                            <img src="uploads/<?= $row['foto_lahan'] ?>" alt="<?= $row['komoditas'] ?>" class="w-full h-48 object-cover">
                            
                            <div class="p-5 flex-1 flex flex-col">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-bold text-lg text-soil"><?= htmlspecialchars($row['komoditas']) ?></h3>
                                    <?php if($row['status_listing'] == 'Buka'): ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded">BUKA</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded">TUTUP</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="text-sm text-gray-600 mb-4 space-y-1">
                                    <p>📏 Luas: <span class="font-medium"><?= htmlspecialchars($row['luas_lahan']) ?></span></p>
                                    <p>⏱️ Estimasi Panen: <span class="font-medium"><?= date('d M Y', strtotime($row['estimasi_panen'])) ?></span></p>
                                    <p>💰 Harga: <span class="font-medium">Rp <?= number_format($row['harga_min'],0,',','.') ?> - Rp <?= number_format($row['harga_max'],0,',','.') ?></span></p>
                                </div>

                                <div class="mt-auto pt-4 border-t border-gray-100 flex flex-wrap gap-2">
                                    <a href="edit_lahan.php?id=<?= $row['id'] ?>" class="flex-1 text-center px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded hover:bg-gray-200 transition">Edit</a>
                                    
                                    <a href="lahan_saya.php?action=hapus&id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus lahan ini?')" class="flex-1 text-center px-3 py-2 bg-red-50 text-red-600 text-sm font-medium rounded hover:bg-red-100 transition">Hapus</a>
                                    
                                    <?php if($row['status_listing'] == 'Buka'): ?>
                                        <a href="lahan_saya.php?action=tutup&id=<?= $row['id'] ?>" onclick="return confirm('Tutup listing ini? Pembeli tidak akan bisa mengajukan minat lagi.')" class="w-full text-center px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded hover:bg-gray-50 transition mt-1">Tutup Listing (Deal)</a>
                                    <?php else: ?>
                                        <a href="lahan_saya.php?action=buka&id=<?= $row['id'] ?>" class="w-full text-center px-3 py-2 border border-green-500 text-green-600 text-sm font-medium rounded hover:bg-green-50 transition mt-1">Buka Listing Kembali</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full bg-white p-8 rounded-2xl text-center border border-gray-100">
                        <div class="text-4xl mb-3">🌾</div>
                        <h3 class="text-lg font-bold text-soil mb-1">Belum Ada Lahan</h3>
                        <p class="text-gray-500 mb-4">Anda belum menambahkan data listing lahan apapun.</p>
                        <a href="tambah_lahan.php" class="inline-block px-6 py-2 bg-leaf text-white rounded-lg font-medium hover:bg-sprout transition">Tambah Lahan Sekarang</a>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>