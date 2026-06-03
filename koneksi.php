<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "rentalmobil_rpl1";

$koneksi = mysqli_connect($host, $user, $pass, $db);

// Perbaikan: Check koneksi dengan cara yang lebih robust
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset agar tidak ada masalah encoding
mysqli_set_charset($koneksi, "utf8");
?>