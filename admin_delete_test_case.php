<?php
session_start();
require_once 'includes/db.php';

// Bảo vệ
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: index.php');
    exit("Bạn không có quyền truy cập.");
}

$test_case_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$problem_id = isset($_GET['problem_id']) ? (int)$_GET['problem_id'] : 0; // Cần problem_id để chuyển hướng lại

if ($test_case_id > 0) {
    $stmt = $pdo->prepare("DELETE FROM test_cases WHERE id = ?");
    $stmt->execute([$test_case_id]);
}

// Chuyển hướng về lại trang sửa bài tập
if ($problem_id > 0) {
    header('Location: admin_edit_problem.php?id=' . $problem_id);
} else {
    header('Location: admin_problems.php'); // Nếu không có problem_id thì về trang danh sách
}
exit();
?>