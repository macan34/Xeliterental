<?php 
session_start();
include '../koneksi.php';

// Proteksi halaman penyewa
if(!isset($_SESSION['role']) || $_SESSION['role'] != "penyewa"){
    header("location:../login.php?pesan=belum_login");
    exit();
}

$id_user_login = $_SESSION['id_user'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penyewa - Elite Rental Mobil</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <nav class="navbar">
        <a href="#" class="brand">X Elite Rental</a>
        <div class="nav-info">
            <span>👤 <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="container">
        <!-- ================= DAFTAR MOBIL TERSEDIA ================= -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">🚙 Daftar Mobil Tersedia</h2>
                <span class="badge badge-primary">Siap Disewa</span>
            </div>
            
            <div class="grid grid-3">
                <?php 
                /** @var mysqli $koneksi */
                $query_mobil = mysqli_query($koneksi, "SELECT * FROM mobil WHERE jumlah > 0 ORDER BY nama_mobil");
                
                if(mysqli_num_rows($query_mobil) > 0) {
                    while($row = mysqli_fetch_assoc($query_mobil)){
                        $harga = number_format($row['harga_sewa'], 0, ',', '.');
                ?>
                    <div class="product-card">
                        <div class="product-image">🚗</div>
                        <div class="product-body">
                            <h3 class="product-title"><?php echo htmlspecialchars($row['nama_mobil']); ?></h3>
                            <div class="product-info">
                                <p><strong>Kondisi:</strong> <?php echo htmlspecialchars($row['kondisi']); ?></p>
                                <p><strong>Stok:</strong> <?php echo $row['jumlah']; ?> Unit</p>
                            </div>
                            <p class="product-price">Rp <?php echo $harga; ?><span style="font-size: 0.7rem; color: #6b7280;">/hari</span></p>
                            <div class="product-footer">
                                <span class="stock-badge">Tersedia</span>
                                <a href="form_sewa.php?id=<?php echo $row['id_mobil']; ?>" class="btn btn-primary btn-small">Sewa</a>
                            </div>
                        </div>
                    </div>
                <?php 
                    }
                } else {
                    echo "<div class='card' style='grid-column: 1/-1;'><p class='text-center text-muted'>Tidak ada mobil yang tersedia saat ini</p></div>";
                }
                ?>
            </div>
        </div>

        <!-- ================= PENYEWAAN AKTIF ================= -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📋 Penyewaan Aktif Saya</h2>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No Transaksi</th>
                            <th>Nama Mobil</th>
                            <th>Jumlah Unit</th>
                            <th>Tanggal Sewa</th>
                            <th>Harus Dikembalikan</th>
                            <th>Total Bayar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $query_aktif = mysqli_query($koneksi, "
                            SELECT penyewaan.*, mobil.nama_mobil, mobil.harga_sewa 
                            FROM penyewaan
                            JOIN mobil ON penyewaan.id_mobil = mobil.id_mobil
                            WHERE penyewaan.id_user = '$id_user_login' AND (penyewaan.tanggal_kembali IS NULL OR penyewaan.tanggal_kembali = '')
                            ORDER BY penyewaan.id_sewa DESC
                        ");
                        
                        if(!$query_aktif || mysqli_num_rows($query_aktif) == 0) {
                            echo "<tr><td colspan='7' class='text-center text-muted'>Tidak ada penyewaan aktif</td></tr>";
                        } else {
                            while($aktif = mysqli_fetch_assoc($query_aktif)){
                                $total = $aktif['jumlah_sewa'] * $aktif['harga_sewa'];
                                // Hitung tanggal harus dikembalikan (default 7 hari jika tidak ada data ekspektasi)
                                $tgl_kembali_ekspektasi = isset($aktif['tanggal_kembali_ekspektasi']) && !empty($aktif['tanggal_kembali_ekspektasi']) 
                                    ? date('d-m-Y', strtotime($aktif['tanggal_kembali_ekspektasi']))
                                    : date('d-m-Y', strtotime($aktif['tanggal_sewa'] . ' + 7 days'));
                            ?>
                            <tr>
                                <td><strong>TRNS-<?php echo $aktif['id_sewa']; ?></strong></td>
                                <td><?php echo htmlspecialchars($aktif['nama_mobil']); ?></td>
                                <td><?php echo $aktif['jumlah_sewa']; ?> Unit</td>
                                <td><?php echo date('d-m-Y', strtotime($aktif['tanggal_sewa'])); ?></td>
                                <td><strong style="color: #dc3545;">📅 <?php echo $tgl_kembali_ekspektasi; ?></strong></td>
                                <td><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                                <td><a href="kembalikan_mobil.php?id=<?php echo $aktif['id_sewa']; ?>" class="btn btn-danger btn-small">Kembalikan</a></td>
                            </tr>
                            <?php } 
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= MENUNGGU APPROVAL ================= -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">⏳ Menunggu Persetujuan Admin</h2>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No Transaksi</th>
                            <th>Nama Mobil</th>
                            <th>Tanggal Sewa</th>
                            <th>Seharusnya Kembali</th>
                            <th>Dikembalikan</th>
                            <th>Kondisi</th>
                            <th>Estimasi Denda</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $query_pending = mysqli_query($koneksi, "
                            SELECT penyewaan.*, mobil.nama_mobil, mobil.harga_sewa 
                            FROM penyewaan
                            JOIN mobil ON penyewaan.id_mobil = mobil.id_mobil
                            WHERE penyewaan.id_user = '$id_user_login' AND penyewaan.status_pengembalian = 'pending'
                            ORDER BY penyewaan.id_sewa DESC
                        ");
                        
                        if(!$query_pending || mysqli_num_rows($query_pending) == 0) {
                            echo "<tr><td colspan='8' class='text-center text-muted'>Tidak ada yang menunggu persetujuan</td></tr>";
                        } else {
                            while($pending = mysqli_fetch_assoc($query_pending)){
                                $tgl_kembali_ekspektasi = isset($pending['tanggal_kembali_ekspektasi']) && !empty($pending['tanggal_kembali_ekspektasi']) 
                                    ? date('d-m-Y', strtotime($pending['tanggal_kembali_ekspektasi']))
                                    : date('d-m-Y', strtotime($pending['tanggal_sewa'] . ' + 7 days'));
                            ?>
                            <tr>
                                <td><strong>TRNS-<?php echo $pending['id_sewa']; ?></strong></td>
                                <td><?php echo htmlspecialchars($pending['nama_mobil']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($pending['tanggal_sewa'])); ?></td>
                                <td><strong style="color: #059669;">📅 <?php echo $tgl_kembali_ekspektasi; ?></strong></td>
                                <td><?php echo isset($pending['tanggal_kembali']) ? date('d-m-Y', strtotime($pending['tanggal_kembali'])) : '-'; ?></td>
                                <td><?php echo isset($pending['kondisi_akhir']) ? htmlspecialchars($pending['kondisi_akhir']) : '-'; ?></td>
                                <td><strong>Rp <?php echo isset($pending['denda']) ? number_format($pending['denda'], 0, ',', '.') : '0'; ?></strong></td>
                                <td><span class="badge badge-warning">Menunggu</span></td>
                            </tr>
                            <?php } 
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= RIWAYAT PENYEWAAN ================= -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">✓ Riwayat Penyewaan Selesai</h2>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No Transaksi</th>
                            <th>Nama Mobil</th>
                            <th>Jumlah Unit</th>
                            <th>Tanggal Sewa</th>
                            <th>Seharusnya Kembali</th>
                            <th>Total Bayar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $query_riwayat = mysqli_query($koneksi, "
                            SELECT penyewaan.*, mobil.nama_mobil, mobil.harga_sewa 
                            FROM penyewaan
                            JOIN mobil ON penyewaan.id_mobil = mobil.id_mobil
                            WHERE penyewaan.id_user = '$id_user_login' AND penyewaan.status_pengembalian = 'approved'
                            ORDER BY penyewaan.id_sewa DESC
                        ");
                        
                        if(!$query_riwayat || mysqli_num_rows($query_riwayat) == 0) {
                            echo "<tr><td colspan='7' class='text-center text-muted'>Belum ada riwayat penyewaan yang selesai</td></tr>";
                        } else {
                            while($riwayat = mysqli_fetch_assoc($query_riwayat)){
                                $total = $riwayat['jumlah_sewa'] * $riwayat['harga_sewa'];
                                $tgl_kembali_ekspektasi = isset($riwayat['tanggal_kembali_ekspektasi']) && !empty($riwayat['tanggal_kembali_ekspektasi']) 
                                    ? date('d-m-Y', strtotime($riwayat['tanggal_kembali_ekspektasi']))
                                    : date('d-m-Y', strtotime($riwayat['tanggal_sewa'] . ' + 7 days'));
                            ?>
                            <tr>
                                <td><strong>TRNS-<?php echo $riwayat['id_sewa']; ?></strong></td>
                                <td><?php echo htmlspecialchars($riwayat['nama_mobil']); ?></td>
                                <td><?php echo $riwayat['jumlah_sewa']; ?> Unit</td>
                                <td><?php echo date('d-m-Y', strtotime($riwayat['tanggal_sewa'])); ?></td>
                                <td><strong style="color: #059669;">📅 <?php echo $tgl_kembali_ekspektasi; ?></strong></td>
                                <td><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                                <td><span class="badge badge-success">Selesai<?php if(isset($riwayat['denda']) && $riwayat['denda'] > 0) echo " (Denda)"; ?></span></td>
                            </tr>
                            <?php } 
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>