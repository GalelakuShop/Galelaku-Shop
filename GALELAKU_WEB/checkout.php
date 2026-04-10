<?php
session_start();
include 'config.php';

// Dapatkan user_id
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch();

if (!$user) {
    die("User tidak ditemukan");
}

$user_id = $user['id'];

// Ambil data cart
$stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['product_price'] * $item['quantity'];
}

// Proses checkout jika form submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        // Ambil data dari form HTML
        $recipient_name = $_POST['recipient_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        $city = $_POST['city'] ?? '';
        $postal_code = $_POST['postal_code'] ?? '';
        $payment_method = $_POST['payment_method'] ?? '';

        // Buat order beserta data alamatnya
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status, recipient_name, phone, address, city, postal_code, payment_method) VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $total + 25000, $recipient_name, $phone, $address, $city, $postal_code, $payment_method]);
        $order_id = $pdo->lastInsertId();

        // Tambahkan order items
        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['product_price']]);
        }
        
        // Kosongkan keranjang
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        $pdo->commit();
        
        header("Location: order_success.php?id=$order_id");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Terjadi kesalahan saat memproses order: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--Logo-->
    <link rel="icon" href="Img Galelaku/GALELAKU.png">
    <title>Checkout - CosplayShop</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <style>
    :root {
        --warna-hijau: #ad0101;
        --warna-hitam: #000000;
        --warna-putih: #ffffff;
    }
    
    body {
        font-family: "Poppins", sans-serif;
        background-color: var(--warna-hitam);
        color: var(--warna-putih);
    }
    
    .btn-custom {
        background: var(--warna-hijau);
        color: white;
        border-radius: 50px;
        padding: 10px 25px;
        font-weight: 500;
    }
    
    .btn-custom:hover {
        background: #c76c67;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(143, 29, 29);
    }
    
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(143, 29, 29);
    }
    
    .card-header {
        background: rgba(143, 29, 29);
        border-bottom: 2px solid var(--warna-hijau);
        font-weight: 600;
    }
    
    .list-group-item {
        background-color: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
    }
    
    .form-control, .form-select {
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
    }
    
    .form-control:focus, .form-select:focus {
        background-color: rgba(255, 255, 255, 0.2);
        border-color: var(--warna-hijau);
        color: white;
        box-shadow: 0 0 0 0.25rem rgba(143, 29, 29);
    }
    /* Tambahkan ini */
    .form-select {
        color: white !important;
    }
    
    .form-select option {
        color: white;
        background-color: #333;
    }
    
    .form-select option:checked,
    .form-select option:hover {
        background-color: var(--warna-hijau);
        color: white;
    }
    
    hr {
        border-color: rgba(255, 255, 255, 0.1);
    }
    
    .alert {
        border-radius: 10px;
    }
    
    .alert-danger {
        background-color: rgba(220, 53, 69, 0.2);
        border-color: rgba(220, 53, 69, 0.3);
        color: #ff6b6b;
    }
    
    .alert-info {
        background-color: rgba(13, 202, 240, 0.2);
        border-color: rgba(13, 202, 240, 0.3);
        color: #66d9e8;
    }
        /* Overlay Loading / Processing */
    #processingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.85);
        z-index: 9999;
        display: none; /* Disembunyikan secara default */
        flex-direction: column;
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(5px);
    }
    
    .spinner-border {
        width: 5rem;
        height: 5rem;
        border-width: 0.4em;
        color: var(--warna-hijau);
        margin-bottom: 20px;
    }
    
    .processing-text {
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--warna-hijau);
        text-align: center;
    }
    </style>
</head>

<body>
    <div id="processingOverlay">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="processing-text">
            Pesanan Sedang Diproses...<br>
            <span style="font-size: 1rem; color: #fff; font-weight: normal;">Mohon jangan tutup halaman ini.</span>
        </div>
    </div>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-black sticky-top py-3">
        <div class="container">
             <a class="navbar-brand d-flex align-items-center fw-bold" href="#">
                <img src="Img Galelaku/GALELAKU.png" width="200" height="40" class="me-2">
                
            </a>
            
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="logout.php" class="btn btn-sm btn-custom">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Checkout Content -->
    <div class="container py-5">
        <h2 class="fw-bold mb-4 text-ungu">Checkout</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
            <div class="alert alert-info">
                Keranjang belanja Anda kosong.
            </div>
            <a href="index.php" class="btn btn-custom">Kembali Belanja</a>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card bg-dark text-white mb-4">
                        <div class="card-header">
                            <h4><i class="bi bi-cart-check me-2"></i>Ringkasan Pesanan</h4>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($cart_items as $item): ?>
                                <li class="list-group-item bg-dark text-white d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1"><?= htmlspecialchars($item['product_name']) ?></h5>
                                        <small class="text-muted">Jumlah: <?= $item['quantity'] ?></small>
                                    </div>
                                    <span class="badge bg-ungu rounded-pill">Rp <?= number_format($item['product_price'] * $item['quantity'], 0, ',', '.') ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card bg-dark text-white">
                        <div class="card-header">
                            <h4><i class="bi bi-truck me-2"></i>Informasi Pengiriman</h4>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label class="form-label">Nama Penerima</label>
                                    <input type="text" name="recipient_name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">No. Telepon</label>
                                    <input type="tel" name="phone" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Alamat Lengkap</label>
                                    <textarea name="address" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kota</label>
                                        <input type="text" name="city" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kode Pos</label>
                                        <input type="text" name="postal_code" class="form-control" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Metode Pembayaran</label>
                                    <select name="payment_method" class="form-select" required>
                                        <option value="">Pilih metode pembayaran</option>
                                        <option value="transfer_bsi">Transfer Bank BSI</option>
                                        <option value="transfer_bri">Transfer Bank BRI</option>
                                        <option value="transfer_mandiri">Transfer Bank Mandiri</option>
                                        <option value="qris">QRIS</option>
                                    </select>
                                </div>
                                <!-- Inside the checkout content section, after the form but before the closing else statement -->
                                    <div class="mt-4 d-flex justify-content-between">
                                        <a href="cart.php" class="btn" style="
                                            color: #8f1d1d; 
                                            border: 1px solid #8f1d1d;
                                            background-color: transparent;
                                            border-radius: 50px;
                                            padding: 8px 20px;
                                            transition: all 0.3s ease;">
                                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Keranjang
                                        </a>
                                        <button type="submit" class="btn btn-custom">
                                            <i class="bi bi-credit-card me-2"></i>Proses Pembayaran
                                        </button>
                                    </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card bg-dark text-white sticky-top" style="top: 20px;">
                        <div class="card-header">
                            <h4><i class="bi bi-receipt me-2"></i>Total Pembayaran</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Ongkos Kirim:</span>
                                <span>Rp 20.000</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Asuransi:</span>
                                <span>Rp 5.000</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold h5">
                                <span>Total:</span>
                                <span class="text-ungu">|| Rp <?= number_format($total + 25000, 0, ',', '.') ?><br>
                                <span data-price="<?= $total ?>">
                                </span>
                            </div>
                            <div class="mt-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                                    <label class="form-check-label small" for="agreeTerms">
                                        Saya menyetujui <a href="#" class="text-ungu">Syarat dan Ketentuan</a> serta <a href="#" class="text-ungu">Kebijakan Privasi</a>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Footer -->
    <footer class="bg-black text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">© <?= date('Y') ?> CosplayShop. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Validasi form sebelum submit
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!document.getElementById('agreeTerms').checked) {
                e.preventDefault();
                alert('Anda harus menyetujui syarat dan ketentuan terlebih dahulu');
            }
        });
       // Hover effect for back button
const backBtn = document.querySelector('a[href="cart.php"]');
backBtn.addEventListener('mouseenter', function() {
    this.style.color = 'white';
    this.style.backgroundColor = '#8f1d1d'; // Ganti jadi hijau saat kursor masuk
});
backBtn.addEventListener('mouseleave', function() {
    this.style.color = '#8f1d1d'; // Balik jadi teks hijau dan transparan saat kursor pergi
    this.style.backgroundColor = 'transparent';
});
    </script>;
        
    </script>
</body>
</html>