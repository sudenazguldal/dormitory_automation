<?php


session_start();
session_unset();       // Tüm oturum değişkenlerini temizler
session_destroy();     // Oturumu tamamen sonlandırır
header("Location: login.php"); // login.php'ye yönlendir
exit;
