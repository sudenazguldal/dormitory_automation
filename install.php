<?php
require_once __DIR__ . '/config/db.php';
$mysqli = new mysqli($host, $username, $password, '', $port);
if ($mysqli->connect_errno) {
    die('MySQL bağlantı hatası: ' . $mysqli->connect_error);
}

// 1) Database yoksa oluştur ve seç:
if (! $mysqli->select_db($dbname)) {
    $mysqli->query(
      "CREATE DATABASE IF NOT EXISTS `$dbname`
       DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci"
    ) or die($mysqli->error);
    $mysqli->select_db($dbname);
}

// 2) SQL dosyasını oku
$sql = file_get_contents(__DIR__ . '/database.sql')
    or die('database.sql bulunamadı!');

// 3) Multi‐query ile çalıştır
if (! $mysqli->multi_query($sql)) {
    die('Import hatası: ' . $mysqli->error);
}
// Sonuçları temizle
do {
    if ($res = $mysqli->store_result()) {
        $res->free();
    }
} while ($mysqli->more_results() && $mysqli->next_result());

echo "✅ Import tamamlandı; mevcut veriler silinmedi.";

