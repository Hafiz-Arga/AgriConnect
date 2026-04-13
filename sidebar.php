<?php
$current = basename($_SERVER['PHP_SELF']);
?>

<!-- SIDEBAR -->
<aside id="sidebar"
class="fixed md:static top-0 left-0 h-full w-64 bg-white shadow-xl z-50
transform -translate-x-full md:translate-x-0
transition-transform duration-300 flex flex-col">

    <!-- LOGO -->
    <div class="p-6 border-b border-gray-100">
        <h1 class="text-2xl font-serif text-leaf font-bold flex items-center gap-3">
            <img src="uploads/logo.png" class="w-10 h-10 object-contain">
            <span>AgriConnect</span>
        </h1>
    </div>

    <!-- USER -->
    <div class="p-6 border-b border-gray-100">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-leaf text-white flex items-center justify-center font-bold text-lg">
                <?= strtoupper(substr($user_nama, 0, 1)) ?>
            </div>
            <div>
                <div class="font-semibold text-sm truncate w-32">
                    <?= htmlspecialchars($user_nama) ?>
                </div>
                <div class="text-xs text-gray-400">
                    <?= $user_role ?>
                </div>
            </div>
        </div>
    </div>

    <!-- MENU -->
    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">

        <div class="text-xs font-semibold text-gray-400 uppercase mb-2 mt-2 px-2">
            Menu Utama
        </div>

        <!-- DASHBOARD -->
        <a href="index.php"
           class="flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition
           <?= $current == 'index.php' ? 'bg-leaf text-white' : 'text-soil hover:bg-gray-100' ?>">
           📊 Dashboard
        </a>

        <!-- ================= PETANI ================= -->
        <?php if ($user_role == 'Petani'): ?>

            <div class="text-xs font-semibold text-gray-400 uppercase mt-6 px-2">
                Pre-Panen
            </div>

            <a href="lahan_saya.php"
               class="flex items-center gap-2 px-4 py-2 rounded-lg transition
               <?= $current == 'lahan_saya.php' ? 'bg-leaf text-white' : 'hover:bg-gray-100' ?>">
               🌱 Lahan Saya
            </a>

            <a href="peminat_lahan.php"
               class="flex items-center gap-2 px-4 py-2 rounded-lg transition
               <?= $current == 'peminat_lahan.php' ? 'bg-leaf text-white' : 'hover:bg-gray-100' ?>">
               👥 Peminat Lahan
            </a>

            <div class="text-xs font-semibold text-gray-400 uppercase mt-6 px-2">
                Pasca-Panen
            </div>

            <a href="produk_saya.php"
               class="flex items-center gap-2 px-4 py-2 rounded-lg transition
               <?= $current == 'produk_saya.php' ? 'bg-leaf text-white' : 'hover:bg-gray-100' ?>">
               📦 Produk Saya
            </a>

            <a href="pesanan_masuk.php"
               class="flex items-center gap-2 px-4 py-2 rounded-lg transition
               <?= $current == 'pesanan_masuk.php' ? 'bg-leaf text-white' : 'hover:bg-gray-100' ?>">
               🛒 Pesanan Masuk
            </a>

        <!-- ================= PEMBELI ================= -->
        <?php elseif ($user_role == 'Pembeli'): ?>

            <div class="text-xs font-semibold text-gray-400 uppercase mt-6 px-2">
                Pre-Panen
            </div>

            <a href="cari_lahan.php"
               class="flex items-center gap-2 px-4 py-2 rounded-lg transition
               <?= $current == 'cari_lahan.php' ? 'bg-leaf text-white' : 'hover:bg-gray-100' ?>">
               🔍 Cari Lahan
            </a>

            <a href="status_minat.php"
               class="flex items-center gap-2 px-4 py-2 rounded-lg transition
               <?= $current == 'status_minat.php' ? 'bg-leaf text-white' : 'hover:bg-gray-100' ?>">
               📄 Status Minat
            </a>

            <div class="text-xs font-semibold text-gray-400 uppercase mt-6 px-2">
                Pasca-Panen
            </div>

            <a href="belanja.php"
               class="flex items-center gap-2 px-4 py-2 rounded-lg transition
               <?= $current == 'belanja.php' ? 'bg-leaf text-white' : 'hover:bg-gray-100' ?>">
               🛍️ Belanja Produk
            </a>

            <a href="keranjang.php"
               class="flex items-center gap-2 px-4 py-2 rounded-lg transition
               <?= $current == 'keranjang.php' ? 'bg-leaf text-white' : 'hover:bg-gray-100' ?>">
               🧺 Keranjang
            </a>

            <a href="riwayat_pesanan.php"
               class="flex items-center gap-2 px-4 py-2 rounded-lg transition
               <?= $current == 'riwayat_pesanan.php' ? 'bg-leaf text-white' : 'hover:bg-gray-100' ?>">
               📜 Pesanan Saya
            </a>

        <!-- ================= ADMIN ================= -->
        <?php elseif ($user_role == 'Admin'): ?>

            <div class="text-xs font-semibold text-gray-400 uppercase mt-6 px-2">
                Manajemen
            </div>

            <a href="verifikasi_petani.php"
               class="flex items-center gap-2 px-4 py-2 rounded-lg transition
               <?= $current == 'verifikasi_petani.php' ? 'bg-leaf text-white' : 'hover:bg-gray-100' ?>">
               👨‍🌾 Verifikasi Petani
            </a>

            <a href="verifikasi_lahan.php"
               class="flex items-center gap-2 px-4 py-2 rounded-lg transition
               <?= $current == 'verifikasi_lahan.php' ? 'bg-leaf text-white' : 'hover:bg-gray-100' ?>">
               🌾 Verifikasi Lahan
            </a>

            <a href="statistik.php"
               class="flex items-center gap-2 px-4 py-2 rounded-lg transition
               <?= $current == 'statistik.php' ? 'bg-leaf text-white' : 'hover:bg-gray-100' ?>">
               📊 Statistik
            </a>

        <?php endif; ?>

    </nav>

    <!-- LOGOUT -->
    <div class="p-4 border-t border-gray-100">
        <a href="logout.php"
           class="block w-full text-center px-4 py-2 border border-red-500 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition">
            Keluar
        </a>
    </div>

</aside>