<?php
require_once 'koneksi.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    $conn->query("UPDATE users SET status_verifikasi='Ditolak' WHERE id=$id");
}

header("Location: verifikasi_petani.php?msg=Akun berhasil ditolak");
exit;