<?php
session_start();
require_once 'koneksi.php';

// Jika user sudah login, arahkan ke dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$pesan = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $email        = $conn->real_escape_string($_POST['email']);
    $password     = $_POST['password'];
    $role         = $conn->real_escape_string($_POST['role']);

    $cek_email = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($cek_email->num_rows > 0) {
        $pesan = '<div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm text-center">Email sudah terdaftar!</div>';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $status_verifikasi = ($role == 'Petani') ? 'Menunggu' : 'Terverifikasi';

        $sql = "INSERT INTO users (nama_lengkap, email, password, role, status_verifikasi) 
                VALUES ('$nama_lengkap', '$email', '$hashed_password', '$role', '$status_verifikasi')";

        if ($conn->query($sql) === TRUE) {
            $pesan = '<div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 text-sm text-center">Pendaftaran berhasil! Silakan login.</div>';
        } else {
            $pesan = '<div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm text-center">Terjadi kesalahan sistem.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun — AgriConnect</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 
                        soil: '#2C1A0E', 
                        cream: '#FAF5E9', 
                        leaf: '#2D5A27', 
                        sprout: '#6FA85A' 
                    },
                    fontFamily: { 
                        sans: ['DM Sans', 'sans-serif'], 
                        serif: ['Playfair Display', 'serif'] 
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-cream font-sans flex items-center justify-center min-h-screen p-4">

    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">

        <!-- HEADER LOGO -->
        <div class="text-center mb-8">

            <div class="flex justify-center items-center gap-2 mb-2">
                <img src="uploads/logo.png" alt="AgriConnect" class="h-10">
                <span class="text-2xl font-serif text-leaf font-bold">
                    AgriConnect
                </span>
            </div>

            <p class="text-soil opacity-70 text-sm">
                Mulai langkahmu di ekosistem pertanian digital.
            </p>

        </div>

        <?= $pesan ?>

        <form action="" method="POST" id="registerForm" onsubmit="return validasiRegister()">

            <div class="mb-4">
                <label class="block text-sm font-medium text-soil mb-1">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-leaf focus:ring-1 focus:ring-leaf">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-soil mb-1">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-leaf focus:ring-1 focus:ring-leaf">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-soil mb-1">Password</label>
                <input type="password" name="password" id="password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-leaf focus:ring-1 focus:ring-leaf">
                <p id="error-password" class="text-red-500 text-xs mt-1 hidden">Password minimal 6 karakter!</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-soil mb-1">Konfirmasi Password</label>
                <input type="password" id="konfirmasi_password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-leaf focus:ring-1 focus:ring-leaf">
                <p id="error-match" class="text-red-500 text-xs mt-1 hidden">Password tidak cocok!</p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-soil mb-1">Daftar Sebagai</label>
                <select name="role" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-leaf focus:ring-1 focus:ring-leaf bg-white">
                    <option value="" disabled selected>Pilih peran...</option>
                    <option value="Pembeli">Pembeli</option>
                    <option value="Petani">Petani</option>
                </select>
            </div>

            <button type="submit" class="w-full bg-leaf hover:bg-sprout text-white font-semibold py-3 rounded-lg transition duration-300">
                Daftar Sekarang
            </button>

        </form>

        <p class="text-center text-sm text-soil mt-6">
            Sudah punya akun? 
            <a href="login.php" class="text-leaf font-semibold hover:underline">
                Masuk di sini
            </a>
        </p>

    </div>

    <script>
        function validasiRegister() {
            const password = document.getElementById('password').value;
            const konfirmasi = document.getElementById('konfirmasi_password').value;
            let isValid = true;

            document.getElementById('error-password').classList.add('hidden');
            document.getElementById('error-match').classList.add('hidden');

            if (password.length < 6) {
                document.getElementById('error-password').classList.remove('hidden');
                isValid = false;
            }

            if (password !== konfirmasi) {
                document.getElementById('error-match').classList.remove('hidden');
                isValid = false;
            }

            return isValid;
        }
    </script>

</body>
</html>