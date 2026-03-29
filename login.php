<?php
session_start();
// Jika sudah ada session (sudah login), langsung arahkan ke dashboard
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Cek apakah ada cookie remember_username
$saved_username = isset($_COOKIE['remember_username']) ? $_COOKIE['remember_username'] : '';
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <title>Login AgriConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-green-50 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-md">
      <h2 class="text-2xl font-bold text-center text-green-600 mb-2">AgriConnect</h2>
      <p class="text-center text-gray-500 mb-6">Masuk ke akun Anda</p>

      <form method="post" action="proses_login.php" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Username / Email</label>
          <input type="text" name="username" required value="<?php echo htmlspecialchars($saved_username); ?>"
                 class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400"
                 placeholder="Masukkan username" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input type="password" name="password" required
                 class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400"
                 placeholder="Masukkan password" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Login sebagai</label>
          <select name="role"
                  class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400">
            <option value="petani">Petani</option>
            <option value="pembeli">Pembeli</option>
          </select>
        </div>

        <div class="flex items-center justify-between">
          <label class="flex items-center text-sm text-gray-600">
            <input type="checkbox" name="remember" class="mr-2" <?php if($saved_username != '') echo 'checked'; ?> />
            Ingat saya
          </label>
          <a href="#" class="text-sm text-green-600 hover:underline">Lupa password?</a>
        </div>

        <button type="submit"
                class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">
          Login
        </button>
      </form>
    </div>
  </body>
</html>