<?php
// install.php

// 1) Veritabanı ayarlarını yükle
require_once __DIR__ . '/config/db.php';

// 2) MySQL’e mysqli ile bağlan (DB adı boş çünkü CREATE DATABASE de çalışacak)
$mysqli = new mysqli($host, $username, $password, '', $port);
if ($mysqli->connect_errno) {
    die('❌ MySQL bağlantı hatası: ' . $mysqli->connect_error);
}

// 3) Veritabanı yoksa oluştur ve seç
if (! $mysqli->select_db($dbname)) {
    if (! $mysqli->query(
        "CREATE DATABASE IF NOT EXISTS `$dbname`
         DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci"
    )) {
        die('❌ Veritabanı oluşturulamadı: ' . $mysqli->error);
    }
    $mysqli->select_db($dbname);
}

// 4) Bir kere çalışsın diye kontrol
if (file_exists(__DIR__ . '/.installed')) {
    echo '✅ Zaten kurulmuş.';
    return;
}

// 5) SQL dump’ınızı oku
$sql = file_get_contents(__DIR__ . '/database.sql');
if ($sql === false) {
    die('❌ database.sql dosyası bulunamadı!');
}

// 6) hepsini çalıştır
if (! $mysqli->multi_query($sql)) {
    die('❌ SQL import hatası: ' . $mysqli->error);
}
do {
    if ($r = $mysqli->store_result()) {
        $r->free();
    }
} while ($mysqli->more_results() && $mysqli->next_result());

// 7) bayrak dosyasını koy
file_put_contents(__DIR__ . '/.installed', date('c'));

// 8) mesaj
echo "✅ Veritabanı kurulumu tamamlandı!";
