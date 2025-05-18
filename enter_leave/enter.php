<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "security") {
    header("Location: ../public/login.php");
    exit;
}

$security_id = $_SESSION["user_id"];

// Giriş/Çıkış işlemi
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'], $_POST['student_id'])) {
    $student_id = $_POST['student_id'];
    $action = $_POST['action'];

    // Son işlem kontrolü 
    $stmt = $pdo->prepare("SELECT action FROM student_entry_logs WHERE student_id = ? ORDER BY timestamp DESC LIMIT 1");
    $stmt->execute([$student_id]);
    $lastAction = $stmt->fetchColumn();

    // İzin kontrolü
    $now = date("Y-m-d");
    $permissionStmt = $pdo->prepare("SELECT * FROM permissions WHERE student_id = ? AND ? BETWEEN start_date AND end_date");
    $permissionStmt->execute([$student_id, $now]);
    $activePermission = $permissionStmt->fetch();

    if ($lastAction === $action) {
        $message = "Bu öğrenci zaten '" . ($action === 'enter' ? 'giriş' : 'çıkış') . "' yaptı.";
    } elseif ($activePermission) {
        $message = "Bu öğrenci şu anda izinde ({$activePermission['start_date']} - {$activePermission['end_date']}).";
    } else {
        $stmt = $pdo->prepare("INSERT INTO student_entry_logs (student_id, security_id, action) VALUES (?, ?, ?)");
        $stmt->execute([$student_id, $security_id, $action]);
        $message = "İşlem başarıyla kaydedildi.";
    }
}

// Öğrenci ve son işlem bilgisi
$students = $pdo->query("SELECT s.student_id, s.TC_no, s.first_name, s.last_name,
    (SELECT action FROM student_entry_logs WHERE student_id = s.student_id ORDER BY timestamp DESC LIMIT 1) AS last_action,
    (SELECT timestamp FROM student_entry_logs WHERE student_id = s.student_id ORDER BY timestamp DESC LIMIT 1) AS last_time
    FROM students s ORDER BY s.first_name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Giriş / Çıkış Kontrol</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            display: flex;
            font-family: 'Segoe UI', sans-serif;
            background-color: #e6f0ff;
            min-height: 100vh;
        }

        .main {
            flex: 1;
            padding: 30px;
            background-color: #e6f0ff;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #0a2342;
        }

        .search-box {
            max-width: 300px;
            margin: 0 auto 20px;
            text-align: center;
        }

        .search-box input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #ccc;
        }

        th {

            background-color: #123060;
            color: white;
        }

        form {
            display: inline-block;
        }

        .btn {
            padding: 6px 12px;
            margin-left: 4px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .enter {
            background-color: #3794ff;
            color: white;
        }

        .leave {
            background-color: #345678;
            color: white;
        }

        .message {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            color: darkred;
        }
    </style>
    <script>
        function searchStudents() {
            const input = document.getElementById("searchInput").value.toLowerCase();
            const rows = document.querySelectorAll("tbody tr");
            rows.forEach(row => {
                const name = row.querySelector("td:nth-child(2)").textContent.toLowerCase();
                const tc = row.querySelector("td:nth-child(1)").textContent;
                row.style.display = name.includes(input) || tc.includes(input) ? "" : "none";
            });
        }
    </script>
</head>

<body>
    <?php include "../includes/sidebar.php"; ?>
    <div class="main">
        <h2>Öğrenci Giriş / Çıkış Takibi</h2>
        <?php if (isset($message)) echo "<p class='message'>$message</p>"; ?>
        <div class="search-box">
            <input type="text" id="searchInput" onkeyup="searchStudents()" placeholder="Öğrenci adı veya TC ile ara...">
        </div>
        <table>
            <thead>
                <tr>
                    <th>TC Kimlik No</th>
                    <th>Ad Soyad</th>
                    <th>Son İşlem</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= htmlspecialchars($student['TC_no']) ?></td>
                        <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                        <td>
                            <?php
                            if ($student['last_action']) {
                                echo ($student['last_action'] === 'enter' ? 'Giriş' : ($student['last_action'] === 'leave' ? 'Çıkış' : ''));
                                echo ' (' . date("d.m.Y H:i", strtotime($student['last_time'])) . ')';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">
                                <button type="submit" name="action" value="enter" class="btn enter">Giriş</button>
                                <button type="submit" name="action" value="leave" class="btn leave">Çıkış</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>