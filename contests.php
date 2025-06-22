<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

// 1. CÁC BIẾN SỐ VÀ TÍNH TOÁN
$items_per_page = 10; // 10 kỳ thi mỗi trang
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) { $current_page = 1; }

$count_stmt = $pdo->query("SELECT COUNT(*) FROM contests");
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

if ($current_page > $total_pages && $total_pages > 0) { $current_page = $total_pages; }
$offset = ($current_page - 1) * $items_per_page;


// 2. CẬP NHẬT SQL VỚI LIMIT & OFFSET
$sql = "SELECT id, name, start_time, end_time,
        CASE
            WHEN NOW() < start_time THEN 'Sắp diễn ra'
            WHEN NOW() BETWEEN start_time AND end_time THEN 'Đang diễn ra'
            ELSE 'Đã kết thúc'
        END AS status
        FROM contests
        ORDER BY start_time DESC
        LIMIT :limit OFFSET :offset";
        
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$contests = $stmt->fetchAll();
?>

<h1 class="mb-4">Danh sách các Kỳ thi</h1>

<div class="table-responsive">
  <table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th>Tên Kỳ thi</th>
        <th>Bắt đầu</th>
        <th>Kết thúc</th>
        <th class="text-center">Trạng thái</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($contests)): ?>
      <tr>
        <td colspan="4" class="text-center">Chưa có kỳ thi nào.</td>
      </tr>
      <?php else: ?>
      <?php foreach ($contests as $contest):
                    $status_class = 'bg-secondary';
                    if ($contest['status'] == 'Đang diễn ra') $status_class = 'bg-success';
                    elseif ($contest['status'] == 'Sắp diễn ra') $status_class = 'bg-info';
                ?>
      <tr>
        <td>
          <a class="text-decoration-none fw-bold" href="contest_view.php?id=<?php echo $contest['id']; ?>">
            <?php echo htmlspecialchars($contest['name']); ?>
          </a>
        </td>
        <td><?php echo date('H:i, d-m-Y', strtotime($contest['start_time'])); ?></td>
        <td><?php echo date('H:i, d-m-Y', strtotime($contest['end_time'])); ?></td>
        <td class="text-center">
          <span class="badge <?php echo $status_class; ?>">
            <?php echo $contest['status']; ?>
          </span>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($total_pages > 1): ?>
<nav aria-label="Page navigation">
  <ul class="pagination justify-content-center">
    <li class="page-item <?php if($current_page <= 1) echo 'disabled'; ?>">
      <a class="page-link" href="?page=<?php echo $current_page - 1; ?>">Previous</a>
    </li>
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
    <li class="page-item <?php if($i == $current_page) echo 'active'; ?>">
      <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
    </li>
    <?php endfor; ?>
    <li class="page-item <?php if($current_page >= $total_pages) echo 'disabled'; ?>">
      <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Next</a>
    </li>
  </ul>
</nav>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>