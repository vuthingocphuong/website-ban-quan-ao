
<?php
require_once "includes/db.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare(
    "SELECT SanPham.*, DanhMuc.TenDanhMuc
     FROM SanPham
     LEFT JOIN DanhMuc
     ON SanPham.MaDanhMuc = DanhMuc.MaDanhMuc
     WHERE SanPham.MaSP = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$sp = $result->fetch_assoc();

if (!$sp) {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết sản phẩm - Fashion Store</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="header">
    <a href="index.php" class="logo">FASHION STORE</a>
    <nav>
        <a href="index.php">Trang chủ</a>
        <a href="index.php#san-pham">Sản phẩm</a>
        <a href="lien-he.php">Liên hệ</a>
        <a href="gio-hang.php">Giỏ hàng</a>
    </nav>
</header>

<main class="detail-container">
    <?php if ($sp): ?>

       <div class="detail-image">
    <img
        src="<?= htmlspecialchars($sp['HinhAnh']) ?>"
        alt="<?= htmlspecialchars($sp['TenSP']) ?>"
        class="detail-product-image"
    >
</div>

        <div class="detail-info">
            <p class="category">
                <?= htmlspecialchars($sp['TenDanhMuc'] ?? 'Thời trang') ?>
            </p>

            <h1><?= htmlspecialchars($sp['TenSP']) ?></h1>

            <p class="detail-price">
                <?= number_format($sp['Gia'], 0, ',', '.') ?> đ
            </p>

            <p class="detail-description">
                <?= htmlspecialchars($sp['MoTa'] ?? 'Sản phẩm thời trang') ?>
            </p>

            <p class="quantity">
                Số lượng còn lại: <?= (int)$sp['SoLuong'] ?>
            </p>

            <?php if ((int)$sp['SoLuong'] > 0): ?>
                <a class="buy-btn"
                   href="gio-hang.php?id=<?= (int)$sp['MaSP'] ?>">
                    Thêm vào giỏ hàng
                </a>
            <?php else: ?>
                <p>Sản phẩm hiện đã hết hàng.</p>
            <?php endif; ?>

            <p class="back-link">
                <a href="index.php">← Quay lại trang chủ</a>
            </p>
        </div>

    <?php else: ?>
        <h2>Không tìm thấy sản phẩm!</h2>
        <p>Sản phẩm không tồn tại hoặc đã bị xóa.</p>
        <a href="index.php">Quay lại trang chủ</a>
    <?php endif; ?>
</main>

<footer class="footer">
    <p>© 2026 Fashion Store</p>
</footer>

</body>
</html>
