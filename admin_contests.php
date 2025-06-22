<?php
require 'includes/header.php'; // Dùng header chung
require 'includes/db.php';

// Bảo vệ trang: Chỉ admin (user id = 1) mới được vào
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    die("Bạn không có quyền truy cập trang này.");
}
?>

<div class="container" style="max-width: 900px; margin: auto;">
  <h1>Quản lý Contest</h1>
  <a href="admin_create_contest.php"
    style="display: inline-block; margin-bottom: 20px; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;">Tạo
    Contest Mới</a>

  <h2>Danh sách các Contest đã tạo</h2>
  <table border="1" style="width: 100%; border-collapse: collapse;">
    <thead>
      <tr>
        <th>ID</th>
        <th>Tên Contest</th>
        <th>Bắt đầu</th>
        <th>Kết thúc</th>
      </tr>
    </thead>
    <tbody>
      <?php
            $stmt = $pdo->query("SELECT * FROM contests ORDER BY start_time DESC");
            while ($contest = $stmt->fetch()): ?>
      <tr>
        <td><?php echo $contest['id']; ?></td>
        <td><?php echo htmlspecialchars($contest['name']); ?></td>
        <td><?php echo $contest['start_time']; ?></td>
        <td><?php echo $contest['end_time']; ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

</div>
</body>

</html>