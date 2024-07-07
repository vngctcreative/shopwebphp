<?php
session_start();

// Kiểm tra và lưu giao diện người dùng đã chọn
if (isset($_POST['theme'])) {
    $_SESSION['theme'] = $_POST['theme'];
}

// Đặt giao diện mặc định là sáng
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';

// Kiểm tra xem người dùng đã đăng nhập hay chưa
$loggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$userInfo = null;

if ($loggedIn) {
    // Kết nối đến cơ sở dữ liệu
    require __DIR__ . '/cai-dat/load.php';
    loadEnv(__DIR__ . '/cai-dat/.env');
    
    $servername = getenv('DB_SERVERNAME');
    $username = getenv('DB_USERNAME');
    $password = getenv('DB_PASSWORD');
    $dbname = getenv('DB_NAME');

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Kiểm tra kết nối
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Lấy thông tin người dùng
    $sql = "SELECT id, username, money FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $userInfo = $result->fetch_assoc();
    }

    $conn->close();
}

// Hàm định dạng tiền Việt
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' ₫';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>
    <link rel="stylesheet" href="giao-dien/main.css">
</head>
<body class="<?= $theme ?>">
    <div class="container">
        <header>
            <nav id="navbar">
                <div class="logo">
                    <img src="images/logo_shop_creative.png" alt="Logo">
                </div>
                <ul>
                    <li><a href="#" onclick="showCategory('account')">Tài Khoản VIP</a></li>
                    <li><a href="#" onclick="showCategory('key')">Key Phần Mềm</a></li>
                </ul>
                <div class="navbar-right">
                    <div class="theme-switch">
                        <input type="checkbox" id="theme-toggle" <?= $theme == 'dark' ? 'checked' : '' ?>>
                        <label for="theme-toggle" id="theme-label"></label>
                    </div>
                    <?php if ($loggedIn && $userInfo): ?>
                        <div class="user-info">
                            <img src="images/avatar.jpg" alt="Avatar" class="avatar">
                            <span>ID: <?= $userInfo['id'] ?> - <?= $userInfo['username'] ?> - <?= formatCurrency($userInfo['money']) ?></span>
                            <form method="post" action="logout.php" style="display:inline;">
                                <button type="submit" class="logout-button">Đăng Xuất</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="login-register">
                            <button onclick="window.location.href='login.php'" class="login-button">Đăng Nhập</button>
                            <button onclick="window.location.href='register.php'" class="register-button">Đăng Ký</button>
                        </div>
                    <?php endif; ?>
                </div>
            </nav>
        </header>
        <main>
            <div id="account-category" class="category">
                <!-- <div class="product-box">
                    <div class="product-title">Tài Khoản VIP</div>
                    <div class="product-description">Truy cập không giới hạn vào tất cả các nội dung cao cấp.</div>
                    <div class="product-price">$50</div>
                </div> -->
                <!-- Thêm danh mục tài khoản ở đây -->
            </div>
            <div id="key-category" class="category" style="display: none;">
                <!-- <div class="product-box">
                    <div class="product-title">Key Phần Mềm XYZ</div>
                    <div class="product-description">Bản quyền phần mềm XYZ cho một năm.</div>
                    <div class="product-price">$30</div>
                </div> -->
                <!-- Thêm danh mục key ở đây -->
            </div>
        </main>
    </div>
    <script src="scripts/switch_theme.js"></script>
    <script>
        function showCategory(category) {
            document.getElementById('account-category').style.display = (category === 'account') ? 'block' : 'none';
            document.getElementById('key-category').style.display = (category === 'key') ? 'block' : 'none';
        }

        // Initialize the default category
        showCategory('account');
    </script>
</body>
</html>