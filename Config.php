<?php
/**
 * AGRICONNECT - CONFIGURASI UTAMA
 * Semua konfigurasi database, session, upload, dan helper functions
 */

session_start();

// ======================================
// KONFIGURASI DATABASE
// ======================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'agriconnect');
define('DB_CHARSET', 'utf8mb4');

// ======================================
// KONFIGURASI UPLOAD
// ======================================
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Buat folder upload jika belum ada
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
    chmod(UPLOAD_DIR, 0777);
}

// ======================================
// SESSION & SECURITY
// ======================================
// Session timeout 60 menit
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 3600) {
    session_unset();
    session_destroy();
    header('Location: login.php?expired=1');
    exit();
}

// Anti CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function getCSRFToken() {
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return hash_equals($_SESSION['csrf_token'], $token);
}

// ======================================
// DATABASE CONNECTION
// ======================================
function getConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// ======================================
// SECURITY HELPERS
// ======================================
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^08[0-9]{8,12}$/', $phone);
}

function generateOrderNumber() {
    return 'AGRI-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// ======================================
// UPLOAD HANDLER
// ======================================
function uploadFile($file, $prefix = '') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, ALLOWED_EXTENSIONS)) {
        return false;
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }

    $filename = ($prefix ? $prefix . '_' : '') . time() . '_' . uniqid() . '.' . $file_extension;
    $target_file = UPLOAD_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $filename;
    }
    return false;
}

// ======================================
// FORMAT UTILITY
// ======================================
function formatRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

function getStatusPill($status, $type = 'lahan') {
    $pills = [
        // Lahan status
        'buka' => ['class' => 'pill-buka', 'text' => 'Buka', 'color' => 'moss'],
        'tutup' => ['class' => 'pill-tutup', 'text' => 'Tutup', 'color' => 'bark'],
        
        // Peminat status
        'menunggu' => ['class' => 'pill-menunggu', 'text' => 'Menunggu', 'color' => 'sun'],
        'dipilih' => ['class' => 'pill-dipilih', 'text' => 'Dipilih', 'color' => 'sprout'],
        'dikonfirmasi' => ['class' => 'pill-konfirmasi', 'text' => 'Dikonfirmasi', 'color' => 'river'],
        'ditolak' => ['class' => 'pill-ditolak', 'text' => 'Ditolak', 'color' => 'tomato'],
        
        // Pesanan status
        'menunggu_konfirmasi' => ['class' => 'pill-menunggu', 'text' => 'Menunggu Pembayaran', 'color' => 'sun'],
        'diproses' => ['class' => 'pill-diproses', 'text' => 'Diproses', 'color' => 'river'],
        'dikirim' => ['class' => 'pill-dikirim', 'text' => 'Dikirim', 'color' => 'river'],
        'selesai' => ['class' => 'pill-selesai', 'text' => 'Selesai', 'color' => 'leaf'],
        'ditolak' => ['class' => 'pill-ditolak', 'text' => 'Ditolak', 'color' => 'tomato'],
        
        // Produk status
        'aktif' => ['class' => 'pill-aktif', 'text' => 'Aktif', 'color' => 'moss'],
        'nonaktif' => ['class' => 'pill-nonaktif', 'text' => 'Nonaktif', 'color' => 'bark']
    ];
    
    return $pills[$status] ?? ['class' => 'pill-menunggu', 'text' => ucfirst($status), 'color' => 'bark'];
}

// ======================================
// JSON RESPONSE HELPER
// ======================================
function jsonResponse($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// ======================================
// REQUIRE AUTH
// ======================================
function requireAuth($roles = []) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    
    if (!empty($roles) && !in_array($_SESSION['role'], $roles)) {
        jsonResponse(['error' => 'Access denied'], 403);
    }
    
    $_SESSION['login_time'] = time(); // Refresh session
}

// ======================================
// GET USER DATA
// ======================================
function getCurrentUser($pdo) {
    if (!isset($_SESSION['user_id'])) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// ======================================
// PAGINATION
// ======================================
function getPagination($total, $current = 1, $perPage = 10) {
    $totalPages = ceil($total / $perPage);
    return [
        'current' => $current,
        'total' => $totalPages,
        'next' => $current < $totalPages ? $current + 1 : null,
        'prev' => $current > 1 ? $current - 1 : null,
        'start' => ($current - 1) * $perPage + 1,
        'end' => min($current * $perPage, $total)
    ];
}
?>