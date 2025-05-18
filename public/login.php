<?php



session_start();

require_once __DIR__ . '/../config/db.php';





// Eğer giriş yapılmışsa direkt yönlendir
if (isset($_SESSION["user_id"])) {
    if ($_SESSION["role"] === "security") {
        header("Location: dashboard_security.php");
    } else {
        header("Location: dashboard_studaff_officer.php");
    }
    exit;
}

// Form gönderildiyse
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tc_no = $_POST["tc_no"];
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE TC_no = ?");
    $stmt->execute([$tc_no]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password"])) {
        // Giriş başarılı
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["role"] = $user["role"];
        $_SESSION["name"] = $user["first_name"] . " " . $user["last_name"];

        if ($user["role"] === "security") {
            header("Location: dashboard_security.php");
        } else {
            header("Location: dashboard_studaff_officer.php");
        }
        exit;
    } else {
        $error = "TC veya şifre hatalı!";
    }
}
?>


<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Yurt Giriş Paneli</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", sans-serif;
            background: linear-gradient(135deg, #1d3557, #457b9d);
            height: 100vh;
            display: flex;
            justify-content: space-around;
            align-items: center;
            color: #333;
        }

        .login-box {
            background-color: white;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            position: absolute;
            top: 50%;
            /* düşeyde ortalar */
            transform: translateY(-50%);
            /* düşey hizalamayı tam ortalamak için gerekli */
            left: 750px;
            /* yatay konumu istediğin gibi ayarla */

        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 24px;
            color: #1d3557;
        }

        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        .login-box button {
            width: 100%;
            background-color: #1d3557;
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .login-box button:hover {
            background-color: #274c77;
        }

        .logo-container {
            position: absolute;
            top: 50%;
            /* düşeyde ortalama */
            transform: translateY(-50%);
            /* düşey hizalamayı tam ortalamak için gerekli */
            left: 250px;
            /* yatay konumu istediğin gibi ayarlama */
            padding: 20px;
            background-color: white;
            border-radius: 12px;
        }

        .logo-container img {
            max-width: 250px;
            height: auto;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="logo-container">
        <img src="../assets/images/logo.png" alt="Logo">
    </div>

    <div class="login-box">
        <h2>Yurt Giriş Sistemi</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <input type="text" name="tc_no" placeholder="TC Kimlik No" maxlength="11" required>
            <input type="password" name="password" placeholder="Şifre" required>
            <button type="submit">Giriş Yap</button>
        </form>
    </div>
</body>

</html>