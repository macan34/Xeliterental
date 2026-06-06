<?php 
session_start();
include '../koneksi.php'; // Memastikan ekstensi .php lengkap agar tidak undefined $koneksi

// Proteksi halaman penyewa
if(!isset($_SESSION['role']) || $_SESSION['role'] != "penyewa"){
    header("location:../login.php?pesan=belum_login");
    exit();
}

$id_user_login = $_SESSION['id_user'];

/* --- LOGIKA PAGINATION --- */
$limit = 8; // Batas maksimal mobil per halaman
$page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

// Simpan filter kategori jika ada
$kategori_url = isset($_GET['kategori']) ? '&kategori=' . urlencode($_GET['kategori']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penyewa - Elite Rental Mobil</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        /* --- RESET & GLOBAL MODERN MINIMALIS --- */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: #334155;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* --- NAVBAR ELEGAN --- */
        .navbar {
            background: #ffffff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border-bottom: 1px solid #f1f5f9;
        }

        .brand {
            font-size: 1.3rem;
            font-weight: 800;
            color: #2563eb;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .nav-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .nav-info span {
            font-size: 0.95rem;
            font-weight: 600;
            color: #475569;
        }

        .logout-btn {
            font-size: 0.9rem;
            font-weight: 600;
            color: #ef4444;
            text-decoration: none;
            padding: 6px 14px;
            border-radius: 8px;
            background: #fef2f2;
            transition: all 0.2s ease;
        }

        .logout-btn:hover {
            background: #fee2e2;
        }

        /* --- HEADER & BADGE --- */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e293b;
        }

        .badge-realtime {
            background: #ecfdf5;
            color: #059669;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid #a7f3d0;
        }

        /* --- CATEGORY FILTER --- */
        .category-filter {
            display: flex;
            gap: 8px;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 18px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            border-radius: 24px;
            cursor: pointer;
            font-weight: 600;
            color: #64748b;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.85rem;
        }

        .filter-btn:hover {
            background: #f8fafc;
            color: #334155;
            border-color: #cbd5e1;
        }

        .filter-btn.active {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        /* --- CAR GRID VERTICAL SYSTEM (FIXED BUG) --- */
        .grid-cars {
            display: grid;
            /* Membagi otomatis menjadi 4 kolom jika layar luas, atau minimal lebar card 240px */
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.25rem;
            padding-bottom: 1.25rem;
        }

        /* --- CAR CARD MODERN COMPACT --- */
        .car-card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        .car-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px -3px rgba(0, 0, 0, 0.06);
            border-color: #cbd5e1;
        }

        .car-image-wrapper {
            background: #f8fafc;
            height: 135px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .car-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .car-card:hover .car-image-wrapper img {
            transform: scale(1.03);
        }

        .category-tag {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(4px);
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 700;
            color: #2563eb;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            z-index: 2;
            border: 1px solid #e2e8f0;
        }

        .car-body {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .car-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .car-specs {
            display: flex;
            gap: 8px;
            margin-bottom: 1rem;
            font-size: 0.75rem;
            color: #64748b;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 4px;
            background: #f1f5f9;
            padding: 3px 6px;
            border-radius: 4px;
            font-weight: 500;
        }

        .car-price-box {
            border-top: 1px solid #f1f5f9;
            padding-top: 0.75rem;
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }

        .car-price {
            font-size: 1rem;
            font-weight: 800;
            color: #2563eb;
            white-space: nowrap;
        }

        .car-price span {
            font-size: 0.7rem;
            color: #64748b;
            font-weight: 400;
        }

        .btn-sewa {
            background: #2563eb;
            color: white;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            transition: background 0.2s;
            text-align: center;
        }

        .btn-sewa:hover {
            background: #1d4ed8;
        }

        /* --- STYLING PAGINATION MODERN --- */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .pagination-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            border-radius: 8px;
            color: #475569;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .pagination-btn:hover:not(.disabled) {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #1e293b;
        }

        .pagination-btn.active {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }

        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f8fafc;
        }

        /* --- CARDS PANELS (TABLES CONTAINER) --- */
        .main-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .card-header-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* --- MODERN MINIMALIST TABLE --- */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9rem;
        }

        th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            padding: 12px 16px;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 16px;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.875rem;
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* --- BADGES STATUS --- */
        .badge-status {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        .badge-warning {
            background: #fef3c7;
            color: #d97706;
        }
        .badge-success {
            background: #dcfce7;
            color: #15803d;
        }

        .btn-action-danger {
            background: #fef2f2;
            color: #ef4444;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid #fee2e2;
            transition: all 0.2s;
        }

        .btn-action-danger:hover {
            background: #fee2e2;
        }

        .text-center { text-align: center; }
        .text-muted { color: #94a3b8; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="#" class="brand">Elite Rental</a>
        <div class="nav-info">
            <span>👤 <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="section-header">
            <h2 class="section-title">🚙 Katalog Armada Pilihan</h2>
            <span class="badge-realtime">Sistem Real-Time</span>
        </div>
        
        <div class="category-filter">
            <button class="filter-btn <?php echo !isset($_GET['kategori']) ? 'active' : ''; ?>" onclick="window.location='<?php echo $_SERVER['PHP_SELF']; ?>'">✨ Semua Tipe</button>
            <button class="filter-btn <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'LCGC') ? 'active' : ''; ?>" onclick="window.location='?kategori=LCGC'">🍃 LCGC (Hemat)</button>
            <button class="filter-btn <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'Eksklusif') ? 'active' : ''; ?>" onclick="window.location='?kategori=Eksklusif'">💎 Eksklusif</button>
            <button class="filter-btn <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'Sport 4x4') ? 'active' : ''; ?>" onclick="window.location='?kategori=Sport 4x4'">🏔️ Sport 4x4</button>
        </div>
            
        <div class="grid-cars">
            <?php 
            /** @var mysqli $koneksi */
            
            // Hitung Total Data untuk Pagination Berdasarkan Filter Kategori
            if(isset($_GET['kategori'])) {
                $kat = mysqli_real_escape_string($koneksi, $_GET['kategori']);
                $query_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM mobil WHERE jumlah > 0 AND kategori = '$kat'");
                $total_data = mysqli_fetch_assoc($query_total)['total'];
                
                $query_mobil = mysqli_query($koneksi, "SELECT * FROM mobil WHERE jumlah > 0 AND kategori = '$kat' ORDER BY nama_mobil LIMIT $start, $limit");
            } else {
                $query_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM mobil WHERE jumlah > 0");
                $total_data = mysqli_fetch_assoc($query_total)['total'];
                
                $query_mobil = mysqli_query($koneksi, "SELECT * FROM mobil WHERE jumlah > 0 ORDER BY nama_mobil LIMIT $start, $limit");
            }
            
            $total_pages = ceil($total_data / $limit);
            
            if(mysqli_num_rows($query_mobil) > 0) {
                while($row = mysqli_fetch_assoc($query_mobil)){
                    $harga = number_format($row['harga_sewa'], 0, ',', '.');
                    $kategori_mobil = !empty($row['kategori']) ? $row['kategori'] : 'Umum';

                    if (!empty($row['gambar']) && file_exists('../img/' . $row['gambar'])) {
                        $path_gambar = '../img/' . $row['gambar'];
                    } else {
                        $path_gambar = '../assets/default-car.png'; 
                    }
            ?>
                <div class="car-card">
                    <div class="car-image-wrapper">
                        <span class="category-tag"><?php echo htmlspecialchars($kategori_mobil); ?></span>
                        <img src="<?php echo $path_gambar; ?>" alt="Foto <?php echo htmlspecialchars($row['nama_mobil']); ?>">
                    </div>
                    <div class="car-body">
                        <h3 class="car-title"><?php echo htmlspecialchars($row['nama_mobil']); ?></h3>
                        
                        <div class="car-specs">
                            <div class="spec-item">🔧 <span><?php echo htmlspecialchars($row['kondisi']); ?></span></div>
                            <div class="spec-item">📦 <span>Sisa <?php echo $row['jumlah']; ?> Unit</span></div>
                        </div>
                        
                        <div class="car-price-box">
                            <div class="car-price">Rp <?php echo $harga; ?><span>/hari</span></div>
                            <a href="form_sewa.php?id=<?php echo $row['id_mobil']; ?>" class="btn-sewa">Sewa</a>
                        </div>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo "<div style='grid-column: 1 / -1; background:#fff; text-align:center; padding: 50px 20px; border-radius:12px; border:1px solid #e2e8f0;'><p class='text-muted'>Tidak ada armada di kategori ini yang siap disewa saat ini.</p></div>";
            }
            ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <?php if ($page > 1): ?>
                    <a href="?halaman=<?php echo $page - 1 . $kategori_url; ?>" class="pagination-btn">&lt;</a>
                <?php else: ?>
                    <a href="#" class="pagination-btn disabled">&lt;</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?halaman=<?php echo $i . $kategori_url; ?>" class="pagination-btn <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?halaman=<?php echo $page + 1 . $kategori_url; ?>" class="pagination-btn">&gt;</a>
                <?php else: ?>
                    <a href="#" class="pagination-btn disabled">&gt;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="main-card">
            <h2 class="card-header-title">📋 Penyewaan Aktif Saya</h2>
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
                                <td><a href="kembalikan_mobil.php?id=<?php echo $aktif['id_sewa']; ?>" class="btn-action-danger">Kembalikan</a></td>
                            </tr>
                            <?php } 
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="main-card">
            <h2 class="card-header-title">⏳ Menunggu Persetujuan Admin</h2>
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
                                <td><span class="badge-status badge-warning">Menunggu</span></td>
                            </tr>
                            <?php } 
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="main-card">
            <h2 class="card-header-title">✓ Riwayat Penyewaan Selesai</h2>
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
                                <td><span class="badge-status badge-success">Selesai<?php if(isset($riwayat['denda']) && $riwayat['denda'] > 0) echo " (Denda)"; ?></span></td>
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