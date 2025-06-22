<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

// 1. CÁC BIẾN SỐ VÀ TÍNH TOÁN
$items_per_page = 20; // 20 bài tập mỗi trang
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) { $current_page = 1; }

$count_stmt = $pdo->query("SELECT COUNT(*) FROM problems");
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

if ($current_page > $total_pages && $total_pages > 0) { $current_page = $total_pages; }
$offset = ($current_page - 1) * $items_per_page;


// 2. CẬP NHẬT SQL VỚI LIMIT & OFFSET
$stmt = $pdo->prepare("SELECT id, title, slug FROM problems ORDER BY id ASC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$problems = $stmt->fetchAll();
?>

<h1 class="mb-4">Danh sách bài tập</h1>

<div class="table-responsive">
  <table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th scope="col" style="width: 10%;">ID</th>
        <th scope="col">Tên bài</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($problems)): ?>
      <tr>
        <td colspan="2" class="text-center">Chưa có bài tập nào.</td>
      </tr>
      <?php else: ?>
      <?php foreach ($problems as $row): ?>
      <tr>
        <td><?php echo $row['id']; ?></td>
        <td>
          <a class="text-decoration-none fw-bold" href="problem.php?slug=<?php echo htmlspecialchars($row['slug']); ?>">
            <?php echo htmlspecialchars($row['title']); ?>
          </a>
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