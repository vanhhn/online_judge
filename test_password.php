<?php
// test_password.php

$password_input = 'admin123';
$hash_from_db = '$2y$10$fW.j2sI4UOT0K/C2uJ5g8.yL4T5vX.kP/d/PzZqN.Z7M.tJ4iZJd2';

echo "<h3>Kiểm tra chức năng password_verify</h3>";
echo "Mật khẩu nhập vào: " . $password_input . "<br>";
echo "Chuỗi hash từ DB: " . $hash_from_db . "<br><br>";

if (password_verify($password_input, $hash_from_db)) {
    echo "<h1 style='color: green;'>THÀNH CÔNG: Mật khẩu và hash khớp nhau!</h1>";
    echo "Điều này có nghĩa là hàm password_verify() hoạt động đúng. Vấn đề có thể do một ký tự vô hình nào đó trong file login.php.";
} else {
    echo "<h1 style='color: red;'>THẤT BẠI: Mật khẩu và hash KHÔNG khớp!</h1>";
    echo "Điều này rất bất thường và gần như chắc chắn là do có lỗi trong phiên bản PHP của bạn. Bạn nên cân nhắc cập nhật XAMPP.";
}

echo "<hr>";

echo "<h3>Tạo hash mới trên chính máy của bạn</h3>";
$new_hash = password_hash($password_input, PASSWORD_DEFAULT);
echo "Nếu bạn chạy lại file này nhiều lần, chuỗi hash mới này sẽ thay đổi, nhưng nó vẫn sẽ khớp với 'admin123'.<br>";
echo "<b>Hash mới được tạo:</b> " . $new_hash;
?>