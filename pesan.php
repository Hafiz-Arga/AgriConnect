<?php
require_once 'header.php';
require_once 'sidebar.php';

$user_id = $_SESSION['user_id'];

// Ambil daftar lawan bicara (orang yang pernah chat dengan user ini)
$sql = "SELECT DISTINCT 
            CASE WHEN pengirim_id = $user_id THEN penerima_id ELSE pengirim_id END AS lawan_id,
            MAX(created_at) as terakhir
        FROM chat_messages 
        WHERE pengirim_id = $user_id OR penerima_id = $user_id
        GROUP BY lawan_id
        ORDER BY terakhir DESC";

$query_kontak = $conn->query($sql);
?>

<main class="flex-1 flex flex-col h-full overflow-hidden bg-cream">
    <div class="flex-1 overflow-y-auto p-6 md:p-8">
        <div class="max-w-2xl mx-auto">
            <h2 class="text-3xl font-serif font-bold text-soil mb-6">Pesan Masuk</h2>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <?php if($query_kontak->num_rows > 0): ?>
                    <?php while($row = $query_kontak->fetch_assoc()): 
                        $lawan_id = $row['lawan_id'];
                        $info_lawan = $conn->query("SELECT nama_lengkap, role, foto_profil FROM users WHERE id = $lawan_id")->fetch_assoc();
                        $last_msg = $conn->query("SELECT pesan, pengirim_id FROM chat_messages 
                                                 WHERE (pengirim_id = $user_id AND penerima_id = $lawan_id) 
                                                 OR (pengirim_id = $lawan_id AND penerima_id = $user_id) 
                                                 ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
                    ?>
                        <a href="chat.php?id=<?= $lawan_id ?>" class="flex items-center gap-4 p-4 hover:bg-gray-50 border-b border-gray-50 transition">
                            <div class="w-12 h-12 bg-sprout rounded-full flex-shrink-0 flex items-center justify-center text-white font-bold">
                                <?= strtoupper(substr($info_lawan['nama_lengkap'], 0, 1)) ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center mb-1">
                                    <h4 class="font-bold text-soil truncate"><?= htmlspecialchars($info_lawan['nama_lengkap']) ?></h4>
                                    <span class="text-[10px] text-gray-400"><?= date('H:i', strtotime($row['terakhir'])) ?></span>
                                </div>
                                <p class="text-sm text-gray-500 truncate">
                                    <?= $last_msg['pengirim_id'] == $user_id ? 'Anda: ' : '' ?>
                                    <?= htmlspecialchars($last_msg['pesan']) ?>
                                </p>
                            </div>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="p-12 text-center">
                        <p class="text-gray-400">Belum ada percakapan.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>