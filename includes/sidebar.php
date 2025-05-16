<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$role = $_SESSION["role"] ?? "";
?>

<div class="sidebar">
    <h2>Ocean Breeze</h2>

    <?php if ($role === "security"): ?>
        <a href="../enter_leave/enter.php" class="menu-button">Giriş / Çıkış</a>
        <a href="../permission/permission_create.php" class="menu-button">İzin Alma</a>
    <?php elseif ($role === "students affair"): ?>
        <a href="../students/list_it.php" class="menu-button">Öğrenci Listesi</a>
        <a href="../students/student_register.php" class="menu-button">Öğrenci Kaydı</a>
        <a href="../permission/permission_approve.php" class="menu-button">İzin Onayla</a>
        <a href="../permission/permission_exceed.php" class="menu-button">İzin Takip</a>
        <a href="../payments/list_payment.php" class="menu-button">Bekleyen Ödemeler</a>
        <a href="../payments/payment_history.php" class="menu-button">Aylık Özet</a>
        
    <?php endif; ?>

    <a href="../public/logout.php" class="menu-button">Çıkış Yap</a>
    
</div>
