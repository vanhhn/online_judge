<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

$contest_id = isset($_GET['contest_id']) ? (int)$_GET['contest_id'] : 0;
if (!isset($_GET['slug'])) { die("Lỗi: Không tìm thấy bài tập."); }
$slug = $_GET['slug'];
$stmt = $pdo->prepare("SELECT * FROM problems WHERE slug = ?");
$stmt->execute([$slug]);
$problem = $stmt->fetch();
if (!$problem) { die("Lỗi: Bài tập không tồn tại."); }

echo "<script>document.title = " . json_encode(htmlspecialchars($problem['title'])) . ";</script>";
?>

<h1 class="mb-3"><?php echo htmlspecialchars($problem['title']); ?></h1>

<?php
if ($contest_id > 0) {
    $stmt_contest = $pdo->prepare("SELECT name FROM contests WHERE id = ?");
    $stmt_contest->execute([$contest_id]);
    $contest_name = $stmt_contest->fetchColumn();
    if ($contest_name) {
        echo '<div class="alert alert-info">Bạn đang nộp bài cho kỳ thi: <strong>' . htmlspecialchars($contest_name) . '</strong></div>';
    }
}
?>

<div class="problem-description bg-light border rounded p-4 mb-5" style="line-height: 1.7;">
  <?php echo $problem['description']; ?>
</div>

<div class="submission-form">
  <h2 class="mb-3">Nộp bài</h2>
  <form action="submit.php" method="POST" id="submission-form">
    <input type="hidden" name="problem_id" value="<?php echo htmlspecialchars($problem['id']); ?>">
    <?php if ($contest_id > 0): ?>
    <input type="hidden" name="contest_id" value="<?php echo $contest_id; ?>">
    <?php endif; ?>

    <div class="mb-3">
      <label for="language" class="form-label">Ngôn ngữ:</label>
      <select name="language" id="language" class="form-select" style="max-width: 200px;">
        <option value="cpp" selected>C++</option>
        <option value="java">Java</option>
        <option value="python">Python</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="source_code" class="form-label">Mã nguồn:</label>
      <textarea name="source_code" id="source_code" rows="20" class="form-control font-monospace"></textarea>
    </div>

    <div>
      <button type="submit" class="btn btn-success btn-lg">Nộp bài</button>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var editor = CodeMirror.fromTextArea(document.getElementById("source_code"), {
    lineNumbers: true,
    mode: "text/x-c++src",
    theme: "dracula",
    matchBrackets: true,
    indentUnit: 4,
    smartIndent: true
  });

  const languageSelect = document.getElementById('language');
  languageSelect.addEventListener('change', function() {
    let mode = 'text/plain';
    switch (this.value) {
      case 'cpp':
        mode = 'text/x-c++src';
        break;
      case 'java':
        mode = 'text/x-java';
        break;
      case 'python':
        mode = 'python';
        break;
    }
    editor.setOption("mode", mode);
  });

  const form = document.getElementById('submission-form');
  form.addEventListener('submit', function() {
    editor.save(); // Cập nhật code từ editor vào textarea trước khi submit
  });
});
</script>

<?php require_once 'includes/footer.php'; ?>