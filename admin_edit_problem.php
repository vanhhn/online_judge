<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

// Bảo vệ trang
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: index.php');
    exit("Bạn không có quyền truy cập trang này.");
}

$problem_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$problem_id) {
    die("ID bài tập không hợp lệ.");
}

// Xử lý khi admin gửi form CẬP NHẬT BÀI TẬP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_problem'])) {
    // ... (Code xử lý cập nhật bài tập giữ nguyên như cũ, tôi rút gọn ở đây)
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $description = $_POST['description'];
    $id = (int)$_POST['id'];
    $stmt_update = $pdo->prepare("UPDATE problems SET title = ?, slug = ?, description = ? WHERE id = ?");
    $stmt_update->execute([$title, $slug, $description, $id]);
    // Thêm message thành công nếu cần
}


// Lấy dữ liệu hiện tại của bài toán
$stmt = $pdo->prepare("SELECT * FROM problems WHERE id = ?");
$stmt->execute([$problem_id]);
$problem = $stmt->fetch();
if (!$problem) { die("Không tìm thấy bài tập."); }

// LẤY DỮ LIỆU CÁC TEST CASE CỦA BÀI TẬP NÀY
$tc_stmt = $pdo->prepare("SELECT * FROM test_cases WHERE problem_id = ? ORDER BY id ASC");
$tc_stmt->execute([$problem_id]);
$test_cases = $tc_stmt->fetchAll();
?>

<h1 class="mb-4">Sửa bài tập: <?php echo htmlspecialchars($problem['title']); ?></h1>
<a href="admin_problems.php">&larr; Quay lại danh sách</a>
<form action="admin_edit_problem.php?id=<?php echo $problem['id']; ?>" method="POST"
  class="mt-3 border rounded p-3 mb-5">
  <input type="hidden" name="id" value="<?php echo $problem['id']; ?>">
  <div class="mb-3">
    <label for="title" class="form-label">Tiêu đề bài tập</label>
    <input type="text" name="title" id="title" class="form-control"
      value="<?php echo htmlspecialchars($problem['title']); ?>" required>
  </div>
  <div class="mb-3">
    <label for="description" class="form-label">Nội dung đề bài (Hỗ trợ HTML)</label>
    <textarea name="description" id="description" rows="15"
      class="form-control"><?php echo htmlspecialchars($problem['description']); ?></textarea>
  </div>
  <button type="submit" name="update_problem" class="btn btn-primary">Lưu thay đổi</button>
</form>

<hr>

<h2 class="mb-4">Quản lý Test Case</h2>

<div class="table-responsive mb-4">
  <table class="table table-bordered">
    <thead class="table-light">
      <tr>
        <th style="width: 5%;">ID</th>
        <th>Input</th>
        <th>Output</th>
        <th style="width: 10%;" class="text-center">Hành động</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($test_cases as $tc): ?>
      <tr>
        <td><?php echo $tc['id']; ?></td>
        <td>
          <pre class="m-0"><?php echo htmlspecialchars($tc['input']); ?></pre>
        </td>
        <td>
          <pre class="m-0"><?php echo htmlspecialchars($tc['output']); ?></pre>
        </td>
        <td class="text-center">
          <a href="admin_delete_test_case.php?id=<?php echo $tc['id']; ?>&problem_id=<?php echo $problem_id; ?>"
            class="btn btn-danger btn-sm" onclick="return confirm('Bạn chắc chắn muốn xóa test case này?');">Xóa</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<h3 class="mb-3">Thêm Test Case mới</h3>
<form action="admin_add_test_case.php" method="POST" class="border rounded p-3">
  <input type="hidden" name="problem_id" value="<?php echo $problem_id; ?>">
  <div class="row">
    <div class="col-md-6">
      <div class="mb-3">
        <label for="input_data" class="form-label">Input Data</label>
        <textarea name="input_data" id="input_data" rows="8" class="form-control font-monospace" required></textarea>
      </div>
    </div>
    <div class="col-md-6">
      <div class="mb-3">
        <label for="output_data" class="form-label">Output Data</label>
        <textarea name="output_data" id="output_data" rows="8" class="form-control font-monospace" required></textarea>
      </div>
    </div>
  </div>
  <button type="submit" class="btn btn-success">Thêm Test Case</button>
</form>

<?php require_once 'includes/footer.php'; ?>