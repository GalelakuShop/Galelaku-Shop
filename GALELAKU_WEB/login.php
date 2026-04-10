<?php
session_start();

// Cek cookie remember me
if (!isset($_SESSION['username'])) {
    if (isset($_COOKIE['remember_username'])) {
        $_SESSION['username'] = $_COOKIE['remember_username'];
        
        // Ambil user_id dari database
        require 'config.php';
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$_SESSION['username']]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Galelaku Frozen Food Healty</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--Logo-->
    <link rel="icon" href="Img Galelaku/GALELAKU.png">
    <title>Galelaku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
      background: url('Img Galelaku/background_login.png') no-repeat center center fixed;
      background-size: cover;
    }
    .container {
      background-color: rgba(255, 255, 255, 0.262);
      backdrop-filter: blur(10px);
      padding: 2rem;
      border-radius: 1rem;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
    }
    .error-message {
        color: #dc3545;
        font-weight: bold;
    }
    /* Tambahan untuk Tombol Tema Frozen Food */
    .btn-hijau {
        background-color:#000000;
        border-color: #000000;
        color: white;
        transition: all 0.3s ease;
    }
    .btn-hijau:hover {
        background-color: #ad0101;
        border-color: #ad0101;
        color: white;
    }
    /* Link styling biar senada */
    .link-hijau {
        color: #ad0101;
        text-decoration: none;
        font-weight: bold;
    }
    .link-hijau:hover {
        color: #248236;
    }
    </style>
</head>
<body>
    <div class="container col-md-4 mt-5 text-dark p-4 rounded-3">
        <h2 class="text-center mb-4">LOGIN</h2>
        
        <?php if (isset($_SESSION['login_error'])): ?>
            <div class="alert alert-danger text-center mb-3">
                <?= $_SESSION['login_error'] ?>
                <?php unset($_SESSION['login_error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['register']) && $_GET['register'] === 'success'): ?>
            <div class="alert alert-success text-center mb-3">
                Registrasi berhasil! Silakan login.
            </div>
        <?php endif; ?>
        
        <form action="login_proses.php" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required 
                    value="<?= isset($_COOKIE['remember_username']) ? htmlspecialchars($_COOKIE['remember_username']) : '' ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember" 
                    <?= isset($_COOKIE['remember_username']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="remember">Ingat saya</label> 
            </div>
            <button type="submit" class="btn btn-hijau w-100 fw-bold" >Login</button>
            
            <p class="mt-3 text-center">Belum punya akun? <a href="registrasi.php" class="link-hijau">Daftar di sini</a></p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>