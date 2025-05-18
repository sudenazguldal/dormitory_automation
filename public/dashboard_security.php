<?php
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
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <style>
        html,
        body {

            padding: 0;
            height: 100%;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
            

        }




        .main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background: url('../assets/images/blue_ocean.jpg') no-repeat center right fixed;

            background-size: cover;
            height: 100vh;
            
            width: 100vw;
            
            overflow-y: auto;
            /* içerik taşarsa içerikte kaydır */

        }


        .welcome-box {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px 50px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
            font-size: 28px;
            font-weight: bold;
            color: #1d3557;
            text-align: center;
        }


        @media (max-width: 768px) {
            .main {
                flex-direction: column;
                justify-content: center;
                align-items: center;
                height: auto;
                /* İçeriğe göre ayarlanır */
                width: 100%;
                /* Tam ekran genişliği */

            }

            .welcome-box {
                font-size: 22px;
                padding: 20px 30px;
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