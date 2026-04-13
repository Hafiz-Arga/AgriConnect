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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nama_produk = $conn->real_escape_string($_POST['nama_produk']);
    $kategori    = $conn->real_escape_string($_POST['kategori']);
    $harga       = str_replace('.', '', $_POST['harga']);
    $stok        = (int)$_POST['stok'];
    $satuan      = $conn->real_escape_string($_POST['satuan']);
    $deskripsi   = $conn->real_escape_string($_POST['deskripsi']);
    $foto_produk = '';

    // =========================
    // PASTIKAN FOLDER UPLOAD ADA
    // =========================
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // =========================
    // UPLOAD FOTO
    // =========================
    if (isset($_FILES['foto_produk']) && $_FILES['foto_produk']['error'] == 0) {

        $allowed = ['jpg','jpeg','png'];
        $ext = strtolower(pathinfo($_FILES['foto_produk']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {

            if ($_FILES['foto_produk']['size'] <= 2097152) {

                $nama_file_baru = time() . '_produk_' . $petani_id . '.' . $ext;
                $target = 'uploads/' . $nama_file_baru;

                if (move_uploaded_file($_FILES['foto_produk']['tmp_name'], $target)) {
                    $foto_produk = $nama_file_baru;
                } else {
                    $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded">❌ Upload gagal (server)</div>';
                }

            } else {
                $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded">❌ Ukuran max 2MB</div>';
            }

        } else {
            $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded">❌ Format harus JPG/PNG</div>';
        }

    } else {
        $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded">❌ Foto wajib diupload</div>';
    }

    // =========================
    // INSERT DATABASE
    // =========================
    if (empty($pesan)) {

        $sql = "INSERT INTO produk 
        (petani_id, nama_produk, kategori, harga, stok, satuan, foto_produk, deskripsi) 
        VALUES 
        ('$petani_id', '$nama_produk', '$kategori', '$harga', '$stok', '$satuan', '$foto_produk', '$deskripsi')";

        if ($conn->query($sql)) {

            echo "<script>
                alert('Produk berhasil ditambahkan!');
                window.location.href='produk_saya.php';
            </script>";
            exit;

        } else {
            $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded">
                ❌ Gagal insert: '.$conn->error.'
            </div>';
        }
    }
}
?>

<main class="flex-1 flex flex-col h-full overflow-hidden bg-cream">
    <header class="md:hidden bg-white shadow-sm p-4 flex justify-between items-center z-10">
        <h1 class="text-xl font-serif text-leaf font-bold">AgriConnect</h1>
    </header>

    <div class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="max-w-3xl mx-auto">
            <div class="mb-6 flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-serif font-bold text-soil">Tambah Produk Pasca-Panen</h2>
                    <p class="opacity-70 mt-1 text-sm">Unggah hasil panen Anda.</p>
                </div>
                <a href="produk_saya.php" class="text-leaf text-sm font-semibold hover:underline">&larr; Batal</a>
            </div>

            <?= $pesan ?>

            <div class="bg-white rounded-2xl shadow-sm border p-6">
                <form method="POST" enctype="multipart/form-data">

                    <input type="text" name="nama_produk" placeholder="Nama Produk" required class="w-full mb-3 p-2 border rounded">

                    <select name="kategori" required class="w-full mb-3 p-2 border rounded">
                        <option value="">Pilih Kategori</option>
                        <option>Beras & Biji-bijian</option>
                        <option>Sayuran</option>
                        <option>Buah-buahan</option>
                        <option>Umbi-umbian</option>
                        <option>Bumbu & Rempah</option>
                        <option>Lainnya</option>
                    </select>

                    <input type="number" name="harga" placeholder="Harga" required class="w-full mb-3 p-2 border rounded">

                    <input type="number" name="stok" placeholder="Stok" required class="w-full mb-3 p-2 border rounded">

                    <select name="satuan" required class="w-full mb-3 p-2 border rounded">
                        <option>Kg</option>
                        <option>Gram</option>
                        <option>Ton</option>
                        <option>Pcs</option>
                    </select>

                    <input type="file" name="foto_produk" required class="mb-3">

                    <textarea name="deskripsi" placeholder="Deskripsi" class="w-full mb-3 p-2 border rounded"></textarea>

                    <button class="bg-green-600 text-white px-4 py-2 rounded">
                        Simpan Produk
                    </button>

                </form>
            </div>

        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>