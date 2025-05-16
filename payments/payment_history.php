<?php
session_start();
require_once "../config/db.php";

// Yetki kontrolü
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "students affair") {
    header("Location: ../public/login.php");
    exit;
}

// VIEW’dan veriyi çek
$stmt    = $pdo->query("SELECT * FROM v_monthly_payments");
$summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Aylık Ödeme Özeti</title>
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <style>
    body { display:flex; 
        margin:0; 
        min-height:100vh; 
        background:#e6f0ff;
    font-family: 'Segoe UI', sans-serif;  
 }
    main { flex:1; padding:30px; }
    h2 { text-align:center; color:#0a2342; margin-bottom:20px; }
    table { width:80%; margin:0 auto; border-collapse:collapse; background:#fff; box-shadow:0 0 5px rgba(0,0,0,0.1); }
    th, td { padding:10px 12px; border:1px solid #ddd; text-align:center; }
    th { background:#123060; color:#fff; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>
  <main>
    <h2>Aylık Ödeme Özeti</h2>
    <?php if (empty($summary)): ?>
      <p style="text-align:center;">Henüz hiç ödeme yok.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Ay</th>
            <th>Toplam Tahsilat (₺)</th>
            <th>Ödeme Sayısı</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($summary as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['month']) ?></td>
            <td><?= number_format($row['total_received'], 2, ',', '.') ?></td>
            <td><?= htmlspecialchars($row['payment_count']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </main>
</body>
</html>
