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

// Ambil ID Lahan dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID Lahan tidak ditemukan!'); window.location.href='lahan_saya.php';</script>";
    exit;
}

$id_lahan = (int)$_GET['id'];

// Ambil data lahan saat ini (Pastikan lahan ini benar milik petani yang sedang login)
$query = $conn->query("SELECT * FROM lahan WHERE id = $id_lahan AND petani_id = $petani_id");
if ($query->num_rows == 0) {
    echo "<script>alert('Data lahan tidak ditemukan atau Anda tidak memiliki hak akses!'); window.location.href='lahan_saya.php';</script>";
    exit;
}
$data = $query->fetch_assoc();

// Jika form disubmit (Proses Update Data)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $komoditas      = $conn->real_escape_string($_POST['komoditas']);
    $luas_lahan     = $conn->real_escape_string($_POST['luas_lahan']);
    $estimasi_panen = $conn->real_escape_string($_POST['estimasi_panen']);
    $harga_min      = str_replace('.', '', $_POST['harga_min']); 
    $harga_max      = str_replace('.', '', $_POST['harga_max']);
    $bisa_nego      = isset($_POST['bisa_nego']) ? 1 : 0;
    $alamat_lahan   = $conn->real_escape_string($_POST['alamat_lahan']);
    $link_gmaps     = $conn->real_escape_string($_POST['link_gmaps']);
    $deskripsi      = $conn->real_escape_string($_POST['deskripsi']);

    $foto_lahan_baru = $data['foto_lahan']; // Default: gunakan foto lama

    // Proses Upload Foto (Hanya jika user mengunggah foto baru)
    if (isset($_FILES['foto_lahan']) && $_FILES['foto_lahan']['error'] == 0) {
        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg');
        $nama_file = $_FILES['foto_lahan']['name'];
        $x = explode('.', $nama_file);
        $ekstensi = strtolower(end($x));
        $ukuran = $_FILES['foto_lahan']['size'];
        $file_tmp = $_FILES['foto_lahan']['tmp_name'];
        
        $nama_file_baru = time() . '_edit_' . $petani_id . '.' . $ekstensi;

        if (in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
            if ($ukuran < 2097152) { // Maksimal 2MB
                move_uploaded_file($file_tmp, 'uploads/' . $nama_file_baru);
                
                // Hapus foto lama dari folder uploads agar tidak menumpuk
                if (file_exists('uploads/' . $data['foto_lahan'])) {
                    unlink('uploads/' . $data['foto_lahan']);
                }
                
                $foto_lahan_baru = $nama_file_baru;
            } else {
                $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded-lg mb-4 text-sm font-medium">Ukuran file terlalu besar! Maksimal 2MB.</div>';
            }
        } else {
            $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded-lg mb-4 text-sm font-medium">Ekstensi file tidak diperbolehkan! Gunakan JPG atau PNG.</div>';
        }
    }

    // Jika tidak ada error saat upload, lakukan UPDATE database
    if (empty($pesan)) {
        $sql_update = "UPDATE lahan SET 
                        komoditas = '$komoditas', 
                        luas_lahan = '$luas_lahan', 
                        estimasi_panen = '$estimasi_panen', 
                        deskripsi = '$deskripsi', 
                        harga_min = '$harga_min', 
                        harga_max = '$harga_max', 
                        bisa_nego = '$bisa_nego', 
                        alamat_lahan = '$alamat_lahan', 
                        link_gmaps = '$link_gmaps', 
                        foto_lahan = '$foto_lahan_baru' 
                       WHERE id = $id_lahan AND petani_id = $petani_id";

        if ($conn->query($sql_update) === TRUE) {
            $pesan = '<div class="bg-green-100 text-green-700 p-4 rounded-lg mb-4 text-sm font-medium">Perubahan data lahan berhasil disimpan!</div>';
            // Perbarui array $data agar form langsung menampilkan data terbaru
            $query = $conn->query("SELECT * FROM lahan WHERE id = $id_lahan");
            $data = $query->fetch_assoc();
        } else {
            $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded-lg mb-4 text-sm font-medium">Error: ' . $conn->error . '</div>';
        }
    }
}
?>

<main class="flex-1 flex flex-col h-full overflow-hidden bg-cream">
    <header class="md:hidden bg-white shadow-sm p-4 flex justify-between items-center z-10">
        <h1 class="text-xl font-serif text-leaf font-bold">🌱 TaniDirect</h1>
    </header>

    <div class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="max-w-4xl mx-auto">
            <div class="mb-6 flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-serif font-bold text-soil">Edit Listing Lahan</h2>
                    <p class="opacity-70 mt-1 text-sm">Perbarui informasi lahan Anda di bawah ini.</p>
                </div>
                <a href="lahan_saya.php" class="text-leaf text-sm font-semibold hover:underline">&larr; Kembali ke Daftar</a>
            </div>

            <?= $pesan ?>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                <form action="" method="POST" enctype="multipart/form-data" id="formEditLahan" onsubmit="return validasiEditLahan()">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-soil mb-1">Jenis Komoditas <span class="text-red-500">*</span></label>
                            <input type="text" name="komoditas" id="komoditas" required value="<?= htmlspecialchars($data['komoditas']) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-leaf focus:border-leaf">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-soil mb-1">Luas Lahan <span class="text-red-500">*</span></label>
                            <input type="text" name="luas_lahan" id="luas_lahan" required value="<?= htmlspecialchars($data['luas_lahan']) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-leaf focus:border-leaf">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-soil mb-1">Estimasi Tanggal Panen <span class="text-red-500">*</span></label>
                            <input type="date" name="estimasi_panen" id="estimasi_panen" required value="<?= $data['estimasi_panen'] ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-leaf focus:border-leaf">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-soil mb-1">Ganti Foto Lahan (Opsional)</label>
                            <input type="file" name="foto_lahan" id="foto_lahan" accept="image/png, image/jpeg, image/jpg" 
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-leaf focus:border-leaf text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-soil hover:file:bg-gray-200 cursor-pointer">
                            <p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak ingin mengganti foto saat ini.</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-soil mb-2">Foto Saat Ini:</label>
                        <img src="uploads/<?= $data['foto_lahan'] ?>" alt="Foto Lahan" class="w-48 h-32 object-cover rounded-lg border">
                    </div>

                    <hr class="my-6 border-gray-100">

                    <h3 class="font-semibold text-soil mb-4">Estimasi Harga Penjualan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-soil mb-1">Harga Minimum (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="harga_min" id="harga_min" required value="<?= round($data['harga_min']) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-leaf focus:border-leaf">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-soil mb-1">Harga Maksimum (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="harga_max" id="harga_max" required value="<?= round($data['harga_max']) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-leaf focus:border-leaf">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center text-sm text-soil cursor-pointer">
                            <input type="checkbox" name="bisa_nego" class="mr-2 rounded text-leaf focus:ring-leaf" <?= ($data['bisa_nego'] == 1) ? 'checked' : '' ?>>
                            Tandai harga bisa dinegosiasikan dengan pembeli
                        </label>
                    </div>

                    <hr class="my-6 border-gray-100">

                    <h3 class="font-semibold text-soil mb-4">Lokasi & Detail</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-soil mb-1">Alamat Lahan <span class="text-red-500">*</span></label>
                        <textarea name="alamat_lahan" id="alamat_lahan" rows="2" required
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-leaf focus:border-leaf"><?= htmlspecialchars($data['alamat_lahan']) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-soil mb-1">Link Google Maps <span class="text-red-500">*</span></label>
                        <input type="url" name="link_gmaps" id="link_gmaps" required value="<?= htmlspecialchars($data['link_gmaps']) ?>"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-leaf focus:border-leaf">
                    </div>

                    <div class="mb-8">
                        <label class="block text-sm font-medium text-soil mb-1">Deskripsi Lahan</label>
                        <textarea name="deskripsi" id="deskripsi" rows="4" 
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-1 focus:ring-leaf focus:border-leaf"><?= htmlspecialchars($data['deskripsi']) ?></textarea>
                    </div>

                    <div class="flex justify-end gap-4">
                        <button type="submit" class="px-6 py-2 bg-leaf text-white rounded-lg font-medium hover:bg-sprout transition">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    // Validasi JavaScript untuk Form Edit
    function validasiEditLahan() {
        const hargaMin = parseInt(document.getElementById('harga_min').value);
        const hargaMax = parseInt(document.getElementById('harga_max').value);
        const estimasiPanen = new Date(document.getElementById('estimasi_panen').value);
        const hariIni = new Date();
        hariIni.setHours(0,0,0,0);

        // Validasi Harga Range
        if (hargaMax < hargaMin) {
            alert('Harga Maksimum tidak boleh lebih kecil dari Harga Minimum!');
            document.getElementById('harga_max').focus();
            return false;
        }

        // Validasi Tanggal Panen (Harus di masa depan atau minimal hari ini)
        if (estimasiPanen < hariIni) {
            alert('Estimasi tanggal panen tidak boleh di masa lalu!');
            document.getElementById('estimasi_panen').focus();
            return false;
        }

        // Validasi File Upload (hanya jika user memilih file baru)
        const foto = document.getElementById('foto_lahan');
        if (foto.value !== "") {
            const fileName = foto.value;
            const idxDot = fileName.lastIndexOf(".") + 1;
            const extFile = fileName.substr(idxDot, fileName.length).toLowerCase();
            
            if (extFile !== "jpg" && extFile !== "jpeg" && extFile !== "png"){
                alert('Hanya file foto dengan ekstensi JPG, JPEG, atau PNG yang diperbolehkan!');
                foto.value = ""; // Reset file input
                return false;
            }
        }

        return true;
    }
</script>

<?php require_once 'footer.php'; ?>