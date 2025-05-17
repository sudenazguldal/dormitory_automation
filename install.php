<?php
// install.php

// 1) Sadece parametreler
require_once __DIR__ . '/config/db_config.php';

// 2) Veritabanı seçmeden mysqli ile bağlan
$mysqli = new mysqli($host, $username, $password, '', $port);
if ($mysqli->connect_errno) {
    die('MySQL bağlantı hatası: ' . $mysqli->connect_error);
}

// 3) DB yoksa yarat ve seç
if (! $mysqli->select_db($dbname)) {
    $mysqli->query(
      "CREATE DATABASE IF NOT EXISTS `$dbname`
       DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci"
    ) or die('DB oluşturma hatası: ' . $mysqli->error);
    $mysqli->select_db($dbname);
}

// 4) Tekrar çalışmasın
if (file_exists(__DIR__ . '/.installed')) {
    exit('⚙️ Zaten kurulmuş.');
}

// 5) Dump’ı oku ve import et
$sql = file_get_contents(__DIR__ . '/database.sql')
    or die('database.sql bulunamadı!');
if (! $mysqli->multi_query($sql)) {
    die('Import hatası: ' . $mysqli->error);
}
do {
    if ($res = $mysqli->store_result()) $res->free();
} while ($mysqli->more_results() && $mysqli->next_result());

// 6) Bayrak dosyasını yaz
file_put_contents(__DIR__ . '/.installed', date('c'))
    or die('⚠️ .installed yazılamadı!');

// 7) Başarı
echo "✅ Veritabanı kurulumu tamamlandı!";
