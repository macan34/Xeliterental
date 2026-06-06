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
    $kategori   = $_POST['kategori']; 

    $nama_gambar = $_FILES['gambar']['name'];
    $tmp_gambar  = $_FILES['gambar']['tmp_name'];

    // Jika admin mengunggah gambar baru
    if(!empty($nama_gambar)) {
        // Ambil ekstensi berkas gambar
        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg', 'webp');
        $x = explode('.', $nama_gambar);
        $ekstensi = strtolower(end($x));
        
        // Buat nama unik baru untuk menghindari duplikasi nama file di folder
        $nama_gambar_baru = time() . '-' . $nama_gambar;

        if(in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
            // Path folder penyimpanan gambar (sesuaikan dengan struktur folder dashboard user sebelumnya yaitu '../img/')
            $folder_tujuan = '../img/' . $nama_gambar_baru;

            if(move_uploaded_file($tmp_gambar, $folder_tujuan)) {
                // Hapus gambar lama dari server jika file fisik tersebut ada
                if(!empty($data['gambar']) && file_exists('../img/' . $data['gambar'])) {
                    unlink('../img/' . $data['gambar']);
                }

                // Query update termasuk gambar baru
                $sql = "UPDATE mobil SET nama_mobil='$nama_mobil', jumlah='$jumlah', kondisi='$kondisi', harga_sewa='$harga_sewa', kategori='$kategori', gambar='$nama_gambar_baru' WHERE id_mobil='$id'";
            } else {
                echo "<script>alert('Gagal mengunggah gambar baru ke server.');</script>";
                $sql = "";
            }
        } else {
            echo "<script>alert('Ekstensi gambar tidak diperbolehkan! Gunakan png, jpg, jpeg, atau webp.');</script>";
            $sql = "";
        }
    } else {
        // Jika admin TIDAK mengunggah gambar baru, gunakan nama gambar yang lama
        $sql = "UPDATE mobil SET nama_mobil='$nama_mobil', jumlah='$jumlah', kondisi='$kondisi', harga_sewa='$harga_sewa', kategori='$kategori' WHERE id_mobil='$id'";
    }

    // Eksekusi query ke database jika valid
    if(!empty($sql)) {
        $update = mysqli_query($koneksi, $sql);
        if($update){
            header("location:dashboard.php");
            exit();
        } else {
            echo "<script>alert('Gagal memperbarui data mobil ke database.');</script>";
        }
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
            background-color: #eef2f7; 
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
            color: #1e3a8a; 
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

        /* Desain Input Field & Select Dropdown */
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

        /* Efek fokus input saat diklik */
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #1e3a8a;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(30, 58, 138, 0.1);
        }

        /* --- STYLE UNTUK PRATINJAU GAMBAR --- */
        .img-preview-box {
            margin-top: 10px;
            width: 100%;
            height: 160px;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-color: #f3f4f6;
        }

        .img-preview-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        .text-helper {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 4px;
            display: block;
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
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama_mobil">Nama Mobil</label>
                <input type="text" id="nama_mobil" name="nama_mobil" value="<?php echo htmlspecialchars($data['nama_mobil']); ?>" required>
            </div>

            <div class="form-group">
                <label for="kategori">Kategori Armada</label>
                <select id="kategori" name="kategori" required>
                    <option value="LCGC" <?php echo ($data['kategori'] == 'LCGC') ? 'selected' : ''; ?>>🍃 LCGC (Hemat)</option>
                    <option value="Eksklusif" <?php echo ($data['kategori'] == 'Eksklusif') ? 'selected' : ''; ?>>💎 Eksklusif</option>
                    <option value="Sport 4x4" <?php echo ($data['kategori'] == 'Sport 4x4') ? 'selected' : ''; ?>>🏔️ Sport 4x4</option>
                </select>
            </div>

            <div class="form-group">
                <label for="jumlah">Jumlah Unit</label>
                <input type="number" id="jumlah" name="jumlah" min="0" value="<?php echo htmlspecialchars($data['jumlah']); ?>" required>
            </div>

            <div class="form-group">
                <label for="gambar">Foto Mobil</label>
                <input type="file" id="gambar" name="gambar" accept="image/*" onchange="previewImage()">
                <small class="text-helper">*Kosongkan jika tidak ingin mengubah gambar.</small>
                
                <div class="img-preview-box">
                    <?php 
                    // Menampilkan pratinjau gambar lama yang tersimpan di database saat ini
                    if(!empty($data['gambar']) && file_exists('../img/' . $data['gambar'])) {
                        $foto_lama = '../img/' . $data['gambar'];
                    } else {
                        $foto_lama = '../assets/default-car.png';
                    }
                    ?>
                    <img id="img-preview" src="<?php echo $foto_lama; ?>" alt="Pratinjau Gambar">
                </div>
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

    <script>
        function previewImage() {
            const gambar = document.querySelector('#gambar');
            const imgPreview = document.querySelector('#img-preview');

            // Membuat URL temporary untuk file gambar yang baru dipilih
            if(gambar.files && gambar.files[0]) {
                const oFReader = new FileReader();
                oFReader.readAsDataURL(gambar.files[0]);

                oFReader.onload = function(oFREvent) {
                    imgPreview.src = oFREvent.target.result;
                }
            }
        }
    </script>
</body>
</html>