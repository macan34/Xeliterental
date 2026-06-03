<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Elite Rental Mobil</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            padding: 2.5rem 2rem;
            text-align: center;
            color: white;
        }
        
        .login-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .login-header p {
            font-size: 0.95rem;
            opacity: 0.9;
        }
        
        .login-body {
            padding: 2.5rem 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.6rem;
            display: block;
            color: #1f2937;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.875rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #1e40af;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }
        
        .login-btn {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(30, 64, 175, 0.4);
        }
        
        .alert {
            margin-bottom: 1.5rem;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .demo-info {
            background: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: #0c2d6b;
        }
        
        .demo-info strong {
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .demo-info p {
            margin: 0.25rem 0;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-header">
            <h1>X Elite Rental</h1>
            <p>Sistem Manajemen Rental Mobil</p>
        </div>
        
        <div class="login-body">
            <?php 
            if(isset($_GET['pesan'])){
                if($_GET['pesan'] == "gagal"){
                    echo "<div class='alert alert-danger'>
                        <span>⚠️</span>
                        <span><strong>Login Gagal!</strong> Username atau password salah.</span>
                    </div>";
                } else if($_GET['pesan'] == "belum_login"){
                    echo "<div class='alert alert-warning'>
                        <span>ℹ️</span>
                        <span>Anda harus login terlebih dahulu untuk mengakses halaman tersebut.</span>
                    </div>";
                }
            }
            ?>

            <form action="proses_login.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>

                <button type="submit" name="login" class="login-btn">Login Sekarang</button>
            </form>

        </div>
    </div>

</body>
</html>