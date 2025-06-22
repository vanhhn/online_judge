<?php
// includes/db.php

$host = 'localhost:3307';
$dbname = 'online_judge_db'; // Tên DB bạn vừa tạo
$user = 'root'; // User mặc định của XAMPP
$pass = ''; // Mật khẩu mặc định của XAMPP là rỗng
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>