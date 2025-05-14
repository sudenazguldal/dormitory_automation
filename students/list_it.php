<?php
// students/list_it.php
session_start();
require_once __DIR__ . '/../config/db.php';

// Öğrenciler + varsa oda & yatak bilgisi
$sql = "
  SELECT
    s.student_id,
    CONCAT(s.first_name,' ',s.last_name) AS full_name,
    s.TC_no      AS tc_no,
    s.stud_telNo AS phone,
    r.room_number,
    b.bed_no
  FROM students s
  LEFT JOIN room_assignments ra ON ra.student_id = s.student_id
  LEFT JOIN beds b               ON b.bed_id      = ra.bed_id
  LEFT JOIN rooms r              ON r.room_id     = b.room_id
  ORDER BY s.last_name, s.first_name
";
$students = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Öğrenci Listesi</title>
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <style>
    

    html, body { height: 100%; font-family: 'Segoe UI', sans-serif; }

    /* Flex konteynerine çeviriyoruz */
    body { display: flex; 
        font-family: 'Segoe UI', sans-serif; 
        min-height: 100vh; 
        background: #e6f0ff;
        box-sizing: border-box; 
        margin:0; padding:0;}

    

    /* Main içerik flex:1 ile kalan alanı kaplasın */
    main.content {
      flex: 1;
      padding: 30px;
      
    }

    h2 {  color: #0a2342;
            text-align: center;
            margin-bottom: 20px; }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }
    th, td {
      border: 1px solid #ddd;
      padding: 8px 12px;
      text-align: left;
    }
    th {
      background-color: #123060;
      font-weight: 600;
      color: white;
    }

    /* İşlem butonu stili */
    .btn {
      display: inline-block;
      padding: 4px 8px;
      background: #007bff;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
    }
    .btn:hover {
      background: #0056b3;
    }
  </style>
</head>
<body>
    <?php include "../includes/sidebar.php"; ?>


  <!-- 2) Main İçerik -->
  <main class="content">
    <h2>Öğrenci Listesi</h2>

    <table>
      <thead>
        <tr>
          <th>Ad Soyad</th>
          <th>T.C. No</th>
          <th>Telefon</th>
          <th>Oda No</th>
          <th>Yatak No</th>
          <th>İşlemler</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($students)): ?>
          <tr>
            <td colspan="6" style="text-align:center; padding:20px;">
              <em>Henüz kayıtlı öğrenci yok.</em>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($students as $st): ?>
          <tr>
            <td><?= htmlspecialchars($st['full_name']) ?></td>
            <td><?= htmlspecialchars($st['tc_no']) ?></td>
            <td><?= htmlspecialchars($st['phone']     ?? '—') ?></td>
            <td><?= htmlspecialchars($st['room_number'] ?? '—') ?></td>
            <td><?= htmlspecialchars($st['bed_no']      ?? '—') ?></td>
            <td>
              <a
                href="room_assign.php?student_id=<?= $st['student_id'] ?>"
                class="btn"
              >Oda Atama</a>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </main>

</body>
</html>
