<?php
/** @var mysqli $koneksi */
session_start();
include '../koneksi.php';

// Proteksi halaman admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("location:../login.php");
    exit();
}

if(!isset($_GET['id'])) {
    echo "ID Transaksi tidak ditemukan!";
    exit();
}

$id_sewa = $_GET['id'];

// Ambil data detail untuk kebutuhan nota resmi
// Ambil data detail untuk kebutuhan nota resmi
$query = mysqli_query($koneksi, "
    SELECT penyewaan.*, 
           user.nama as nama_penyewa,
           mobil.nama_mobil, mobil.harga_sewa 
    FROM penyewaan
    JOIN user ON penyewaan.id_user = user.id_user
    JOIN mobil ON penyewaan.id_mobil = mobil.id_mobil
    WHERE penyewaan.id_sewa = '$id_sewa' AND penyewaan.status_pengembalian = 'approved'
");

if(mysqli_num_rows($query) == 0) {
    echo "Data transaksi tidak valid atau belum di-approve!";
    exit();
}

$nota = mysqli_fetch_assoc($query);

// Hitung total sewa dasar (Jika struktur DB Anda menyimpan total, silakan sesuaikan)
$tgl_awal = new DateTime($nota['tanggal_sewa']);
$tgl_akhir = new DateTime($nota['tanggal_kembali']);
$durasi = $tgl_awal->diff($tgl_akhir)->days;
if($durasi == 0) $durasi = 1; // Minimal hitungan 1 hari

$total_sewa_dasar = $nota['harga_sewa'] * $nota['jumlah_sewa'] * $durasi;
$grand_total = $total_sewa_dasar + $nota['denda'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Bukti Pengembalian #TRNS-<?php echo $nota['id_sewa']; ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace; /* Khas gaya struk/nota */
            color: #000;
            background: #fff;
            padding: 20px;
            font-size: 14px;
        }
        .nota-box {
            max-width: 600px;
            margin: auto;
            border: 1px dashed #000;
            padding: 20px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .header-title { font-size: 20px; font-weight: bold; margin: 0; }
        hr { border: none; border-top: 1px dashed #000; margin: 15px 0; }
        .table-info { width: 100%; border-collapse: collapse; }
        .table-info td { padding: 4px 0; vertical-align: top; }
        .table-items { width: 100%; margin-top: 15px; }
        .table-items th { border-bottom: 1px dashed #000; text-align: left; padding: 5px 0; }
        .table-items td { padding: 5px 0; }
        .footer-note { margin-top: 30px; font-size: 12px; font-style: italic; }
        
        /* Hilangkan tombol cetak saat kertas di-print */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="max-width: 600px; margin: 0 auto 10px auto; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 15px; background: #059669; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">🖨️ Cetak Langsung</button>
        <button onclick="window.close()" style="padding: 8px 15px; background: #6b7280; color: white; border: none; border-radius: 4px; cursor: pointer;">Tutup</button>
    </div>

    <div class="nota-box">
        <div class="text-center">
            <p class="header-title">🚗 ELITE RENTAL</p>
            <p style="margin: 5px 0 0 0;">Jasa Penyewaan Mobil Terbaik & Terpercaya</p>
            <p style="font-size: 11px; margin: 2px 0;">Yogyakarta, Indonesia</p>
        </div>
        
        <hr>
        
        <table class="table-info">
            <tr>
                <td style="width: 40%;"><strong>No. Transaksi</strong></td>
                <td>: TRNS-<?php echo $nota['id_sewa']; ?></td>
            </tr>
            <tr>
                <td><strong>Nama Penyewa</strong></td>
                <td>: <?php echo htmlspecialchars($nota['nama_penyewa']); ?></td>
            </tr>
            <tr>
                <td><strong>Tanggal Cetak</strong></td>
                <td>: <?php echo date('d-m-Y H:i:s'); ?></td>
            </tr>
            <tr>
                <td><strong>Status Transaksi</strong></td>
                <td>: <strong>LUNAS & SELESAI</strong></td>
            </tr>
        </table>
        
        <hr>
        
        <table class="table-info">
            <tr>
                <td style="width: 40%;"><strong>Unit Kendaraan</strong></td>
                <td>: <?php echo htmlspecialchars($nota['nama_mobil']); ?> (<?php echo $nota['jumlah_sewa']; ?> Unit)</td>
            </tr>
            <tr>
                <td><strong>Tanggal Sewa</strong></td>
                <td>: <?php echo date('d-m-Y', strtotime($nota['tanggal_sewa'])); ?></td>
            </tr>
            <tr>
                <td><strong>Tanggal Kembali</strong></td>
                <td>: <?php echo date('d-m-Y', strtotime($nota['tanggal_kembali'])); ?></td>
            </tr>
            <tr>
                <td><strong>Kondisi Pengembalian</strong></td>
                <td>: <?php echo htmlspecialchars($nota['kondisi_akhir']); ?></td>
            </tr>
        </table>
        
        <hr>
        
        <table class="table-items">
            <thead>
                <tr>
                    <th>Deskripsi Ringkas</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Biaya Sewa Dasaran (<?php echo $durasi; ?> Hari x Rp <?php echo number_format($nota['harga_sewa'], 0, ',', '.'); ?>)</td>
                    <td class="text-right">Rp <?php echo number_format($total_sewa_dasar, 0, ',', '.'); ?></td>
                </tr>
                <?php if($nota['denda'] > 0) { ?>
                <tr>
                    <td style="color: red;">Denda Kerusakan / Keterlambatan</td>
                    <td class="text-right" style="color: red;">Rp <?php echo number_format($nota['denda'], 0, ',', '.'); ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <td style="border-top: 1px dashed #000; padding-top: 8px;"><strong>GRAND TOTAL PEMBAYARAN</strong></td>
                    <td class="text-right" style="border-top: 1px dashed #000; padding-top: 8px;"><strong>Rp <?php echo number_format($grand_total, 0, ',', '.'); ?></strong></td>
                </tr>
            </tbody>
        </table>
        
        <hr>
        
        <div class="text-center footer-note">
            <p>Terima kasih telah menggunakan layanan Elite Rental.</p>
            <p>Nota ini sah dikeluarkan oleh sistem dan digunakan sebagai bukti pengembalian armada yang valid.</p>
        </div>
    </div>

    <script>
        // Otomatis memicu fungsi cetak bawaan browser saat halaman dimuat
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>