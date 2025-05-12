<?php
$host = "localhost";
$port = "3306"; // varsayılan MySQL portu
$dbname = "dormitory_database";
$username = "root";
$password = "2210"; // Workbench bağlantı şifren

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Veritabanı bağlantısı başarılı!";
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>

