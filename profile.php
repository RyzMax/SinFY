<?php
require 'db.php';


$userId = $_SESSION['user_id'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['follow_action']) && $userId) {
    $targetId = (int)($_POST['target_id'] ?? 0);
    if ($targetId && $targetId !== (int)$userId) {
        if ($_POST['follow_action'] === 'follow') {
            $stmt = $pdo->prepare('INSERT IGNORE INTO follows (follower_id, followed_id) VALUES (?, ?)');
            $stmt->execute([$userId, $targetId]);
        } elseif ($_POST['follow_action'] === 'unfollow') {
            $stmt = $pdo->prepare('DELETE FROM follows WHERE follower_id = ? AND followed_id = ?');
            $stmt->execute([$userId, $targetId]);
        }
    }
    header('Location: profile.php?user=' . $targetId);
    exit;
}
$profileUserId = $_GET['user'] ?? null;

if (!$profileUserId) {
    if (empty($userId)) {
        header('Location: login.php');
        exit;
    }
    $profileUserId = $userId;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$profileUserId]);
$profileUser = $stmt->fetch(PDO::FETCH_ASSOC);

$isOwnProfile = ($profileUserId == $userId);


$isFollowing = false;
if ($userId && !$isOwnProfile) {
    $stmtF = $pdo->prepare('SELECT 1 FROM follows WHERE follower_id = ? AND followed_id = ? LIMIT 1');
    $stmtF->execute([$userId, $profileUserId]);
    $isFollowing = (bool)$stmtF->fetchColumn();
}


if (!$profileUser) {
    die('Пользователь не найден');
}


$stmtTracks = $pdo->prepare('SELECT * FROM tracks WHERE user_id = ? ORDER BY upload_date DESC LIMIT 12');
$stmtTracks->execute([$profileUserId]);
$tracks = $stmtTracks->fetchAll(PDO::FETCH_ASSOC);


$isOwnProfile = $profileUserId == $userId;
$pageTitle = $isOwnProfile ? 'Мой профиль' : 'Профиль ' . $profileUser['login'];
?>

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_track_id']) && $isOwnProfile) {
    $trackId = $_POST['delete_track_id'];
    
    $stmt = $pdo->prepare('SELECT * FROM tracks WHERE id = ? AND user_id = ?');
    $stmt->execute([$trackId, $userId]);
    $track = $stmt->fetch();
    
    if ($track) {
        if (file_exists($track['audio_path'])) unlink($track['audio_path']);
        if (!empty($track['cover_path']) && file_exists($track['cover_path'])) unlink($track['cover_path']);
        
        $stmt = $pdo->prepare('DELETE FROM tracks WHERE id = ? AND user_id = ?');
        $stmt->execute([$trackId, $userId]);
        
        $_SESSION['success'] = 'Трек удалён!';
    }
    
    header('Location: profile.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?> - SinFY</title>
    <link rel="stylesheet" href="profile.css">
    <link rel="icon" href="note.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tiny5&display=swap" rel="stylesheet">
</head>
<body>
<header class="header">
    <?php if (!$isOwnProfile): ?>
        <a href="profile.php" class="back-btn">← Мой профиль</a>
    <?php else: ?>
        <a href="index.php" class="back-btn">← На главную</a>
    <?php endif; ?>
    <h1><?php echo $pageTitle; ?></h1>
    <?php if ($isOwnProfile): ?>
        <a href="settings.php" class="back-btn">⚙️ Настройки</a>
        <a href="logout.php" class="back-btn">Выйти</a>
    <?php endif; ?>
</header>

<main class="profile-page">
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="success-msg"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error-msg"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>


    <section class="user-info">
        <div class="avatar" 
             style="background-image: url('<?php echo htmlspecialchars($profileUser['avatar'] ?? 'avatar.jpg'); ?>');">
        </div>
        <div class="user-details">
            <h1 class="nickname">@<?php echo htmlspecialchars($profileUser['login']); ?></h1>
            <p class="user-description">
                <?php echo nl2br(htmlspecialchars($profileUser['about'] ?? 'Описание пока не заполнено.')); ?>
            </p>
                <div class="user-stats">
        <span class="stat"><?php echo count($tracks); ?> треков</span>
        <span class="stat">
            <?php
 
            $followersCount = $pdo->prepare('SELECT COUNT(*) FROM follows WHERE followed_id = ?');
            $followersCount->execute([$profileUserId]);
            echo $followersCount->fetchColumn() . ' подписчиков';
            ?>
        </span>
        <span class="stat">
            <?php
    
            $followingCount = $pdo->prepare('SELECT COUNT(*) FROM follows WHERE follower_id = ?');
            $followingCount->execute([$profileUserId]);
            echo $followingCount->fetchColumn() . ' подписок';
            ?>
        </span>
    </div>

    <?php if ($isOwnProfile): ?>
        <a href="settings.php" class="edit-profile-btn">Редактировать профиль</a>
    <?php else: ?>
        <form method="post" style="margin-top: 12px;">
            <input type="hidden" name="target_id" value="<?php echo $profileUserId; ?>">
            <?php if ($isFollowing): ?>
                <button type="submit" name="follow_action" value="unfollow" class="follow-btn unfollow">
                    Отписаться
                </button>
            <?php else: ?>
                <button type="submit" name="follow_action" value="follow" class="follow-btn">
                    Подписаться
                </button>
            <?php endif; ?>
        </form>
    <?php endif; ?>
            <?php if ($isOwnProfile): ?>
                <a href="settings.php" class="edit-profile-btn">Редактировать профиль</a>
            <?php endif; ?>
        </div>
    </section>

    <section class="tracks-section">
        <h2><?php echo $isOwnProfile ? 'Мои треки' : 'Треки автора'; ?></h2>
        <div class="tracks-grid">
            <?php if (empty($tracks)): ?>
                <p><?php echo $isOwnProfile ? 'У вас пока нет треков.' : 'У этого пользователя пока нет треков.'; ?>
                <?php if ($isOwnProfile): ?> <a href="upload.php">Загрузить первый</a><?php endif; ?></p>
            <?php else: ?>
                <?php foreach ($tracks as $track): ?>
                    <div class="track-card">
                        <?php if (!empty($track['cover_path']) && file_exists($track['cover_path'])): ?>
                            <img src="<?php echo htmlspecialchars($track['cover_path']); ?>" alt="Обложка" class="track-cover">
                        <?php else: ?>
                            <div class="track-cover no-cover-placeholder">🎵</div>
                        <?php endif; ?>
                        <div class="track-info">
                            <h3><?php echo htmlspecialchars($track['title']); ?></h3>
                            <p class="track-author"><?php echo htmlspecialchars($track['author']); ?></p>
                            <p class="track-meta"><?php echo date('d.m.Y', strtotime($track['upload_date'])); ?></p>
                            <audio controls preload="metadata">
                                <source src="<?php echo htmlspecialchars($track['audio_path']); ?>" type="audio/mpeg">
                            </audio>
                           
                            <?php if ($isOwnProfile): ?>
                                <form method="post" style="margin-top:12px;">
                                    <input type="hidden" name="delete_track_id" value="<?php echo $track['id']; ?>">
                                    <button type="submit" class="delete-btn" 
                                            onclick="return confirm('Удалить «<?php echo htmlspecialchars($track['title']); ?>»?')">
                                        🗑️ Удалить
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="footer-inner">
        <span>© 2026 SinFY</span>
        <a href="support.html">Поддержка</a>
    </div>
</footer>
</body>
</html>
