<?php
require_once 'header.php';
require_once 'sidebar.php';

// CEK ROLE
if ($_SESSION['role'] !== 'Petani') {
    echo "<script>alert('Akses ditolak!'); window.location.href='index.php';</script>";
    exit;
}

$petani_id = $_SESSION['user_id'];
$pesan = '';

// ==========================
// HAPUS PRODUK
// ==========================
if (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['id'])) {

    $id_produk = (int) $_GET['id'];

    // ambil foto
    $cek = $conn->query("SELECT foto_produk FROM produk WHERE id=$id_produk AND petani_id=$petani_id");

    if ($cek && $cek->num_rows > 0) {
        $data = $cek->fetch_assoc();

        if (!empty($data['foto_produk']) && file_exists('uploads/' . $data['foto_produk'])) {
            unlink('uploads/' . $data['foto_produk']);
        }
    }

    $hapus = $conn->query("DELETE FROM produk WHERE id=$id_produk AND petani_id=$petani_id");

    if ($hapus) {
        $pesan = '<div class="bg-green-100 text-green-700 p-4 rounded-lg mb-4 text-sm">Produk berhasil dihapus!</div>';
    } else {
        $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded-lg mb-4 text-sm">Gagal menghapus produk!</div>';
    }
}

// ==========================
// AMBIL DATA PRODUK
// ==========================
$query_produk = $conn->query("SELECT * FROM produk WHERE petani_id=$petani_id ORDER BY id DESC");
?>

<main class="flex-1 flex flex-col h-full bg-gray-100">

    <div class="p-6 md:p-8">

        <div class="max-w-6xl mx-auto">

            <!-- HEADER -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-700">Daftar Produk Saya</h2>
                    <p class="text-sm text-gray-500">Kelola produk Anda</p>
                </div>

                <a href="tambah_produk.php"
                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    + Tambah Produk
                </a>
            </div>

            <?= $pesan ?>

            <!-- GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <?php if ($query_produk && $query_produk->num_rows > 0): ?>

                    <?php while ($row = $query_produk->fetch_assoc()): ?>

                        <div class="bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden flex flex-col">

                            <!-- FOTO -->
                            <div class="h-40 bg-gray-200">
                                <?php if (!empty($row['foto_produk'])): ?>
                                    <img src="uploads/<?= $row['foto_produk'] ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="flex items-center justify-center h-full text-gray-400">
                                        Tidak ada foto
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- BODY -->
                            <div class="p-4 flex flex-col flex-1">

                                <!-- NAMA -->
                                <h3 class="font-semibold text-gray-800 mb-2">
                                    <?= htmlspecialchars($row['nama_produk'] ?? '-') ?>
                                </h3>

                                <!-- HARGA -->
                                <p class="text-green-600 font-bold mb-2">
                                    Rp <?= number_format($row['harga'] ?? 0, 0, ',', '.') ?>
                                    <?php if (!empty($row['satuan'])): ?>
                                        / <?= htmlspecialchars($row['satuan']) ?>
                                    <?php endif; ?>
                                </p>

                                <!-- STOK -->
                                <p class="text-sm text-gray-500 mb-3">
                                    Stok: 
                                    <span class="font-semibold">
                                        <?= htmlspecialchars($row['stok'] ?? 0) ?>
                                        <?= !empty($row['satuan']) ? htmlspecialchars($row['satuan']) : '' ?>
                                    </span>
                                </p>

                                <!-- KATEGORI (AMAN) -->
                                <?php if (!empty($row['kategori'])): ?>
                                    <p class="text-xs text-gray-400 mb-2">
                                        Kategori: <?= htmlspecialchars($row['kategori']) ?>
                                    </p>
                                <?php endif; ?>

                                <!-- BUTTON -->
                                <div class="mt-auto flex gap-2">

                                    <a href="edit_produk.php?id=<?= $row['id'] ?>"
                                       class="flex-1 text-center bg-gray-100 py-2 rounded text-sm hover:bg-gray-200">
                                        Edit
                                    </a>

                                    <a href="produk_saya.php?action=hapus&id=<?= $row['id'] ?>"
                                       onclick="return confirm('Yakin hapus produk ini?')"
                                       class="flex-1 text-center bg-red-100 text-red-600 py-2 rounded text-sm hover:bg-red-200">
                                        Hapus
                                    </a>

                                </div>

                            </div>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <!-- KOSONG -->
                    <div class="col-span-full text-center bg-white p-10 rounded-xl shadow">

                        <div class="text-4xl mb-3">📦</div>

                        <h3 class="font-bold text-gray-700 mb-2">
                            Belum ada produk
                        </h3>

                        <p class="text-gray-500 mb-4">
                            Silakan tambahkan produk pertama Anda
                        </p>

                        <a href="tambah_produk.php"
                           class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Tambah Sekarang
                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</main>

<?php require_once 'footer.php'; ?>