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
    $kategori   = $_POST['kategori']; 

    // ---- PROSES UPLOAD GAMBAR ----
    $nama_gambar   = $_FILES['gambar']['name'];
    $ukuran_gambar = $_FILES['gambar']['size'];
    $error_gambar  = $_FILES['gambar']['error'];
    $tmp_gambar    = $_FILES['gambar']['tmp_name'];

    // Cek apakah user benar-benar mengupload gambar
    if($error_gambar === 0) {
        // Ambil ekstensi file (jpg, jpeg, png, webp)
        $ekstensi_valid = ['jpg', 'jpeg', 'png', 'webp'];
        $ekstensi_file  = explode('.', $nama_gambar);
        $ekstensi_file  = strtolower(end($ekstensi_file));

        // Cek format file valid atau tidak
        if(in_array($ekstensi_file, $ekstensi_valid)) {
            // Batasi ukuran maksimal (contoh: 2MB)
            if($ukuran_gambar < 2097152) {
                
                // Buat nama file baru agar unik
                $nama_gambar_baru = uniqid() . '.' . $ekstensi_file;
                
                // --- OTOMATISASI FOLDER DI SINI ---
                $folder_target = '../img/';

                // Jika folder '../img/' belum ada di File Explorer, buat otomatis lewat script
                if (!is_dir($folder_target)) {
                    mkdir($folder_target, 0777, true);
                }
                // ----------------------------------

                // Gabungkan folder tujuan dengan nama file unik
                $lokasi_simpan = $folder_target . $nama_gambar_baru;

                if(move_uploaded_file($tmp_gambar, $lokasi_simpan)) {
                    // Query INSERT dengan kolom 'kategori' dan 'gambar'
                    $insert = mysqli_query($koneksi, "INSERT INTO mobil (nama_mobil, jumlah, kondisi, harga_sewa, kategori, gambar) VALUES ('$nama_mobil', '$jumlah', '$kondisi', '$harga_sewa', '$kategori', '$nama_gambar_baru')");
                    
                    if($insert){
                        header("location:dashboard.php");
                        exit();
                    } else {
                        echo "<script>alert('Gagal menyimpan data ke database.');</script>";
                    }
                } else {
                    echo "<script>alert('Gagal memindahkan file ke folder server.');</script>";
                }
            } else {
                echo "<script>alert('Ukuran gambar terlalu besar! Maksimal 2MB.');</script>";
            }
        } else {
            echo "<script>alert('Format file salah! Harus JPG, JPEG, PNG, atau WEBP.');</script>";
        }
    } else {
        echo "<script>alert('Wajib mengunggah gambar mobil!');</script>";
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
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #eef2f7; 
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .form-container {
            background-color: #ffffff;
            width: 100%;
            max-width: 480px;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e1e8ed;
        }

        h3 {
            color: #1e3a8a; 
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
        }

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

        .form-group input, .form-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background-color: #f9fafb;
            color: #1f2937;
            transition: all 0.3s ease;
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #1e3a8a;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(30, 58, 138, 0.1);
        }

        /* Desain area file input agar lebih rapi */
        .form-group input[type="file"] {
            padding: 0.5rem;
            background-color: #ffffff;
            cursor: pointer;
        }

        /* Box Pratinjau Gambar */
        .img-preview-box {
            width: 100%;
            max-height: 200px;
            margin-top: 0.75rem;
            border-radius: 8px;
            overflow: hidden;
            border: 2px dashed #d1d5db;
            display: none; /* Berubah jadi block lewat JS saat gambar dipilih */
            align-items: center;
            justify-content: center;
            background-color: #f9fafb;
        }

        .img-preview-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1.75rem;
        }

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
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama_mobil">Nama Mobil</label>
                <input type="text" id="nama_mobil" name="nama_mobil" placeholder="Masukkan nama mobil (cth: Pajero)" required>
            </div>

            <div class="form-group">
                <label for="kategori">Kategori Armada</label>
                <select id="kategori" name="kategori" required>
                    <option value="" disabled selected>-- Pilih Kategori --</option>
                    <option value="LCGC">🍃 LCGC (Hemat)</option>
                    <option value="Eksklusif">💎 Eksklusif</option>
                    <option value="Sport 4x4">🏔️ Sport 4x4</option>
                </select>
            </div>

            <div class="form-group">
                <label for="gambar">Foto Mobil</label>
                <input type="file" id="gambar" name="gambar" accept="image/*" onchange="previewImage()" required>
                <div class="img-preview-box" id="preview-box">
                    <img id="img-preview" src="" alt="Pratinjau Gambar">
                </div>
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

    <script>
        function previewImage() {
            const gambar = document.querySelector('#gambar');
            const previewBox = document.querySelector('#preview-box');
            const imgPreview = document.querySelector('#img-preview');

            if(gambar.files && gambar.files[0]) {
                previewBox.style.display = 'flex';
                const oFReader = new FileReader();
                oFReader.readAsDataURL(gambar.files[0]);

                oFReader.onload = function(oFREvent) {
                    imgPreview.src = oFREvent.target.result;
                };
            }
        }
    </script>
</body>
</html>