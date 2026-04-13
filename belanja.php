<?php
require_once 'header.php';
require_once 'sidebar.php';

// Hanya Pembeli
if ($_SESSION['role'] !== 'Pembeli') {
    echo "<script>alert('Akses ditolak!'); window.location.href='index.php';</script>";
    exit;
}

$pembeli_id = $_SESSION['user_id'];
$pesan_notif = '';

/* =========================
   TAMBAH KE KERANJANG
========================= */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {

    $produk_id = (int)$_POST['produk_id'];
    $jumlah_tambah = 1;

    // Ambil data produk
    $cek_produk = $conn->query("SELECT stok, nama_produk FROM produk WHERE id = $produk_id");

    if ($cek_produk->num_rows == 0) {
        $pesan_notif = '<div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6">Produk tidak ditemukan!</div>';
    } else {

        $produk = $cek_produk->fetch_assoc();

        // Cek apakah sudah ada di keranjang
        $cek_keranjang = $conn->query("
            SELECT id, jumlah 
            FROM keranjang 
            WHERE pembeli_id = $pembeli_id 
            AND produk_id = $produk_id
        ");

        if ($cek_keranjang->num_rows > 0) {

            $data = $cek_keranjang->fetch_assoc();
            $jumlah_baru = $data['jumlah'] + $jumlah_tambah;

            if ($jumlah_baru > $produk['stok']) {
                $pesan_notif = '<div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6">
                Stok '.$produk['nama_produk'].' tidak cukup!
                </div>';
            } else {
                $conn->query("
                    UPDATE keranjang 
                    SET jumlah = $jumlah_baru 
                    WHERE id = ".$data['id']."
                ");

                $pesan_notif = '<div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 flex justify-between">
                    <span>Jumlah produk berhasil ditambahkan!</span>
                    <a href="keranjang.php" class="underline font-bold">Lihat Keranjang →</a>
                </div>';
            }

        } else {

            if ($jumlah_tambah > $produk['stok']) {
                $pesan_notif = '<div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6">
                Stok tidak cukup!
                </div>';
            } else {
                $conn->query("
                    INSERT INTO keranjang (pembeli_id, produk_id, jumlah) 
                    VALUES ($pembeli_id, $produk_id, $jumlah_tambah)
                ");

                $pesan_notif = '<div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 flex justify-between">
                    <span>Produk berhasil ditambahkan ke keranjang!</span>
                    <a href="keranjang.php" class="underline font-bold">Lihat Keranjang →</a>
                </div>';
            }
        }
    }
}

/* =========================
   FILTER & SEARCH
========================= */
$search_keyword = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$kategori_filter = isset($_GET['kategori']) ? $conn->real_escape_string($_GET['kategori']) : '';

$sql = "SELECT p.*, u.nama_lengkap AS nama_petani 
        FROM produk p 
        JOIN users u ON p.petani_id = u.id 
        WHERE p.stok > 0";

if ($search_keyword !== '') {
    $sql .= " AND p.nama_produk LIKE '%$search_keyword%'";
}

if ($kategori_filter !== '') {
    $sql .= " AND p.kategori = '$kategori_filter'";
}

$sql .= " ORDER BY p.created_at DESC";

$query_produk = $conn->query($sql);
?>

<main class="flex-1 flex flex-col h-full overflow-hidden bg-cream">

    <header class="md:hidden bg-white shadow-sm p-4 flex justify-between items-center">
        <h1 class="text-xl font-bold text-leaf">🌱 TaniDirect</h1>
        <a href="keranjang.php">🛒</a>
    </header>

    <div class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="max-w-6xl mx-auto">

            <h2 class="text-3xl font-bold mb-2">Pasar Hasil Panen</h2>
            <p class="text-gray-500 mb-6">Beli langsung dari petani</p>

            <?= $pesan_notif ?>

            <!-- FILTER -->
            <form method="GET" class="flex flex-wrap gap-2 mb-6">
                <select name="kategori" class="border px-3 py-2 rounded">
                    <option value="">Semua</option>
                    <option value="Sayuran">Sayuran</option>
                    <option value="Buah-buahan">Buah</option>
                </select>

                <input type="text" name="search" placeholder="Cari..." 
                       class="border px-3 py-2 rounded">

                <button class="bg-green-600 text-white px-4 rounded">Cari</button>
            </form>

            <!-- PRODUK -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                <?php if($query_produk->num_rows > 0): ?>
                    <?php while($row = $query_produk->fetch_assoc()): ?>

                        <div class="bg-white p-3 rounded shadow flex flex-col">

                            <img src="uploads/<?= $row['foto_produk'] ?>" 
                                 class="h-40 object-cover rounded mb-2">

                            <h3 class="font-bold text-sm"><?= $row['nama_produk'] ?></h3>

                            <p class="text-xs text-gray-500">
                                <?= $row['nama_petani'] ?>
                            </p>

                            <p class="text-green-600 font-bold mt-1">
                                Rp <?= number_format($row['harga'],0,',','.') ?>
                            </p>

                            <p class="text-xs text-gray-400">
                                Stok: <?= $row['stok'] ?>
                            </p>

                            <form method="POST" class="mt-auto">
                                <input type="hidden" name="produk_id" value="<?= $row['id'] ?>">
                                
                                <button name="add_to_cart"
                                        class="mt-2 bg-green-600 text-white py-1 rounded text-sm">
                                    + Keranjang
                                </button>
                            </form>

                        </div>

                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Produk tidak ditemukan</p>
                <?php endif; ?>

            </div>

        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>