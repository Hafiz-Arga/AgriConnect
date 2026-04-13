<?php
require_once 'header.php';
require_once 'sidebar.php';

// Proteksi login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user
$query = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $query->fetch_assoc();
?>

<main class="flex-1 flex flex-col h-full overflow-hidden bg-cream">
    <div class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="max-w-3xl mx-auto">

        <div class="mb-6 flex items-center justify-between">

            <!-- BACK BUTTON -->
            <a href="index.php" 
            class="flex items-center gap-2 text-sm text-leaf font-semibold hover:underline md:hidden">
                ← Kembali
            </a>

            <!-- TITLE -->
            <div class="text-right md:text-left w-full">
                <h2 class="text-2xl md:text-3xl font-serif font-bold text-soil">
                    Profil Saya
                </h2>
                <p class="text-gray-500 text-xs md:text-sm mt-1">
                    Informasi akun Anda
                </p>
            </div>

        </div>

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">

                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-full bg-sprout text-white flex items-center justify-center text-2xl font-bold">
                        <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-soil"><?= htmlspecialchars($user['nama_lengkap']) ?></h3>
                        <p class="text-sm text-gray-500"><?= $user['role'] ?></p>
                    </div>
                </div>

                <div class="space-y-4 text-sm">
                    <div>
                        <span class="text-gray-400">Email</span>
                        <p class="font-medium text-soil"><?= htmlspecialchars($user['email']) ?></p>
                    </div>

                    <div>
                        <span class="text-gray-400">Role</span>
                        <p class="font-medium text-soil"><?= $user['role'] ?></p>
                    </div>

                    <div>
                        <span class="text-gray-400">Bergabung Sejak</span>
                        <p class="font-medium text-soil"><?= date('d M Y', strtotime($user['created_at'])) ?></p>
                    </div>
                </div>

            </div>

        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>