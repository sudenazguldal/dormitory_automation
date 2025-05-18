<?php

session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}

$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
if (!$student_id) exit('Geçersiz öğrenci ID.');

$st = $pdo->prepare("SELECT first_name, last_name, TC_no, stud_telNo FROM students WHERE student_id = ?");
$st->execute([$student_id]);
$student = $st->fetch(PDO::FETCH_ASSOC);
if (!$student) exit('Öğrenci bulunamadı.');

// Mevcut atama kontrolü
$asgn = $pdo->prepare("
  SELECT ra.room_assignments_id, b.bed_id, b.bed_no, r.room_id, r.room_number
  FROM room_assignments ra
  JOIN beds b   ON b.bed_id   = ra.bed_id
  JOIN rooms r  ON r.room_id  = b.room_id
  WHERE ra.student_id = ?
");
$asgn->execute([$student_id]);
$current = $asgn->fetch(PDO::FETCH_ASSOC);

// Boş yatak listesi
$bedsStmt = $pdo->query("
  SELECT b.bed_id, r.room_number, b.bed_no
  FROM beds b
  JOIN rooms r ON r.room_id = b.room_id
  LEFT JOIN room_assignments ra ON ra.bed_id = b.bed_id
  WHERE ra.bed_id IS NULL
  ORDER BY r.room_number, b.bed_no
");
$availableBeds = $bedsStmt->fetchAll(PDO::FETCH_ASSOC);

// POST işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($_POST['action'] === 'assign') {
    $bed_id = (int)$_POST['bed_id'];
    $pdo->prepare("INSERT INTO room_assignments (student_id, bed_id) VALUES (?,?)")
        ->execute([$student_id, $bed_id]);
    header("Location: room_assign.php?student_id=$student_id&ok=assign");
    exit;
  }
  if ($_POST['action'] === 'change') {
    $new_bed = (int)$_POST['bed_id'];
    $pdo->prepare("UPDATE room_assignments SET bed_id=? WHERE room_assignments_id=?")
        ->execute([$new_bed, $current['room_assignments_id']]);
    header("Location: room_assign.php?student_id=$student_id&ok=change");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Oda İşlemleri — <?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?></title>
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <style>
    
    body { display: flex; 
        font-family: 'Segoe UI', sans-serif; 
        min-height: 100vh; 
        background: #e6f0ff;
        box-sizing: border-box; 
        margin:0; padding:0; }
    
    main { flex:1; padding: 30px; }

    .card {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      padding: 44px;
      margin-bottom: 40px;
       margin: 0 auto 20px;      /* otomatik yatay ortalama */
    max-width: 1000px;  
    }
    h2 { margin-bottom: 36px; 
        color: #333;
          }
    h3 { margin-bottom: 12px; color: #555;
     }

    .student-info {
      font-size: 1.95rem;
      margin-bottom: 24px;
      color: #1d3557;
      text-align: center;
         margin-bottom: 20px;
    }
    .student-info strong {  color: #1d3557;
}

    form { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; }
    label { flex:1; display: flex; flex-direction: column; font-size: 0.9rem; color: #333; }
    select { margin-top: 6px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
    .btn {
      padding: 10px 18px;
      border: none;
      border-radius: 4px;
      font-size: 0.95rem;
      cursor: pointer;
      transition: background 0.2s;
    }
    .btn-primary {
      background: #007bff; color: #fff;
    }
    .btn-primary:hover { background: #0056b3; }
    .btn-secondary {
      background: #6c757d; color: #fff;
    }
    .btn-secondary:hover { background: #5a6268; }

    .message {
      padding: 12px;
      border-radius: 4px;
      margin-bottom: 20px;
      font-size: 0.9rem;
    }
    .msg-success { background: #e6ffed; border: 1px solid #b2f2bb; color: #2f855a; }
    .msg-info    { background: #e9ecef; border: 1px solid #ced4da; color: #495057; }

    .back-link {
      display: inline-block;
      margin-top: 12px;
      color: #007bff;
      text-decoration: none;
      font-size: 0.9rem;
    }
    .back-link:hover { text-decoration: underline; }
  </style>
</head>
<body>

  <!-- SIDEBAR -->
 <?php include "../includes/sidebar.php"; ?>

  <!-- MAIN -->
  <main>
    <h2>Oda Atama</h2>

    <!-- Öğrenci Bilgisi -->
    <div class="student-info">
      <strong><?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?></strong>
      &mdash; TC: <?= htmlspecialchars($student['TC_no']) ?>
      &mdash; Tel: <?= htmlspecialchars($student['stud_telNo'] ?? '—') ?>
    </div>

    <!-- Başarı Mesajı -->
    <?php if (!empty($_GET['ok'])): ?>
      <div class="message msg-success">
        <?= $_GET['ok'] === 'assign' ? 'Oda başarıyla atandı!' : 'Oda başarıyla değiştirildi!' ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <?php if (!$current): ?>
        <!-- İlk Atama -->
        <h3>Yeni Oda Atama</h3>
        <?php if (empty($availableBeds)): ?>
          <div class="message msg-info">Şu anda boş yatak bulunmuyor.</div>
        <?php else: ?>
          <form method="post">
            <input type="hidden" name="action" value="assign">
            <label>
              Boş Yatak Seçin:
              <select name="bed_id" required>
                <option value="">— Seçin —</option>
                <?php foreach ($availableBeds as $b): ?>
                  <option value="<?= $b['bed_id'] ?>">
                    Oda <?= htmlspecialchars($b['room_number']) ?> &ndash;
                    Yatak <?= $b['bed_no'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </label>
            <button type="submit" class="btn btn-primary">Ata</button>
          </form>
        <?php endif; ?>

      <?php else: ?>
        <!-- Mevcut Atama ve Değiştirme -->
        <h3>Mevcut Atama</h3>
        <p>
          Oda: <strong><?= htmlspecialchars($current['room_number']) ?></strong>,
          Yatak: <strong><?= $current['bed_no'] ?></strong>
        </p>
        <hr style="margin: 20px 0;">

        <h3>Oda Değiştir</h3>
        <?php if (empty($availableBeds)): ?>
          <div class="message msg-info">Taşınabilecek boş yatak kalmadı.</div>
        <?php else: ?>
          <form method="post">
            <input type="hidden" name="action" value="change">
            <label>
              Yeni Yatak Seçin:
              <select name="bed_id" required>
                <option value="">— Seçin —</option>
                <?php foreach ($availableBeds as $b): ?>
                  <option value="<?= $b['bed_id'] ?>">
                    Oda <?= htmlspecialchars($b['room_number']) ?> &ndash;
                    Yatak <?= $b['bed_no'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </label>
            <button type="submit" class="btn btn-primary">Değiştir</button>
          </form>
        <?php endif; ?>

      <?php endif; ?>
    </div>

    <a href="list_it.php" class="back-link">&larr; Öğrenci Listesine Dön</a>
  </main>

</body>
</html>
