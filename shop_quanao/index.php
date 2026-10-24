
<?php
require_once "includes/db.php";

$sql = "SELECT SanPham.*, DanhMuc.TenDanhMuc
        FROM SanPham
        LEFT JOIN DanhMuc
        ON SanPham.MaDanhMuc = DanhMuc.MaDanhMuc";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fashion Store - Cửa hàng quần áo</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">FASHION STORE</a>

    <nav>
        <a href="index.php">Trang chủ</a>
        <a href="#san-pham">Sản phẩm</a>
        <a href="lien-he.php">Liên hệ</a>
        <a href="gio-hang.php">Giỏ hàng</a>
    </nav>
</header>

<section class="banner">
    <p class="subtitle">BỘ SƯU TẬP MỚI</p>
    <h1>Mặc đẹp mỗi ngày</h1>
    <p>Khám phá phong cách thời trang dành riêng cho bạn.</p>
    <a href="#san-pham" class="btn">Mua sắm ngay</a>
</section>

<section class="products" id="san-pham">
    <h2>Sản phẩm nổi bật</h2>
    <p class="section-desc">Khám phá các mẫu thời trang mới nhất</p>

    <div class="product-list">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($sp = $result->fetch_assoc()): ?>
                <div class="product-card">
                  

                    <div class="product-info">
                        <p class="category">
                            <?= htmlspecialchars($sp['TenDanhMuc'] ?? 'Thời trang') ?>
                        </p>
<img
    src="<?= htmlspecialchars($sp['HinhAnh']) ?>"
    alt="<?= htmlspecialchars($sp['TenSP']) ?>"
    class="product-image"
>
                        <h3><?= htmlspecialchars($sp['TenSP']) ?></h3>

                        <p class="price">
                            <?= number_format($sp['Gia'], 0, ',', '.') ?> đ
                        </p>

                        <p class="quantity">
                            Còn <?= (int)$sp['SoLuong'] ?> sản phẩm
                        </p>

                        <a class="buy-btn"
                           href="chi-tiet.php?id=<?= (int)$sp['MaSP'] ?>">
                            Xem chi tiết
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Chưa có sản phẩm nào.</p>
        <?php endif; ?>
    </div>
</section>

<footer class="footer">
    <p>© 2026 Fashion Store</p>
    <p>Cảm ơn bạn đã ghé thăm cửa hàng!</p>
</footer>

</body>
</html>

