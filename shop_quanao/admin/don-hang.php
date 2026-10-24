
<?php
require_once "../includes/db.php";

$thongBao = "";

// Xử lý cập nhật trạng thái
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $maDH = (int)($_POST["MaDH"] ?? 0);
    $trangThai = $_POST["TrangThai"] ?? "";

    $trangThaiHopLe = [
        "Cho xu ly",
        "Dang giao",
        "Da giao",
        "Da huy"
    ];

    if ($maDH > 0 && in_array($trangThai, $trangThaiHopLe, true)) {
        $stmt = $conn->prepare(
            "UPDATE donhang SET TrangThai = ? WHERE MaDH = ?"
        );
        $stmt->bind_param("si", $trangThai, $maDH);

        if ($stmt->execute()) {
            $thongBao = "Cập nhật trạng thái thành công!";
        } else {
            $thongBao = "Cập nhật trạng thái thất bại!";
        }
    }
}

// Lấy danh sách đơn hàng
$sql = "SELECT
            dh.MaDH,
            kh.HoTen,
            kh.SoDienThoai,
            kh.DiaChi,
            dh.NgayDat,
            dh.TongTien,
            dh.TrangThai
        FROM donhang dh
        LEFT JOIN khachhang kh ON dh.MaKH = kh.MaKH
        ORDER BY dh.MaDH DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="admin-container">
    <h1>Quản lý đơn hàng</h1>

    <p>
        <a href="san-pham.php">Quản lý sản phẩm</a> |
        <a href="../index.php">Về trang chủ</a>
    </p>

    <p style="color: green;">
        <?= htmlspecialchars($thongBao) ?>
    </p>

    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Số điện thoại</th>
                    <th>Địa chỉ</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($dh = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$dh['MaDH'] ?></td>

                            <td>
                                <?= htmlspecialchars($dh['HoTen'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($dh['SoDienThoai'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($dh['DiaChi'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($dh['NgayDat'] ?? '') ?>
                            </td>

                            <td>
                                <?= number_format(
                                    (float)$dh['TongTien'],
                                    0, ',', '.'
                                ) ?> đ
                            </td>

                            <td>
                                <form method="POST">
                                    <input type="hidden" name="MaDH"
                                           value="<?= (int)$dh['MaDH'] ?>">

                                    <select name="TrangThai">
                                        <option value="Cho xu ly"
                                            <?= $dh['TrangThai'] === 'Cho xu ly' ? 'selected' : '' ?>>
                                            Chờ xử lý
                                        </option>

                                        <option value="Dang giao"
                                            <?= $dh['TrangThai'] === 'Dang giao' ? 'selected' : '' ?>>
                                            Đang giao
                                        </option>

                                        <option value="Da giao"
                                            <?= $dh['TrangThai'] === 'Da giao' ? 'selected' : '' ?>>
                                            Đã giao
                                        </option>

                                        <option value="Da huy"
                                            <?= $dh['TrangThai'] === 'Da huy' ? 'selected' : '' ?>>
                                            Đã hủy
                                        </option>
                                    </select>

                                    <button type="submit">Cập nhật</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="7">Chưa có đơn hàng nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
