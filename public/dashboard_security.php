<?php
// dashboard_security.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../config/db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "security") {
    header("Location: login.php");
    exit;
}

$name = $_SESSION["name"] ?? "Güvenlik";
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Güvenlik Paneli</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            font-family: 'Segoe UI', sans-serif;
            background-color:rgb(110, 162, 207); /* bebek mavisi */
        }

        body {
            display: flex;
            overflow: hidden;
        }

        .sidebar {
            width: 250px;
            background-color: #0a2342;
            color: white;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sidebar h2 {
            font-size: 22px;
            margin-bottom: 40px;
            text-align: center;
            border-bottom: 2px solid white;
            padding-bottom: 10px;
            width: 100%;
        }

        .menu-button {
            width: 100%;
            background-color: #123060;
            color: white;
            text-align: center;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .menu-button:hover {
            background-color: #1b3b75;
        }

        .main {
            flex: 1;
            background-color:rgb(179, 220, 255)
;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .welcome-box {
            background-color: rgba(255, 255, 255, 0.85);
            padding: 30px 50px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
            font-size: 28px;
            font-weight: bold;
            color: #1d3557;
        }

        @media (max-width: 768px) {
            .welcome-box {
                font-size: 22px;
                padding: 20px 30px;
            }

            .sidebar {
                width: 180px;
            }
        }
    </style>
</head>
<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">
    <div class="welcome-box">
        Hoş geldiniz, <?= htmlspecialchars($name) ?> 👋
    </div>
</div>

</body>
</html>
