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

// Xử lý tạo danh mục mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['category_name']) && isset($_POST['category_type'])) {
    $category_name = $_POST['category_name'];
    $category_type = $_POST['category_type'];
    $category_quantity = $_POST['category_quantity'];
    $category_sold = $_POST['category_sold'];
    $category_price = $_POST['category_price'];
    $category_demo = $_POST['category_demo'];
    $sql_create_category = "INSERT INTO categories (name, type, quantity, sold, price, demo) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_create_category = $conn->prepare($sql_create_category);
    $stmt_create_category->bind_param("ssiiis", $category_name, $category_type, $category_quantity, $category_sold, $category_price, $category_demo);
    $stmt_create_category->execute();
}

// Lấy danh sách các danh mục
$sql_categories = "SELECT * FROM categories";
$result_categories = $conn->query($sql_categories);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục</title>
    <link rel="stylesheet" href="giao-dien/admin-main.css">
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
        <h1>Danh sách danh mục</h1>
        <div class="category-list">
            <?php if ($result_categories->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Loại danh mục</th>
                            <th>Tên danh mục</th>
                            <th>Số lượng</th>
                            <th>Đã bán</th>
                            <th>Giá tiền</th>
                            <th>Demo</th>
                            <th>Tạo danh mục con</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($category = $result_categories->fetch_assoc()): ?>
                            <tr class="category-box">
                                <td><?= htmlspecialchars($category['type']) ?></td>
                                <td><?= htmlspecialchars($category['name']) ?></td>
                                <td><?= htmlspecialchars($category['quantity']) ?></td>
                                <td><?= htmlspecialchars($category['sold']) ?></td>
                                <td><?= htmlspecialchars($category['price']) ?></td>
                                <td><?= htmlspecialchars($category['demo']) ?></td>
                                <td><button onclick="document.querySelector('.sub-category-create').style.display='block'">Tạo danh mục con</button></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Không có danh mục nào.</p>
            <?php endif; ?>
        </div>
        <button onclick="document.getElementById('createCategoryModal').style.display='block'">Tạo danh mục mới</button>
        <div id="createCategoryModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="document.getElementById('createCategoryModal').style.display='none'">&times;</span>
                <h2>Tạo danh mục mới</h2>
                <form method="post" action="category_manager.php">
                    <div class="form-group">
                        <label for="category_type">Loại danh mục:</label>
                        <select id="category_type" name="category_type" required>
                            <option value="account">Tài khoản</option>
                            <option value="software_key">Key phần mềm</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="category_name">Tên danh mục:</label>
                        <input type="text" id="category_name" name="category_name" required>
                    </div>
                    <div class="form-group">
                        <label for="category_quantity">Số lượng:</label>
                        <input type="number" id="category_quantity" name="category_quantity" required>
                    </div>
                    <div class="form-group">
                        <label for="category_sold">Đã bán:</label>
                        <input type="number" id="category_sold" name="category_sold" required>
                    </div>
                    <div class="form-group">
                        <label for="category_price">Giá tiền:</label>
                        <input type="number" id="category_price" name="category_price" required>
                    </div>
                    <div class="form-group">
                        <label for="category_demo">Demo:</label>
                        <input type="text" id="category_demo" name="category_demo" required>
                    </div>
                    <button type="submit">Tạo danh mục</button>
                </form>
            </div>
        </div>
    </div>
    <script src="scripts/admin-main.js"></script>
</body>
</html>