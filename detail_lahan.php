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

// Ambil ID dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Lahan tidak ditemukan!'); window.location.href='cari_lahan.php';</script>";
    exit;
}

$id_lahan = (int)$_GET['id'];

// =======================
// PROSES AJUKAN MINAT
// =======================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajukan_minat'])) {

    $pesan_singkat = $conn->real_escape_string($_POST['pesan_singkat']);

    $sql_insert = "INSERT INTO pengajuan_minat 
    (lahan_id, pembeli_id, pesan, status) 
    VALUES 
    ('$id_lahan', '$pembeli_id', '$pesan_singkat', 'Menunggu')";

    if ($conn->query($sql_insert)) {
        $pesan_notif = '<div class="bg-green-100 text-green-700 p-4 rounded mb-4">
        Minat berhasil diajukan!
        </div>';
    } else {
        $pesan_notif = '<div class="bg-red-100 text-red-700 p-4 rounded mb-4">
        Error: '.$conn->error.'
        </div>';
    }
}

// =======================
// AMBIL DATA LAHAN (FIX)
// =======================
$sql = "SELECT l.*, u.nama_lengkap AS nama_petani, u.email AS email_petani
        FROM lahan l
        JOIN users u ON l.petani_id = u.id
        WHERE l.id = $id_lahan";

$query = $conn->query($sql);

// Debug jika error
if (!$query) {
    die("Query error: " . $conn->error);
}

// Jika tidak ada data
if ($query->num_rows == 0) {
    echo "<script>alert('Data lahan tidak ditemukan!'); window.location.href='cari_lahan.php';</script>";
    exit;
}

$data = $query->fetch_assoc();

// =======================
// CEK SUDAH AJUKAN MINAT
// =======================
$cek = $conn->query("SELECT status FROM pengajuan_minat 
                     WHERE lahan_id = $id_lahan AND pembeli_id = $pembeli_id");

$sudah = $cek->num_rows > 0;
$status = $sudah ? $cek->fetch_assoc()['status'] : null;
?>

<main class="flex-1 p-6 bg-gray-100">

    <a href="cari_lahan.php" class="text-green-600 mb-4 inline-block">
        ← Kembali
    </a>

    <?= $pesan_notif ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- DETAIL LAHAN -->
        <div class="bg-white p-6 rounded shadow">

            <img src="uploads/<?= $data['foto_lahan'] ?>" 
                 class="w-full h-60 object-cover rounded mb-4">

            <h2 class="text-xl font-bold"><?= $data['komoditas'] ?></h2>

            <p class="text-gray-600 mt-2"><?= $data['deskripsi'] ?></p>

            <div class="mt-4 space-y-2 text-sm">
                <p><b>Luas:</b> <?= $data['luas_lahan'] ?></p>
                <p><b>Panen:</b> <?= $data['estimasi_panen'] ?></p>
                <p><b>Alamat:</b> <?= $data['alamat_lahan'] ?></p>
                <div class="mt-2">
            <a href="<?= $data['link_gmaps'] ?>" target="_blank"
            class="inline-block bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
            📍 Lihat di Google Maps
            </a>
        </div>
                <p><b>Harga:</b> Rp <?= number_format($data['harga_min']) ?> - Rp <?= number_format($data['harga_max']) ?></p>
            </div>

        </div>

        <!-- PROFIL PETANI + MINAT -->
        <div class="bg-white p-6 rounded shadow">

            <h3 class="font-bold mb-2">Petani</h3>
            <p><?= $data['nama_petani'] ?></p>
            <p class="text-sm text-gray-500"><?= $data['email_petani'] ?></p>

            <hr class="my-4">

            <?php if ($sudah): ?>
                <div class="bg-yellow-100 p-3 rounded text-sm">
                    Anda sudah mengajukan minat.<br>
                    Status: <b><?= $status ?></b>
                </div>

            <?php else: ?>
                <form method="POST">

                    <textarea name="pesan_singkat" required
                        class="w-full border p-2 rounded mb-3"
                        placeholder="Tulis pesan ke petani..."></textarea>

                    <button name="ajukan_minat"
                        class="bg-green-600 text-white px-4 py-2 rounded">
                        Ajukan Minat
                    </button>

                </form>
            <?php endif; ?>

        </div>

    </div>

</main>

<?php require_once 'footer.php'; ?>