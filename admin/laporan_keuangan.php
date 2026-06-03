<?php
session_start();
include '../koneksi.php';

// Proteksi halaman admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){ 
    header("location:../login.php"); 
    exit(); 
}

/** @var mysqli $koneksi */

// Ambil bulan dan tahun dari filter form, jika tidak ada default ke bulan & tahun saat ini
$bulan_pilihan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilihan = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

// QUERY UTAMA: Mendukung format DATE standar (YYYY-MM-DD) dan format TEXT/VARCHAR (DD-MM-YYYY)
$query_sewa = mysqli_query($koneksi, "
    SELECT penyewaan.*, user.nama, mobil.nama_mobil, mobil.harga_sewa 
    FROM penyewaan
    JOIN user ON penyewaan.id_user = user.id_user
    JOIN mobil ON penyewaan.id_mobil = mobil.id_mobil
    WHERE (
        (MONTH(penyewaan.tanggal_sewa) = '$bulan_pilihan' AND YEAR(penyewaan.tanggal_sewa) = '$tahun_pilihan')
        OR 
        (penyewaan.tanggal_sewa LIKE '%-$bulan_pilihan-$tahun_pilihan%')
    )
    ORDER BY penyewaan.id_sewa ASC
");

// Siapkan variabel awal untuk kalkulasi chart dan total box
$data_pendapatan = [];
$grand_total_bulan = 0;

if ($query_sewa) {
    while($row = mysqli_fetch_assoc($query_sewa)) {
        $total_bayar = $row['jumlah_sewa'] * $row['harga_sewa'];
        $grand_total_bulan += $total_bayar;
        
        // Kelompokkan total pendapatan per nama mobil untuk Grafik
        if(isset($data_pendapatan[$row['nama_mobil']])) {
            $data_pendapatan[$row['nama_mobil']] += $total_bayar;
        } else {
            $data_pendapatan[$row['nama_mobil']] = $total_bayar;
        }
    }

    // Kembalikan pointer data query ke awal agar bisa di-looping lagi pada tabel HTML di bawah
    if(mysqli_num_rows($query_sewa) > 0) {
        mysqli_data_seek($query_sewa, 0);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Diagram Keuangan - Aksa Rental</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #eef2f7; color: #333; padding: 20px; }
        .container { max-width: 1100px; margin: 0 auto; }
        
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .page-header h2 { color: #1e3a8a; }
        
        .filter-container { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; background: white; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .filter-form { display: flex; gap: 8px; }
        .filter-select { padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; background-color: #fff; font-size: 0.95rem; }
        .btn-filter { padding: 8px 16px; background-color: #1e3a8a; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.95rem; }
        .btn-filter:hover { background-color: #1d4ed8; }
        
        .total-box { background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px 20px; border-radius: 8px; color: #166534; font-size: 1.1rem; font-weight: bold; }
        
        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px; }
        @media (max-width: 768px) { .dashboard-grid { grid-template-columns: 1fr; } }
        
        .card { background: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e1e8ed; overflow: hidden; margin-bottom: 20px; }
        .card-header { padding: 15px 20px; border-bottom: 1px solid #e5e7eb; background: #f9fafb; color: #1e3a8a; font-weight: 600; }
        .card-body { padding: 20px; }

        .table-responsive { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background-color: #f9fafb; color: #4b5563; font-weight: 600; padding: 12px 20px; border-bottom: 2px solid #e5e7eb; font-size: 0.9rem; }
        td { padding: 12px 20px; border-bottom: 1px solid #e5e7eb; font-size: 0.95rem; }
        tr:hover { background-color: #f8fafc; }
        
        .text-center { text-align: center; }
        .text-muted { color: #6b7280; }
        
        .btn-back { padding: 8px 16px; background-color: #f3f4f6; color: #4b5563; text-decoration: none; border-radius: 8px; font-weight: 600; border: 1px solid #d1d5db; font-size: 0.95rem; }
        .btn-back:hover { background-color: #e5e7eb; }
    </style>
</head>
<body>

    <div class="container">
        <div class="page-header">
            <h2>📊 Dashboard Analisis Keuangan</h2>
            <a href="dashboard.php" class="btn-back">← Kembali ke Dashboard</a>
        </div>

        <div class="filter-container">
            <form method="GET" class="filter-form">
                <select name="bulan" class="filter-select">
                    <?php foreach ($nama_bulan as $m_num => $m_name): ?>
                        <option value="<?= $m_num; ?>" <?= $bulan_pilihan == $m_num ? 'selected' : ''; ?>><?= $m_name; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="tahun" class="filter-select">
                    <?php for($i = date('Y')-2; $i <= date('Y')+2; $i++): ?>
                        <option value="<?= $i; ?>" <?= $tahun_pilihan == $i ? 'selected' : ''; ?>><?= $i; ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn-filter">Filter Grafik</button>
            </form>
            
            <div class="total-box">
                Omset Bersih: Rp <?= number_format($grand_total_bulan, 0, ',', '.'); ?>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">📈 Grafik Pendapatan Per Jenis Mobil (Bulan <?= $nama_bulan[$bulan_pilihan] ?>)</div>
                <div class="card-body">
                    <canvas id="financialChart" style="max-height: 300px;"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-header">ℹ️ Insight Ringkas</div>
                <div class="card-body">
                    <p style="font-size: 0.95rem; line-height: 1.6; color: #4b5563;">
                        Grafik di samping menampilkan kontribusi omset dari masing-masing unit mobil yang disewa pada bulan <strong><?= $nama_bulan[$bulan_pilihan] ?> <?= $tahun_pilihan ?></strong>.<br><br>
                        Gunakan data visual ini untuk menganalisis mobil apa yang paling laku dan mendatangkan keuntungan tertinggi bagi bisnis rentalmu.
                    </p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">📋 Rincian Transaksi Pendukung</div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No Transaksi</th>
                            <th>Nama Penyewa</th>
                            <th>Mobil</th>
                            <th>Jumlah Unit</th>
                            <th>Tanggal Sewa</th>
                            <th>Total Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if($query_sewa && mysqli_num_rows($query_sewa) > 0) {
                            while($transaksi = mysqli_fetch_assoc($query_sewa)){
                                $total_bayar = $transaksi['jumlah_sewa'] * $transaksi['harga_sewa'];
                        ?>
                        <tr>
                            <td><strong>TRNS-<?= $transaksi['id_sewa']; ?></strong></td>
                            <td><?= htmlspecialchars($transaksi['nama']); ?></td>
                            <td><?= htmlspecialchars($transaksi['nama_mobil']); ?></td>
                            <td><?= $transaksi['jumlah_sewa']; ?> Unit</td>
                            <td><?= date('d-m-Y', strtotime($transaksi['tanggal_sewa'])); ?></td>
                            <td><strong>Rp <?= number_format($total_bayar, 0, ',', '.'); ?></strong></td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center text-muted' style='padding: 30px;'>Tidak ada transaksi pada periode bulan ini.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const namaMobilLabels = <?php echo json_encode(array_keys($data_pendapatan)); ?>;
        const totalUangData = <?php echo json_encode(array_values($data_pendapatan)); ?>;

        const ctx = document.getElementById('financialChart').getContext('2d');
        const financialChart = new Chart(ctx, {
            type: 'bar', 
            data: {
                labels: namaMobilLabels,
                datasets: [{
                    label: 'Total Pendapatan (Rp)',
                    data: totalUangData,
                    backgroundColor: [
                        'rgba(30, 58, 138, 0.7)',  
                        'rgba(16, 185, 129, 0.7)', 
                        'rgba(245, 158, 11, 0.7)', 
                        'rgba(239, 68, 68, 0.7)',  
                        'rgba(139, 92, 246, 0.7)'  
                    ],
                    borderColor: [
                        '#1e3a8a', '#10b981', '#f59e0b', '#ef4444', '#8b5a2b'
                    ],
                    borderWidth: 1.5,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
</body>
</html>