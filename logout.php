<?php
session_start();
require_once 'koneksi.php';

if (isset($_SESSION['user_id'])) {

    $id = $_SESSION['user_id'];

    // hapus token di database
    $conn->query("UPDATE users SET remember_token=NULL WHERE id=$id");
}

// hancurkan session
session_unset();
session_destroy();

header("Location: login.php");
exit;