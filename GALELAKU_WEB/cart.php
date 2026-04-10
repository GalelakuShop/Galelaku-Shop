<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch();

if (!$user) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

$user_id = $user['id'];

$stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['product_price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="Img Galelaku/GALELAKU.png">
    <title>Keranjang Belanja - Cosplay.Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --warna-hijau: #ad0101;
            --warna-hitam: #000000;
        }
        body {
            font-family: "Poppins", sans-serif;
            background-color: var(--warna-hitam);
            color: #fff;
        }
        .btn-custom {
            background: var(--warna-hijau);
            color: white;
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }
        .btn-custom:hover {
            background: #850101;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(173, 1, 1, 0.4);
        }
        .table-dark { --bs-table-bg: transparent !important; }
        .text-merah { color: var(--warna-hijau) !important; }
        
        /* Desktop: Sembunyikan label mobile */
        .mobile-label { display: none; }

        @media (max-width: 768px) {
            thead { display: none; }
            table, tbody, tr, td { display: block; width: 100%; }
            tr {
                border: 1px solid #333;
                border-radius: 15px;
                padding: 15px;
                margin-bottom: 20px;
                background: rgba(255, 255, 255, 0.05);
            }
            td {
                border: none !important;
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 0 !important;
                text-align: right;
            }
            td::before {
                content: attr(data-label);
                font-weight: bold;
                color: var(--warna-hijau);
                text-align: left;
            }
            /* Form update di mobile agar rata kanan */
            .form-update-mobile {
                display: flex;
                justify-content: flex-end;
                width: 100%;
            }
            .form-update-mobile input {
                width: 60px !important;
                margin-right: 8px;
                background: #222;
                border: 1px solid #444;
                color: #fff;
                text-align: center;
            }
            /* Tombol hapus di mobile */
            .td-action {
                border-top: 1px dashed #444 !important;
                margin-top: 10px;
                padding-top: 15px !important;
                justify-content: flex-end !important;
            }
            .td-action span { display: none; } /* Sembunyikan teks 'Hapus' */
        }
    </style>
</head>
<body>

    <div class="container py-5">
        <div class="d-flex align-items-center mb-4 pb-2 border-bottom border-secondary">
            <i class="bi bi-cart3 fs-1 me-3 text-white"></i> 
            <h1 class="display-5 fw-bold m-0 text-white">
                KERANJANG <span class="text-merah">BELANJA</span>
            </h1>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="text-center py-5">
                <i class="bi bi-cart-x mb-3 d-block" style="font-size: 3rem; color: #ad0101;"></i>
                <h4 class="fw-bold">Wah, keranjangmu kosong!</h4>
                <a href="index.php" class="btn btn-custom mt-3">Mulai Belanja</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-dark align-middle">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th class="text-center">Jumlah</th>
                            <th>Subtotal</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td data-label="Produk"><?= htmlspecialchars($item['product_name']) ?></td>
                            <td data-label="Harga">Rp <?= number_format($item['product_price'], 0, ',', '.') ?></td>
                            <td data-label="Jumlah">
                                <form method="post" action="update_cart.php" class="d-flex align-items-center justify-content-md-center gap-2">
                                    <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">

                                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" 
                                           class="form-control form-control-sm text-center" 
                                           style="max-width: 70px; background: #222; color: #fff; border: 1px solid #444;">

                                    <button type="submit" class="btn btn-sm btn-custom py-1 px-3">Update</button>
                                </form>
                            </td>
                            <td data-label="Subtotal">Rp <?= number_format($item['product_price'] * $item['quantity'], 0, ',', '.') ?></td>
                            <td class="td-action text-md-center">
                                <a href="remove_from_cart.php?id=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus produk?')">
                                    <i class="bi bi-trash"></i> <span>Hapus</span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="border-top border-secondary">
                        <tr>
                            <td colspan="3" class="text-end d-none d-md-table-cell text-white fw-bold">
                                Total Keseluruhan:
                            </td>

                            <td class="p-3">
                                <div class="d-flex justify-content-between justify-content-md-start align-items-center">
                                    <span class="text-white fw-bold d-md-none ">Total:</span> 
                                    <span class="ms-md-2" style="color: #ad0101 !important; font-weight: 800; font-size: 1.25rem;">
                                        Rp <?= number_format($total, 0, ',', '.') ?>
                                    </span>
                                </div>
                            </td>

                            <td class="d-none d-md-table-cell"></td>
                        </tr>
                    </tfoot>
                  </table>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between mt-4 gap-3">
                <a href="index.php" class="btn btn-outline-light rounded-pill px-4">
                    <i class="bi bi-arrow-left"></i> Kembali Belanja
                </a>
                <a href="checkout.php" class="btn btn-custom px-5 py-2 fs-5">
                    Proses Checkout <i class="bi bi-credit-card ms-2"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>