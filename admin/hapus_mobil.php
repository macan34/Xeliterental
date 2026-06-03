<?php
/** @var mysqli $koneksi */
session_start();
// Perbaikan: Pastikan file koneksi.php terbaca dengan benar dari luar folder admin
include '../koneksi.php';

// Perbaikan keamanan & pengalihan halaman login jika session salah
if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("location:../login.php");
    exit();
}

// Mengambil ID mobil yang akan dihapus
$id = $_GET['id'];

// Eksekusi penghapusan data berdasarkan id_mobil
$hapus = mysqli_query($koneksi, "DELETE FROM mobil WHERE id_mobil='$id'");

if ($hapus) {
    // Jika berhasil, kembali ke dashboard admin
    header("location:dashboard.php");
    exit();
} else {
    // Jika gagal (biasanya karena foreign key / terikat dengan tabel penyewaan)
    echo "Gagal menghapus data. Data mungkin terikat dengan transaksi di tabel penyewaan.";
}
?>