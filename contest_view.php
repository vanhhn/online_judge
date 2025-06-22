<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

$contest_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$contest_id) {
    die("Contest không hợp lệ.");
}

// Lấy thông tin cơ bản của contest - Dòng này đã được sửa lỗi cú pháp SQL
$stmt = $pdo->prepare("SELECT contests.*, NOW() as `current_time` FROM contests WHERE id = ?");
$stmt->execute([$contest_id]);
$contest = $stmt->fetch();

if (!$contest) {
    die("Contest không tồn tại.");
}

// Xác định trạng thái contest
$start_time_ts = strtotime($contest['start_time']);
$end_time_ts = strtotime($contest['end_time']);
$current_time_ts = strtotime($contest['current_time']);

$status = 'Đã kết thúc';
if ($current_time_ts < $start_time_ts) {
    $status = 'Sắp diễn ra';
} elseif ($current_time_ts >= $start_time_ts && $current_time_ts <= $end_time_ts) {
    $status = 'Đang diễn ra';
}
?>

<div class="row">

  <div class="col-lg-8">
    <h1 class="mb-3"><?php echo htmlspecialchars($contest['name']); ?></h1>
    <ul class="list-unstyled mb-3">
      <li><strong>Trạng thái:</strong> <?php echo $status; ?></li>
      <li><strong>Thời gian:</strong> <?php echo date('H:i, d/m/Y', $start_time_ts); ?> -
        <?php echo date('H:i, d/m/Y', $end_time_ts); ?></li>
    </ul>

    <hr class="mb-4">

    <?php if ($status == 'Sắp diễn ra'): ?>
    <div class="alert alert-info">Kỳ thi chưa bắt đầu. Vui lòng quay lại sau.</div>
    <?php else: ?>
    <h2 class="mb-3">Các bài tập</h2>
    <div class="list-group">
      <?php
                $problem_stmt = $pdo->prepare("
                    SELECT p.id, p.title, p.slug FROM problems p
                    JOIN contest_problems cp ON p.id = cp.problem_id
                    WHERE cp.contest_id = ?
                ");
                $problem_stmt->execute([$contest_id]);
                while ($problem = $problem_stmt->fetch()):
                ?>
      <a href="problem.php?slug=<?php echo $problem['slug']; ?>&contest_id=<?php echo $contest_id; ?>"
        class="list-group-item list-group-item-action">
        <?php echo htmlspecialchars($problem['title']); ?>
      </a>
      <?php endwhile; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-4">
    <h2 class="mb-3">Bảng xếp hạng</h2>
    <?php if ($status != 'Sắp diễn ra'): 
            $scoreboard_stmt = $pdo->prepare("
                SELECT
                    u.username,
                    COUNT(DISTINCT s.problem_id) AS score,
                    MAX(s.submitted_at) AS last_accepted_time
                FROM submissions s
                JOIN users u ON s.user_id = u.id
                WHERE
                    s.problem_id IN (SELECT problem_id FROM contest_problems WHERE contest_id = :contest_id)
                    AND s.status = 'Accepted'
                    AND s.submitted_at BETWEEN :start_time AND :end_time
                GROUP BY s.user_id, u.username
                ORDER BY score DESC, last_accepted_time ASC
            ");
            $scoreboard_stmt->execute([
                ':contest_id' => $contest_id,
                ':start_time' => $contest['start_time'],
                ':end_time' => $contest['end_time']
            ]);
            $scoreboard = $scoreboard_stmt->fetchAll();
        ?>
    <div class="table-responsive">
      <table class="table table-striped table-sm border">
        <thead class="table-dark">
          <tr>
            <th scope="col" class="text-center">#</th>
            <th scope="col">Người dùng</th>
            <th scope="col" class="text-center">Điểm</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($scoreboard)): ?>
          <tr>
            <td colspan="3" class="text-center fst-italic py-3">Chưa có ai nộp bài thành công.</td>
          </tr>
          <?php else: ?>
          <?php foreach($scoreboard as $rank => $row): ?>
          <tr>
            <th scope="row" class="text-center"><?php echo $rank + 1; ?></th>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td class="text-center fw-bold"><?php echo $row['score']; ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="alert alert-light">Bảng xếp hạng sẽ hiển thị khi kỳ thi bắt đầu.</div>
    <?php endif; ?>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>