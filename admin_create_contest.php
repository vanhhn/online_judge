<?php
require 'includes/header.php';
require 'includes/db.php';

// Bảo vệ trang
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    die("Bạn không có quyền truy cập trang này.");
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $problem_ids = isset($_POST['problems']) ? $_POST['problems'] : [];

    // Validation
    if (empty($name) || empty($start_time) || empty($end_time)) {
        $errors[] = 'Vui lòng điền đầy đủ thông tin.';
    }
    if (strtotime($end_time) <= strtotime($start_time)) {
        $errors[] = 'Thời gian kết thúc phải sau thời gian bắt đầu.';
    }
    if (empty($problem_ids)) {
        $errors[] = 'Vui lòng chọn ít nhất một bài tập cho contest.';
    }

    if (empty($errors)) {
        try {
            // Bắt đầu transaction
            $pdo->beginTransaction();

            // 1. Thêm contest vào bảng `contests`
            $stmt = $pdo->prepare("INSERT INTO contests (name, start_time, end_time) VALUES (?, ?, ?)");
            $stmt->execute([$name, $start_time, $end_time]);
            $contest_id = $pdo->lastInsertId();

            // 2. Thêm các bài tập vào bảng `contest_problems`
            $stmt_problems = $pdo->prepare("INSERT INTO contest_problems (contest_id, problem_id) VALUES (?, ?)");
            foreach ($problem_ids as $problem_id) {
                $stmt_problems->execute([$contest_id, $problem_id]);
            }

            // Hoàn tất transaction
            $pdo->commit();
            $success = "Tạo contest thành công!";

        } catch (Exception $e) {
            // Nếu có lỗi, rollback lại
            $pdo->rollBack();
            $errors[] = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}

// Lấy danh sách tất cả các bài tập để hiển thị checkbox
$problems_stmt = $pdo->query("SELECT id, title FROM problems ORDER BY id");
$all_problems = $problems_stmt->fetchAll();
?>

<div class="container" style="max-width: 700px; margin: auto;">
  <h1>Tạo Contest Mới</h1>
  <a href="admin_contests.php">&larr; Quay lại danh sách</a>

  <?php if(!empty($errors)): ?>
  <div style="color: red; border: 1px solid red; padding: 10px; margin-top: 15px;">
    <?php foreach($errors as $err) echo "<p>$err</p>"; ?>
  </div>
  <?php endif; ?>

  <?php if($success): ?>
  <div style="color: green; border: 1px solid green; padding: 10px; margin-top: 15px;">
    <p><?php echo $success; ?></p>
  </div>
  <?php endif; ?>

  <form action="" method="POST" style="margin-top: 20px;">
    <p>
      <label for="name">Tên Contest:</label><br>
      <input type="text" id="name" name="name" required style="width: 100%; padding: 8px;">
    </p>
    <p>
      <label for="start_time">Thời gian bắt đầu:</label><br>
      <input type="datetime-local" id="start_time" name="start_time" required>
    </p>
    <p>
      <label for="end_time">Thời gian kết thúc:</label><br>
      <input type="datetime-local" id="end_time" name="end_time" required>
    </p>

    <h3>Chọn bài tập cho Contest:</h3>
    <div style="height: 200px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px;">
      <?php foreach ($all_problems as $problem): ?>
      <label>
        <input type="checkbox" name="problems[]" value="<?php echo $problem['id']; ?>">
        <?php echo htmlspecialchars($problem['title']); ?>
      </label><br>
      <?php endforeach; ?>
    </div>

    <p>
      <button type="submit" style="padding: 10px 20px; font-size: 16px;">Tạo Contest</button>
    </p>
  </form>
</div>

</div>
</body>

</html>