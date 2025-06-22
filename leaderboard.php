<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

// Câu lệnh SQL để lấy bảng xếp hạng
$sql = "
    SELECT
        u.username,
        COUNT(DISTINCT s.problem_id) AS problems_solved
    FROM
        submissions s
    JOIN
        users u ON s.user_id = u.id
    WHERE
        s.status = 'Accepted'
    GROUP BY
        u.id, u.username
    ORDER BY
        problems_solved DESC,
        MAX(s.submitted_at) ASC
";

$stmt = $pdo->query($sql);
$rankings = $stmt->fetchAll();
?>

<h1 class="mb-4">Bảng xếp hạng</h1>

<div class="table-responsive">
  <table class="table table-striped table-hover">
    <thead class="table-dark">
      <tr>
        <th scope="col" style="width: 10%;">Hạng</th>
        <th scope="col">Người dùng</th>
        <th scope="col" class="text-center">Số bài giải đúng</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rankings)): ?>
      <tr>
        <td colspan="3" class="text-center fst-italic">Chưa có ai giải đúng bài nào.</td>
      </tr>
      <?php else: ?>
      <?php foreach ($rankings as $index => $row): ?>
      <tr>
        <th scope="row" class="text-center"><?php echo $index + 1; ?></th>
        <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
        <td class="text-center fs-5"><?php echo $row['problems_solved']; ?></td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once 'includes/footer.php'; ?>