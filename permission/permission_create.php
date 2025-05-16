<?php

//view ve trigger kullanıldı
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../config/db.php";

// Yetki kontrolü
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "security") {
    header("Location: ../public/login.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];
$error   = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id       = $_POST['student_id'];
    $start_date       = $_POST['start_date'];
    $end_date         = $_POST['end_date'];
    $permissions_type = $_POST['permissions_type'];

    // Geçmiş tarih kontrolü
    if ($start_date < date('Y-m-d') || $end_date < date('Y-m-d')) {
        $error = "Geçmiş tarih için izin alınamaz.";
    } else {
        // Tarih çakışması kontrolü
        $check = $pdo->prepare(
            "SELECT 1 FROM permissions
             WHERE student_id = ? AND (
               (start_date <= ? AND end_date >= ?) OR
               (start_date <= ? AND end_date >= ?) OR
               (start_date >= ? AND end_date <= ?)
             )"
        );
        $check->execute([
            $student_id,
            $start_date, $start_date,
            $end_date,   $end_date,
            $start_date, $end_date
        ]);

        if ($check->fetch()) {
            $error = "Bu tarihler arasında zaten izin mevcut.";
        } else {
            // Trigger'ın kullanacağı MySQL session değişkenini set et
            $pdo->exec("SET @current_user_id = {$user_id}");

            // Sadece permissions tablosuna insert
            $stmt = $pdo->prepare(
                "INSERT INTO permissions
                 (student_id, start_date, end_date, permissions_type)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([
                $student_id,
                $start_date,
                $end_date,
                $permissions_type
            ]);

            $message = "İzin başarıyla kaydedildi.";
        }
    }
}

// Öğrenci listesini al
$students = $pdo
    ->query("SELECT student_id, first_name, last_name FROM students ORDER BY first_name")
    ->fetchAll();

// Son 3 izni view'den çek
$recent = $pdo
    ->query("SELECT * FROM v_recent_permissions LIMIT 3")
    ->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>İzin Talebi</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <style>
        body {
            display: flex;
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #e6f0ff;
        }

        .main {
            flex: 1;
            padding: 30px;
        }

        form {
            max-width: 500px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #0a2342;
        }

        label {
            display: block;
            margin: 12px 0 6px;
        }

        select, input[type="date"] {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        button {
            margin-top: 20px;
            padding: 12px;
            background-color: #3794ff;
            color: white;
            border: none;
            border-radius: 6px;
            width: 100%;
            font-weight: bold;
        }

        .error, .message {
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
        }

        .error { color: red; }
        .message { color: green; }
            .recent-permissions {
            max-width: 700px;
            margin: 40px auto 0;
        }

        .recent-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .recent-table th, .recent-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .recent-table th {
            background-color: #123060;
            color: white;
        }

        .recent-table tr:last-child td {
            border-bottom: none;
        }

        .recent-table td {
            background-color: #f9fbff;
        }
    </style>


</head>
<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
    <h2>Öğrenci İzin Talebi</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

    <form method="post">
        <label>Öğrenci:</label>
        <select name="student_id" required>
            <option value="">Seçiniz...</option>
            <?php foreach ($students as $s): ?>
                <option value="<?= $s['student_id'] ?>"><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Başlangıç Tarihi:</label>
        <input type="date" name="start_date" required>

        <label>Bitiş Tarihi:</label>
        <input type="date" name="end_date" required>

        <label>İzin Türü:</label>
        <select name="permissions_type" required>
            <option value="">Seçiniz...</option>
            <option value="Weekend">Haftasonu</option>
            <option value="Holiday">Tatil</option>
            <option value="Medical">Sağlık</option>
            <option value="Family">Aile</option>
            <option value="Another">Diğer</option>
        </select>

        <button type="submit">Kaydet</button>
    </form>

    <div class="recent-permissions">
        <h3 style="text-align:center; margin-top: 40px; color:#0a2342;">Son 3 İzin</h3>
        <table class="recent-table">
            <thead>
                <tr>
                    <th>Öğrenci</th>
                    <th>Başlangıç</th>
                    <th>Bitiş</th>
                    <th>Tür</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $recent = $pdo->query("SELECT p.start_date, p.end_date, p.permissions_type, s.first_name, s.last_name
                    FROM permissions p
                    JOIN students s ON s.student_id = p.student_id
                    ORDER BY p.permission_id DESC LIMIT 3")->fetchAll();
                foreach ($recent as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                        <td><?= htmlspecialchars($r['start_date']) ?></td>
                        <td><?= htmlspecialchars($r['end_date']) ?></td>
                        <td><?= htmlspecialchars($r['permissions_type']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
