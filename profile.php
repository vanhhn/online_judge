<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

// Người dùng phải đăng nhập để xem trang này
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// --- PHẦN XỬ LÝ ĐỔI MẬT KHẨU ---
$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // Validation
    if (empty($old_password) || empty($new_password) || empty($confirm_new_password)) {
        $errors[] = 'Vui lòng điền đầy đủ các trường.';
    } elseif ($new_password !== $confirm_new_password) {
        $errors[] = 'Mật khẩu mới không khớp.';
    } elseif (strlen($new_password) < 6) {
        $errors[] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
    } else {
        // Lấy mật khẩu hiện tại từ DB để xác thực
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user && password_verify($old_password, $user['password'])) {
            // Mật khẩu cũ chính xác, tiến hành cập nhật mật khẩu mới
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($update_stmt->execute([$hashed_new_password, $user_id])) {
                $success_message = 'Đổi mật khẩu thành công!';
            } else {
                $errors[] = 'Lỗi hệ thống, không thể cập nhật mật khẩu.';
            }
        } else {
            $errors[] = 'Mật khẩu cũ không đúng.';
        }
    }
}


// --- LẤY CÁC SỐ LIỆU THỐNG KÊ (giữ nguyên như cũ) ---
$total_submissions_stmt = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE user_id = ?");
$total_submissions_stmt->execute([$user_id]);
$total_submissions = $total_submissions_stmt->fetchColumn();

$accepted_problems_stmt = $pdo->prepare("SELECT COUNT(DISTINCT problem_id) FROM submissions WHERE user_id = ? AND status = 'Accepted'");
$accepted_problems_stmt->execute([$user_id]);
$accepted_problems = $accepted_problems_stmt->fetchColumn();

$verdicts_stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM submissions WHERE user_id = ? GROUP BY status");
$verdicts_stmt->execute([$user_id]);
$verdicts = $verdicts_stmt->fetchAll();

$recent_submissions_stmt = $pdo->prepare("SELECT s.id, s.status, s.submitted_at, p.title AS problem_title FROM submissions s JOIN problems p ON s.problem_id = p.id WHERE s.user_id = ? ORDER BY s.submitted_at DESC LIMIT 10");
$recent_submissions_stmt->execute([$user_id]);
$recent_submissions = $recent_submissions_stmt->fetchAll();
?>

<h1 class="mb-4">Hồ sơ của <span class="text-primary"><?php echo htmlspecialchars($username); ?></span></h1>

<div class="row">
  <div class="col-lg-8">
    <div class="row mb-4">
      <div class="col-md-6 mb-3">
        <div class="card text-center h-100">
          <div class="card-header fw-bold">Số bài giải đúng</div>
          <div class="card-body">
            <h2 class="card-title display-4"><?php echo $accepted_problems; ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="card text-center h-100">
          <div class="card-header fw-bold">Tổng số lần nộp bài</div>
          <div class="card-body">
            <h2 class="card-title display-4"><?php echo $total_submissions; ?></h2>
          </div>
        </div>
      </div>
    </div>
    <div class="card mb-4">
      <div class="card-header fw-bold">Thống kê kết quả</div>
      <ul class="list-group list-group-flush">
        <?php foreach($verdicts as $verdict): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <?php echo htmlspecialchars($verdict['status']); ?>
          <span class="badge bg-primary rounded-pill"><?php echo $verdict['count']; ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header fw-bold">Đổi mật khẩu</div>
      <div class="card-body">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <?php foreach ($errors as $error) echo "<p class='mb-0'>$error</p>"; ?>
        </div>
        <?php endif; ?>
        <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form action="profile.php" method="POST">
          <div class="mb-3">
            <label for="old_password" class="form-label">Mật khẩu cũ</label>
            <input type="password" name="old_password" id="old_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="new_password" class="form-label">Mật khẩu mới</label>
            <input type="password" name="new_password" id="new_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="confirm_new_password" class="form-label">Xác nhận mật khẩu mới</label>
            <input type="password" name="confirm_new_password" id="confirm_new_password" class="form-control" required>
          </div>
          <button type="submit" name="change_password" class="btn btn-primary w-100">Cập nhật</button>
        </form>
      </div>
    </div>
  </div>
</div>

<h2 class="mb-3 mt-4">Các bài nộp gần đây</h2>
<div class="table-responsive">
</div>

<?php require_once 'includes/footer.php'; ?>