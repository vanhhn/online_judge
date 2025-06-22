<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

$submission_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($submission_id === 0) {
    die("ID không hợp lệ.");
}

$sql = "SELECT 
            s.id, s.status, s.language, s.submitted_at, s.source_code, s.error_details, 
            p.title AS problem_title, 
            u.username
        FROM submissions AS s
        JOIN problems AS p ON s.problem_id = p.id
        JOIN users AS u ON s.user_id = u.id
        WHERE s.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$submission_id]);
$submission = $stmt->fetch();

if (!$submission) {
    die("Không tìm thấy lần nộp bài này.");
}

// Logic chọn màu cho status badge
$status_class = 'bg-secondary'; // Màu mặc định cho Pending
if ($submission['status'] == 'Accepted') $status_class = 'bg-success';
elseif ($submission['status'] == 'Wrong Answer') $status_class = 'bg-danger';
elseif ($submission['status'] == 'Compilation Error') $status_class = 'bg-warning text-dark';
elseif ($submission['status'] == 'Runtime Error') $status_class = 'bg-dark';
elseif ($submission['status'] == 'Time Limit Exceeded') $status_class = 'bg-info text-dark';
?>

<h1 class="mb-4">Chi tiết Submission #<?php echo $submission['id']; ?></h1>
<p><a href="submissions.php">&larr; Quay lại lịch sử</a></p>

<div class="card mb-4">
  <div class="card-header fw-bold">Thông tin chi tiết</div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-2"><strong>Bài tập:</strong></div>
      <div class="col-md-10"><?php echo htmlspecialchars($submission['problem_title']); ?></div>
    </div>
    <hr>
    <div class="row">
      <div class="col-md-2"><strong>Người nộp:</strong></div>
      <div class="col-md-10"><?php echo htmlspecialchars($submission['username']); ?></div>
    </div>
    <hr>
    <div class="row">
      <div class="col-md-2"><strong>Thời gian:</strong></div>
      <div class="col-md-10"><?php echo date('H:i:s, d/m/Y', strtotime($submission['submitted_at'])); ?></div>
    </div>
    <hr>
    <div class="row align-items-center">
      <div class="col-md-2"><strong>Trạng thái:</strong></div>
      <div class="col-md-10">
        <span class="badge fs-6 <?php echo $status_class; ?>">
          <?php echo htmlspecialchars($submission['status']); ?>
        </span>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($submission['error_details'])): ?>
<div class="error-details-container mt-4">
  <h2 class="h4">Chi tiết lỗi</h2>
  <pre
    class="bg-light border rounded p-3"><code><?php echo htmlspecialchars($submission['error_details']); ?></code></pre>
</div>
<?php endif; ?>

<h2 class="mt-4">Mã nguồn đã nộp</h2>
<pre
  class="bg-dark text-white border rounded p-3"><code><?php echo htmlspecialchars($submission['source_code']); ?></code></pre>

<?php require_once 'includes/footer.php'; ?>