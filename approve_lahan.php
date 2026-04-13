<?php
require_once 'admin_only.php';
require_once 'koneksi.php';

$id = (int)$_GET['id'];

$conn->query("UPDATE lahan SET status='approved' WHERE id=$id");

header("Location: verifikasi_lahan.php");