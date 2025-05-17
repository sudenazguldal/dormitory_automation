<?php 
session_start();
require_once "../config/db.php";

date_default_timezone_set("Europe/Istanbul");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "students affair") {
    header("Location: ../public/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Onayla
   if (isset($_POST['approve_id'])) {
  try {
    $ins = $pdo->prepare("
      INSERT INTO permission_approved_by 
        (permission_id, user_id, approved_at)
      VALUES (?, ?, NOW())
    ");
    $ins->execute([$_POST['approve_id'], $_SESSION['user_id']]);
    $_SESSION['success_message'] = "İzin başarıyla onaylandı.";
  } catch (PDOException $e) {
    // trigger’dan gelen özel mesajı al (ör. "Başlangıç tarihi bugünden önce olamaz…")
    $msg = isset($e->errorInfo[2]) ? $e->errorInfo[2] : $e->getMessage();
    $_SESSION['error_message'] = $msg;
}
  header("Location: permission_approve.php");
  exit;
}

    // Düzenle
    if (isset($_POST['edit_id'], $_POST['edit_start'], $_POST['edit_end'], $_POST['edit_type'])) {
        $edit_start = trim($_POST['edit_start']);
        $edit_end   = trim($_POST['edit_end']);
        $edit_type  = trim($_POST['edit_type']);

        if (empty($edit_start) || empty($edit_end)) {
            $_SESSION['error_message'] = "Tarih alanları boş bırakılamaz.";
        } else {
            try {
                $stmt = $pdo->prepare("
                    UPDATE permissions
                    SET start_date = ?, end_date = ?, permissions_type = ?
                    WHERE permission_id = ?
                ");
                $stmt->execute([
                    $edit_start,
                    $edit_end,
                    $edit_type,
                    $_POST['edit_id']
                ]);
                $_SESSION['success_message'] = "İzin başarıyla güncellendi.";
            } catch (PDOException $e) {
        // trigger’dan gelen özel mesajı al (ör. "Başlangıç tarihi bugünden önce olamaz…")
         $msg = isset($e->errorInfo[2]) ? $e->errorInfo[2] : $e->getMessage();
         $_SESSION['error_message'] = $msg;
}
        header("Location: permission_approve.php");
        exit;
    }

    // Onay iptal et
    if (isset($_POST['cancel_id'])) {
        $pdo->prepare("DELETE FROM permission_approved_by WHERE permission_id = ?")
            ->execute([$_POST['cancel_id']]);
        $_SESSION['success_message'] = "İzin onayı iptal edildi.";
        header("Location: permission_approve.php");
        exit;
    }

    // Onaylı izni sil
    if (isset($_POST['delete_selected_id'])) {
        $pdo->prepare("DELETE FROM permission_approved_by WHERE permission_id = ?")
            ->execute([$_POST['delete_selected_id']]);
        $pdo->prepare("DELETE FROM permission_created_by WHERE permission_id = ?")
            ->execute([$_POST['delete_selected_id']]);
        $pdo->prepare("DELETE FROM permissions WHERE permission_id = ?")
            ->execute([$_POST['delete_selected_id']]);
        $_SESSION['success_message'] = "Seçilen onaylı izin başarıyla silindi.";
        header("Location: permission_approve.php");
        exit;
    }

    // Onaysız izni sil
    if (isset($_POST['delete_unapproved_id'])) {
        $pdo->prepare("DELETE FROM permission_created_by WHERE permission_id = ?")
            ->execute([$_POST['delete_unapproved_id']]);
        $pdo->prepare("DELETE FROM permissions WHERE permission_id = ?")
            ->execute([$_POST['delete_unapproved_id']]);
        $_SESSION['success_message'] = "Seçilen onaysız izin başarıyla silindi.";
        header("Location: permission_approve.php");
        exit;
    }
}
} // <-- Add this closing brace to properly end the main if ($_SERVER["REQUEST_METHOD"] === "POST") block

// ——— Burada artık VIEW’ları kullanıyoruz ———

// 1) Bekleyen izinler (VIEW: view_pending_permissions)
$permissions = $pdo
    ->query("SELECT permission_id, student_name, start_date, end_date, permissions_type
             FROM view_pending_permissions
             ORDER BY start_date ASC")
    ->fetchAll();

// 2) Onaylı son 5 izin (VIEW: view_recent_approvals)
$approved = $pdo
    ->query("SELECT permission_id, student_name, start_date, end_date, permissions_type
             FROM view_recent_approvals")
    ->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>İzin Onayı</title>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <style>
      
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #e6f0ff;
            display: flex;
        }
        .main {
            flex: 1;
            padding: 30px 60px;
        }
        
.main table {
  margin-bottom: 20px;
}

        h2 {
            color: #0a2342;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 16px;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #123060;
            color: white;
        }
        form {
            display: inline;
        }
        button {
            background-color: #3794ff;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin-right: 4px;
        }
        .danger {
            background-color: #d90429;
        }
        .alert {
            padding: 10px 20px;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        .alert-error {
            background-color: #ffcccc;
            color: #a00;
            border-left: 4px solid red;
        }
        .alert-success {
            background-color: #ccffcc;
            color: #060;
            border-left: 4px solid green;
        }

        /* Yeni estetik silme formları */
        .select-delete {
            background-color: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .select-delete form {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .select-delete label {
            font-weight: bold;
            min-width: 160px;
        }
        .select-delete select {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
            min-width: 300px;
        }
    </style>
</head>
<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">
    <!-- Hata / Başarı Mesajları -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php elseif (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <!-- Bekleyen İzinler -->
    <h2>Onay Bekleyen İzinler</h2>
    <table>
        <thead>
            <tr>
                <th>Ad Soyad</th>
                <th>Başlangıç</th>
                <th>Bitiş</th>
                <th>İzin Türü</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($permissions as $perm): ?>
                <tr>
                    <form method="post">
                        <td><?= htmlspecialchars($perm['student_name']) ?></td>
                        <td><input type="date"   name="edit_start" value="<?= $perm['start_date'] ?>" required></td>
                        <td><input type="date"   name="edit_end"   value="<?= $perm['end_date']   ?>" required></td>
                        <td>
                            <select name="edit_type">
                                <?php foreach (["Weekend","Holiday","Medical","Family","Another"] as $type): ?>
                                    <option value="<?= $type ?>"
                                        <?= $type === $perm['permissions_type'] ? 'selected' : '' ?>>
                                        <?= $type ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="hidden" name="edit_id"    value="<?= $perm['permission_id'] ?>">
                            <button type="submit">Güncelle</button>
                            <button type="submit" name="approve_id" value="<?= $perm['permission_id'] ?>">
                                Onayla
                            </button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Estetik silme formları -->
    <div class="select-delete">
        <form method="post">
            <label for="delete_unapproved_id">Onaysız izni sil:</label>
            <select name="delete_unapproved_id" required>
                <option value="" disabled selected>İzin seçiniz</option>
                <?php foreach ($permissions as $perm): ?>
                    <option value="<?= $perm['permission_id'] ?>">
                        <?= htmlspecialchars($perm['student_name']) ?>
                        &nbsp;|&nbsp;
                        <?= $perm['start_date'] ?>—<?= $perm['end_date'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="danger">Sil</button>
        </form>
    </div>

    <!-- Onaylanmış Son 5 İzin -->
    <h2>Onaylanmış Son 5 İzin</h2>
    <table>
        <thead>
            <tr>
                <th>Ad Soyad</th>
                <th>Başlangıç</th>
                <th>Bitiş</th>
                <th>İzin Türü</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($approved as $perm): ?>
                <tr>
                    <td><?= htmlspecialchars($perm['student_name']) ?></td>
                    <td><?= htmlspecialchars($perm['start_date']) ?></td>
                    <td><?= htmlspecialchars($perm['end_date']) ?></td>
                    <td><?= htmlspecialchars($perm['permissions_type']) ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="cancel_id" value="<?= $perm['permission_id'] ?>">
                            <button type="submit" class="danger">İptal Et</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Estetik silme formları -->
    <div class="select-delete">
        <form method="post">
            <label for="delete_selected_id">Onaylı izni sil:</label>
            <select name="delete_selected_id" required>
                <option value="" disabled selected>İzin seçiniz</option>
                <?php foreach ($approved as $perm): ?>
                    <option value="<?= $perm['permission_id'] ?>">
                        <?= htmlspecialchars($perm['student_name']) ?>
                        &nbsp;|&nbsp;
                        <?= $perm['start_date'] ?>—<?= $perm['end_date'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="danger">Sil</button>
        </form>
    </div>

</div>

</body>
</html>