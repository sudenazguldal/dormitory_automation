<?php
// config/db.php
require_once __DIR__ . '/db_config.php';

try {
    // Veritabanı zaten var ise PDO burada bağlanacak
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
