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
   HAPUS ITEM
========================= */
if (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['id'])) {
    $id_keranjang = (int)$_GET['id'];

    $conn->query("DELETE FROM keranjang 
                  WHERE id = $id_keranjang 
                  AND pembeli_id = $pembeli_id");

    $pesan_notif = '<div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 text-sm">Produk dihapus dari keranjang.</div>';
}

/* =========================
   UPDATE QTY
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_qty'])) {

    $id_keranjang = (int)$_POST['id_keranjang'];
    $qty_baru = (int)$_POST['qty'];

    // Ambil stok
    $cek = $conn->query("
        SELECT p.stok, p.nama_produk 
        FROM keranjang k
        JOIN produk p ON k.produk_id = p.id
        WHERE k.id = $id_keranjang
    ");

    if ($cek->num_rows > 0) {
        $data = $cek->fetch_assoc();

        if ($qty_baru > $data['stok']) {
            $pesan_notif = "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-6 text-sm'>
            Stok {$data['nama_produk']} hanya {$data['stok']}
            </div>";

        } elseif ($qty_baru < 1) {
            $conn->query("DELETE FROM keranjang 
                          WHERE id = $id_keranjang 
                          AND pembeli_id = $pembeli_id");

            $pesan_notif = '<div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 text-sm">Produk dihapus.</div>';

        } else {
            $conn->query("UPDATE keranjang 
                          SET jumlah = $qty_baru 
                          WHERE id = $id_keranjang 
                          AND pembeli_id = $pembeli_id");

            $pesan_notif = '<div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 text-sm">Jumlah diperbarui.</div>';
        }
    }
}

/* =========================
   AMBIL DATA KERANJANG
========================= */
$sql = "
SELECT 
    k.id AS keranjang_id,
    k.jumlah,
    p.nama_produk,
    p.harga,
    p.satuan,
    p.foto_produk,
    p.stok,
    u.nama_lengkap AS nama_petani
FROM keranjang k
JOIN produk p ON k.produk_id = p.id
JOIN users u ON p.petani_id = u.id
WHERE k.pembeli_id = $pembeli_id
ORDER BY k.id DESC
";

$query = $conn->query($sql);

$total = 0;
?>

<main class="flex-1 p-6 bg-cream">

    <h2 class="text-2xl font-bold mb-4">Keranjang</h2>

    <?= $pesan_notif ?>

    <?php if($query->num_rows > 0): ?>

        <div class="grid md:grid-cols-3 gap-6">

            <!-- LIST PRODUK -->
            <div class="md:col-span-2 space-y-4">

                <?php while($row = $query->fetch_assoc()): 
                    $subtotal = $row['harga'] * $row['jumlah'];
                    $total += $subtotal;
                ?>

                <div class="bg-white p-4 rounded shadow flex gap-4">

                    <img src="uploads/<?= $row['foto_produk'] ?>" 
                         class="w-24 h-24 object-cover rounded">

                    <div class="flex-1">
                        <h3 class="font-bold"><?= $row['nama_produk'] ?></h3>
                        <p class="text-sm text-gray-500">
                            Petani: <?= $row['nama_petani'] ?>
                        </p>

                        <p class="text-green-600 font-bold mt-1">
                            Rp <?= number_format($row['harga'],0,',','.') ?>
                        </p>

                        <p class="text-xs text-gray-400">
                            Stok: <?= $row['stok'] ?>
                        </p>
                    </div>

                    <div class="flex flex-col justify-between items-end">

                        <form method="POST" class="flex gap-2">
                            <input type="hidden" name="id_keranjang" value="<?= $row['keranjang_id'] ?>">
                            
                            <input type="number" name="qty" value="<?= $row['jumlah'] ?>" 
                                   min="1" max="<?= $row['stok'] ?>"
                                   class="w-16 border rounded text-center">

                            <button name="update_qty" 
                                    class="bg-gray-200 px-2 rounded text-sm">
                                ✔
                            </button>
                        </form>

                        <a href="keranjang.php?action=hapus&id=<?= $row['keranjang_id'] ?>" 
                           class="text-red-500 text-sm"
                           onclick="return confirm('Hapus?')">
                           Hapus
                        </a>

                        <p class="font-bold">
                            Rp <?= number_format($subtotal,0,',','.') ?>
                        </p>

                    </div>

                </div>

                <?php endwhile; ?>

            </div>

            <!-- SUMMARY -->
            <div class="bg-white p-4 rounded shadow h-fit">

                <h3 class="font-bold mb-3">Ringkasan</h3>

                <div class="flex justify-between mb-2">
                    <span>Total</span>
                    <span>Rp <?= number_format($total,0,',','.') ?></span>
                </div>

                <a href="checkout.php" 
                   class="block mt-4 bg-green-600 text-white text-center py-2 rounded">
                   Checkout
                </a>

            </div>

        </div>

    <?php else: ?>

        <div class="bg-white p-10 text-center rounded">
            <h3 class="text-lg font-bold mb-2">Keranjang kosong</h3>
            <a href="belanja.php" class="text-green-600">Belanja sekarang</a>
        </div>

    <?php endif; ?>

</main>

<?php require_once 'footer.php'; ?>