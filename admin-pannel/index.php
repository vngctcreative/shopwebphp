<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập hay không
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// Tải các biến môi trường từ tệp .env
require __DIR__ . '/../cai-dat/load.php';
loadEnv(__DIR__ . '/../cai-dat/.env');

$servername = getenv('DB_SERVERNAME');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Kiểm tra quyền của người dùng
$sql_permission = "SELECT permission FROM users WHERE username = ?";
$stmt_permission = $conn->prepare($sql_permission);
$stmt_permission->bind_param("s", $_SESSION['username']);
$stmt_permission->execute();
$result_permission = $stmt_permission->get_result();

if ($result_permission->num_rows > 0) {
    $user_permission = $result_permission->fetch_assoc()['permission'];
    if ($user_permission !== 'admin') {
        // Nếu không phải admin, chuyển hướng đến trang chủ
        header("Location: ../index.php");
        exit();
    }
} else {
    // Nếu không tìm thấy người dùng, chuyển hướng đến trang đăng nhập
    header("Location: ../login.php");
    exit();
}

// Lấy thông tin số tài khoản
$sql_accounts = "SELECT COUNT(*) AS total_accounts FROM users";
$result_accounts = $conn->query($sql_accounts);
$total_accounts = $result_accounts->fetch_assoc()['total_accounts'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="/admin-pannel/giao-dien/admin-main.css">
</head>
<body class="<?= isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light' ?>">
    <div class="sidebar">
        <a href="index.php" class="admin-panel-title">Admin Panel</a>
        <a href="category_manager.php">Quản lý danh mục</a>
        <a href="accounts_manager.php">Quản lý tài khoản</a>
        <a href="website_manager.php">Quản lý trang web</a>
        <div class="theme-switch">
            <input type="checkbox" id="theme-toggle" <?= isset($_SESSION['theme']) && $_SESSION['theme'] == 'dark' ? 'checked' : '' ?>>
            <label for="theme-toggle" id="theme-label"></label>
        </div>
    </div>
    <div class="content">
        <div class="stat-box">
            <h2><?= $total_accounts ?></h2>
            <p>Tổng số tài khoản</p>
        </div>
        <!-- Thêm các box thống kê khác nếu cần -->
    </div>
    <script>
        document.getElementById('theme-toggle').addEventListener('change', function () {
            var theme = this.checked ? 'dark' : 'light';
            document.body.className = theme;
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "../set_theme.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send("theme=" + theme);
        });
    </script>
</body>
</html>