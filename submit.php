<?php
// submit.php (Phiên bản hoàn chỉnh)
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Bạn phải đăng nhập để nộp bài. <a href='login.php'>Đăng nhập</a>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    require 'includes/db.php';

    // Lấy dữ liệu từ form
    $problem_id = $_POST['problem_id'];
    $language = $_POST['language'];
    $source_code = $_POST['source_code'];
    $user_id = $_SESSION['user_id'];
    
    // Lấy contest_id từ form (nếu không có sẽ là 0)
    $contest_id = isset($_POST['contest_id']) ? (int)$_POST['contest_id'] : 0;

    // ================= LOGIC KIỂM TRA CONTEST =================
    if ($contest_id > 0) {
        // Nếu là bài nộp cho contest, tiến hành kiểm tra thời gian
        $stmt = $pdo->prepare("SELECT start_time, end_time FROM contests WHERE id = ?");
        $stmt->execute([$contest_id]);
        $contest = $stmt->fetch();

        if (!$contest) {
            die("Lỗi: Kỳ thi không hợp lệ.");
        }

        $start_time = strtotime($contest['start_time']);
        $end_time = strtotime($contest['end_time']);
        $current_time = time(); // Lấy thời gian hiện tại của server

        if ($current_time < $start_time) {
            die("Lỗi: Kỳ thi chưa bắt đầu. Không thể nộp bài.");
        }

        if ($current_time > $end_time) {
            die("Lỗi: Kỳ thi đã kết thúc. Không thể nộp bài.");
        }
    }
    // ================= KẾT THÚC LOGIC KIỂM TRA =================


    // Nếu mọi thứ hợp lệ, tiếp tục lưu submission và gọi judge
    try {
        // 1. Lưu lần nộp bài vào CSDL
        $stmt = $pdo->prepare(
            "INSERT INTO submissions (user_id, problem_id, language, source_code, status) VALUES (?, ?, ?, ?, 'Pending')"
        );
        $stmt->execute([$user_id, $problem_id, $language, $source_code]);

        // 2. Lấy ID của lần nộp bài vừa tạo
        $submission_id = $pdo->lastInsertId();

        // 3. Gọi kịch bản chấm bài ở chế độ nền
        $command = "php " . __DIR__ . "/judge/judge_script.php {$submission_id}";

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
             pclose(popen("start /B " . $command, "r"));
        } else {
            shell_exec($command . " > /dev/null 2>&1 &");
        }
        
        // 4. Chuyển hướng đến trang chi tiết submission
        header("Location: submission_details.php?id=" . $submission_id);
        exit();

    } catch (\PDOException $e) {
        die("Lỗi CSDL: " . $e->getMessage());
    }
} else {
    // Nếu không phải POST request, chuyển về trang chủ
    header("Location: index.php");
    exit();
}
?>