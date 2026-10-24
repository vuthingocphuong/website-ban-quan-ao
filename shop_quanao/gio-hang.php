
<?php
session_start();
require_once "includes/db.php";

if (!isset($_SESSION['gio_hang'])) {
    $_SESSION['gio_hang'] = [];
}

$thongBao = "";

// Them san pham vao gio
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $conn->prepare(
        "SELECT MaSP, TenSP, Gia, SoLuong
         FROM SanPham WHERE MaSP = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $sp = $stmt->get_result()->fetch_assoc();

    if ($sp && (int)$sp['SoLuong'] > 0) {
        $soLuongHienTai = $_SESSION['gio_hang'][$id] ?? 0;

        if ($soLuongHienTai < (int)$sp['SoLuong']) {
            $_SESSION['gio_hang'][$id] = $soLuongHienTai + 1;
            header("Location: gio-hang.php");
            exit;
        } else {
            $thongBao = "Số lượng trong giỏ đã đạt giới hạn tồn kho.";
        }
    } else {
        $thongBao = "Sản phẩm không tồn tại hoặc đã hết hàng.";
    }
}

// Cap nhat so luong
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['cap_nhat'])) {

    foreach ($_POST['so_luong'] ?? [] as $id => $soLuong) {
        $id = (int)$id;
        $soLuong = (int)$soLuong;

        if ($soLuong <= 0) {
            unset($_SESSION['gio_hang'][$id]);
            continue;
        }

        $stmt = $conn->prepare(
            "SELECT SoLuong FROM SanPham WHERE MaSP = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $sp = $stmt->get_result()->fetch_assoc();

        if ($sp) {
            $_SESSION['gio_hang'][$id] =
                min($soLuong, (int)$sp['SoLuong']);
        } else {
            unset($_SESSION['gio_hang'][$id]);
        }
    }

    header("Location: gio-hang.php");
    exit;
}

// Xoa mot san pham
if (isset($_GET['xoa'])) {
    $id = (int)$_GET['xoa'];
    unset($_SESSION['gio_hang'][$id]);

    header("Location: gio-hang.php");
    exit;
}

// Lay thong tin cac san pham trong gio
$danhSach = [];
$tongTien = 0;

foreach ($_SESSION['gio_hang'] as $id => $soLuong) {
    $stmt = $conn->prepare(
        "SELECT MaSP, TenSP, Gia, SoLuong
         FROM SanPham WHERE MaSP = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $sp = $stmt->get_result()->fetch_assoc();

    if ($sp) {
        $sp['SoLuongMua'] = min(
            (int)$soLuong,
            (int)$sp['SoLuong']
        );

        $sp['ThanhTien'] =
            $sp['Gia'] * $sp['SoLuongMua'];

        $tongTien += $sp['ThanhTien'];
        $danhSach[] = $sp;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - Fashion Store</title>
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

<main class="cart-container">
    <h1>Giỏ hàng của bạn</h1>

    <?php if ($thongBao !== ''): ?>
        <p><?= htmlspecialchars($thongBao) ?></p>
    <?php endif; ?>

    <?php if (count($danhSach) > 0): ?>
        <form method="post" action="gio-hang.php">
            <div class="table-wrap">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($danhSach as $sp): ?>
                        <tr>
                            <td><?= htmlspecialchars($sp['TenSP']) ?></td>
                            <td>
                                <?= number_format($sp['Gia'], 0, ',', '.') ?> đ
                            </td>
                            <td>
                                <input
                                    type="number"
                                    name="so_luong[<?= (int)$sp['MaSP'] ?>]"
                                    value="<?= (int)$sp['SoLuongMua'] ?>"
                                    min="0"
                                    max="<?= (int)$sp['SoLuong'] ?>"
                                    class="quantity-input"
                                >
                            </td>
                            <td>
                                <?= number_format($sp['ThanhTien'], 0, ',', '.') ?> đ
                            </td>
                            <td>
                                <a href="gio-hang.php?xoa=<?= (int)$sp['MaSP'] ?>">
                                    Xóa
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <button type="submit" name="cap_nhat" class="cart-button">
                Cập nhật giỏ hàng
            </button>
        </form>

        <h2 class="cart-total">
            Tổng tiền: <?= number_format($tongTien, 0, ',', '.') ?> đ
        </h2>

<div style="margin-top: 20px;">
    <a href="dat-hang.php" class="btn">Tiến hành đặt hàng</a>
</div>

        <a href="index.php" class="continue-link">
            ← Tiếp tục mua sắm
        </a>

    <?php else: ?>
        <p>Giỏ hàng của bạn đang trống.</p>
        <a href="index.php" class="cart-button">
            Khám phá sản phẩm
        </a>
    <?php endif; ?>
</main>

<footer class="footer">
    <p>© 2026 Fashion Store</p>
</footer>

</body>
</html>
