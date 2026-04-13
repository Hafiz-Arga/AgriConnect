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

// Fitur 4: Proses Update Status Peminat
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $id_pengajuan = (int)$_POST['id_pengajuan'];
    $status_baru  = $conn->real_escape_string($_POST['status_baru']);

    // Validasi keamanan ekstra: pastikan lahan dari pengajuan ini benar-benar milik petani yang sedang login
    $cek_kepemilikan = $conn->query("SELECT p.id FROM pengajuan_minat p JOIN lahan l ON p.lahan_id = l.id WHERE p.id = $id_pengajuan AND l.petani_id = $petani_id");
    
    if ($cek_kepemilikan->num_rows > 0) {
        $update = $conn->query("UPDATE pengajuan_minat SET status = '$status_baru' WHERE id = $id_pengajuan");
        if ($update) {
            $pesan_notif = '<div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 text-sm font-medium">Status peminat berhasil diperbarui menjadi: ' . $status_baru . '</div>';
        }
    }
}

// Fitur 3 & 5: Ambil daftar peminat beserta detail lahan dan profil pembeli
$sql = "SELECT p.id as id_pengajuan, p.pesan, p.status as status_pengajuan, p.created_at, 
               l.komoditas, l.luas_lahan, 
               u.id as id_pembeli, u.nama_lengkap as nama_pembeli, u.email as email_pembeli 
        FROM pengajuan_minat p 
        JOIN lahan l ON p.lahan_id = l.id 
        JOIN users u ON p.pembeli_id = u.id 
        WHERE l.petani_id = $petani_id 
        ORDER BY p.created_at DESC";

$query_peminat = $conn->query($sql);
?>

<main class="flex-1 flex flex-col h-full overflow-hidden bg-cream">
    <header class="md:hidden bg-white shadow-sm p-4 flex justify-between items-center z-10">
        <h1 class="text-xl font-serif text-leaf font-bold">🌱 TaniDirect</h1>
    </header>

    <div class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="max-w-5xl mx-auto">
            <div class="mb-6">
                <h2 class="text-2xl font-serif font-bold text-soil">Daftar Peminat Lahan</h2>
                <p class="opacity-70 mt-1 text-sm">Lihat siapa saja pembeli yang berminat dengan listing lahan Anda.</p>
            </div>

            <?= $pesan_notif ?>

            <?php if($query_peminat->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while($row = $query_peminat->fetch_assoc()): ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row gap-6">
                            
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="px-3 py-1 bg-mist text-leaf text-xs font-bold rounded-full">Lahan: <?= htmlspecialchars($row['komoditas']) ?></span>
                                    <span class="text-xs text-gray-400"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></span>
                                </div>
                                <h3 class="text-lg font-bold text-soil mb-1">Pesan dari Pembeli:</h3>
                                <p class="text-gray-600 text-sm italic bg-gray-50 p-4 rounded-lg border border-gray-100">
                                    "<?= htmlspecialchars($row['pesan']) ? $row['pesan'] : 'Tidak ada pesan yang dilampirkan.' ?>"
                                </p>
                            </div>

                            <div class="w-full md:w-72 bg-cream p-4 rounded-lg border border-wheat flex flex-col justify-between">
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Profil Pembeli</h4>
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 rounded-full bg-sprout text-white flex items-center justify-center font-bold">
                                            <?= substr($row['nama_pembeli'], 0, 1) ?>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-soil text-sm"><?= htmlspecialchars($row['nama_pembeli']) ?></p>
                                            <p class="text-xs text-gray-500"><?= htmlspecialchars($row['email_pembeli']) ?></p>
                                        </div>
                                    </div>
                                    
                                    <a href="chat.php?tujuan=<?= $row['id_pembeli'] ?>" class="block w-full text-center py-2 mb-4 bg-white border border-leaf text-leaf text-sm font-semibold rounded hover:bg-mist transition">
                                        💬 Chat Pembeli
                                    </a>
                                </div>

                                <form action="" method="POST" class="mt-auto border-t border-gray-200 pt-3">
                                    <input type="hidden" name="id_pengajuan" value="<?= $row['id_pengajuan'] ?>">
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Status Peminat:</label>
                                    <div class="flex gap-2">
                                        <select name="status_baru" class="flex-1 text-sm px-2 py-1.5 border rounded focus:ring-1 focus:ring-leaf outline-none bg-white font-medium
                                            <?php 
                                                echo $row['status_pengajuan'] == 'Menunggu' ? 'text-orange-600' : '';
                                                echo $row['status_pengajuan'] == 'Dipilih' ? 'text-blue-600' : '';
                                                echo $row['status_pengajuan'] == 'Dikonfirmasi' ? 'text-green-600' : '';
                                                echo $row['status_pengajuan'] == 'Ditolak' ? 'text-red-600' : '';
                                            ?>
                                        ">
                                            <option value="Menunggu" <?= $row['status_pengajuan'] == 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                            <option value="Dipilih" <?= $row['status_pengajuan'] == 'Dipilih' ? 'selected' : '' ?>>Dipilih (Nego)</option>
                                            <option value="Dikonfirmasi" <?= $row['status_pengajuan'] == 'Dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi (Deal)</option>
                                            <option value="Ditolak" <?= $row['status_pengajuan'] == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                                        </select>
                                        <button type="submit" name="update_status" class="px-3 py-1.5 bg-leaf text-white text-sm font-semibold rounded hover:bg-sprout transition">Simpan</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="bg-white p-12 rounded-2xl text-center border border-gray-100 shadow-sm">
                    <div class="text-5xl mb-4 opacity-50">👀</div>
                    <h3 class="text-xl font-bold text-soil mb-2">Belum Ada Peminat</h3>
                    <p class="text-gray-500 max-w-md mx-auto">Saat ini belum ada pembeli yang mengajukan minat pada listing lahan Anda. Coba perbarui deskripsi lahan Anda agar lebih menarik.</p>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>