<?php 
require_once 'header.php'; 
require_once 'sidebar.php'; 

// Hanya Petani
if ($_SESSION['role'] !== 'Petani') {
    echo "<script>alert('Akses ditolak!'); window.location.href='index.php';</script>";
    exit;
}

$pesan = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $petani_id      = $_SESSION['user_id'];
    $komoditas      = $conn->real_escape_string($_POST['komoditas']);
    $luas_lahan     = $conn->real_escape_string($_POST['luas_lahan']);
    $estimasi_panen = $_POST['estimasi_panen'];
    $harga_min      = (int)$_POST['harga_min'];
    $harga_max      = (int)$_POST['harga_max'];
    $bisa_nego      = isset($_POST['bisa_nego']) ? 1 : 0;
    $alamat_lahan   = $conn->real_escape_string($_POST['alamat_lahan']);
    $link_gmaps     = $conn->real_escape_string($_POST['link_gmaps']);
    $deskripsi      = $conn->real_escape_string($_POST['deskripsi']);

    $foto_lahan = "";

    // ======================
    // UPLOAD FOTO
    // ======================
    if (isset($_FILES['foto_lahan']) && $_FILES['foto_lahan']['error'] == 0) {

        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['foto_lahan']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png'];

        if (!in_array($ext, $allowed)) {
            $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded">Format harus JPG/PNG!</div>';
        } elseif ($_FILES['foto_lahan']['size'] > 2097152) {
            $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded">Ukuran max 2MB!</div>';
        } else {

            $nama_file_baru = time() . '_' . $petani_id . '.' . $ext;
            $target = 'uploads/' . $nama_file_baru;

            if (move_uploaded_file($_FILES['foto_lahan']['tmp_name'], $target)) {
                $foto_lahan = $nama_file_baru;
            } else {
                $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded">Gagal upload foto!</div>';
            }
        }
    }

    // ======================
    // VALIDASI TAMBAHAN
    // ======================
    if ($harga_max < $harga_min) {
        $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded">Harga max tidak boleh lebih kecil dari harga min!</div>';
    }

    // ======================
    // INSERT DATABASE
    // ======================
    if (empty($pesan)) {

        $sql = "INSERT INTO lahan 
        (petani_id, komoditas, luas_lahan, estimasi_panen, deskripsi, harga_min, harga_max, bisa_nego, alamat_lahan, link_gmaps, foto_lahan, status) 
        VALUES 
        ('$petani_id', '$komoditas', '$luas_lahan', '$estimasi_panen', '$deskripsi', '$harga_min', '$harga_max', '$bisa_nego', '$alamat_lahan', '$link_gmaps', '$foto_lahan', 'pending')";

        if ($conn->query($sql)) {

            $pesan = '
            <div class="bg-green-100 text-green-800 p-4 rounded-lg shadow">
                ✅ Pengajuan berhasil! <br>
                ⏳ Status: <b>Menunggu verifikasi admin</b>
            </div>';

        } else {
            $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded">Error DB: '.$conn->error.'</div>';
        }
    }
}
?>

<main class="flex-1 p-6 bg-gray-100">

    <h2 class="text-2xl font-bold mb-4">Tambah Lahan</h2>

    <?= $pesan ?>

    <div class="bg-white p-6 rounded shadow">

        <form method="POST" enctype="multipart/form-data">

            <input type="text" name="komoditas" placeholder="Komoditas" required class="w-full mb-3 p-2 border rounded">

            <input type="text" name="luas_lahan" placeholder="Luas Lahan" required class="w-full mb-3 p-2 border rounded">

            <input type="date" name="estimasi_panen" required class="w-full mb-3 p-2 border rounded">

            <input type="number" name="harga_min" placeholder="Harga Min" required class="w-full mb-3 p-2 border rounded">

            <input type="number" name="harga_max" placeholder="Harga Max" required class="w-full mb-3 p-2 border rounded">

            <textarea name="alamat_lahan" placeholder="Alamat" required class="w-full mb-3 p-2 border rounded"></textarea>

            <input type="url" name="link_gmaps" placeholder="Link Maps" required class="w-full mb-3 p-2 border rounded">

            <textarea name="deskripsi" placeholder="Deskripsi" class="w-full mb-3 p-2 border rounded"></textarea>

            <input type="file" name="foto_lahan" required class="mb-3">

            <label class="block mb-3">
                <input type="checkbox" name="bisa_nego" checked> Bisa nego
            </label>

            <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Simpan
            </button>

        </form>

    </div>

</main>

<?php require_once 'footer.php'; ?>