<?php
require __DIR__ . '/cai-dat/load.php';

// Tải các biến môi trường từ tệp .env
loadEnv(__DIR__ . '/cai-dat/.env');

session_start();

// Kết nối đến cơ sở dữ liệu
$servername = getenv('DB_SERVERNAME');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $displayName = $_POST['display_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Kiểm tra mật khẩu
    if ($password !== $confirmPassword) {
        $error = "Mật khẩu không khớp.";
    } else {
        // Mã hóa mật khẩu
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Kiểm tra xem tên đăng nhập hoặc email đã tồn tại chưa
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Tên đăng nhập hoặc email đã tồn tại.";
        } else {
            // Thêm người dùng mới vào cơ sở dữ liệu
            $sql = "INSERT INTO users (username, password, email, phone, permission, money) VALUES (?, ?, ?, ?, 'user', 0.00)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $username, $hashedPassword, $email, $phone);
            if ($stmt->execute()) {
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $username;
                header("Location: index.php");
                exit();
            } else {
                $error = "Đăng ký thất bại. Vui lòng thử lại.";
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký</title>
    <link rel="stylesheet" href="giao-dien/register-login.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="form-title">Đăng Ký</h2>
            <?php if ($error != ""): ?>
                <p class="error"><?= $error ?></p>
            <?php endif; ?>
            <?php if ($success != ""): ?>
                <p class="success"><?= $success ?></p>
            <?php endif; ?>
            <form method="post" action="register.php">
                <div class="form-group">
                    <label for="display_name">Tên Hiển Thị:</label>
                    <input type="text" id="display_name" name="display_name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Số Điện Thoại:</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="username">Tên Đăng Nhập:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Mật Khẩu:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Nhập Lại Mật Khẩu:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit">Đăng Ký</button>
            </form>
            <p class="register-link">Đã có tài khoản? <a href="login.php">Đăng nhập tại đây</a></p>
        </div>
    </div>
</body>
</html>