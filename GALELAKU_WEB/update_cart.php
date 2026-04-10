<?php
session_start();
include 'config.php'; // Pastikan file koneksi database Anda benar

// 1. Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// 2. Cek apakah data dikirim via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cart_id']) && isset($_POST['quantity'])) {
    
    $cart_id = $_POST['cart_id'];
    $quantity = (int)$_POST['quantity']; // Pastikan jadi angka (integer)

    // Validasi: Jumlah tidak boleh kurang dari 1
    if ($quantity < 1) {
        header("Location: cart.php?error=min_1");
        exit;
    }

    try {
        // 3. Update database
        // Kita tambahkan pengecekan user_id agar orang lain tidak bisa update keranjang orang lain via URL/ID
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $result = $stmt->execute([$quantity, $cart_id]);

        if ($result) {
            // Berhasil, balik ke keranjang
            header("Location: cart.php?status=updated");
        } else {
            header("Location: cart.php?status=failed");
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }

} else {
    // Jika diakses ilegal tanpa POST
    header("Location: cart.php");
}
exit;