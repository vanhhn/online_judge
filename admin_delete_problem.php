<?php
session_start();
require_once 'includes/db.php';

// Bảo vệ
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: index.php');
    exit("Bạn không có quyền truy cập.");
}

$problem_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($problem_id > 0) {
    // Nhờ có ON DELETE CASCADE trong CSDL, các test case và submission liên quan cũng sẽ tự động bị xóa.
    $stmt = $pdo->prepare("DELETE FROM problems WHERE id = ?");
    $stmt->execute([$problem_id]);
}

// Chuyển hướng về trang quản lý
header('Location: admin_problems.php');
exit();
?>