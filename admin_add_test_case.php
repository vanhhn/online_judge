<?php
session_start();
require_once 'includes/db.php';

// Bảo vệ
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: index.php');
    exit("Bạn không có quyền truy cập.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $problem_id = isset($_POST['problem_id']) ? (int)$_POST['problem_id'] : 0;
    $input_data = $_POST['input_data'];
    $output_data = $_POST['output_data'];

    if ($problem_id > 0 && !empty($input_data) && !empty($output_data)) {
        $stmt = $pdo->prepare("INSERT INTO test_cases (problem_id, input, output) VALUES (?, ?, ?)");
        $stmt->execute([$problem_id, $input_data, $output_data]);
    }

    // Chuyển hướng người dùng về lại trang sửa bài tập
    header('Location: admin_edit_problem.php?id=' . $problem_id);
    exit();
} else {
    // Nếu không phải POST request, chuyển về trang chủ
    header('Location: index.php');
    exit();
}
?>