<?php
require 'db.php';

$trackId = $_GET['id'] ?? 0;
if (!$trackId || !is_numeric($trackId)) {
    die('Трек не найден');
}

try {
    $stmt = $pdo->prepare('
        SELECT t.*, u.login as author_login, u.avatar as author_avatar, u.about as author_about 
        FROM tracks t 
        LEFT JOIN users u ON t.user_id = u.id 
        WHERE t.id = ? 
        LIMIT 1
    ');
    $stmt->execute([$trackId]);
    $track = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$track) {
        die('Трек не найден');
    }

    $stmtViews = $pdo->prepare('UPDATE tracks SET plays = plays + 1 WHERE id = ?');
    $stmtViews->execute([$trackId]);

} catch (Exception $e) {
    die('Ошибка: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($track['title']); ?> - Maxusic</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="note.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tiny5&display=swap" rel="stylesheet">
</head>
<body>
<header class="header">
    <a href="index.php" class="back-btn">← Все треки</a>
    <h1>SinFY</h1>
    <div class="header-right">
        <a href="upload.php" class="upload-btn">Загрузить</a>
        <div class="user-menu">
            <button class="user-icon" id="userBtn">
                <svg viewBox="0 0 24 24" width="24" height="24">
                    <circle cx="12" cy="7" r="4" fill="currentColor"/>
                    <path d="M20,19c0-3.87-3.13-7-7-7s-7,3.13-7,7" stroke="currentColor" stroke-width="2" fill="none"/>
                </svg>
            </button>
            <div class="user-dropdown" id="userDropdown">
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <a href="profile.php">Профиль</a>
                    <a href="settings.php">Настройки</a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php">Выйти</a>
                <?php else: ?>
                    <a href="login.php">Войти/Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<main class="track-page">
    <section class="track-main">
        <div class="track-artwork" 
             style="background-image: url('<?php echo htmlspecialchars($track['cover_path'] ?? ''); ?>');">
            <?php if (empty($track['cover_path'])): ?>
                <div class="no-cover">🎵</div>
            <?php endif; ?>
        </div>
        
        <div class="track-details">
            <h1><?php echo htmlspecialchars($track['title']); ?></h1>
            <p class="track-author"><?php echo htmlspecialchars($track['author']); ?></p>
            <p class="track-description"><?php echo nl2br(htmlspecialchars($track['description'] ?? 'Описание отсутствует.')); ?></p>
            
            <div class="track-meta">
                <span>⏱️ <?php echo date('d.m.Y H:i', strtotime($track['upload_date'])); ?></span>
                <span>▶️ <?php echo $track['plays'] ?? 0; ?> просл.</span>
            </div>
            
            <div class="track-player-container">
                <audio controls preload="metadata" class="main-player">
                    <source src="<?php echo htmlspecialchars($track['audio_path']); ?>" type="audio/mpeg">
                    Ваш браузер не поддерживает воспроизведение.
                </audio>
            </div>
        </div>
    </section>


    <?php if ($track['user_id'] && !empty($track['author_login'])): ?>
    <section class="track-author-section">
        <h2>Автор трека</h2>
        <a href="profile.php?user=<?php echo $track['user_id']; ?>" class="author-card">
            <div class="author-avatar" 
                 style="background-image: url('<?php echo htmlspecialchars($track['author_avatar'] ?? 'avatar.jpg'); ?>');">
            </div>
            <div class="author-info">
                <h3>@<?php echo htmlspecialchars($track['author_login']); ?></h3>
                <p><?php echo htmlspecialchars($track['author_about'] ?? 'Инди-музыкант'); ?></p>
            </div>
        </a>
    </section>
    <?php endif; ?>
</main>

<footer class="footer">
    <div class="footer-inner">
        <span>© 2026 SinFY</span>
        <a href="support.html" class="footer-link">Поддержка</a>
    </div>
    <script src="scriptIndex.js"></script>
</footer>
</body>
</html>
