<?php
session_start();
require_once 'koneksi.php';

// ==========================
// AUTO LOGIN DARI COOKIE TOKEN
// ==========================
if (!isset($_SESSION['user_id']) && isset($_COOKIE['login_token'])) {

    $token = $conn->real_escape_string($_COOKIE['login_token']);
    $query = $conn->query("SELECT * FROM users WHERE remember_token = '$token'");

    if ($query && $query->num_rows > 0) {

        $user = $query->fetch_assoc();

        // 🚨 CEK VERIFIKASI PETANI
        if ($user['role'] == 'Petani' && $user['status_verifikasi'] != 'Disetujui') {
            setcookie('login_token', '', time() - 3600, '/');
        } else {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['nama']    = $user['nama_lengkap'];
            $_SESSION['last_activity'] = time();
        }

    } else {
        setcookie('login_token', '', time() - 3600, '/');
    }
}

// ==========================
// REDIRECT JIKA SUDAH LOGIN
// ==========================
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// ==========================
// AMBIL COOKIE EMAIL
// ==========================
$email_cookie = $_COOKIE['remember_email'] ?? "";

$pesan = "";

// ==========================
// PROSES LOGIN
// ==========================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $query = $conn->query("SELECT * FROM users WHERE email = '$email'");

    if ($query && $query->num_rows > 0) {

        $user = $query->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            // ==========================
            // CEK VERIFIKASI PETANI
            // ==========================
            if ($user['role'] == 'Petani') {

                if ($user['status_verifikasi'] == 'Menunggu') {

                    $pesan = '<div class="bg-yellow-100 text-yellow-800 p-3 rounded-lg text-sm text-center">
                        ⏳ Akun Anda sedang menunggu verifikasi admin
                    </div>';

                } elseif ($user['status_verifikasi'] == 'Ditolak') {

                    $pesan = '<div class="bg-red-100 text-red-700 p-3 rounded-lg text-sm text-center">
                        ❌ Akun Anda ditolak admin
                    </div>';

                } else {

                    // LOGIN PETANI (SUDAH DISETUJUI)
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role']    = $user['role'];
                    $_SESSION['nama']    = $user['nama_lengkap'];
                    $_SESSION['last_activity'] = time();

                    // REMEMBER ME
                    if (isset($_POST['remember'])) {
                        $token = bin2hex(random_bytes(32));
                        $conn->query("UPDATE users SET remember_token='$token' WHERE id=".$user['id']);

                        setcookie('login_token', $token, time() + 600, "/", "", false, true);
                        setcookie('remember_email', $email, time() + 600, "/", "", false, true);
                    } else {
                        setcookie('remember_email', '', time() - 3600, '/');
                    }

                    header("Location: index.php");
                    exit;
                }

            } else {

                // LOGIN PEMBELI
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role']    = $user['role'];
                $_SESSION['nama']    = $user['nama_lengkap'];
                $_SESSION['last_activity'] = time();

                // REMEMBER ME
                if (isset($_POST['remember'])) {
                    $token = bin2hex(random_bytes(32));
                    $conn->query("UPDATE users SET remember_token='$token' WHERE id=".$user['id']);

                    setcookie('login_token', $token, time() + 600, "/", "", false, true);
                    setcookie('remember_email', $email, time() + 600, "/", "", false, true);
                } else {
                    setcookie('remember_email', '', time() - 3600, '/');
                }

                header("Location: index.php");
                exit;
            }

        } else {
            $pesan = '<div class="bg-red-100 text-red-700 p-3 rounded-lg text-sm text-center">Password salah!</div>';
        }

    } else {
        $pesan = '<div class="bg-red-100 text-red-700 p-3 rounded-lg text-sm text-center">Email tidak ditemukan!</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — AgriConnect</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <!-- SWEET ALERT -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body class="bg-[#F8F5F0] flex items-center justify-center min-h-screen px-4 font-['Inter']">

<!-- POPUP SESSION HABIS -->
<?php if(isset($_GET['expired'])): ?>
<script>
window.onload = function() {
    Swal.fire({
        icon: 'warning',
        title: 'Session Habis',
        text: 'Silakan login kembali.',
        confirmButtonColor: '#4F6F52'
    });
}
</script>
<?php endif; ?>

<div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-gray-100 p-8 md:p-10">

    <div class="text-center mb-8">
        <img src="uploads/logo.png" class="w-20 h-20 mx-auto mb-4">
        <h1 class="text-3xl font-bold text-[#3A4D39]">AgriConnect</h1>
        <p class="text-gray-500 text-sm mt-2">Masuk ke akun Anda</p>
    </div>

    <?php if($pesan): ?>
        <div class="mb-6"><?= $pesan ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">

        <div>
            <label class="text-xs text-gray-400">Email</label>
            <input 
                type="email" 
                name="email" 
                required 
                value="<?= htmlspecialchars($email_cookie) ?>"
                class="w-full px-4 py-3 border rounded-xl">
        </div>

        <div>
            <label class="text-xs text-gray-400">Password</label>
            <input type="password" name="password" required class="w-full px-4 py-3 border rounded-xl">
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" <?= $email_cookie ? 'checked' : '' ?>>
            Ingat Saya
        </label>

        <button class="w-full py-3 bg-[#4F6F52] text-white rounded-xl">
            Masuk
        </button>
    </form>

    <div class="text-center text-sm mt-6">
        Belum punya akun? <a href="register.php" class="text-green-700">Daftar</a>
    </div>

</div>

</body>
</html>