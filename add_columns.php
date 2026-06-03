<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "rentalmobil_rpl1";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Array of ALTER TABLE queries
$queries = array(
    "ALTER TABLE penyewaan ADD COLUMN IF NOT EXISTS status_pengembalian ENUM('pending', 'approved', 'rejected') DEFAULT NULL",
    "ALTER TABLE penyewaan ADD COLUMN IF NOT EXISTS tanggal_kembali DATETIME DEFAULT NULL",
    "ALTER TABLE penyewaan ADD COLUMN IF NOT EXISTS kondisi_akhir VARCHAR(100) DEFAULT NULL",
    "ALTER TABLE penyewaan ADD COLUMN IF NOT EXISTS catatan TEXT DEFAULT NULL",
    "ALTER TABLE penyewaan ADD COLUMN IF NOT EXISTS denda DECIMAL(10,2) DEFAULT 0"
);

echo "<h2>Menambahkan Kolom ke Tabel Penyewaan</h2>";

foreach($queries as $query) {
    if(mysqli_query($koneksi, $query)) {
        echo "<p style='color: green;'>✓ Query berhasil: " . substr($query, 0, 50) . "...</p>";
    } else {
        echo "<p style='color: red;'>✗ Error: " . mysqli_error($koneksi) . "</p>";
    }
}

echo "<p><a href='../penyewa/dashboard.php'>← Kembali ke Dashboard Penyewa</a></p>";

mysqli_close($koneksi);
?>
