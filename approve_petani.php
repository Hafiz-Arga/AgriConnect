<?php
require_once 'koneksi.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    $conn->query("UPDATE users SET status_verifikasi='Disetujui' WHERE id=$id");
}

header("Location: verifikasi_petani.php?msg=Akun berhasil disetujui");
exit;