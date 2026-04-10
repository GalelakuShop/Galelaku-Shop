<?php
session_start();
require 'config.php';

// Validasi input
if (empty($_POST['username']) || empty($_POST['password'])) {
    $_SESSION['login_error'] = "Username dan password harus diisi";
    header("Location: login.php");
    exit;
}

$username = trim($_POST['username']);
$password = $_POST['password'];
$remember = isset($_POST['remember']);

try {
    // 1. Cek user di database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // 2. Verifikasi password
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_id'] = $user['id'];
        
        if ($remember) {
            setcookie("remember_username", $user['username'], time() + (7 * 24 * 60 * 60), "/", "", false, true);
        }
        
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['login_error'] = "Username atau password salah";
        header("Location: login.php");
        exit;
    }
} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage());
    $_SESSION['login_error'] = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
    header("Location: login.php");
    exit;
}
?>