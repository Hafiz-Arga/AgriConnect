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

// Ambil ID Produk dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>window.location.href='produk_saya.php';</script>";
    exit;
}

$id_produk = (int)$_GET['id'];

// Ambil data produk yang sudah ada
$query = $conn->query("SELECT * FROM produk WHERE id = $id_produk AND petani_id = $petani_id");
if ($query->num_rows == 0) {
    echo "<script>alert('Produk tidak ditemukan!'); window.location.href='produk_saya.php';</script>";
    exit;
}
$data = $query->fetch_assoc();

// Proses Update Form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_produk = $conn->real_escape_string($_POST['nama_produk']);
    $kategori    = $conn->real_escape_string($_POST['kategori']);
    $harga       = str_replace('.', '', $_POST['harga']); // Bersihkan format titik
    $stok        = (int)$_POST['stok'];
    $satuan      = $conn->real_escape_string($_POST['satuan']);
    $deskripsi   = $conn->real_escape_string($_POST['deskripsi']);
    
    $foto_baru = $data['foto_produk']; // Default gunakan foto lama

    // Jika ada upload foto baru
    if (isset($_FILES['foto_produk']) && $_FILES['foto_produk']['error'] == 0) {
        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg');
        $nama_file = $_FILES['foto_produk']['name'];
        $x = explode('.', $nama_file);
        $ekstensi = strtolower(end($x));
        $ukuran = $_FILES['foto_produk']['size'];
        $file_tmp = $_FILES['foto_produk']['tmp_name'];
        
        $nama_file_baru = time() . '_produk_' . $petani_id . '.' . $ekstensi;

        if (in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
            if ($ukuran < 2097152) { // Maksimal 2MB
                // Hapus foto lama jika ada
                if ($data['foto_produk'] != '' && file_exists('uploads/' . $data['foto_produk'])) {
                    unlink('uploads/' . $data['foto_produk']);
                }
                
                move_uploaded_file($file_tmp, 'uploads/' . $nama_file_baru);
                $foto_baru = $nama_file_baru;
            } else {
                $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded-lg mb-4 text-sm font-medium">Ukuran foto terlalu besar! Maksimal 2MB.</div>';
            }
        } else {
            $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded-lg mb-4 text-sm font-medium">Ekstensi file tidak diperbolehkan! Gunakan JPG atau PNG.</div>';
        }
    }

    if (empty($pesan)) {
        $sql = "UPDATE produk SET 
                nama_produk = '$nama_produk', 
                kategori = '$kategori', 
                harga = '$harga', 
                stok = '$stok', 
                satuan = '$satuan', 
                deskripsi = '$deskripsi', 
                foto_produk = '$foto_baru' 
                WHERE id = $id_produk AND petani_id = $petani_id";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Produk berhasil diperbarui!'); window.location.href='produk_saya.php';</script>";
            exit;
        } else {
            $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded-lg mb-4 text-sm font-medium">Error: ' . $conn->error . '</div>';
        }
    }
}
?>

<main class="flex-1 flex flex-col h-full overflow-hidden bg-cream">
    <div class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="max-w-3xl mx-auto">
            <div class="mb-6 flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-serif font-bold text-soil">Edit Produk</h2>
                    <p class="opacity-70 mt-1 text-sm">Perbarui stok, harga, atau deskripsi produk Anda.</p>
                </div>
                <a href="produk_saya.php" class="text-leaf text-sm font-semibold hover:underline">&larr; Kembali</a>
            </div>

            <?= $pesan ?>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                <form action="" method="POST" enctype="multipart/form-data">
                    
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-soil mb-1">Nama Produk <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_produk" value="<?= htmlspecialchars($data['nama_produk']) ?>" required 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-leaf focus:border-leaf">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                        <div>
                            <label class="block text-sm font-medium text-soil mb-1">Kategori <span class="text-red-500">*</span></label>
                            <select name="kategori" required class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-leaf bg-white">
                                <option value="Beras & Biji-bijian" <?= $data['kategori'] == 'Beras & Biji-bijian' ? 'selected' : '' ?>>Beras & Biji-bijian</option>
                                <option value="Sayuran" <?= $data['kategori'] == 'Sayuran' ? 'selected' : '' ?>>Sayuran</option>
                                <option value="Buah-buahan" <?= $data['kategori'] == 'Buah-buahan' ? 'selected' : '' ?>>Buah-buahan</option>
                                <option value="Umbi-umbian" <?= $data['kategori'] == 'Umbi-umbian' ? 'selected' : '' ?>>Umbi-umbian</option>
                                <option value="Bumbu & Rempah" <?= $data['kategori'] == 'Bumbu & Rempah' ? 'selected' : '' ?>>Bumbu & Rempah</option>
                                <option value="Lainnya" <?= $data['kategori'] == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-soil mb-1">Harga per Satuan (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="harga" value="<?= htmlspecialchars($data['harga']) ?>" required 
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-leaf">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                        <div>
                            <label class="block text-sm font-medium text-soil mb-1">Update Stok <span class="text-red-500">*</span></label>
                            <input type="number" name="stok" value="<?= htmlspecialchars($data['stok']) ?>" required min="0"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-leaf">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-soil mb-1">Satuan <span class="text-red-500">*</span></label>
                            <select name="satuan" required class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-leaf bg-white">
                                <option value="Kg" <?= (isset($data['satuan']) && $data['satuan'] == 'Kg') ? 'selected' : '' ?>>Kilogram (Kg)</option>
                                <option value="Gram" <?= (isset($data['satuan']) && $data['satuan'] == 'Gram') ? 'selected' : '' ?>>Gram (g)</option>
                                <option value="Ton" <?= (isset($data['satuan']) && $data['satuan'] == 'Ton') ? 'selected' : '' ?>>Ton</option>
                                <option value="Ikat" <?= (isset($data['satuan']) && $data['satuan'] == 'Ikat') ? 'selected' : '' ?>>Ikat</option>
                                <option value="Karung" <?= (isset($data['satuan']) && $data['satuan'] == 'Karung') ? 'selected' : '' ?>>Karung</option>
                                <option value="Pcs" <?= (isset($data['satuan']) && $data['satuan'] == 'Pcs') ? 'selected' : '' ?>>Pcs / Kemasan</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-soil mb-1">Ubah Foto Produk (Opsional)</label>
                        <?php if($data['foto_produk']): ?>
                            <div class="mb-3">
                                <img src="uploads/<?= $data['foto_produk'] ?>" alt="Current Photo" class="h-24 w-24 object-cover rounded-lg border">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="foto_produk" accept="image/png, image/jpeg, image/jpg"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-leaf text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-soil hover:file:bg-gray-200 cursor-pointer">
                        <p class="text-xs text-gray-400 mt-1">Biarkan kosong jika tidak ingin mengubah foto.</p>
                    </div>

                    <div class="mb-8">
                        <label class="block text-sm font-medium text-soil mb-1">Deskripsi Produk</label>
                        <textarea name="deskripsi" rows="4" class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-leaf"><?= htmlspecialchars($data['deskripsi']) ?></textarea>
                    </div>

                    <div class="flex justify-end gap-4">
                        <button type="submit" class="px-6 py-2 bg-leaf text-white rounded-lg font-medium hover:bg-sprout transition w-full md:w-auto">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>