<?php
/** @var mysqli $koneksi */
session_start();
include '../koneksi.php';

// Proteksi halaman admin agar lebih aman
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){ 
    header("location:../login.php"); 
    exit(); 
}

// Mengambil ID dari URL untuk memuat data lama
if(!isset($_GET['id'])) {
    header("location:dashboard.php");
    exit();
}

$id = $_GET['id'];
$query = mysqli_query($koneksi, "SELECT * FROM mobil WHERE id_mobil='$id'");

// Jika data mobil tidak ditemukan
if(mysqli_num_rows($query) == 0) {
    header("location:dashboard.php");
    exit();
}

$data = mysqli_fetch_assoc($query);

if(isset($_POST['update'])) {
    $nama_mobil = $_POST['nama_mobil'];
    $jumlah     = $_POST['jumlah'];
    $kondisi    = $_POST['kondisi'];
    $harga_sewa = $_POST['harga_sewa'];

    $update = mysqli_query($koneksi, "UPDATE mobil SET nama_mobil='$nama_mobil', jumlah='$jumlah', kondisi='$kondisi', harga_sewa='$harga_sewa' WHERE id_mobil='$id'");
    
    if($update){
        header("location:dashboard.php");
        exit();
    } else {
        echo "<script>alert('Gagal memperbarui data mobil.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mobil - Aksa Rental Admin</title>
    <style>
        /* Menggunakan font modern global yang serasi dengan dashboard */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #eef2f7; /* Background abu-abu lembut */
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* Desain Card Utama */
        .form-container {
            background-color: #ffffff;
            width: 100%;
            max-width: 480px;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e1e8ed;
        }

        /* Judul Halaman */
        h3 {
            color: #1e3a8a; /* Warna biru navy Aksa Rental */
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
        }

        /* Layout Form Group */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 0.5rem;
        }

        /* Desain Input Field */
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background-color: #f9fafb;
            color: #1f2937;
            transition: all 0.3s ease;
        }

        /* Efek fokus input saat diklik */
        .form-group input:focus {
            outline: none;
            border-color: #1e3a8a;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(30, 58, 138, 0.1);
        }

        /* Wrapper Aksi Tombol */
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1.75rem;
        }

        /* Tombol Update (Biru Navy) */
        .btn-submit {
            width: 100%;
            padding: 0.8rem;
            font-size: 1rem;
            font-weight: 600;
            color: #ffffff;
            background-color: #1e3a8a;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease;
            text-align: center;
        }

        .btn-submit:hover {
            background-color: #1d4ed8;
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        /* Tombol Batal */
        .btn-cancel {
            width: 100%;
            padding: 0.8rem;
            font-size: 0.95rem;
            font-weight: 600;
            color: #4b5563;
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s ease;
        }

        .btn-cancel:hover {
            background-color: #e5e7eb;
            color: #1f2937;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <h3>✏️ Edit Data Mobil</h3>
        
        <form method="POST">
            <div class="form-group">
                <label for="nama_mobil">Nama Mobil</label>
                <input type="text" id="nama_mobil" name="nama_mobil" value="<?php echo htmlspecialchars($data['nama_mobil']); ?>" required>
            </div>

            <div class="form-group">
                <label for="jumlah">Jumlah Unit</label>
                <input type="number" id="jumlah" name="jumlah" min="0" value="<?php echo htmlspecialchars($data['jumlah']); ?>" required>
            </div>

            <div class="form-group">
                <label for="kondisi">Kondisi Mobil</label>
                <input type="text" id="kondisi" name="kondisi" value="<?php echo htmlspecialchars($data['kondisi']); ?>" required>
            </div>

            <div class="form-group">
                <label for="harga_sewa">Harga Sewa / Hari</label>
                <input type="number" id="harga_sewa" name="harga_sewa" min="0" value="<?php echo htmlspecialchars($data['harga_sewa']); ?>" required>
            </div>

            <div class="button-group">
                <button type="submit" name="update" class="btn-submit">Perbarui Data</button>
                <a href="dashboard.php" class="btn-cancel">Batal</a>
            </div>
        </form>
    </div>

</body>
</html>