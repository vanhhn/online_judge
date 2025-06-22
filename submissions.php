<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

// --- BƯỚC 1: CÁC BIẾN SỐ VÀ TÍNH TOÁN CHO PHÂN TRANG ---

// 1.1. Đặt số lượng mục hiển thị trên mỗi trang
$items_per_page = 15;

// 1.2. Lấy số trang hiện tại từ URL, nếu không có thì mặc định là trang 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
// Đảm bảo số trang luôn là số dương
if ($current_page < 1) {
    $current_page = 1;
}

// 1.3. Đếm tổng số lượng submissions trong database
$count_stmt = $pdo->query("SELECT COUNT(*) FROM submissions");
$total_items = $count_stmt->fetchColumn();

// 1.4. Tính tổng số trang cần có
$total_pages = ceil($total_items / $items_per_page);
// Đảm bảo trang hiện tại không lớn hơn tổng số trang
if ($current_page > $total_pages && $total_pages > 0) {
    $current_page = $total_pages;
}

// 1.5. Tính OFFSET (vị trí bắt đầu lấy dữ liệu trong query)
$offset = ($current_page - 1) * $items_per_page;


// --- BƯỚC 2: CẬP NHẬT CÂU LỆNH SQL VỚI LIMIT VÀ OFFSET ---

$sql = "SELECT
            s.id, s.status, s.language, s.submitted_at,
            p.title AS problem_title,
            u.username
        FROM
            submissions AS s
        JOIN
            problems AS p ON s.problem_id = p.id
        JOIN
            users AS u ON s.user_id = u.id
        ORDER BY
            s.submitted_at DESC
        LIMIT :limit OFFSET :offset"; // Thêm LIMIT và OFFSET vào cuối

$stmt = $pdo->prepare($sql);
// Gán giá trị vào các tham số trong câu lệnh SQL một cách an toàn
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$submissions = $stmt->fetchAll();

?>

<h1 class="mb-4">Lịch sử nộp bài</h1>

<div class="table-responsive">
  <table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th scope="col">ID</th>
        <th scope="col">Thời gian</th>
        <th scope="col">Người nộp</th>
        <th scope="col">Bài tập</th>
        <th scope="col">Ngôn ngữ</th>
        <th scope="col" class="text-center">Trạng thái</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($submissions)): ?>
      <tr>
        <td colspan="6" class="text-center">Chưa có bài nộp nào.</td>
      </tr>
      <?php else: ?>
      <?php foreach ($submissions as $row):
                    // Logic chọn màu cho status
                    $status_class = 'bg-secondary';
                    if ($row['status'] == 'Accepted') $status_class = 'bg-success';
                    elseif ($row['status'] == 'Wrong Answer') $status_class = 'bg-danger';
                    elseif ($row['status'] == 'Compilation Error') $status_class = 'bg-warning text-dark';
                    elseif ($row['status'] == 'Time Limit Exceeded') $status_class = 'bg-info text-dark';
                    elseif ($row['status'] == 'Runtime Error') $status_class = 'bg-dark';
                ?>
      <tr>
        <td>
          <a class="fw-bold text-decoration-none" href="submission_details.php?id=<?php echo $row['id']; ?>">
            #<?php echo $row['id']; ?>
          </a>
        </td>
        <td><?php echo date('H:i:s d/m/Y', strtotime($row['submitted_at'])); ?></td>
        <td><?php echo htmlspecialchars($row['username']); ?></td>
        <td><?php echo htmlspecialchars($row['problem_title']); ?></td>
        <td><?php echo htmlspecialchars($row['language']); ?></td>
        <td class="text-center">
          <span class="badge <?php echo $status_class; ?>">
            <?php echo htmlspecialchars($row['status']); ?>
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