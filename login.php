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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Kiểm tra thông tin đăng nhập
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user;
            header("Location: index.php");
            exit();
        } else {
            $error = "Tên đăng nhập hoặc mật khẩu không đúng.";
        }
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không đúng.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link rel="stylesheet" href="giao-dien/register-login.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="form-title">Đăng Nhập</h2>
            <?php if ($error != ""): ?>
                <p class="error"><?= $error ?></p>
            <?php endif; ?>
            <form method="post" action="login.php">
                <div class="form-group">
                    <label for="username">Tên Đăng Nhập:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Mật Khẩu:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Đăng Nhập</button>
            </form>
            <p class="register-link">Chưa có tài khoản? <a href="register.php">Đăng ký tài khoản</a></p>
        </div>
    </div>
</body>
</html>