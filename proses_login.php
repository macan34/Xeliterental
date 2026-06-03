<?php
// Mengaktifkan session PHP
session_start();

// Menghubungkan dengan koneksi database
include 'koneksi.php';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password']; // Jika di database di-hash (misal MD5), gunakan md5($_POST['password'])

    // Query untuk mencocokkan data sesuai struktur tabel 'user' di image_115322.png
    $query = "SELECT * FROM user WHERE username='$username' AND password='$password'";
    $login = mysqli_query($koneksi, $query);
    $cek   = mysqli_num_rows($login);

    if ($cek > 0) {
        $data = mysqli_fetch_assoc($login);

        // Membuat session login
        $_SESSION['id_user']  = $data['id_user'];
        $_SESSION['nama']     = $data['nama'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['role']     = $data['role'];

        // Pengalihan halaman berdasarkan role enum('admin', 'penyewa')
        if ($data['role'] == "admin") {
            header("location:admin/dashboard.php");
        } else if ($data['role'] == "penyewa") {
            header("location:penyewa/dashboard.php");
        } else {
            header("location:login.php?pesan=gagal");
        }
    } else {
        header("location:login.php?pesan=gagal");
    }
}
?>