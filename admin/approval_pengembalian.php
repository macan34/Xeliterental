<?php
/** @var mysqli $koneksi */
session_start();
include '../koneksi.php';

// Proteksi halaman admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("location:../login.php");
    exit();
}

// Proses approval
if(isset($_GET['action']) && isset($_GET['id'])) {
    $id_sewa = $_GET['id'];
    $action = $_GET['action'];

    // Ambil data penyewaan
    $query = mysqli_query($koneksi, "
        SELECT penyewaan.*, mobil.id_mobil, mobil.harga_sewa 
        FROM penyewaan
        JOIN mobil ON penyewaan.id_mobil = mobil.id_mobil
        WHERE penyewaan.id_sewa = '$id_sewa' AND penyewaan.status_pengembalian = 'pending'
    ");

    if(mysqli_num_rows($query) == 0) {
        echo "<script>alert('Data tidak ditemukan!'); window.location='approval_pengembalian.php';</script>";
        exit();
    }

    $data_sewa = mysqli_fetch_assoc($query);

    if($action == 'approve') {
        // Approve: Update status dan tambah stok mobil
        $update_sewa = "UPDATE penyewaan 
                        SET status_pengembalian = 'approved'
                        WHERE id_sewa = '$id_sewa'";
        
        $update_mobil = "UPDATE mobil 
                         SET jumlah = jumlah + {$data_sewa['jumlah_sewa']}
                         WHERE id_mobil = {$data_sewa['id_mobil']}";

        if(mysqli_query($koneksi, $update_sewa) && mysqli_query($koneksi, $update_mobil)) {
            echo "<script>alert('Pengembalian mobil berhasil di-approve!'); window.location='approval_pengembalian.php';</script>";
            exit();
        } else {
            echo "<script>alert('Gagal approve pengembalian!'); window.location='approval_pengembalian.php';</script>";
            exit();
        }
    } else if($action == 'reject') {
        // Reject: Reset tanggal_kembali dan status
        $update_sewa = "UPDATE penyewaan 
                        SET status_pengembalian = 'rejected',
                            tanggal_kembali = NULL,
                            kondisi_akhir = NULL,
                            catatan = NULL,
                            denda = 0
                        WHERE id_sewa = '$id_sewa'";

        if(mysqli_query($koneksi, $update_sewa)) {
            echo "<script>alert('Pengembalian mobil ditolak. Penyewa harus mengajukan ulang!'); window.location='approval_pengembalian.php';</script>";
            exit();
        } else {
            echo "<script>alert('Gagal reject pengembalian!'); window.location='approval_pengembalian.php';</script>";
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Pengembalian - Elite Rental Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .approval-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .approval-header {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
            align-items: start;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        
        .approval-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .approval-row {
            display: flex;
            flex-direction: column;
        }
        
        .approval-row strong {
            color: var(--text-primary);
            font-size: 0.9rem;
        }
        
        .approval-row span {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-top: 0.25rem;
        }
        
        .approval-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        
        .tab-menu {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .tab-btn {
            padding: 0.75rem 1.5rem;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: 600;
            color: var(--text-secondary);
            transition: all 0.3s;
        }
        
        .tab-btn.active {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="#" class="brand">🚗 Elite Rental - Approval Pengembalian</a>
        <div class="nav-info">
            <a href="dashboard.php">← Kembali</a>
        </div>
    </nav>

    <div class="container">
        <div class="tab-menu">
            <button class="tab-btn active" onclick="switchTab('pending')">⏳ Menunggu Approval (<?php 
                $pending_count = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM penyewaan WHERE status_pengembalian = 'pending'"))['total'];
                echo $pending_count;
            ?>)</button>
            <button class="tab-btn" onclick="switchTab('approved')">✓ Sudah Di-Approve</button>
        </div>

        <!-- ================= TAB PENDING ================= -->
        <div id="pending" class="tab-content active">
            <?php 
            $query_pending = mysqli_query($koneksi, "
                SELECT penyewaan.*, user.nama as nama_penyewa, mobil.nama_mobil
                FROM penyewaan
                JOIN user ON penyewaan.id_user = user.id_user
                JOIN mobil ON penyewaan.id_mobil = mobil.id_mobil
                WHERE penyewaan.status_pengembalian = 'pending'
                ORDER BY penyewaan.id_sewa DESC
            ");
            
            if(mysqli_num_rows($query_pending) == 0) {
                echo "<div class='card'><p class='text-center text-muted'>Tidak ada pengembalian yang menunggu approval</p></div>";
            } else {
                while($pending = mysqli_fetch_assoc($query_pending)){
            ?>
            <div class="approval-card">
                <div class="approval-header">
                    <div>
                        <h3 style="margin-bottom: 0.5rem;">📦 TRNS-<?php echo $pending['id_sewa']; ?></h3>
                        <p style="color: var(--text-secondary); font-size: 0.9rem;">Diajukan oleh: <strong><?php echo htmlspecialchars($pending['nama_penyewa']); ?></strong></p>
                    </div>
                    <span class="badge badge-warning">Menunggu</span>
                </div>

                <div class="approval-info">
                    <div class="approval-row">
                        <strong>🚙 Mobil</strong>
                        <span><?php echo htmlspecialchars($pending['nama_mobil']); ?></span>
                    </div>
                    <div class="approval-row">
                        <strong>📅 Tanggal Sewa</strong>
                        <span><?php echo date('d-m-Y', strtotime($pending['tanggal_sewa'])); ?></span>
                    </div>
                    <div class="approval-row">
                        <strong>⏰ Seharusnya Kembali</strong>
                        <span style="color: #059669; font-weight: 600;">
                            <?php 
                            $tgl_kembali_ekspektasi = isset($pending['tanggal_kembali_ekspektasi']) && !empty($pending['tanggal_kembali_ekspektasi']) 
                                ? date('d-m-Y', strtotime($pending['tanggal_kembali_ekspektasi']))
                                : date('d-m-Y', strtotime($pending['tanggal_sewa'] . ' + 7 days'));
                            echo $tgl_kembali_ekspektasi;
                            ?>
                        </span>
                    </div>
                    <div class="approval-row">
                        <strong>🔄 Tanggal Kembali Aktual</strong>
                        <span><?php echo date('d-m-Y', strtotime($pending['tanggal_kembali'])); ?></span>
                    </div>
                    <div class="approval-row">
                        <strong>📊 Jumlah Unit</strong>
                        <span><?php echo $pending['jumlah_sewa']; ?> Unit</span>
                    </div>
                    <div class="approval-row">
                        <strong>🔍 Kondisi Akhir</strong>
                        <span><?php echo htmlspecialchars($pending['kondisi_akhir']); ?></span>
                    </div>
                    <div class="approval-row">
                        <strong>💰 Denda</strong>
                        <span style="color: var(--danger); font-weight: 600;">Rp <?php echo number_format($pending['denda'], 0, ',', '.'); ?></span>
                    </div>
                </div>

                <?php if($pending['catatan']) { ?>
                <div style="background: #f3f4f6; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                    <strong style="display: block; margin-bottom: 0.5rem;">📝 Catatan dari Penyewa:</strong>
                    <p style="color: var(--text-secondary);"><?php echo htmlspecialchars($pending['catatan']); ?></p>
                </div>
                <?php } ?>

                <div class="approval-actions">
                    <a href="?action=approve&id=<?php echo $pending['id_sewa']; ?>" class="btn btn-success" onclick="return confirm('Setujui pengembalian mobil ini?')">✓ Approve</a>
                    <a href="?action=reject&id=<?php echo $pending['id_sewa']; ?>" class="btn btn-danger" onclick="return confirm('Tolak pengembalian mobil ini?')">✗ Tolak</a>
                </div>
            </div>
            <?php }} ?>
        </div>

        <!-- ================= TAB APPROVED ================= -->
        <div id="approved" class="tab-content">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No Transaksi</th>
                            <th>Nama Penyewa</th>
                            <th>Mobil</th>
                            <th>Seharusnya Kembali</th>
                            <th>Tanggal Dikembalikan</th>
                            <th>Kondisi</th>
                            <th>Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $query_approved = mysqli_query($koneksi, "
                            SELECT penyewaan.*, user.nama as nama_penyewa, mobil.nama_mobil
                            FROM penyewaan
                            JOIN user ON penyewaan.id_user = user.id_user
                            JOIN mobil ON penyewaan.id_mobil = mobil.id_mobil
                            WHERE penyewaan.status_pengembalian = 'approved'
                            ORDER BY penyewaan.id_sewa DESC
                            LIMIT 50
                        ");
                        
                        if(mysqli_num_rows($query_approved) == 0) {
                            echo "<tr><td colspan='7' class='text-center text-muted'>Belum ada pengembalian yang di-approve</td></tr>";
                        } else {
                            while($approved = mysqli_fetch_assoc($query_approved)){
                                $tgl_kembali_ekspektasi = isset($approved['tanggal_kembali_ekspektasi']) && !empty($approved['tanggal_kembali_ekspektasi']) 
                                    ? date('d-m-Y', strtotime($approved['tanggal_kembali_ekspektasi']))
                                    : date('d-m-Y', strtotime($approved['tanggal_sewa'] . ' + 7 days'));
                        ?>
                        <tr>
                            <td><strong>TRNS-<?php echo $approved['id_sewa']; ?></strong></td>
                            <td><?php echo htmlspecialchars($approved['nama_penyewa']); ?></td>
                            <td><?php echo htmlspecialchars($approved['nama_mobil']); ?></td>
                            <td><strong style="color: #059669;">📅 <?php echo $tgl_kembali_ekspektasi; ?></strong></td>
                            <td><?php echo date('d-m-Y', strtotime($approved['tanggal_kembali'])); ?></td>
                            <td><?php echo htmlspecialchars($approved['kondisi_akhir']); ?></td>
                            <td><strong>Rp <?php echo number_format($approved['denda'], 0, ',', '.'); ?></strong></td>
                        </tr>
                        <?php }} ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>

</body>
</html>
