<?php
ob_start(); // 🔥 WAJIB biar aman dari error header
session_start();
require_once 'koneksi.php';

// ==========================
// CEK LOGIN
// ==========================
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$lawan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// VALIDASI LAWAN CHAT
if ($lawan_id == 0 || $lawan_id == $user_id) {
    header("Location: pesan.php");
    exit;
}

// ==========================
// AMBIL DATA LAWAN
// ==========================
$info_lawan = $conn->query("SELECT nama_lengkap, role FROM users WHERE id = $lawan_id")->fetch_assoc();

// ==========================
// KIRIM PESAN
// ==========================
if (isset($_POST['kirim_pesan']) && !empty(trim($_POST['pesan']))) {

    $pesan = $conn->real_escape_string($_POST['pesan']);

    $conn->query("INSERT INTO chat_messages (pengirim_id, penerima_id, pesan) 
                  VALUES ($user_id, $lawan_id, '$pesan')");

    header("Location: chat.php?id=$lawan_id");
    exit;
}

// ==========================
// AMBIL CHAT
// ==========================
$chats = $conn->query("SELECT * FROM chat_messages 
                       WHERE (pengirim_id = $user_id AND penerima_id = $lawan_id) 
                       OR (pengirim_id = $lawan_id AND penerima_id = $user_id) 
                       ORDER BY created_at ASC");
?>

<?php require_once 'header.php'; ?>
<?php require_once 'sidebar.php'; ?>

<main class="flex-1 flex flex-col h-full overflow-hidden bg-cream pt-4 md:pt-0">

    <!-- HEADER CHAT -->
    <div class="bg-white p-4 shadow-sm flex items-center gap-3 border-b border-gray-100">
        <a href="pesan.php" class="text-leaf">❮</a>
        <div>
            <h3 class="font-bold text-soil">
                <?= htmlspecialchars($info_lawan['nama_lengkap']) ?>
            </h3>
            <p class="text-[10px] text-leaf uppercase font-bold tracking-widest">
                <?= $info_lawan['role'] ?>
            </p>
        </div>
    </div>

    <!-- CHAT AREA -->
    <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-[#F8F5F0]">

        <?php while($c = $chats->fetch_assoc()): 
            $is_me = ($c['pengirim_id'] == $user_id);
        ?>

        <div class="flex <?= $is_me ? 'justify-end' : 'justify-start' ?>">
            <div class="max-w-[80%] p-3 rounded-2xl shadow-sm text-sm 
                <?= $is_me 
                    ? 'bg-leaf text-white rounded-tr-none' 
                    : 'bg-white text-soil rounded-tl-none border border-gray-100' ?>">

                <?= htmlspecialchars($c['pesan']) ?>

                <p class="text-[9px] mt-1 opacity-70 text-right">
                    <?= date('H:i', strtotime($c['created_at'])) ?>
                </p>

            </div>
        </div>

        <?php endwhile; ?>

    </div>

    <!-- INPUT -->
    <div class="p-4 bg-white border-t border-gray-100">
        <form method="POST" class="flex gap-2">

            <input type="text" name="pesan" placeholder="Ketik pesan..." required
                   autocomplete="off"
                   class="flex-1 px-4 py-2 border border-gray-200 rounded-full focus:ring-1 focus:ring-leaf outline-none text-sm">

            <button type="submit" name="kirim_pesan"
                    class="w-10 h-10 bg-leaf text-white rounded-full flex items-center justify-center hover:bg-sprout transition">
                ➔
            </button>

        </form>
    </div>

</main>

<?php require_once 'footer.php'; ?>