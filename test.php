<?php
$host = "localhost";
$port = "3306";
$dbname = "dormitory_database";
$username = "root";  // veya phpuser
$password = "2210";  // kendi şifren

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Veritabanına başarıyla bağlanıldı!";
} catch (PDOException $e) {
    echo "❌ Veritabanı bağlantı hatası: " . $e->getMessage();
}
?>
