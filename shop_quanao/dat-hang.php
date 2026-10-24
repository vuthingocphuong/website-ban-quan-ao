
<?php
session_start();
require_once "includes/db.php";

$gio_hang = $_SESSION['gio_hang'] ?? [];
$thong_bao = "";

if (empty($gio_hang)) {
    die('Giỏ hàng đang trống. <a href="index.php">Quay lại mua hàng</a>');
}

// Tính tổng tiền từ database, không tin giá gửi từ trình duyệt
$tongTien = 0;
$san_pham_dat = [];

foreach ($gio_hang as $maSP => $soLuong) {
    $maSP = (int)$maSP;
    $soLuong = (int)$soLuong;

    if ($maSP <= 0 || $soLuong <= 0) {
        continue;
    }

    $stmt = $conn->prepare(
        "SELECT MaSP, TenSP, Gia, SoLuong
         FROM SanPham WHERE MaSP = ?"
    );
    $stmt->bind_param("i", $maSP);
    $stmt->execute();
    $sp = $stmt->get_result()->fetch_assoc();

    if (!$sp) {
        die('Có sản phẩm không còn tồn tại. <a href="gio-hang.php">Kiểm tra giỏ hàng</a>');
    }

    if ($soLuong > (int)$sp['SoLuong']) {
        die('Sản phẩm "' . htmlspecialchars($sp['TenSP']) .
            '" không đủ hàng. Vui lòng giảm số lượng trong <a href="gio-hang.php">giỏ hàng</a>.');
    }

    $sp['SoLuongDat'] = $soLuong;
    $san_pham_dat[] = $sp;
    $tongTien += (float)$sp['Gia'] * $soLuong;
}

if (empty($san_pham_dat)) {
    die('Giỏ hàng không hợp lệ. <a href="gio-hang.php">Quay lại giỏ hàng</a>');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoTen = trim($_POST['HoTen'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $soDienThoai = trim($_POST['SoDienThoai'] ?? '');
    $diaChi = trim($_POST['DiaChi'] ?? '');

    if ($hoTen === '' || $soDienThoai === '' || $diaChi === '') {
        $thong_bao = "Vui lòng nhập họ tên, số điện thoại và địa chỉ.";
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $thong_bao = "Email không hợp lệ.";
    } else {
        try {
            $conn->begin_transaction();

            // Tạo khách hàng
            $stmt = $conn->prepare(
                "INSERT INTO khachhang (HoTen, Email, SoDienThoai, DiaChi)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $hoTen, $email, $soDienThoai, $diaChi);
            $stmt->execute();
            $maKH = $conn->insert_id;

            // Tạo đơn hàng
            $trangThai = "Cho xu ly";
            $stmt = $conn->prepare(
                "INSERT INTO donhang (MaKH, TongTien, TrangThai)
                 VALUES (?, ?, ?)"
            );
            $stmt->bind_param("ids", $maKH, $tongTien, $trangThai);
            $stmt->execute();
            $maDH = $conn->insert_id;

            // Lưu chi tiết đơn hàng và cập nhật tồn kho
            $stmtCT = $conn->prepare(
                "INSERT INTO chitietdonhang (MaDH, MaSP, SoLuong, DonGia)
                 VALUES (?, ?, ?, ?)"
            );

            $stmtKho = $conn->prepare(
                "UPDATE SanPham SET SoLuong = SoLuong - ?
                 WHERE MaSP = ? AND SoLuong >= ?"
            );

            foreach ($san_pham_dat as $sp) {
                $maSP = (int)$sp['MaSP'];
                $soLuong = (int)$sp['SoLuongDat'];
                $donGia = (float)$sp['Gia'];

                $stmtCT->bind_param("iiid", $maDH, $maSP, $soLuong, $donGia);
                $stmtCT->execute();

                $stmtKho->bind_param("iii", $soLuong, $maSP, $soLuong);
                $stmtKho->execute();

                if ($stmtKho->affected_rows !== 1) {
                    throw new Exception("Số lượng sản phẩm đã thay đổi. Vui lòng thử lại.");
                }
            }

            $conn->commit();

            unset($_SESSION['gio_hang']);

            echo "<h2>Đặt hàng thành công!</h2>";
            echo "<p>Mã đơn hàng: " . (int)$maDH . "</p>";
            echo "<p>Tổng tiền: " . number_format($tongTien, 0, ',', '.') . " đ</p>";
            echo '<p><a href="index.php">Tiếp tục mua sắm</a></p>';
            exit;
        } catch (Throwable $e) {
            $conn->rollback();
            $thong_bao = "Đặt hàng chưa thành công. Vui lòng thử lại.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng - Shop Quần Áo</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h1>Thông tin đặt hàng</h1>

    <h3>Sản phẩm đã chọn</h3>
    <?php foreach ($san_pham_dat as $sp): ?>
        <p>
            <?= htmlspecialchars($sp['TenSP']) ?>
            × <?= (int)$sp['SoLuongDat'] ?>
            — <?= number_format($sp['Gia'] * $sp['SoLuongDat'], 0, ',', '.') ?> đ
        </p>
    <?php endforeach; ?>

    <h3>Tổng tiền: <?= number_format($tongTien, 0, ',', '.') ?> đ</h3>

    <p style="color:red;">
        <?= htmlspecialchars($thong_bao) ?>
    </p>

    <form method="POST" class="admin-form">
        <label>Họ và tên *</label>
        <input type="text" name="HoTen"
               value="<?= htmlspecialchars($_POST['HoTen'] ?? '') ?>" required>

        <label>Email</label>
        <input type="email" name="Email"
               value="<?= htmlspecialchars($_POST['Email'] ?? '') ?>">

        <label>Số điện thoại *</label>
        <input type="tel" name="SoDienThoai"
               value="<?= htmlspecialchars($_POST['SoDienThoai'] ?? '') ?>" required>

        <label>Địa chỉ nhận hàng *</label>
        <textarea name="DiaChi" required><?= htmlspecialchars($_POST['DiaChi'] ?? '') ?></textarea>

        <button type="submit">Xác nhận đặt hàng</button>
    </form>

    <p><a href="gio-hang.php">Quay lại giỏ hàng</a></p>
</div>
</body>
</html>
