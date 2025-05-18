<?php
session_start();

require_once __DIR__ . '/../config/db.php';



if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "students affair") {
    header("Location: ../public/login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tc_no = $_POST['tc_no'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $birth_date = $_POST['birth_date'];
    $tel_no = $_POST['tel_no'];

    $stmt = $pdo->prepare("INSERT INTO students (TC_no, first_name, last_name, birth_date, stud_telNo) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$tc_no, $first_name, $last_name, $birth_date, $tel_no]);
    $message = "Öğrenci başarıyla kaydedildi.";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Öğrenci Kaydı</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #e6f0ff;
            display: flex;
        }

        .main {
            flex: 1;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        form {
            width: 100%;
            max-width: 500px;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #0a2342;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin: 12px 0 6px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="date"] {
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
            cursor: pointer;
        }

        .message {
            text-align: center;
            color: green;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
    <form method="post">
        <h2>Öğrenci Kaydı</h2>
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

        <label for="tc_no">TC Kimlik No:</label>
        <input type="text" name="tc_no" maxlength="11" required>

        <label for="first_name">Ad:</label>
        <input type="text" name="first_name" required>

        <label for="last_name">Soyad:</label>
        <input type="text" name="last_name" required>

        <label for="birth_date">Doğum Tarihi:</label>
        <input type="date" name="birth_date" required>

        <label for="tel_no">Telefon Numarası:</label>
        <input type="text" name="tel_no" required>

        <button type="submit">Kaydet</button>
    </form>
</div>
</body>
</html>
