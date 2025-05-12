<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../public/login.php");
    exit;
}

// Filtre parametrelerini al
$search = $_GET["search"] ?? "";
$start_date = $_GET["start_date"] ?? "";
$end_date = $_GET["end_date"] ?? "";

// SQL filtreyi hazırlama
$query = "
    SELECT logs.*, 
           s.first_name AS student_first, 
           s.last_name AS student_last, 
           u.first_name AS security_first, 
           u.last_name AS security_last
    FROM student_entry_logs logs
    JOIN students s ON logs.student_id = s.student_id
    JOIN users u ON logs.security_id = u.user_id
    WHERE 1=1
";

$params = [];

if (!empty($search)) {
    $query .= " AND (s.first_name LIKE ? OR s.last_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($start_date)) {
    $query .= " AND logs.timestamp >= ?";
    $params[] = $start_date . " 00:00:00";
}

if (!empty($end_date)) {
    $query .= " AND logs.timestamp <= ?";
    $params[] = $end_date . " 23:59:59";
}

$query .= " ORDER BY logs.timestamp DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş / Çıkış Geçmişi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 30px;
        }
        h2 {
            color: #1d3557;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f7f7f7;
        }
        form.filter-form {
            margin-bottom: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        input[type="text"], input[type="date"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            padding: 8px 16px;
            background-color: #1d3557;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h2>Öğrenci Giriş / Çıkış Geçmişi</h2>

    <form method="get" class="filter-form">
        <input type="text" name="search" placeholder="Ad veya Soyad" value="<?= htmlspecialchars($search) ?>">
        <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
        <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
        <button type="submit">Filtrele</button>
        <a href="enter.php"><button type="button">Temizle</button></a>
    </form>

    <table>
        <thead>
            <tr>
                <th>Öğrenci</th>
                <th>İşlem</th>
                <th>Tarih/Saat</th>
                <th>Güvenlik Görevlisi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log["student_first"] . " " . $log["student_last"]) ?></td>
                    <td><?= strtoupper($log["action"]) === "ENTER" ? "Giriş" : "Çıkış" ?></td>
                    <td><?= htmlspecialchars($log["timestamp"]) ?></td>
                    <td><?= htmlspecialchars($log["security_first"] . " " . $log["security_last"]) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
