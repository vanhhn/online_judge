<?php
// logout.php
session_start(); // Bắt đầu session để có thể hủy nó
session_unset(); // Xóa tất cả các biến session
session_destroy(); // Hủy session
header('Location: index.php'); // Chuyển về trang chủ
exit();
?>