<?php
session_start();
require 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['role'] == 'pembeli') {
    $pembeli_id = $_SESSION['user_id'];
    $produk_id = (int)$_POST['produk_id'];
    $jumlah = (int)$_POST['jumlah'];
    $alamat = $conn->real_escape_string($_POST['alamat_pengiriman']);

    $query_produk = $conn->query("SELECT harga, stok FROM produk WHERE id='$produk_id'");
    if ($query_produk->num_rows > 0) {
        $produk = $query_produk->fetch_assoc();
        if ($jumlah <= $produk['stok']) {
            $total_harga = $jumlah * $produk['harga'];
            if ($conn->query("INSERT INTO pesanan (pembeli_id, produk_id, jumlah, total_harga, alamat_pengiriman) VALUES ('$pembeli_id', '$produk_id', '$jumlah', '$total_harga', '$alamat')")) {
                $conn->query("UPDATE produk SET stok=stok-$jumlah WHERE id='$produk_id'");
                echo "<script>alert('Pesanan berhasil dibuat!'); window.location.href='dashboard_pembeli.php';</script>";
            }
        } else {
            echo "<script>alert('Stok tidak cukup!'); window.location.href='dashboard_pembeli.php';</script>";
        }
    }
}
?>