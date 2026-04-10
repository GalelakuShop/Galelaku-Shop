<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username harus diisi";
    } elseif (strlen($username) < 4) {
        $errors[] = "Username minimal 4 karakter";
    }

    if (empty($email)) {
        $errors[] = "Email harus diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }

    if (empty($password)) {
        $errors[] = "Password harus diisi";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Password dan konfirmasi password tidak sama";
    }

    // Cek apakah username atau email sudah ada
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username atau email sudah digunakan";
        }
    } catch (PDOException $e) {
        $errors[] = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
    }

    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password]);
            
            // Redirect ke halaman login dengan pesan sukses
            header("Location: login.php?register=success");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan saat menyimpan data. Silakan coba lagi.";
        }
    }

    // Jika ada error, tampilkan kembali form dengan pesan error
    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        $_SESSION['old_username'] = $username;
        $_SESSION['old_email'] = $email;
        header("Location: registrasi.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrasi</title>
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
      background-color: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
      padding: 2rem;
      border-radius: 1rem;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
    }
    /* Tambahan untuk Tombol Tema Frozen Food */
    .btn-hijau {
        background-color: #000000;
        border-color:  #000000;
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
        <h2 class="text-center mb-4">REGISTRASI</h2>
        
        <?php if (isset($_SESSION['register_errors'])): ?>
            <div class="alert alert-danger">
                <?php foreach ($_SESSION['register_errors'] as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
                <?php unset($_SESSION['register_errors']); ?>
            </div>
        <?php endif; ?>
        
        <form action="registrasi.php" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required
                    value="<?= isset($_SESSION['old_username']) ? htmlspecialchars($_SESSION['old_username']) : '' ?>">
                <?php unset($_SESSION['old_username']); ?>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required
                    value="<?= isset($_SESSION['old_email']) ? htmlspecialchars($_SESSION['old_email']) : '' ?>">
                <?php unset($_SESSION['old_email']); ?>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-hijau w-100 fw-bold">Daftar</button>
            <p class="mt-3 text-center">Sudah punya akun? <a href="login.php"  class="link-hijau">Login di sini</a></p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>