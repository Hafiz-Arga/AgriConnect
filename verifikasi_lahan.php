<?php
require_once 'header.php';
require_once 'sidebar.php';

// CEK ADMIN
if ($_SESSION['role'] !== 'Admin') {
    echo "<script>alert('Akses ditolak!'); window.location.href='index.php';</script>";
    exit;
}

// ======================
// QUERY DATA
// ======================
$data = $conn->query("SELECT * FROM lahan WHERE status='pending'");

// DEBUG (lihat jumlah data)
$total = $data->num_rows;
?>

<main class="flex-1 p-6 bg-cream-100 min-h-screen">

    <h2 class="text-2xl font-bold text-gray-700 mb-2">
        Verifikasi Lahan
    </h2>

    <p class="text-sm text-gray-500 mb-6">
        Total pengajuan: <b><?= $total ?></b>
    </p>

    <?php if($total == 0): ?>

        <!-- JIKA KOSONG -->
        <div class="bg-yellow-100 text-yellow-800 p-4 rounded-lg shadow">
            ⚠️ Belum ada pengajuan lahan dari petani
        </div>

    <?php else: ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <?php while($row = $data->fetch_assoc()): ?>
        
        <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition p-5 flex flex-col">

            <!-- FOTO -->
            <?php if(!empty($row['foto_lahan'])): ?>
                <img src="uploads/<?= $row['foto_lahan'] ?>" 
                     class="w-full h-40 object-cover rounded-lg mb-3">
            <?php else: ?>
                <div class="w-full h-40 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400 mb-3">
                    Tidak ada foto
                </div>
            <?php endif; ?>

            <!-- ISI -->
            <h3 class="text-lg font-semibold text-green-700 mb-2">
                <?= $row['komoditas'] ?>
            </h3>

            <p class="text-sm text-gray-600">📍 <?= $row['alamat_lahan'] ?></p>
            <p class="text-sm text-gray-600">🌾 <?= $row['luas_lahan'] ?></p>
            <p class="text-sm text-gray-600">📅 <?= $row['estimasi_panen'] ?></p>

            <p class="text-sm text-gray-500 mt-2 line-clamp-3">
                <?= $row['deskripsi'] ?>
            </p>

            <!-- STATUS BADGE -->
            <span class="mt-3 inline-block text-xs bg-yellow-200 text-yellow-800 px-2 py-1 rounded-full w-fit">
                ⏳ Pending
            </span>

            <!-- BUTTON -->
            <div class="flex gap-3 mt-4">

                <a href="approve_lahan.php?id=<?= $row['id'] ?>"
                   onclick="return confirm('Setujui lahan ini?')"
                   class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm font-medium shadow">
                   ✔ Setujui
                </a>

                <a href="reject_lahan.php?id=<?= $row['id'] ?>"
                   onclick="return confirm('Tolak lahan ini?')"
                   class="flex-1 text-center bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg text-sm font-medium shadow">
                   ✖ Tolak
                </a>

            </div>

        </div>

        <?php endwhile; ?>

    </div>

    <?php endif; ?>

</main>

<?php require_once 'footer.php'; ?>