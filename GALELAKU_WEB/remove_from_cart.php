<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $cart_id = $_GET['id'];
    
    // Ambil user_id untuk memastikan hanya pemilik yang bisa menghapus
    $stmt = $pdo->prepare("SELECT user_id FROM cart WHERE id = ?");
    $stmt->execute([$cart_id]);
    $item = $stmt->fetch();
    
    if ($item) {
        $stmt_user = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt_user->execute([$_SESSION['username']]);
        $user = $stmt_user->fetch();
        
        if ($item['user_id'] == $user['id']) {
            $stmt_delete = $pdo->prepare("DELETE FROM cart WHERE id = ?");
            $stmt_delete->execute([$cart_id]);
        }
    }
}

header("Location: cart.php");
exit;
?>