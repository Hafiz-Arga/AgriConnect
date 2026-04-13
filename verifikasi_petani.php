<?php
require_once 'header.php';
require_once 'sidebar.php';

// CEK ADMIN
if ($_SESSION['role'] !== 'Admin') {
    echo "<script>alert('Akses ditolak!'); window.location.href='index.php';</script>";
    exit;
}

// AMBIL DATA PETANI
$data = $conn->query("SELECT * FROM users 
WHERE role='Petani' AND status_verifikasi='Menunggu'");
?>

<main class="flex-1 p-6 bg-cream-100 min-h-screen">

    <h2 class="text-2xl font-bold mb-6 text-gray-700">
        Verifikasi Akun Petani
    </h2>

    <!-- NOTIF -->
    <?php if(isset($_GET['msg'])): ?>
        <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
            <?= $_GET['msg'] ?>
        </div>
    <?php endif; ?>

    <!-- KALAU KOSONG -->
    <?php if($data->num_rows == 0): ?>
        <div class="bg-yellow-100 text-yellow-800 p-4 rounded">
            Tidak ada pengajuan verifikasi saat ini
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <?php while($row = $data->fetch_assoc()): ?>

        <div class="bg-white p-5 rounded-2xl shadow hover:shadow-lg transition">

            <!-- AVATAR -->
            <div class="flex items-center gap-3 mb-3">
                <div class="w-12 h-12 bg-green-600 text-white flex items-center justify-center rounded-full text-lg font-bold">
                    <?= strtoupper(substr($row['nama_lengkap'],0,1)) ?>
                </div>
                <div>
                    <p class="font-semibold"><?= htmlspecialchars($row['nama_lengkap']) ?></p>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($row['email']) ?></p>
                </div>
            </div>

            <!-- STATUS -->
            <p class="text-sm text-gray-600 mb-2">
                Status: <span class="text-yellow-600 font-semibold">Menunggu</span>
            </p>

            <!-- BUTTON -->
            <div class="flex gap-3 mt-4">

                <a href="approve_petani.php?id=<?= $row['id'] ?>"
                   onclick="return confirm('Setujui akun ini?')"
                   class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg">
                   ✔ Setujui
                </a>

                <a href="reject_petani.php?id=<?= $row['id'] ?>"
                   onclick="return confirm('Tolak akun ini?')"
                   class="flex-1 text-center bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg">
                   ✖ Tolak
                </a>

            </div>

        </div>

        <?php endwhile; ?>

    </div>

</main>

<?php require_once 'footer.php'; ?>