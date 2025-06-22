<?php
require 'includes/db.php';
$errors = [];
$success_message = '';

// Xử lý khi người dùng gửi form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // --- VALIDATION ---
    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = "Vui lòng điền đầy đủ thông tin.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ.";
    }
    if ($password !== $password_confirm) {
        $errors[] = "Mật khẩu xác nhận không khớp.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Mật khẩu phải có ít nhất 6 ký tự.";
    }

    // Kiểm tra username hoặc email đã tồn tại chưa
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Tên đăng nhập hoặc email đã được sử dụng.";
    }

    // --- INSERT VÀO DATABASE ---
    if (empty($errors)) {
        // *** Rất quan trọng: Luôn mã hóa mật khẩu trước khi lưu! ***
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashed_password])) {
            $success_message = "Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.";
        } else {
            $errors[] = "Đã có lỗi xảy ra. Vui lòng thử lại.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>Đăng ký tài khoản</title>
  <style>
  /* Thêm CSS cho đẹp hơn */
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
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
  }

  .error {
    color: red;
    margin-bottom: 15px;
  }

  .success {
    color: green;
    margin-bottom: 15px;
  }
  </style>
</head>

<body>
  <div class="form-container">
    <h1>Đăng ký</h1>
    <?php if (!empty($errors)): ?>
    <div class="error">
      <?php foreach ($errors as $error): ?>
      <p><?php echo $error; ?></p>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if ($success_message): ?>
    <div class="success">
      <p><?php echo $success_message; ?></p>
    </div>
    <a href="login.php"><button type="button">Tới trang Đăng nhập</button></a>
    <?php else: ?>
    <form action="register.php" method="POST">
      <div class="form-group">
        <label for="username">Tên đăng nhập</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="form-group">
        <label for="password">Mật khẩu</label>
        <input type="password" id="password" name="password" required>
      </div>
      <div class="form-group">
        <label for="password_confirm">Xác nhận mật khẩu</label>
        <input type="password" id="password_confirm" name="password_confirm" required>
      </div>
      <button type="submit">Đăng ký</button>
    </form>
    <p style="text-align: center; margin-top: 15px;">Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
    <?php endif; ?>
  </div>
</body>

</html>