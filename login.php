<?php
// Luôn bắt đầu session ở đầu file
session_start();

// Nếu người dùng đã đăng nhập, chuyển hướng về trang chủ
if (isset($_SESSION['user_id'])) {
  header('Location: index.php');
  exit();
}

require 'includes/db.php';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
  $password = $_POST['password'];

  if (empty($username) || empty($password)) {
    $error = "Vui lòng nhập tên đăng nhập và mật khẩu.";
  } else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    // // --- BẮT ĐẦU ĐOẠN CODE DEBUG ---
    // echo "Dữ liệu bạn nhập vào form:";
    // var_dump($_POST);

    // echo "<br>Dữ liệu lấy từ database cho user '{$username}':";
    // var_dump($user);

    // die("--- KẾT THÚC DEBUG ---");
    // // --- KẾT THÚC ĐOẠN CODE DEBUG ---
    // Kiểm tra user có tồn tại và mật khẩu có khớp không
    if ($user && password_verify($password, $user['password'])) {
      // Đăng nhập thành công, lưu thông tin vào session
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      header('Location: index.php'); // Chuyển hướng đến trang chủ
      exit();
    } else {
      $error = "Tên đăng nhập hoặc mật khẩu không đúng.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>Đăng nhập</title>
  <style>
  body {
    font-family: sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-color: #f4f4f4;
  }

  .form-container {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 350px;
  }

  .form-container h1 {
    text-align: center;
    margin-bottom: 20px;
  }

  .form-group {
    margin-bottom: 15px;
  }

  .form-group label {
    display: block;
    margin-bottom: 5px;
  }

  .form-group input {
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
  }

  button {
    width: 100%;
    padding: 10px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
  }

  .error {
    color: red;
    text-align: center;
    margin-bottom: 15px;
  }
  </style>
</head>

<body>
  <div class="form-container">
    <h1>Đăng nhập</h1>
    <?php if ($error): ?>
    <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <form action="login.php" method="POST">
      <div class="form-group">
        <label for="username">Tên đăng nhập</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="form-group">
        <label for="password">Mật khẩu</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit">Đăng nhập</button>
    </form>
    <p style="text-align: center; margin-top: 15px;">Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
  </div>
</body>

</html>