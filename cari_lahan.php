<?php
require_once 'header.php';
require_once 'sidebar.php';

// Pastikan hanya Pembeli yang bisa mengakses
if ($_SESSION['role'] !== 'Pembeli') {
    echo "<script>alert('Akses ditolak! Hanya Pembeli yang dapat mencari lahan.'); window.location.href='index.php';</script>";
    exit;
}

// Fitur 7: Logika Pencarian & Filter
$search_keyword = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Query dasar: Ambil lahan yang statusnya 'Buka' saja, gabungkan dengan tabel users untuk mendapat nama petani
$sql = "SELECT l.*, u.nama_lengkap AS nama_petani 
        FROM lahan l 
        JOIN users u ON l.petani_id = u.id 
        WHERE l.status_listing = 'Buka'
        AND l.status = 'approved'" ;
        

// Jika ada input pencarian, tambahkan filter ke query
if ($search_keyword !== '') {
    $sql .= " AND (l.komoditas LIKE '%$search_keyword%' OR l.alamat_lahan LIKE '%$search_keyword%')";
}

// Urutkan dari yang terbaru
$sql .= " ORDER BY l.created_at DESC";

$query_lahan = $conn->query($sql);
?>

<main class="flex-1 flex flex-col h-full overflow-hidden bg-cream">
    <header class="md:hidden bg-white shadow-sm p-4 flex justify-between items-center z-10">
        <h1 class="text-xl font-serif text-leaf font-bold">🌱 TaniDirect</h1>
    </header>

    <div class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="max-w-6xl mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between md:items-end mb-8 gap-4">
                <div>
                    <h2 class="text-3xl font-serif font-bold text-soil">Jelajahi Lahan Pre-Panen</h2>
                    <p class="opacity-70 mt-1 text-sm">Temukan dan ajukan minat pada lahan pertanian sebelum masa panen tiba.</p>
                </div>
                
                <form action="cari_lahan.php" method="GET" class="w-full md:w-auto flex">
                    <input type="text" name="search" value="<?= htmlspecialchars($search_keyword) ?>" placeholder="Cari komoditas atau lokasi..." 
                           class="w-full md:w-64 px-4 py-2 border border-gray-300 rounded-l-lg focus:ring-1 focus:ring-leaf focus:border-leaf outline-none text-sm">
                    <button type="submit" class="px-4 py-2 bg-leaf text-white rounded-r-lg font-medium hover:bg-sprout transition text-sm">
                        Cari
                    </button>
                    <?php if($search_keyword !== ''): ?>
                        <a href="cari_lahan.php" class="ml-2 px-3 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm flex items-center">
                            Reset
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if($query_lahan->num_rows > 0): ?>
                    <?php while($row = $query_lahan->fetch_assoc()): ?>
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col hover:shadow-md transition duration-300">
                            <div class="relative h-48 bg-gray-200">
                                <?php if($row['foto_lahan']): ?>
                                    <img src="uploads/<?= $row['foto_lahan'] ?>" alt="<?= htmlspecialchars($row['komoditas']) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">Tidak ada foto</div>
                                <?php endif; ?>
                                
                                <?php if($row['bisa_nego'] == 1): ?>
                                    <div class="absolute top-3 right-3 px-2 py-1 bg-sun text-white text-xs font-bold rounded shadow-sm">
                                        Bisa Nego
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="p-5 flex-1 flex flex-col">
                                <h3 class="font-bold text-xl text-soil mb-1"><?= htmlspecialchars($row['komoditas']) ?></h3>
                                <p class="text-xs text-gray-500 mb-4 flex items-center gap-1">
                                    👨‍🌾 Petani: <span class="font-semibold text-leaf"><?= htmlspecialchars($row['nama_petani']) ?></span>
                                </p>
                                
                                <div class="text-sm text-gray-600 mb-4 space-y-2">
                                    <p class="flex items-start gap-2">
                                        <span>📍</span> 
                                        <span class="truncate" title="<?= htmlspecialchars($row['alamat_lahan']) ?>">
                                            <?= htmlspecialchars($row['alamat_lahan']) ?>
                                        </span>
                                    </p>
                                    <p class="flex items-center gap-2">
                                        <span>📏</span> <?= htmlspecialchars($row['luas_lahan']) ?>
                                    </p>
                                    <p class="flex items-center gap-2">
                                        <span>⏱️</span> Panen: <?= date('d M Y', strtotime($row['estimasi_panen'])) ?>
                                    </p>
                                </div>

                                <div class="mt-auto pt-4 border-t border-gray-100">
                                    <p class="text-xs text-gray-500 mb-1">Estimasi Harga:</p>
                                    <p class="font-bold text-leaf mb-4">
                                        Rp <?= number_format($row['harga_min'],0,',','.') ?> - Rp <?= number_format($row['harga_max'],0,',','.') ?>
                                    </p>
                                    
                                    <a href="detail_lahan.php?id=<?= $row['id'] ?>" class="block w-full text-center px-4 py-2 bg-leaf text-white font-semibold rounded-lg hover:bg-sprout transition shadow-sm">
                                        Lihat Detail & Ajukan Minat
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full bg-white p-12 rounded-2xl text-center border border-gray-100">
                        <div class="text-4xl mb-4 opacity-50">🔍</div>
                        <h3 class="text-lg font-bold text-soil mb-1">Lahan Tidak Ditemukan</h3>
                        <?php if($search_keyword !== ''): ?>
                            <p class="text-gray-500">Tidak ada lahan yang cocok dengan pencarian "<strong><?= htmlspecialchars($search_keyword) ?></strong>".</p>
                        <?php else: ?>
                            <p class="text-gray-500">Saat ini belum ada petani yang membuka listing lahan baru.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>