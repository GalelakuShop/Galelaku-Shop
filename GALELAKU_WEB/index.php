<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Get all user data using PDO
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['add_to_cart'])) {
    try {
        $product_id = $_POST['product_id'];
        $product_name = $_POST['product_name'];
        $product_price = $_POST['product_price'];
        
        // Input validation
        if (!is_numeric($product_id) || !is_numeric($product_price)) {
            throw new Exception("Data produk tidak valid");
        }

        // Get user_id from session
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$_SESSION['username']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception("User tidak ditemukan");
        }
        
        $user_id = $user['id'];
        
        // Check if product already exists in cart
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $existing_item = $stmt->fetch();
        
        if ($existing_item) {
            // Update quantity
            $new_quantity = $existing_item['quantity'] + 1;
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$new_quantity, $existing_item['id']]);
        } else {
            // Insert new item
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, product_name, product_price, quantity) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$user_id, $product_id, $product_name, $product_price]);
        }
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
        
    } catch (PDOException $e) {
        // Handle database errors
        die("Error database: " . $e->getMessage());
    } catch (Exception $e) {
        // Handle other errors
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--Logo-->
    <link rel="icon" href="Img Galelaku/GALELAKU.png">
    <title>Galelaku</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
   <style>
/* Global Styles */

/* Global Styles & Variables */
:root {
    --warna-utama: #ad0101; /* Hijau Segar */
    --warna-aksen: #ff9800; /* Oranye Makanan */
    --warna-gelap: #212529; /* Hitam Soft */
    --warna-terang: #f8f9fa; /* Abu-abu sangat terang */
    --warna-putih: #ffffff;
}

body {
    font-family: "Poppins", sans-serif;
    background-color: var(--warna-terang);
    color: var(--warna-gelap);
}

.text-ungu {
    color: var(--warna-utama) !important;
}
.text-purple {
    color: var(--warna-utama) !important;
}
.bg-purple {
    background-color: var(--warna-utama) !important;
}

/* Buttons */
.btn-custom, .btn-ungu {
    background-color: var(--warna-utama) !important;
    color: var(--warna-putih) !important;
    border-radius: 50px;
    padding: 10px 25px;
    border: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center; /* Ini menengahkan secara vertikal */
    justify-content: center; /* INI MENENGAHKAN SECARA HORIZONTAL */
    gap: 8px; /* INI MEMBERI JARAK ANTARA IKON DAN TEKS */
    font-weight: 500;
}

.btn-custom:hover, .btn-ungu:hover {
    background-color: #ff0000 !important; /* Hijau lebih gelap */
    color: var(--warna-putih) !important;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(46, 163, 69, 0.3);
}

.nav-link.active {
    color: var(--warna-putih) !important;
    border-radius: 50px;
}

/* Category Boxes */
.box1 { background: #e8f5e9; border: 1px solid #ffece3; }
.box2 { background: #fff3e0; border: 1px solid #ffece3; }
.box3 { background: #e3f2fd; border: 1px solid #ffece3; }
.box4 { background: #fbe9e7; border: 1px solid #ffece3; }
.box5 { background: #fff8e1; border: 1px solid #ffece3; }

/* Hero Section */
.hero-slider {
    min-height: 60vh;
    padding: 40px 0;
    
    /* Cara pasang background bergambar:
       1. Gunakan linear-gradient sebagai 'overlay' (lapisan transparan) agar teks tetap kebaca.
       2. Ganti URL gambar dengan path file gambarmu (misal: img/bg-food.jpg).
       3. Saya pakai link placeholder gambar makanan sehat dari Unsplash untuk contoh.
    */
    background-image: linear-gradient(135deg, rgba(255, 255, 255, 0.85) 0%, rgba(255, 255, 255, 0.9) 100%),
                      url('Img Galelaku/visual.png');
    
    background-size: cover;      /* Agar gambar menutupi seluruh area */
    background-position: center; /* Agar gambar selalu di tengah */
    background-repeat: no-repeat; /* Agar gambar tidak mengulang/tiling */
}

.hero-content {
    padding: 20px !important;
    color: var(--warna-gelap) !important; /* Timpa text-white bawaan HTML */
}

.hero-content h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem !important;
    color: var(--warna-gelap);
}

.hero-content span {
    color: var(--warna-aksen) !important;
    font-weight: bold;
}

.hero-slider img {
    max-width: 400px;
    height: auto;
    border-radius: 20px; /* Bikin ujung gambar melengkung soft */
}

/* Product Cards */
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
.card h4.text-purple {
    color: var(--warna-aksen) !important; /* Harga pakai oranye agar menonjol */
}

/* Testimonial Styles */
.testimoni {
    background-color: var(--warna-putih);
}

.testimoni-card {
    border-radius: 15px;
    transition: transform 0.3s ease;
    border-bottom: 4px solid var(--warna-utama) !important;
    background-color: var(--warna-terang);
}

.testimoni-card:hover {
    transform: translateY(-10px);
}

.rating i {
    font-size: 1.2rem;
    margin-right: 3px;
}

/* Footer Styles */
.footer a:hover {
    color: var(--warna-utama) !important;
}

.social-media a {
    transition: all 0.3s ease;
}

.social-media a:hover {
    color: var(--warna-utama) !important;
    transform: translateY(-3px);
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .hero-slider {
        min-height: auto;
        padding: 60px 0;
    }
    .hero-content {
        padding-top: 0 !important;
        text-align: center;
    }
    .btn-custom, .btn-ungu {
        margin: 0 auto;
    }
    .hero-slider img {
        max-width: 300px;
        margin-top: 20px;
    }
}
</style>



<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-black sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center fw-bold" href="#">
                <img src="Img-Galelaku/GALELAKU.png" width="200" height="40" class="me-2">
                
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item mx-1"><a class="nav-link active px-3" href="#home">Home</a></li>
                    <li class="nav-item mx-1"><a class="nav-link px-3" href="#kategori">Kategori</a></li>
                    <li class="nav-item mx-1"><a class="nav-link px-3" href="#produk">Produk</a></li>
                    <li class="nav-item mx-1"><a class="nav-link px-3" href="#tentang">Tentang</a></li>
                    <li class="nav-item mx-1"><a class="nav-link px-3" href="#customers">Testimoni</a></li>
                </ul>
            </div>
            
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Selamat datang, <?= htmlspecialchars($username) ?></span>
                <a href="cart.php" class="btn btn-sm btn-outline-light me-2 position-relative">
                    <i class="bi bi-cart3"></i>
                    <?php 
                    // Count cart items
                    if (isset($_SESSION['username'])) {
                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart c JOIN users u ON c.user_id = u.id WHERE u.username = ?");
                        $stmt->execute([$_SESSION['username']]);
                        $count = $stmt->fetch()['count'];
                        if ($count > 0) {
                            echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">'.$count.'</span>';
                        }
                    }
                    ?>
                </a>
                <a href="logout.php" class="btn btn-sm btn-custom">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section - Compact Version -->
<section class="hero-slider swiper" id="home">
    <div class="swiper-wrapper">
        <div class="swiper-slide">
            <div class="container">
                <div class="row align-items-center py-4"> <!-- Ubah min-vh-100 ke py-4 -->
                    <!-- Content Text -->
                    <div class="col-lg-6 order-lg-1 order-2">
                        <div class="hero-content text-white">
                            <span class="d-block h5 mb-2 text-ungu"> <!-- Ubah h4 ke h5 -->
                                ""Solusi Praktis Makan Sehat Setiap Hari""
                            </span>
                            <h1 class="display-5 fw-bold mb-3"> <!-- Ubah display-4 ke display-5 -->
                                Frozen Food Sehat & Berkualitas<br>
                                Siap Masak, Hemat Waktu,<br>
                                Tetap Bergizi untuk Keluarga
                            </h1>
                            <a href="#produk" class="btn btn-ungu btn-md"> <!-- Ubah btn-lg ke btn-md -->
                                <b class="me-2">Lihat Produk</b>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Image -->
                    <div class="col-lg-6 order-lg-2 order-1 mb-3 mb-lg-0 text-center"> <!-- Tambah text-center -->
                        <img src="Img Galelaku/Hero image.jpg" alt="Koleksi Cosplay" 
                             class="img-fluid rounded-3 shadow-lg hero-image"
                             style="max-width: 80%; height: auto;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Categories Section -->
    <section class="py-5" id="kategori" >
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold"> Kategori <br><span class="text-purple">Terbaik Kami</span></h2>
            
            </div>
            
            <div class="row g-3 justify-content-center">
                <!-- Box 1 -->
                <div class="col-lg-2 col-md-4 col-6 ">
                    <div class="box1 p-3 text-center rounded h-100">
                        <img src="Img-Galelaku/Ayam Organik Potong 10 1kg.jpg" class="img-fluid mb-2" style="height: 200px; object-fit: contain;">
                        <h3 class="h5 fw-bold">Ayam Potong Organik</h3>
                        <small class="text-muted">10 kg Produk</small>
                    </div>
                </div>
                
                <!-- Box 2 -->
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="box1 p-3 text-center rounded h-100">
                        <img src="Img-Galelaku/Jamur kuping hitam organik kering 20gr.png" class="img-fluid mb-2" style="height: 200px; object-fit: contain;">
                        <h3 class="h5 fw-bold">Jamur Kuping Hitam Organik</h3>
                        <small class="text-muted">10 kg Produk</small>
                    </div>
                </div>
                <!-- Box 3 -->
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="box1 p-3 text-center rounded h-100">
                        <img src="Img-Galelaku/Lele Organik Berbumbu.png" class="img-fluid mb-2" style="height: 200px; object-fit: contain;">
                        <h3 class="h5 fw-bold">Lele Organik Marinasi</h3>
                        <small class="text-muted">10 kg Produk</small>
                    </div>
                </div>
                <!-- Box 4 -->
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="box1 p-3 text-center rounded h-100">
                        <img src="Img-Galelaku/Fillet Dada Full Ayam Organik 1kg.jpg" class="img-fluid mb-2" style="height: 200px; object-fit: contain;">
                        <h3 class="h5 fw-bold">Fillet Dada Full Ayam</h3>
                        <small class="text-muted">10 kg Produk</small>
                    </div>
                </div>
                 <!-- Box 5 -->
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="box1 p-3 text-center rounded h-100">
                        <img src="Img-Galelaku/Ayam Giling 500 gram.jpg" class="img-fluid mb-2" style="height: 200px; object-fit: contain;">
                        <h3 class="h5 fw-bold">Ayam Giling 500 gram</h3>
                        <small class="text-muted">10 kg Produk</small>
                    </div>
                </div>
                 <!-- Box 6 -->
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="box1 p-3 text-center rounded h-100">
                        <img src="Img-Galelaku/Ceker Full Ayam Organik 1kg.jpg" class="img-fluid mb-2" style="height: 200px; object-fit: contain;">
                        <h3 class="h5 fw-bold">Ceker Full Ayam Organik</h3>
                        <small class="text-muted">10 kg Produk</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="py-5 bg-light" id="produk">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Produk <br><span class="text-purple">Populer</span></h2>
                <button onclick="myalert()" class="btn btn-custom d-flex align-items-center">
                    <span class="me-2">Belanja Sekarang</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
        </div>
            
        <div class="row g-4">
            <?php
                // Mengambil data produk dari database
                $stmt_products = $pdo->query("SELECT * FROM products");
                $all_products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

                // Looping untuk menampilkan setiap produk
                foreach ($all_products as $produk): 
                ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="position-relative">
                            <img src="Img-Galelaku/<?= htmlspecialchars($produk['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($produk['name']) ?>" style="height: 250px; object-fit: cover;">
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">Organik</span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-success mb-2 align-self-start">Baru</span>
                            <h3 class="h5 mt-2 flex-grow-1"><?= htmlspecialchars($produk['name']) ?></h3>
                         <?php if (!empty($produk['description'])): ?>
    						<p class="small text-muted mb-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
       							 <?= htmlspecialchars($produk['description']) ?>
   							</p>
    						<p class="small text-ungu fw-bold mb-2">
        						<i class="bi bi-box-seam me-1"></i>Berat: <?= $produk['weight_grams'] ?> gr
   							</p>
						<?php endif; ?>
                            <p class="text-muted small mb-2"><?= htmlspecialchars($produk['category']) ?></p>
                            <h4 class="text-purple fw-bold mb-0">
                                Rp <?= number_format($produk['price'], 0, ',', '.') ?>
                            </h4>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0">
                            <form method="post" action="">
                                <input type="hidden" name="product_id" value="<?= $produk['id'] ?>">
                                <input type="hidden" name="product_name" value="<?= htmlspecialchars($produk['name']) ?>">
                                <input type="hidden" name="product_price" value="<?= $produk['price'] ?>">
                                <button type="submit" name="add_to_cart" class="btn btn-custom w-100">
                                    <i class="bi bi-cart3"></i> Tambah ke Keranjang
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-5" id="tentang">
        <div class="container">
            <div class="row align-items-center">
                <!-- Image -->
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <img src="Img-Galelaku/Logo.jpg" alt="Tentang Kami" 
                        class="img-fluid rounded-circle shadow" 
                        style="width: 100%; max-width: 400px; height: auto;">
                </div>
                
                <!-- Text -->
                <div class="col-lg-6">
                    <span class="badge bg-purple text-white mb-3" style="background-color: #2ecc71  ; font-size: 1rem; ">
                        <b>Tentang Kami</b>
                    </span>
                    
                    <p class="text-black">
                       Selamat datang di <b>Galelaku</b>, penyedia frozen food sehat dan berkualitas untuk kebutuhan harian Anda.

                        Kami menghadirkan produk ayam organik yang higienis, bergizi, dan praktis untuk diolah kapan saja. 
                        Cocok untuk keluarga modern yang ingin tetap sehat tanpa ribet.
                    </p>
                    
                    <p class="text-black">
                        Didirikan dengan tujuan membantu masyarakat hidup lebih sehat, 
                        Galelaku berkomitmen menyediakan produk terbaik dengan kualitas terjaga.
                    </p>
                    
                    <p class="text-black fw-bold">
                        Motto Kami: "Sehat, Praktis, dan Terpercaya."
                    </p>
                    
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimoni py-5" id="customers">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold">Mengapa Pelanggan Menyukai Kami?</h2>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card testimoni-card h-100 p-4 border-0 shadow">
                        <div class="card-body">
                            <i class="bi bi-quote fs-1 text-ungu opacity-25"></i>
                            
                            <div class="rating mb-3">
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-half text-warning"></i>
                            </div>
                            
                            <p class="mb-4">"Ayam potong organiknya fresh banget walau frozen! Gampang diolah buat bekal anak sekolah. Praktis dan harganya masuk akal. Pasti langganan! ❤️"</p>
                            
                            <div class="d-flex align-items-center">
                                <img src="Img-Galelaku/siti-marfuah-resmi.jpg" alt="Ibu Siti" 
                                     class="rounded-circle me-3" width="60" height="60">
                                <div>
                                    <h5 class="mb-0 fw-bold">Ibu Siti</h5>
                                    <small class="text-muted">Ibu Rumah Tangga</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card testimoni-card h-100 p-4 border-0 shadow">
                        <div class="card-body">
                            <i class="bi bi-quote fs-1 text-ungu opacity-25"></i>
                            
                            <div class="rating mb-3">
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                            </div>
                            
                            <p class="mb-4">"Pengiriman cepat pakai kurir instan, sampai rumah dagingnya masih beku keras. Kualitas jamur kupingnya juga bagus, bersih dan tinggal masak. 10/10!"</p>
                            
                            <div class="d-flex align-items-center">
                                <img src="Img-Galelaku/Budi.png" alt="Budi Santoso" 
                                     class="rounded-circle me-3" width="60" height="60">
                                <div>
                                    <h5 class="mb-0 fw-bold">Budi Santoso</h5>
                                    <small class="text-muted">Pelanggan Setia</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card testimoni-card h-100 p-4 border-0 shadow">
                        <div class="card-body">
                            <i class="bi bi-quote fs-1 text-ungu opacity-25"></i>
                            
                            <div class="rating mb-3">
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-half text-warning"></i>
                            </div>
                            
                            <p class="mb-4">"Lele marinasinya juara! Bumbunya meresap sampai ke dalam, tinggal goreng pas lagi capek pulang kerja. Sangat membantu buat anak kos kayak saya.😍"</p>
                            
                            <div class="d-flex align-items-center">
                                <img src="Img-Galelaku/andi.png" alt="Andi" 
                                     class="rounded-circle me-3" width="60" height="60">
                                <div>
                                    <h5 class="mb-0 fw-bold">Andi</h5>
                                    <small class="text-muted">Mahasiswa</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="bg-dark text-white pt-5 pb-4" id="footer">
        <div class="container">
            <div class="row g-4">
                <!-- Column 1 - Logo & Social Media -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-brand mb-3">
                        <a href="#" class="d-flex align-items-center text-decoration-none text-white">
                            <img src="Img-Galelaku/GALELAKU.png" alt="Logo" width="200" class="me-2">
                            
                        </a>
                    </div>
                    <p class="mb-3">
                        <i class="bi bi-geo-alt-fill me-2 text-ungu"></i>
                        Villa mahameru blok A 1 no 7, Kuranji, Kec. Kuranji, Kota Padang, Sumatera Barat 25157
                    </p>
                    <div class="social-media d-flex gap-3">
                        <a href="index.php" class="text-white fs-5">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="index.php" class="text-white fs-5">
                            <i class="bi bi-tiktok"></i>
                        </a>
                        <a href="https://www.instagram.com/galelakushop?igsh=azFpcWczeHB6N21o" class="text-white fs-5">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="index.php" class="text-white fs-5">
                            <i class="bi bi-youtube"></i>
                        </a>
                    </div>
                </div>

                <!-- Column 2 - Categories -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="fw-bold mb-3 text-ungu">Kategori</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Ayam Potong Organik</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Jamur Kuping Hitam Organik</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Lele Organik Marinasi</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Ceker Full Ayam Organik</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Paha Atas & Bawah Organik</a></li>
                    </ul>
                </div>

                <!-- Column 3 - Useful Links -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="fw-bold mb-3 text-ungu">Useful Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Payment & Tax</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Terms Of Use</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">My Blog</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Return Policy</a></li>
                    </ul>
                </div>

                <!-- Column 4 - Newsletter -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="fw-bold mb-3 text-ungu">Newsletter</h5>
                    <p class="mb-3">Dapatkan diskon 10% dengan berlangganan newsletter kami</p>
                    <form class="d-flex">
                        <input type="email" class="form-control form-control-sm me-2" placeholder="Email Anda">
                        <button class="btn btn-ungu btn-sm" type="button" onclick="myalert1()">
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </footer>

    <!-- Copyright -->
    <div class="bg-black text-white py-3">
        <div class="container text-center">
            <p class="mb-0 small">© WEB GALELAKU E-COMMERCE. - Dibuat dengan ❤ oleh Caam (sang CTO) </p>
        </div>
    </div>

    <!-- Script Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Script -->
    <script src="main.js"></script>
</body>
</html>