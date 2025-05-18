<?php
session_start();
require_once "../config/db.php";

// Yetki kontrolü
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "students affair") {
    header("Location: ../public/login.php");
    exit;
}

// Gelen ID’yi alıp validate eder
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die('Geçersiz fatura ID.');
}
$invoiceId = (int)$_GET['id'];

//  View’dan invoice detaylarını çekee
$sql = "SELECT * FROM v_invoice_details WHERE invoice_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$invoiceId]);
$inv = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$inv) {
    die('Fatura bulunamadı.');
}

// Ödeme formu işleme
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amt = (float)$_POST['amount'];
    $method = (int)$_POST['method_id'];

    if ($amt <= 0) {
        $errors[] = "Geçerli bir tutar girin.";
    } elseif ($amt > $inv['remaining']) {
        $errors[] = "Kalan {$inv['remaining']} ₺’den fazla ödeme yapamazsınız.";
    }

    if (empty($errors)) {
        $pdo->beginTransaction();
        $pdo->prepare("INSERT INTO payments 
            (student_id,amount,payment_date,status_id,method_id)
         VALUES (?,?,?,?,?)")
         ->execute([$inv['student_id'], $amt, date('Y-m-d H:i:s'), 2, $method]);
        $pid = $pdo->lastInsertId();
        $pdo->prepare("INSERT INTO payment_allocations 
            (payment_id,invoice_id,alloc_amount)
         VALUES (?,?,?)")
         ->execute([$pid, $invoiceId, $amt]);
        $pdo->commit();
        header('Location: list_payment.php');
        exit;
    }
}

// Ödeme yöntemlerini çek
$methods = $pdo->query("SELECT method_id, method_name FROM payment_method")
               ->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Fatura Ödeme #<?= $inv['invoice_id'] ?></title>
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <style>
     html, body {
  height: 100%; /* Tarayıcı penceresinin tamamını kapsasın */
  margin: 0; /* Varsayılan boşlukları kaldır */
  padding: 0;  /* Varsayılan boşlukları kaldır */
  font-family: 'Segoe UI', sans-serif;  
  background: #e6f0ff; /* Sayfanın genel arka plan rengi */
}

body {
  display: flex;/* Body’yi bir Flex konteyneri yapar */
  min-height: 100vh; /* Görünür pencere yüksekliğinin tamamı kadar (en az) */
  overflow: hidden; /* Taşan içeriği gizler; kaydırma çubuklarını gizler */
}



main {
  flex: 1;/* Body içindeki yan yana duran sidebar + main’da   main, kalan tüm alanı kaplasın */                             
  display: flex; /* main’i de Flex konteynerine çevirir */
  flex-direction: column; /* İçindeki çocuk öğeleri (h2, table) dikey dizilir */
  align-items: center; /* Bu dikey sütunda çocukları yatayda ortaya alır */
  padding: 30px; /* main’in kenarlarında iç boşluk bırakır */
}



main h2 {
  color: #0a2342; /* Başlık metni koyu laciverte yakın */
  text-align: center;  /* Kendisi bulunduğu yatay satırda ortaya hizalar */
  margin-bottom: 20px; /* Altında 20px boşluk bırakır */
}



main table {
  width: 250px;  /* Ana alanın %80 genişliğini kaplar */
  margin: 30px auto 30px;  /* Üst 0px, yatay otomatik (ortala), alt 30px boşluk */
  border-collapse: collapse;  /* Hücre kenarlıkları birleşik görünür */
  background: #fff;    /* Tablo arka planı beyaz */
  box-shadow: 0 0 5px rgba(0,0,0,0.1); /* Hafif gölge efekti */
}

main th, main td {
  border: 1px solid #ddd;  /* İnce gri kenarlık */
  padding: 12px 12px;  /* Hücre içi boşluk: üst-alt 8px, sağ-sol 12px */
  text-align: left;  /* Hücre içeriğini sola hizalar */
}

main th {
  background-color: #123060; /* Başlık hücrelerinin koyu mavi arka planı */
  font-weight: 600;  /* Kalınlığı biraz azaltılmış bold */
  color: white;   /* Başlık metni beyaz */
}



.btn-pay {
  display: block;
  margin: 0 auto;
  justify-content: center;  /* Yatayda ortala */
  align-items: center; /* Dikeyde ortala */
  align-items: center;          
  padding: 10px 10px; /* Butonun iç boşluğu: üst-alt 4px, sağ-sol 8px */
  background: #007bff;  /* Mavi buton zemin rengi */
  color: #fff;   /* Buton metni beyaz */
  text-decoration: none; /* Link altı çizgiyi kaldır */
  border-radius: 4px;   /* Hafif yuvarlatılmış köşeler */
}
.btn-pay:hover {
  background: #0056b3; /* Üzerine gelince daha koyu mavi */
}
  </style>
</head>
<body>
 
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

   <main>
    <h2>Fatura Ödeme (#<?= $inv['invoice_id'] ?>)</h2>

    <?php if($errors): ?>
      <div class="errors">
        <ul>
          <?php foreach($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST">
      <table class="invoice-table">
        <tr>
          <th>Öğrenci</th>
          <td><?= htmlspecialchars($inv['student_name']) ?></td>
        </tr>
        <tr>
          <th>Toplam Tutar (₺)</th>
          <td><?= number_format($inv['total_amount'],2,',','.') ?></td>
        </tr>
        <tr>
          <th>Ödenen (₺)</th>
          <td><?= number_format($inv['paid_so_far'],2,',','.') ?></td>
        </tr>
        <tr>
          <th>Kalan (₺)</th>
          <td><?= number_format($inv['remaining'],2,',','.') ?></td>
        </tr>
        <tr>
          <th>Ödenecek Tutar (₺)</th>
          <td>
            <input 
              type="number" 
              name="amount" 
              step="0.01" 
              value="<?= htmlspecialchars($inv['remaining']) ?>" 
              required
            >
          </td>
        </tr>
        <tr>
          <th>Ödeme Yöntemi</th>
          <td>
            <select name="method_id">
              <?php foreach($methods as $m): ?>
                <option value="<?= $m['method_id'] ?>">
                  <?= htmlspecialchars($m['method_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="actions">
            <button type="submit" class="btn-pay">Öde</button>
          </td>
        </tr>
      </table>
    </form>
  </main>
</body>
</html>
