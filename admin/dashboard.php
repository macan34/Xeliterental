<?php 
/** @var mysqli $koneksi */
session_start();
include '../koneksi.php';

// Proteksi halaman admin
if($_SESSION['role'] != "admin"){
    header("location:../login.php?pesan=belum_login");
    exit();
}

// Hitung statistik
$stat_mobil = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM mobil"))['total'];
$stat_penyewaan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM penyewaan"))['total'];
$stat_pending = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM penyewaan WHERE status_pengembalian = 'pending'"))['total'];
$stat_user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM user WHERE role = 'penyewa'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin -  XElite Rental Mobil</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .stat-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            text-align: center;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .stat-card.primary {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
        }
        
        .stat-card.success {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
        }
        
        .stat-card.warning {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: white;
        }
        
        .stat-card.info {
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
            color: white;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .quick-action {
            margin-top: 2rem;
        }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .action-btn {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            border: 2px solid #e5e7eb;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }
        
        .action-btn:hover {
            border-color: #1e40af;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
        }
        
        .action-icon {
            font-size: 2rem;
        }
        
        .action-text {
            font-weight: 600;
            color: var(--text-primary);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="#" class="brand">🚗 X Elite Rental - Admin</a>
        <div class="nav-info">
            <span>👤 <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="container">
        <!-- ================= STATISTIK ================= -->
        <div class="grid grid-4" style="margin-bottom: 2rem;">
            <div class="stat-card primary">
                <div class="stat-label">📊 Total Mobil</div>
                <div class="stat-number"><?php echo $stat_mobil; ?></div>
            </div>
            <div class="stat-card success">
                <div class="stat-label">📋 Total Penyewaan</div>
                <div class="stat-number"><?php echo $stat_penyewaan; ?></div>
            </div>
            <div class="stat-card warning">
                <div class="stat-label">⏳ Menunggu Approval</div>
                <div class="stat-number"><?php echo $stat_pending; ?></div>
            </div>
            <div class="stat-card info">
                <div class="stat-label">👥 Total Penyewa</div>
                <div class="stat-number"><?php echo $stat_user; ?></div>
            </div>
        </div>

        <!-- ================= AKSI CEPAT ================= -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title">⚡ Aksi Cepat</h2>
            </div>
            <div class="action-grid">
                <a href="tambah_mobil.php" class="action-btn">
                    <span class="action-icon">➕</span>
                    <span class="action-text">Tambah Mobil</span>
                </a>
                <a href="approval_pengembalian.php" class="action-btn">
                    <span class="action-icon">✓</span>
                    <span class="action-text">Approval Pengembalian</span>
                </a>
                  <a href="laporan_keuangan.php" class="action-btn">
                    <span class="action-icon">$</span>
                    <span class="action-text">Laporan Keuangan</span>
                </a>
            </div>
        </div>

        <!-- ================= KELOLA MOBIL ================= -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title">🚙 Kelola Data Mobil</h2>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Mobil</th>
                            <th>Jumlah Unit</th>
                            <th>Kondisi</th>
                            <th>Harga/Hari</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        $query_mobil = mysqli_query($koneksi, "SELECT * FROM mobil ORDER BY nama_mobil");
                        if(mysqli_num_rows($query_mobil) > 0) {
                            while($row = mysqli_fetch_assoc($query_mobil)){
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['nama_mobil']); ?></strong></td>
                            <td><?php echo $row['jumlah']; ?> Unit</td>
                            <td><?php echo htmlspecialchars($row['kondisi']); ?></td>
                            <td>Rp <?php echo number_format($row['harga_sewa'], 0, ',', '.'); ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="edit_mobil.php?id=<?php echo $row['id_mobil']; ?>" class="btn btn-warning btn-small">Edit</a>
                                    <a href="hapus_mobil.php?id=<?php echo $row['id_mobil']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Yakin ingin menghapus mobil ini?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center text-muted'>Tidak ada data mobil</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= RIWAYAT PENYEWAAN ================= -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📋 Riwayat Transaksi Penyewaan Terbaru</h2>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No Transaksi</th>
                            <th>Nama Penyewa</th>
                            <th>Mobil yang Disewa</th>
                            <th>Jumlah Unit</th>
                            <th>Tanggal Sewa</th>
                            <th>Total Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $query_sewa = mysqli_query($koneksi, "
                            SELECT penyewaan.*, user.nama, mobil.nama_mobil, mobil.harga_sewa 
                            FROM penyewaan
                            JOIN user ON penyewaan.id_user = user.id_user
                            JOIN mobil ON penyewaan.id_mobil = mobil.id_mobil
                            ORDER BY penyewaan.id_sewa DESC
                            LIMIT 10
                        ");
                        
                        if(mysqli_num_rows($query_sewa) > 0) {
                            while($transaksi = mysqli_fetch_assoc($query_sewa)){
                                $total_bayar = $transaksi['jumlah_sewa'] * $transaksi['harga_sewa'];
                        ?>
                        <tr>
                            <td><strong>TRNS-<?php echo $transaksi['id_sewa']; ?></strong></td>
                            <td><?php echo htmlspecialchars($transaksi['nama']); ?></td>
                            <td><?php echo htmlspecialchars($transaksi['nama_mobil']); ?></td>
                            <td><?php echo $transaksi['jumlah_sewa']; ?> Unit</td>
                            <td><?php echo date('d-m-Y', strtotime($transaksi['tanggal_sewa'])); ?></td>
                            <td><strong>Rp <?php echo number_format($total_bayar, 0, ',', '.'); ?></strong></td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center text-muted'>Tidak ada data penyewaan</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>