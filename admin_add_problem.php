<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

// Bảo vệ trang
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: index.php');
    exit("Bạn không có quyền truy cập trang này.");
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $description = $_POST['description']; // Giả sử bạn sẽ tự nhập HTML

    if (empty($title) || empty($description)) {
        $errors[] = "Tiêu đề và Nội dung không được để trống.";
    }

    // Tự tạo slug nếu người dùng không nhập
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }

    // Kiểm tra slug đã tồn tại chưa
    $stmt = $pdo->prepare("SELECT id FROM problems WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetch()) {
        $errors[] = "Slug này đã tồn tại, vui lòng chọn một slug khác.";
    }

    if (empty($errors)) {
        $stmt_insert = $pdo->prepare("INSERT INTO problems (title, slug, description, created_by) VALUES (?, ?, ?, ?)");
        if ($stmt_insert->execute([$title, $slug, $description, $_SESSION['user_id']])) {
            $success = 'Thêm bài tập mới thành công! Bạn có thể thêm các Test Case cho bài tập này trong database.';
        } else {
            $errors[] = 'Lỗi hệ thống, không thể thêm bài tập.';
        }
    }
}
?>

<h1 class="mb-4">Thêm bài tập mới</h1>
<a href="admin_problems.php">&larr; Quay lại danh sách</a>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger mt-3">
  <?php foreach ($errors as $error) echo "<p class='mb-0'>$error</p>"; ?>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success mt-3"><?php echo $success; ?></div>
<?php endif; ?>

<form action="admin_add_problem.php" method="POST" class="mt-3">
  <div class="mb-3">
    <label for="title" class="form-label">Tiêu đề bài tập</label>
    <input type="text" name="title" id="title" class="form-control" required>
  </div>
  <div class="mb-3">
    <label for="slug" class="form-label">Slug (URL-friendly)</label>
    <input type="text" name="slug" id="slug" class="form-control">
    <div class="form-text">Dùng cho đường dẫn URL. Nếu để trống, hệ thống sẽ tự tạo từ tiêu đề. Ví dụ: 'a-cong-b'.</div>
  </div>
  <div class="mb-3">
    <label for="description" class="form-label">Nội dung đề bài (Hỗ trợ HTML)</label>
    <textarea name="description" id="description" rows="15" class="form-control"></textarea>
    <div class="form-text">Bạn có thể dùng các thẻ HTML như `&lt;h1&gt;`, `&lt;p&gt;`, `&lt;b&gt;`, `&lt;pre&gt;` để
      định dạng.</div>
  </div>
  <button type="submit" class="btn btn-primary">Lưu bài tập</button>
</form>

<?php require_once 'includes/footer.php'; ?>