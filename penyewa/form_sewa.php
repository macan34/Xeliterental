<?php
/** @var mysqli $koneksi */
session_start();
include '../koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != "penyewa"){
    header("location:../login.php");
    exit();
}

$id_mobil = $_GET['id'];
$id_user  = $_SESSION['id_user'];

// Ambil detail mobil untuk validasi stok
$query_mobil = mysqli_query($koneksi, "SELECT * FROM mobil WHERE id_mobil='$id_mobil'");
$mobil = mysqli_fetch_assoc($query_mobil);

if(isset($_POST['proses_sewa'])) {
    $jumlah_sewa   = $_POST['jumlah_sewa'];
    $tanggal_sewa  = $_POST['tanggal_sewa'];
    $jumlah_hari   = $_POST['jumlah_hari'];
    
    // Hitung tanggal kembali
    $tanggal_kembali = date('Y-m-d', strtotime($tanggal_sewa . " + " . $jumlah_hari . " days"));

    // Validasi jika input sewa melebihi stok yang ada
    if($jumlah_sewa > $mobil['jumlah']){
        echo "<script>alert('Gagal! Jumlah sewa melebihi stok mobil yang tersedia.'); window.location='dashboard.php';</script>";
        exit();
    }

    // PANGGIL STORED PROCEDURE
    $sql_procedure = "CALL sewa_mobil('$id_user', '$id_mobil', '$jumlah_sewa', '$tanggal_sewa')";
    $eksekusi = mysqli_query($koneksi, $sql_procedure);

    if($eksekusi) {
        // Update tanggal_kembali yang diharapkan (untuk keperluan admin)
        $update_query = "UPDATE penyewaan SET tanggal_kembali_ekspektasi = '$tanggal_kembali' WHERE id_user = '$id_user' AND id_mobil = '$id_mobil' AND tanggal_sewa = '$tanggal_sewa' ORDER BY id_sewa DESC LIMIT 1";
        mysqli_query($koneksi, $update_query);
        
        header("location:dashboard.php");
        exit();
    } else {
        echo "<script>alert('Gagal melakukan penyewaan.'); window.location='dashboard.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Sewa Mobil - Elite Rental</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 2rem auto;
        }
        
        .form-card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            border: 1px solid #e5e7eb;
        }
        
        .car-info {
            background: linear-gradient(135deg, #eff6ff 0%, #e0f2fe 100%);
            border: 1px solid #7dd3fc;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .car-info h3 {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: #0c2d6b;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }
        
        .info-label {
            font-weight: 600;
            color: #0c2d6b;
        }
        
        .info-value {
            color: #06b6d4;
            font-weight: 600;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .form-actions .btn {
            flex: 1;
        }

        .calculation-box {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            border: 2px solid #22c55e;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .calculation-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .calculation-row strong {
            color: #15803d;
            font-weight: 600;
        }

        .calculation-row .value {
            color: #059669;
            font-weight: 600;
        }

        .calculation-total {
            border-top: 2px solid #22c55e;
            padding-top: 1rem;
            margin-top: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1.1rem;
        }

        .calculation-total .label {
            font-weight: 700;
            color: #15803d;
        }

        .calculation-total .total-value {
            font-size: 1.3rem;
            color: #15803d;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="#" class="brand">X Elite Rental</a>
        <div class="nav-info">
            <a href="dashboard.php" style="text-decoration: underline;">← Kembali ke Dashboard</a>
        </div>
    </nav>

    <div class="form-container">
        <div class="form-card">
            <h2 class="mb-3">🚗 Formulir Penyewaan Mobil</h2>
            
            <div class="car-info">
                <h3><?php echo htmlspecialchars($mobil['nama_mobil']); ?></h3>
                <div class="info-row">
                    <span class="info-label">Kondisi:</span>
                    <span class="info-value"><?php echo htmlspecialchars($mobil['kondisi']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Harga Sewa:</span>
                    <span class="info-value">Rp <?php echo number_format($mobil['harga_sewa'], 0, ',', '.'); ?>/hari</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Stok Tersedia:</span>
                    <span class="info-value"><?php echo $mobil['jumlah']; ?> Unit</span>
                </div>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="jumlah_sewa">Jumlah Unit yang Disewa</label>
                    <input type="number" id="jumlah_sewa" name="jumlah_sewa" min="1" max="<?php echo $mobil['jumlah']; ?>" value="1" required onchange="hitungTotal()" oninput="hitungTotal()">
                    <small style="color: #6b7280; display: block; margin-top: 0.25rem;">Maksimal: <?php echo $mobil['jumlah']; ?> unit</small>
                </div>

                <div class="form-group">
                    <label for="tanggal_sewa">Tanggal Mulai Sewa</label>
                    <input type="date" id="tanggal_sewa" name="tanggal_sewa" value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" required onchange="hitungTotal()" oninput="hitungTotal()">
                </div>

                <div class="form-group">
                    <label for="jumlah_hari">Jumlah Hari</label>
                    <input type="number" id="jumlah_hari" name="jumlah_hari" min="1" value="1" required onchange="hitungTotal()" oninput="hitungTotal()">
                    <small style="color: #6b7280; display: block; margin-top: 0.25rem;">Berapa hari ingin menyewa mobil ini</small>
                </div>

                <!-- ================= KALKULASI BIAYA ================= -->
                <div class="calculation-box">
                    <div class="calculation-row">
                        <strong>💵 Harga per Hari:</strong>
                        <span class="value">Rp <?php echo number_format($mobil['harga_sewa'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="calculation-row">
                        <strong>📊 Unit:</strong>
                        <span class="value" id="display_unit">1</span>
                    </div>
                    <div class="calculation-row">
                        <strong>📅 Hari:</strong>
                        <span class="value" id="display_hari">1</span>
                    </div>
                    <div class="calculation-row">
                        <strong>📅 Tanggal Kembali:</strong>
                        <span class="value" id="display_tgl_kembali"><?php echo date('d-m-Y', strtotime(date('Y-m-d') . ' + 1 days')); ?></span>
                    </div>
                    <div class="calculation-total">
                        <span class="label">💳 TOTAL BIAYA:</span>
                        <span class="total-value" id="total_biaya">Rp <?php echo number_format($mobil['harga_sewa'], 0, ',', '.'); ?></span>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="dashboard.php" class="btn" style="background: linear-gradient(135deg, #d1d5db 0%, #9ca3af 100%); color: white; text-align: center;">Batal</a>
                    <button type="submit" name="proses_sewa" class="btn btn-primary">Konfirmasi Sewa</button>
                </div>
            </form>

            <script>
                const hargaSewa = <?php echo $mobil['harga_sewa']; ?>;

                function hitungTotal() {
                    const jumlahUnit = parseInt(document.getElementById('jumlah_sewa').value) || 1;
                    const jumlahHari = parseInt(document.getElementById('jumlah_hari').value) || 1;
                    const tanggalSewa = document.getElementById('tanggal_sewa').value;

                    // Hitung total biaya
                    const totalBiaya = jumlahUnit * hargaSewa * jumlahHari;

                    // Update display
                    document.getElementById('display_unit').textContent = jumlahUnit;
                    document.getElementById('display_hari').textContent = jumlahHari;
                    document.getElementById('total_biaya').textContent = 'Rp ' + totalBiaya.toLocaleString('id-ID');

                    // Hitung tanggal kembali
                    if (tanggalSewa) {
                        const tanggal = new Date(tanggalSewa);
                        tanggal.setDate(tanggal.getDate() + jumlahHari);
                        
                        const hari = String(tanggal.getDate()).padStart(2, '0');
                        const bulan = String(tanggal.getMonth() + 1).padStart(2, '0');
                        const tahun = tanggal.getFullYear();
                        
                        document.getElementById('display_tgl_kembali').textContent = hari + '-' + bulan + '-' + tahun;
                    }
                }

                // Jalankan saat halaman dimuat
                document.addEventListener('DOMContentLoaded', function() {
                    hitungTotal();
                });
            </script>
        </div>
    </div>

</body>
</html>