<?php
// config/db.php
$host     = '127.0.0.1';
$port     = 3307;               // XAMPP’in MySQL portu
$dbname   = 'dormitory_database'; // Veritabanı adı
$username = 'root';
$password = '';                 // phpMyAdmin şifresizse boş bırakın

try {
    // DSN string’i
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8";
    // PDO nesnesi
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    // Bağlantı başarılıysa sessize alın
    // echo "✅ Bağlantı başarılı!";
} catch (PDOException $e) {
    // Hata varsa öldürür ve mesajı gösterir
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}


?>
