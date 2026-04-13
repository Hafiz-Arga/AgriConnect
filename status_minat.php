<?php
require_once 'header.php';
require_once 'sidebar.php';

// Pastikan hanya Pembeli yang bisa mengakses
if ($_SESSION['role'] !== 'Pembeli') {
    echo "<script>alert('Akses ditolak!'); window.location.href='index.php';</script>";
    exit;
}

$pembeli_id = $_SESSION['user_id'];

// Ambil semua riwayat pengajuan minat milik pembeli ini
$sql = "SELECT p.id as id_pengajuan, p.pesan, p.status, p.created_at, 
               l.komoditas, l.luas_lahan, l.id as lahan_id,
               u.nama_lengkap AS nama_petani, u.id as petani_id
        FROM pengajuan_minat p 
        JOIN lahan l ON p.lahan_id = l.id 
        JOIN users u ON l.petani_id = u.id 
        WHERE p.pembeli_id = $pembeli_id 
        ORDER BY p.created_at DESC";

$query_status = $conn->query($sql);
?>

<main class="flex-1 flex flex-col h-full overflow-hidden bg-cream">
    <header class="md:hidden bg-white shadow-sm p-4 flex justify-between items-center z-10">
        <h1 class="text-xl font-serif text-leaf font-bold">🌱 TaniDirect</h1>
    </header>

    <div class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="max-w-5xl mx-auto">
            
            <div class="mb-8 flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-serif font-bold text-soil">Status Pengajuan Minat</h2>
                    <p class="opacity-70 mt-1 text-sm">Pantau perkembangan negosiasi dan pengajuan lahan Anda di sini.</p>
                </div>
                <a href="cari_lahan.php" class="hidden md:inline-block px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    Cari Lahan Lain
                </a>
            </div>

            <?php if($query_status->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while($row = $query_status->fetch_assoc()): ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row gap-6 items-start md:items-center hover:shadow-md transition">
                            
                            <div class="flex-1 w-full">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="text-lg font-bold text-soil flex items-center gap-2">
                                        🌾 <?= htmlspecialchars($row['komoditas']) ?>
                                    </h3>
                                    <span class="text-xs text-gray-400 block md:hidden"><?= date('d M Y', strtotime($row['created_at'])) ?></span>
                                </div>
                                
                                <div class="text-sm text-gray-600 space-y-1 mb-3">
                                    <p>👨‍🌾 Petani: <span class="font-medium"><?= htmlspecialchars($row['nama_petani']) ?></span></p>
                                    <p>📏 Luas: <span class="font-medium"><?= htmlspecialchars($row['luas_lahan']) ?></span></p>
                                </div>

                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-sm">
                                    <p class="text-gray-500 text-xs font-semibold mb-1 uppercase tracking-wider">Pesan Anda:</p>
                                    <p class="text-soil italic">"<?= htmlspecialchars($row['pesan']) ?: 'Tidak ada pesan.' ?>"</p>
                                </div>
                            </div>

                            <div class="w-full md:w-64 flex flex-col items-start md:items-end gap-3 border-t md:border-t-0 md:border-l border-gray-100 pt-4 md:pt-0 md:pl-6">
                                <span class="text-xs text-gray-400 hidden md:block w-full text-right mb-2">Diajukan: <?= date('d M Y, H:i', strtotime($row['created_at'])) ?></span>
                                
                                <?php 
                                    $bg_color = ''; $text_color = ''; $icon = '';
                                    switch($row['status']) {
                                        case 'Menunggu':
                                            $bg_color = 'bg-orange-100'; $text_color = 'text-orange-700'; $icon = '⏳';
                                            break;
                                        case 'Dipilih':
                                            $bg_color = 'bg-blue-100'; $text_color = 'text-blue-700'; $icon = '🤝';
                                            break;
                                        case 'Dikonfirmasi':
                                            $bg_color = 'bg-green-100'; $text_color = 'text-green-700'; $icon = '✅';
                                            break;
                                        case 'Ditolak':
                                            $bg_color = 'bg-red-100'; $text_color = 'text-red-700'; $icon = '❌';
                                            break;
                                    }
                                ?>
                                <div class="px-4 py-2 <?= $bg_color ?> <?= $text_color ?> rounded-lg font-bold text-sm w-full text-center flex justify-center items-center gap-2">
                                    <span><?= $icon ?></span> <?= strtoupper($row['status']) ?>
                                </div>

                                <div class="w-full space-y-2 mt-2">
                                    <?php if($row['status'] !== 'Ditolak'): ?>
                                        <a href="chat.php?tujuan=<?= $row['petani_id'] ?>" class="block w-full text-center py-2 border border-leaf text-leaf text-sm font-semibold rounded hover:bg-mist transition">
                                            💬 Chat Petani
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="detail_lahan.php?id=<?= $row['lahan_id'] ?>" class="block w-full text-center py-2 text-gray-500 text-sm font-medium hover:text-soil transition">
                                        Lihat Lahan &rarr;
                                    </a>
                                </div>
                            </div>

                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="bg-white p-12 rounded-2xl text-center border border-gray-100 shadow-sm mt-6">
                    <div class="text-5xl mb-4 opacity-50">📝</div>
                    <h3 class="text-xl font-bold text-soil mb-2">Belum Ada Pengajuan</h3>
                    <p class="text-gray-500 max-w-md mx-auto mb-6">Anda belum pernah mengajukan minat ke lahan mana pun. Silakan cari lahan yang sesuai dengan kebutuhan Anda.</p>
                    <a href="cari_lahan.php" class="inline-block px-6 py-3 bg-leaf text-white font-semibold rounded-lg hover:bg-sprout transition">
                        Mulai Cari Lahan
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>