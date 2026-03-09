<?php
require 'db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('Пользователь не найден');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $about = trim($_POST['about'] ?? '');


    $avatarPath = $user['avatar'];

    if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $fileName = 'uploads/avatars/' . $userId . '_' . time() . '.' . $ext;
        if (!file_exists('uploads/avatars')) {
            mkdir('uploads/avatars', 0777, true);
        }
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $fileName)) {
            $avatarPath = $fileName;
        }
    }


    if ($login !== $user['login']) {
        $check = $pdo->prepare('SELECT id FROM users WHERE login = ? AND id <> ?');
        $check->execute([$login, $userId]);
        if ($check->fetch()) {
            $message = 'Логин уже занят другим пользователем';
        } else {
            $user['login'] = $login;
        }
    }

    if (!$message) {
        $stmtUpd = $pdo->prepare('UPDATE users SET login = ?, about = ?, avatar = ? WHERE id = ?');
        $stmtUpd->execute([$user['login'], $about, $avatarPath, $userId]);
        $message = 'Данные профиля обновлены';
        $_SESSION['user_login'] = $user['login'];
        $user['about'] = $about;
        $user['avatar'] = $avatarPath;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Настройки профиля</title>
    <link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body>
<header class="header">
    <a href="../profile.php" class="back-btn">← Назад в профиль</a>
    <h1>Настройки профиля</h1>
</header>

<main class="profile-page">
    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form class="user-info" method="post" enctype="multipart/form-data">
        <div class="avatar" style="background-image: url('<?php echo htmlspecialchars($user['avatar']); ?>');"></div>
        <div class="user-details">
            <label>
                Ник:
                <input type="text" name="login" value="<?php echo htmlspecialchars($user['login']); ?>">
            </label>
            <label>
                Описание:
                <textarea name="about" rows="4"><?php echo htmlspecialchars($user['about']); ?></textarea>
            </label>
            <label>
                Аватар:
                <input type="file" name="avatar" accept="image/*">
            </label>
            <button type="submit" class="edit-profile-btn">Сохранить</button>
        </div>
    </form>
</main>
</body>
</html>
