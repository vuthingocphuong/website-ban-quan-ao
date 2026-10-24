
<?php
require_once "../includes/db.php";

// Lấy mã sản phẩm từ URL
$id = (int)($_GET['id'] ?? 0);

// Lấy thông tin sản phẩm
$stmt = $conn->prepare("SELECT * FROM SanPham WHERE MaSP = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$sp = $result->fetch_assoc();

if (!$sp) {
    die("Không tìm thấy sản phẩm!");
}

$thong_bao = "";

// Xử lý khi nhấn nút cập nhật
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tenSP = trim($_POST['TenSP'] ?? "");
    $gia = (int)($_POST['Gia'] ?? 0);
    $soLuong = (int)($_POST['SoLuong'] ?? 0);
    $moTa = trim($_POST['MoTa'] ?? "");
    $maDanhMuc = (int)($_POST['MaDanhMuc'] ?? 0);

    if ($tenSP !== "" && $gia > 0 && $soLuong >= 0) {
        $stmt = $conn->prepare(
            "UPDATE SanPham
             SET TenSP = ?, Gia = ?, SoLuong = ?, MoTa = ?, MaDanhMuc = ?
             WHERE MaSP = ?"
        );

        $stmt->bind_param(
            "siisii",
            $tenSP,
            $gia,
            $soLuong,
            $moTa,
            $maDanhMuc,
            $id
        );

        if ($stmt->execute()) {
            header("Location: san-pham.php");
            exit;
        } else {
            $thong_bao = "Cập nhật thất bại!";
        }
    } else {
        $thong_bao = "Vui lòng nhập tên, giá hợp lệ và số lượng không âm.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa sản phẩm</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="admin-container">
    <h1>Sửa sản phẩm</h1>

    <p style="color:red;">
        <?= htmlspecialchars($thong_bao) ?>
    </p>

    <form method="POST" class="admin-form">
        <label>Tên sản phẩm</label>
        <input
            type="text"
            name="TenSP"
            value="<?= htmlspecialchars($sp['TenSP']) ?>"
            required
        >

        <label>Danh mục</label>
        <input
            type="number"
            name="MaDanhMuc"
            min="1"
            value="<?= (int)$sp['MaDanhMuc'] ?>"
        >

        <label>Giá (VNĐ)</label>
        <input
            type="number"
            name="Gia"
            min="1"
            step="1"
            value="<?= (int)$sp['Gia'] ?>"
            required
        >

        <label>Số lượng</label>
        <input
            type="number"
            name="SoLuong"
            min="0"
            step="1"
            value="<?= (int)$sp['SoLuong'] ?>"
            required
        >

        <label>Mô tả</label>
        <textarea name="MoTa"><?= htmlspecialchars($sp['MoTa'] ?? '') ?></textarea>

        <button type="submit">Cập nhật sản phẩm</button>
    </form>

    <p><a href="san-pham.php">Quay lại quản lý sản phẩm</a></p>
</div>

</body>
</html>
