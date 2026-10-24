
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ - Shop Quần Áo</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="header">
    <div class="container">
        <h1>SHOP QUẦN ÁO</h1>
        <nav>
            <a href="index.php">Trang chủ</a>
            <a href="gio-hang.php">Giỏ hàng</a>
            <a href="lien-he.php">Liên hệ</a>
        </nav>
    </div>
</header>

<main class="container">
    <section class="contact-section">
        <h2>Liên hệ với chúng tôi</h2>
        <p>Nếu bạn có thắc mắc về sản phẩm hoặc đơn hàng, hãy gửi thông tin cho shop.</p>

        <form method="POST" class="admin-form">
            <label>Họ và tên</label>
            <input type="text" name="ho_ten" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Số điện thoại</label>
            <input type="tel" name="so_dien_thoai" required>

            <label>Nội dung liên hệ</label>
            <textarea name="noi_dung" rows="5" required></textarea>

            <button type="submit" name="gui_lien_he">Gửi liên hệ</button>
        </form>

        <?php
        if (isset($_POST['gui_lien_he'])) {
            echo "<p>Shop đã nhận được thông tin của bạn!</p>";
        }
        ?>
    </section>
</main>

<footer>
    <p>© 2026 Shop Quần Áo</p>
</footer>

</body>
</html>
