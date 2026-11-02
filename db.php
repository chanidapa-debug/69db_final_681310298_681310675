<?php
$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'ecommerce_db';
$user = getenv('DB_USER') ?: 'ecommerce_user';
$pass = getenv('DB_PASS') ?: 'ecommerce_pass';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit("เชื่อมต่อฐานข้อมูลไม่สำเร็จ กรุณาตรวจสอบ Docker และการตั้งค่า DB");
}
