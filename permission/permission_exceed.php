<?php
session_start();
require_once "../config/db.php";

// Yetki kontrolü
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "students affair") {
  header("Location: ../public/login.php");
  exit;
}

// Silme işlemi
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_student_id'])) {
  $deleteId = (int)$_POST['delete_student_id'];

  // Onaylanmış izinlerin toplam gün sayısı
  $stmt = $pdo->prepare("
        SELECT SUM(DATEDIFF(p.end_date, p.start_date) + 1) AS total_days
        FROM students s
        JOIN permissions p ON s.student_id = p.student_id
        JOIN permission_approved_by a ON a.permission_id = p.permission_id
        WHERE s.student_id = ?
    ");
  $stmt->execute([$deleteId]);
  $totalDays = (int)$stmt->fetchColumn();

  if ($totalDays > 45) {
    $pdo->prepare("DELETE FROM permission_approved_by WHERE permission_id IN 
            (SELECT permission_id FROM permissions WHERE student_id = ?)")->execute([$deleteId]);
    $pdo->prepare("DELETE FROM permission_created_by WHERE permission_id IN 
            (SELECT permission_id FROM permissions WHERE student_id = ?)")->execute([$deleteId]);
    $pdo->prepare("DELETE FROM permissions WHERE student_id = ?")->execute([$deleteId]);
    $pdo->prepare("DELETE FROM students WHERE student_id = ?")->execute([$deleteId]);
    $_SESSION['success_message'] = "45 günü geçen öğrenci, tüm kayıtlarıyla birlikte silindi.";
  } else {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
      $pdo->prepare("DELETE FROM permission_approved_by WHERE permission_id IN 
                (SELECT permission_id FROM permissions WHERE student_id = ?)")->execute([$deleteId]);
      $pdo->prepare("DELETE FROM permission_created_by WHERE permission_id IN 
                (SELECT permission_id FROM permissions WHERE student_id = ?)")->execute([$deleteId]);
      $pdo->prepare("DELETE FROM permissions WHERE student_id = ?")->execute([$deleteId]);
      $pdo->prepare("DELETE FROM students WHERE student_id = ?")->execute([$deleteId]);
      $_SESSION['success_message'] = "Öğrenci başarıyla silindi.";
    }
  }

  header("Location: permission_exceed.php");
  exit;
}

// Öğrencileri ve onaylı izin günlerini çek
$students = $pdo->query("
    SELECT s.student_id, s.TC_no, s.first_name, s.last_name,
           COALESCE(SUM(DATEDIFF(p.end_date, p.start_date)+1), 0) AS total_days
      FROM students s
      JOIN permissions p ON p.student_id = s.student_id
      JOIN permission_approved_by a ON a.permission_id = p.permission_id
  GROUP BY s.student_id
  ORDER BY total_days DESC
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8">
  <title>İzin Takip</title>
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #e6f0ff;
      display: flex;
    }

    .main {
      flex: 1;
      padding: 40px;
      max-width: 1000px;
      margin: 0 auto;
    }

    h2 {
      margin-bottom: 40px;
      text-align: center;
      color: #0a2342;
    }

    .table-container {
      max-width: 900px;
      margin: 30px auto;
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    .table-container table {
      width: 100%;
      border-collapse: collapse;
    }

    .table-container th {
      background-color: #123060;
      color: #fff;
      text-align: left;
      padding: 14px 16px;
      font-weight: 600;
      font-size: 1rem;
    }

    .table-container td {
      padding: 12px 16px;
      vertical-align: middle;
      border-bottom: 1px solid #e2e8f0;
    }

    .table-container tbody tr:nth-child(even) td {
      background-color: #f4f8fc;
    }

    .badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 0.9rem;
      font-weight: 500;
      color: #fff;
    }

    .badge-low {
      background-color: #38a169;
    }

    .badge-high {
      background-color: #e53e3e;
    }

    .btn-black {
      background-color: #000;
      color: #fff;
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      font-size: 0.9rem;
      cursor: pointer;
      transition: background-color .2s;
    }

    .btn-black:hover {
      background-color: #333;
    }
  </style>
  <script>
    function confirmDeletion(studentId, days) {
      const formId = "form-" + studentId;
      if (days <= 45) {
        if (!confirm("Bu öğrenciyi silmek istediğinize emin misiniz?")) {
          return false;
        }
        const f = document.getElementById(formId);
        let inp = document.createElement("input");
        inp.type = "hidden";
        inp.name = "confirm";
        inp.value = "yes";
        f.appendChild(inp);
      }
      document.getElementById(formId).submit();
      return false;
    }
  </script>
</head>

<body>
  <?php include "../includes/sidebar.php"; ?>
  <div class="main">
    <?php if (!empty($_SESSION['error_message'])): ?>
      <div style="background:#ffe5e5;color:#a00;padding:10px;border-left:4px solid #e63946;margin-bottom:20px;">
        <?= $_SESSION['error_message'];
        unset($_SESSION['error_message']); ?>
      </div>
    <?php elseif (!empty($_SESSION['success_message'])): ?>
      <div style="background:#e5ffea;color:#060;padding:10px;border-left:4px solid #2a9d8f;margin-bottom:20px;">
        <?= $_SESSION['success_message'];
        unset($_SESSION['success_message']); ?>
      </div>
    <?php endif; ?>

    <h2>Öğrencilerin Toplam İzin Günleri</h2>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Ad Soyad</th>
            <th>TC Kimlik No</th>
            <th>Toplam İzin Günü</th>
            <th>İşlem</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $student): ?>
            <tr>
              <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
              <td><?= htmlspecialchars($student['TC_no']) ?></td>
              <td>
                <span class="badge <?= $student['total_days'] > 45 ? 'badge-high' : 'badge-low' ?>">
                  <?= $student['total_days'] ?> gün
                </span>
              </td>
              <td>
                <form id="form-<?= $student['student_id'] ?>" method="post"
                  onsubmit="return confirmDeletion(<?= $student['student_id'] ?>, <?= $student['total_days'] ?>)">
                  <input type="hidden" name="delete_student_id" value="<?= $student['student_id'] ?>">
                  <button type="submit" class="btn-black">İlişik Kes</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>