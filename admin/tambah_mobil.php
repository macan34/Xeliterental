<?php
/** @var mysqli $koneksi */
session_start();
include '../koneksi.php';

// Proteksi halaman admin agar lebih aman
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){ 
    header("location:../login.php"); 
    exit(); 
}

if(isset($_POST['simpan'])) {
    $nama_mobil = $_POST['nama_mobil'];
    $jumlah     = $_POST['jumlah'];
    $kondisi    = $_POST['kondisi'];
    $harga_sewa = $_POST['harga_sewa'];

    $insert = mysqli_query($koneksi, "INSERT INTO mobil (nama_mobil, jumlah, kondisi, harga_sewa) VALUES ('$nama_mobil', '$jumlah', '$kondisi', '$harga_sewa')");
    
    if($insert){
        header("location:dashboard.php");
        exit();
    } else {
        echo "<script>alert('Gagal menambahkan data mobil.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mobil - Aksa Rental Admin</title>
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

        /* Tombol Simpan (Biru Navy) */
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

        /* Tombol Kembali (Batal) */
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
        <h3>✨ Tambah Mobil Baru</h3>
        
        <form method="POST">
            <div class="form-group">
                <label for="nama_mobil">Nama Mobil</label>
                <input type="text" id="nama_mobil" name="nama_mobil" placeholder="Masukkan nama mobil (cth: Pajero)" required>
            </div>

            <div class="form-group">
                <label for="jumlah">Jumlah Unit</label>
                <input type="number" id="jumlah" name="jumlah" min="1" placeholder="Masukkan jumlah stok unit" required>
            </div>

            <div class="form-group">
                <label for="kondisi">Kondisi Mobil</label>
                <input type="text" id="kondisi" name="kondisi" placeholder="Contoh: Bagus / Rusak" required>
            </div>

            <div class="form-group">
                <label for="harga_sewa">Harga Sewa / Hari</label>
                <input type="number" id="harga_sewa" name="harga_sewa" min="0" placeholder="Masukkan tarif rupiah (cth: 1000000)" required>
            </div>

            <div class="button-group">
                <button type="submit" name="simpan" class="btn-submit">Simpan Data Mobil</button>
                <a href="dashboard.php" class="btn-cancel">Batal & Kembali</a>
            </div>
        </form>
    </div>

</body>
</html>