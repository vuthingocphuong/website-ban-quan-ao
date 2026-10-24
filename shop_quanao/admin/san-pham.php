
<?php
require_once "../includes/db.php";

$thongBao = "";
$loaiThongBao = "success";

// Them san pham
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['them'])) {

    $tenSP = trim($_POST['TenSP'] ?? '');
    $maDanhMuc = (int)($_POST['MaDanhMuc'] ?? 0);
    $gia = (float)($_POST['Gia'] ?? 0);
    $soLuong = (int)($_POST['SoLuong'] ?? 0);
    $moTa = trim($_POST['MoTa'] ?? '');

    if ($tenSP === '' || $gia <= 0 || $soLuong < 0) {
        $thongBao = "Vui lòng nhập tên, giá hợp lệ và số lượng không âm.";
        $loaiThongBao = "error";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO SanPham
             (TenSP, MaDanhMuc, Gia, SoLuong, MoTa)
             VALUES (?, NULLIF(?, 0), ?, ?, ?)"
        );

        $stmt->bind_param(
            "sidis",
            $tenSP,
            $maDanhMuc,
            $gia,
            $soLuong,
            $moTa
        );

        if ($stmt->execute()) {
            header("Location: san-pham.php?them_thanh_cong=1");
            exit;
        } else {
            $thongBao = "Không thể thêm sản phẩm. Vui lòng thử lại.";
            $loaiThongBao = "error";
        }
    }
}

// Xoa san pham
if (isset($_GET['xoa'])) {
    $id = (int)$_GET['xoa'];

    $stmt = $conn->prepare("DELETE FROM SanPham WHERE MaSP = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: san-pham.php?xoa_thanh_cong=1");
        exit;
    } else {
        $thongBao = "Không thể xóa sản phẩm này.";
        $loaiThongBao = "error";
    }
}

if (isset($_GET['them_thanh_cong'])) {
    $thongBao = "Thêm sản phẩm thành công!";
}

if (isset($_GET['xoa_thanh_cong'])) {
    $thongBao = "Đã xóa sản phẩm thành công!";
}

// Lay danh sach danh muc
$danhMuc = $conn->query(
    "SELECT MaDanhMuc, TenDanhMuc FROM DanhMuc ORDER BY MaDanhMuc"
);

// Lay danh sach san pham
$sanPham = $conn->query(
    "SELECT s.*, d.TenDanhMuc
     FROM SanPham s
     LEFT JOIN DanhMuc d ON s.MaDanhMuc = d.MaDanhMuc
     ORDER BY s.MaSP DESC"
);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>


<header class="header">
    <a href="../index.php" class="logo">FASHION STORE</a>
    <nav>
        <a href="../index.php">Xem website</a>
        <a href="san-pham.php">Quản lý sản phẩm</a>
        <a href="don-hang.php">Quản lý đơn hàng</a>
    </nav>
</header>


<main class="admin-container">
    <h1>Quản lý sản phẩm</h1>
    
<nav class="admin-menu">
    <a href="san-pham.php">Quản lý sản phẩm</a>
    <a href="don-hang.php">Quản lý đơn hàng</a>
    <a href="../index.php">Về trang chủ</a>
</nav>


    <?php if ($thongBao !== ''): ?>
        <p class="admin-message <?= $loaiThongBao ?>">
            <?= htmlspecialchars($thongBao) ?>
        </p>
    <?php endif; ?>

    <section class="admin-form">
        <h2>Thêm sản phẩm mới</h2>

        <form method="post" action="san-pham.php">
            <label>Tên sản phẩm</label>
            <input type="text" name="TenSP" required maxlength="150">

            <label>Danh mục</label>
            <select name="MaDanhMuc">
                <option value="0">-- Chưa chọn danh mục --</option>
                <?php while ($dm = $danhMuc->fetch_assoc()): ?>
                    <option value="<?= (int)$dm['MaDanhMuc'] ?>">
                        <?= htmlspecialchars($dm['TenDanhMuc']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Giá bán (VNĐ)</label>
            <input type="number" name="Gia" min="1" step="any" required>

            <label>Số lượng</label>
            <input type="number" name="SoLuong" min="0" value="0" required>

            <label>Mô tả</label>
            <textarea name="MoTa" rows="3"></textarea>

            <button type="submit" name="them" class="cart-button">
                Thêm sản phẩm
            </button>
        </form>
    </section>

    <section class="admin-list">
        <h2>Danh sách sản phẩm</h2>

        <div class="table-wrap">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($sanPham): ?>
                        <?php while ($sp = $sanPham->fetch_assoc()): ?>
                            <tr>
                                <td><?= (int)$sp['MaSP'] ?></td>
                                <td><?= htmlspecialchars($sp['TenSP']) ?></td>
                                <td><?= htmlspecialchars($sp['TenDanhMuc'] ?? 'Chưa phân loại') ?></td>
                                <td><?= number_format($sp['Gia'], 0, ',', '.') ?> đ</td>
                                <td><?= (int)$sp['SoLuong'] ?></td>
                                <td>
                                    <a href="sua-san-pham.php?id=<?= (int)$sp['MaSP'] ?>">Sửa</a>
                                    |
                                    <a
                                        href="san-pham.php?xoa=<?= (int)$sp['MaSP'] ?>"
                                        onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')"
                                    >Xóa</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<footer class="footer">
    <p>© 2026 Fashion Store - Trang quản trị</p>
</footer>

</body>
</html>
