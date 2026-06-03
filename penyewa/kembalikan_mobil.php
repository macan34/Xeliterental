<?php
/** @var mysqli $koneksi */
session_start();
include '../koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != "penyewa"){
    header("location:../login.php");
    exit();
}

$id_sewa = $_GET['id'];
$id_user = $_SESSION['id_user'];

// Ambil data penyewaan yang akan dikembalikan (tanggal_kembali IS NULL = belum dikembalikan)
$query = mysqli_query($koneksi, "
    SELECT penyewaan.*, mobil.nama_mobil, mobil.harga_sewa 
    FROM penyewaan
    JOIN mobil ON penyewaan.id_mobil = mobil.id_mobil
    WHERE penyewaan.id_sewa = '$id_sewa' AND penyewaan.id_user = '$id_user' AND (penyewaan.tanggal_kembali IS NULL OR penyewaan.tanggal_kembali = '')
");

if(mysqli_num_rows($query) == 0) {
    echo "<script>alert('Data penyewaan tidak ditemukan atau sudah diajukan pengembalian!'); window.location='dashboard.php';</script>";
    exit();
}

$data_sewa = mysqli_fetch_assoc($query);

// Jika form dikembalikan
if(isset($_POST['kembalikan'])) {
    $tanggal_kembali = $_POST['tanggal_kembali'];
    $kondisi_akhir = $_POST['kondisi_akhir'];
    $catatan = $_POST['catatan'];

    // Hitung jumlah hari sewa
    $tgl_sewa = strtotime($data_sewa['tanggal_sewa']);
    $tgl_kembali = strtotime($tanggal_kembali);
    $selisih_hari = ceil(($tgl_kembali - $tgl_sewa) / (60 * 60 * 24));
    
    // Hitung denda jika keterlambatan
    $denda = 0;
    if($selisih_hari > 7) { // Jika lebih dari 7 hari
        $hari_terlambat = $selisih_hari - 7;
        $denda = $hari_terlambat * ($data_sewa['harga_sewa'] * 0.5); // 50% dari harga sewa
    }

    // Update penyewaan: set status menunggu approval admin
    $update_sewa = "UPDATE penyewaan 
                    SET tanggal_kembali = '$tanggal_kembali',
                        kondisi_akhir = '$kondisi_akhir',
                        catatan = '$catatan',
                        denda = '$denda',
                        status_pengembalian = 'pending'
                    WHERE id_sewa = '$id_sewa'";

    if(mysqli_query($koneksi, $update_sewa)) {
        echo "<script>
            alert('Permintaan pengembalian mobil telah dikirim.\\nMenunggu persetujuan dari Admin.\\n\\nEstimasi Denda: Rp " . number_format($denda, 0, ',', '.') . "');
            window.location='dashboard.php';
        </script>";
        exit();
    } else {
        echo "<script>alert('Gagal mengajukan pengembalian mobil: " . mysqli_error($koneksi) . "'); window.location='dashboard.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kembalikan Mobil - Elite Rental</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 2rem auto;
        }
        
        .rental-info {
            background: linear-gradient(135deg, #e0e7ff 0%, #f0f4ff 100%);
            border: 1px solid #c7d2fe;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .rental-info h3 {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: #3730a3;
        }
        
        .rental-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(199, 210, 254, 0.5);
        }
        
        .rental-row:last-child {
            border-bottom: none;
        }
        
        .rental-label {
            font-weight: 600;
            color: #3730a3;
        }
        
        .rental-value {
            color: #6366f1;
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
        <div class="card">
            <h2 class="mb-3">🔄 Form Pengembalian Mobil</h2>
            
            <div class="rental-info">
                <h3>📋 Detail Penyewaan</h3>
                <div class="rental-row">
                    <span class="rental-label">Nama Mobil:</span>
                    <span class="rental-value"><?php echo htmlspecialchars($data_sewa['nama_mobil']); ?></span>
                </div>
                <div class="rental-row">
                    <span class="rental-label">Tanggal Sewa:</span>
                    <span class="rental-value"><?php echo date('d-m-Y', strtotime($data_sewa['tanggal_sewa'])); ?></span>
                </div>
                <div class="rental-row">
                    <span class="rental-label">Jumlah Unit:</span>
                    <span class="rental-value"><?php echo $data_sewa['jumlah_sewa']; ?> Unit</span>
                </div>
                <div class="rental-row">
                    <span class="rental-label">Harga/Hari:</span>
                    <span class="rental-value">Rp <?php echo number_format($data_sewa['harga_sewa'], 0, ',', '.'); ?></span>
                </div>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="tanggal_kembali">📅 Tanggal Pengembalian</label>
                    <input type="date" id="tanggal_kembali" name="tanggal_kembali" value="<?php echo date('Y-m-d'); ?>" min="<?php echo $data_sewa['tanggal_sewa']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="kondisi_akhir">🔍 Kondisi Mobil saat Dikembalikan</label>
                    <select id="kondisi_akhir" name="kondisi_akhir" required>
                        <option value="">-- Pilih Kondisi --</option>
                        <option value="Sangat Bagus">✓ Sangat Bagus (Seperti Baru)</option>
                        <option value="Bagus">✓ Bagus (Tanpa Lecet)</option>
                        <option value="Cukup">⚠ Cukup (Ada Beberapa Lecet Kecil)</option>
                        <option value="Rusak">✗ Rusak (Kerusakan Signifikan)</option>
                    </select>
                </div>
  <div class="form-container">
        <form method="POST">
            <div class="form-group">
                <label for="tanggal_kembali">Tanggal Pengembalian:<span style="color:red;">*</span></label>
                <input type="date" name="tanggal_kembali" id="tanggal_kembali" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

                <div class="form-group">
                    <label for="catatan">📝 Catatan/Keterangan Tambahan</label>
                    <textarea id="catatan" name="catatan" placeholder="Tulis catatan jika ada kerusakan atau masalah khusus..."></textarea>
                </div>

                <div class="form-actions">
                    <a href="dashboard.php" class="btn" style="background: linear-gradient(135deg, #d1d5db 0%, #9ca3af 100%); color: white; text-align: center;">Batal</a>
                    <button type="submit" name="kembalikan" class="btn btn-success">Konfirmasi Pengembalian</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>


</body>
</html>
