<?php
session_start();
require_once "../config/db.php";

// Yetki kontrolü
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "students affair") {
    header("Location: ../public/login.php");
    exit;
}

// 4. Aktif (beklemedeki) faturaları view’dan çek
$sql    = "SELECT * FROM v_active_invoices";
$stmt   = $pdo->query($sql);
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Beklemedeki Faturalar</title>
  <
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <style>
   
  html, body {
  height: 100%;  /* Tarayıcı penceresinin tamamını kapsasın */
  margin: 0; /* Varsayılan boşlukları kaldır */
  padding: 0; /* Varsayılan boşlukları kaldır */
  font-family: 'Segoe UI', sans-serif;  
  background: #e6f0ff;  /* Sayfanın genel arka plan rengi */
}

body {
  display: flex;   /* Body’yi bir Flex konteyneri yapar */
  min-height: 100vh;  /* Görünür pencere yüksekliğinin tamamı kadar (en az) */
  overflow: hidden; /* Taşan içeriği gizler; kaydırma çubuklarını gizler */
}



main {
  flex: 1; /* Body içindeki yan yana duran sidebar + main’da main, kalan tüm alanı kaplasın */            
  display: flex;/* main’i de Flex konteynerine çevirir */
  flex-direction: column; /* İçindeki çocuk öğeleri (h2, table) dikey dizilir */
  align-items: center;  /* Bu dikey sütunda çocukları yatayda ortaya alır */
  padding: 30px;/* main’in kenarlarında iç boşluk bırakır */
}



main h2 {
  color: #0a2342; /* Başlık metni koyu laciverte yakın */
  text-align: center; /* Kendisi bulunduğu yatay satırda ortaya hizalar */
  margin-bottom: 20px;/* Altında 20px boşluk bırakır */
}



main table {
  width: 80%;/* Ana alanın %80 genişliğini kaplar */
  margin: 0 auto 30px;  /* Üst 0px, yatay otomatik (ortala), alt 30px boşluk */
  border-collapse: collapse; /* Hücre kenarlıkları birleşik görünür */
  background: #fff;/* Tablo arka planı beyaz */
  box-shadow: 0 0 5px rgba(0,0,0,0.1); /* Hafif gölge efekti */
}

main th, main td {
  border: 1px solid #ddd; /* İnce gri kenarlık */
  padding: 8px 12px; /* Hücre içi boşluk: üst-alt 8px, sağ-sol 12px */
  text-align: left;/* Hücre içeriğini sola hizalar */
}

main th {
  background-color: #123060; /* Başlık hücrelerinin koyu mavi arka planı */
  font-weight: 600;  /* Kalınlığı biraz azaltılmış bold */
  color: white;  /* Başlık metni beyaz */
}



.btn-pay {
  display: inline-block; /* Satır içi blok, padding ve margin alır */
  padding: 4px 8px;/* Butonun iç boşluğu: üst-alt 4px, sağ-sol 8px */
  background: #007bff; /* Mavi buton zemin rengi */
  color: #fff; /* Buton metni beyaz */
  text-decoration: none; /* Link altı çizgiyi kaldır */
  border-radius: 4px; /* Hafif yuvarlatılmış köşeler */
}
.btn-pay:hover {
  background: #0056b3; /* Üzerine gelince daha koyu mavi */
}

  </style>
</head>
<body>
 <?php include "../includes/sidebar.php"; ?>

  <!-- Ana içerik -->
  <main>
    <h2>Beklemedeki Faturalar</h2>

    <?php if (empty($invoices)): ?>
      <p>Şu anda beklemede fatura bulunmuyor.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Fatura ID</th>
            <th>Öğrenci</th>
            <th>Tutar (₺)</th>
            <th>Kesim Tarihi</th>
            <th>Son Ödeme</th>
            <th>Durum</th>
            <th>İşlem</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($invoices as $inv): ?>
            <tr>
              <td><?= htmlspecialchars($inv['invoice_id']) ?></td>
              <td><?= htmlspecialchars($inv['student_name']) ?></td>
              <td><?= number_format($inv['total_amount'], 2, ',', '.') ?></td>
              <td><?= htmlspecialchars($inv['issue_date']) ?></td>
              <td><?= htmlspecialchars($inv['due_date']) ?></td>
              <td><?= htmlspecialchars($inv['status_name']) ?></td>
              <td>
                <a
                  href="pay_invoice.php?id=<?= urlencode($inv['invoice_id']) ?>"
                  class="btn-pay"
                >Öde</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </main>
</body>
</html>
