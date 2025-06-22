<?php
// includes/header.php (Phiên bản cuối cùng, đã cập nhật)

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi" class="h-100">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OJ VAnh</title>
  <link rel="icon" type="image/png" href="assets/icon.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-okaidia.min.css" rel="stylesheet" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/codemirror.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/theme/dracula.min.css">

  <style>
  /* Giúp footer luôn ở dưới cùng trên các trang ít nội dung */
  body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  main {
    flex-grow: 1;
  }

  /* Style cho editor CodeMirror */
  .CodeMirror {
    border: 1px solid #ced4da;
    border-radius: .25rem;
    height: 500px;
  }
  </style>
</head>

<body class="d-flex flex-column h-100">

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="index.php"><strong>OJ Mini</strong></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Bài tập</a></li>
          <li class="nav-item"><a class="nav-link" href="contests.php">Kỳ thi</a></li>
          <li class="nav-item"><a class="nav-link" href="submissions.php">Lịch sử nộp bài</a></li>
          <li class="nav-item"><a class="nav-link" href="leaderboard.php">Bảng xếp hạng</a></li>
        </ul>
        <ul class="navbar-nav">
          <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
              Chào, <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="profile.php">Hồ sơ của tôi</a></li>
              <?php if($_SESSION['user_id'] == 1): // Giả sử admin id = 1 ?>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="admin_problems.php">Quản lý Bài tập</a></li>
              <li><a class="dropdown-item" href="admin_contests.php">Quản lý Contest</a></li>
              <?php endif; ?>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
            </ul>
          </li>
          <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Đăng nhập</a></li>
          <li class="nav-item"><a class="nav-link" href="register.php">Đăng ký</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <main class="container py-4">