<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "security") {
    header("Location: ../public/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = $_POST['student_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $type = $_POST['permissions_type'];

    try {
        //permissions tablosuna ekle 
        $stmt = $pdo->prepare("
            INSERT INTO permissions 
              (student_id, start_date, end_date, permissions_type) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$student_id, $start_date, $end_date, $type]);
        $permission_id = $pdo->lastInsertId();

        //permission_created_by ekleme 
        $stmt2 = $pdo->prepare("
            INSERT INTO permission_created_by 
              (permission_id, user_id) 
            VALUES (?, ?)
        ");
        $stmt2->execute([$permission_id, $user_id]);

        $message = "İzin başarıyla eklendi.";
    } catch (PDOException $e) {
        // trigger’dan gelen hata mesajını almek için
        $msg = isset($e->errorInfo[2])
            ? $e->errorInfo[2]
            : "Beklenmedik bir hata oluştu.";
        $message = $msg;
    }
}

// Son 5 izni çek
$recentPending = $pdo->query("
    SELECT 
      permission_id, 
      student_name, 
      start_date, 
      end_date, 
      permissions_type
    FROM view_pending_permissions
")->fetchAll();

$students = $pdo->query("SELECT student_id, first_name, last_name FROM students ORDER BY first_name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Beklemedeki Faturalar</title>
    <!-- Sidebar & site genel stilleri -->
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <style>
        body {
            display: flex;
            font-family: 'Segoe UI', sans-serif;
            background: #e6f0ff;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .main {
            flex: 1;
            padding: 30px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        h3 {
            text-align: center;
            margin-bottom: 10px;
        }

        form {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 8px;
            margin-top: 6px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        button {
            margin-top: 20px;
            padding: 10px;
            background-color: #3794ff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .sidebar {
            width: 250px;
            background-color: #0a2342;
            color: white;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            position: sticky;
            top: 0;
        }

        /* Tablo stili */
        table {
            width: 80%;
            border-collapse: collapse;
            margin-top: 30px;
            margin: 0 auto;

            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #123060;
            color: white;
        }
    </style>
</head>

<body>
    <?php include "../includes/sidebar.php"; ?>

    <div class="main">
        <h2>İzin Talebi Oluştur</h2>

        <?php if (isset($message)) echo "<p style='color: green; text-align: center;'>$message</p>"; ?>

        <form method="post">
            <label for="student_id">Öğrenci</label>
            <select name="student_id" required>
                <option value="">-- Seçin --</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?= $student['student_id'] ?>">
                        <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="start_date">Başlangıç Tarihi</label>
            <input type="date" name="start_date" required>

            <label for="end_date">Bitiş Tarihi</label>
            <input type="date" name="end_date" required>

            <label for="permissions_type">İzin Türü</label>
            <select name="permissions_type" required>
                <option value="">-- Seçin --</option>
                <option value="Weekend">Hafta Sonu</option>
                <option value="Holiday">Tatil</option>
                <option value="Medical">Sağlık</option>
                <option value="Family">Aile</option>
                <option value="Another">Diğer</option>
            </select>

            <button type="submit">Kaydet</button>
        </form>

        <!-- Son 5 izin -->
        <h3>Son 5 İzin</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Öğrenci</th>
                    <th>Başlangıç</th>
                    <th>Bitiş</th>
                    <th>Tür</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentPending as $perm): ?>
                    <tr>
                        <td><?= $perm['permission_id'] ?></td>
                        <td><?= htmlspecialchars($perm['student_name']) ?></td>
                        <td><?= htmlspecialchars($perm['start_date']) ?></td>
                        <td><?= htmlspecialchars($perm['end_date']) ?></td>
                        <td><?= htmlspecialchars($perm['permissions_type']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>