<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

// Bảo vệ trang: Chỉ admin (user id = 1) mới được vào
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    // Dùng die() hoặc chuyển hướng về trang chủ
    header('Location: index.php');
    exit("Bạn không có quyền truy cập trang này.");
}

// Lấy tất cả bài toán
$stmt = $pdo->query("SELECT id, title, slug FROM problems ORDER BY id DESC");
$problems = $stmt->fetchAll();
?>

<h1 class="mb-4">Quản lý Bài tập</h1>
<a href="admin_add_problem.php" class="btn btn-success mb-3">Thêm bài tập mới</a>

<div class="table-responsive">
  <table class="table table-bordered table-hover">
    <thead class="table-light">
      <tr>
        <th style="width: 5%;">ID</th>
        <th>Tiêu đề</th>
        <th>Slug</th>
        <th style="width: 15%;" class="text-center">Hành động</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($problems as $problem): ?>
      <tr>
        <td><?php echo $problem['id']; ?></td>
        <td><?php echo htmlspecialchars($problem['title']); ?></td>
        <td><?php echo htmlspecialchars($problem['slug']); ?></td>
        <td class="text-center">
          <a href="admin_edit_problem.php?id=<?php echo $problem['id']; ?>" class="btn btn-primary btn-sm">Sửa</a>
          <a href="admin_delete_problem.php?id=<?php echo $problem['id']; ?>" class="btn btn-danger btn-sm"
            onclick="return confirm('Bạn có chắc chắn muốn xóa bài tập này? Mọi dữ liệu liên quan (test case, submission) cũng sẽ bị xóa.');">Xóa</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once 'includes/footer.php'; ?>