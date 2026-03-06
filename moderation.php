<?php
require 'db.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit;
}


$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$stmtCount = $pdo->query('SELECT COUNT(*) FROM tracks WHERE is_approved = 0');
$totalPending = $stmtCount->fetchColumn();
$totalPages = ceil($totalPending / $perPage);


$stmt = $pdo->prepare('
    SELECT t.*, COALESCE(u.login, t.author) as author_display, u.avatar
    FROM tracks t
    LEFT JOIN users u ON t.user_id = u.id
    WHERE t.is_approved = 0
    ORDER BY t.upload_date ASC
    LIMIT :limit OFFSET :offset
');
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$pendingTracks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtPending = $pdo->prepare("
    SELECT c.*, t.title as track_title, u.login as track_author
    FROM comments c
    JOIN tracks t ON c.track_id = t.id
    LEFT JOIN users u ON t.user_id = u.id
    WHERE c.is_approved = 0
    ORDER BY c.created_at DESC
");
$stmtPending->execute();
$pendingComments = $stmtPending->fetchAll();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Модерация - Maxusic</title>
    <link rel="stylesheet" href="moderation.css">
    <link rel="icon" href="note.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Tiny5&display=swap" rel="stylesheet">
    <style>
       
        .header { background: #d32f2f; color: white; padding: 20px; display: flex; justify-content: space-between; }
        .back-btn { color: white; text-decoration: none; padding: 8px 16px; border-radius: 20px; background: rgba(255,255,255,0.2); }
        .moderation-page { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .stats-bar { background: #f5f5f5; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .tracks-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px; }
        .moderation-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .track-preview { display: flex; gap: 15px; margin-bottom: 15px; }
        .track-cover { width: 80px; height: 80px; border-radius: 8px; background: #667eea; display: flex; align-items: center; justify-content: center; }
        .track-info h3 { margin: 0 0 5px 0; }
        .moderation-actions { display: flex; gap: 10px; }
        .btn-approve { background: #4caf50; color: white; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; }
        .btn-reject { background: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; }
        .pagination { text-align: center; margin-top: 30px; }
        .pagination a { padding: 8px 12px; margin: 0 2px; background: #f0f0f0; text-decoration: none; border-radius: 4px; }
        .pagination a.active { background: #d32f2f; color: white; }
    </style>
</head>
<body>
<header class="header">
    <h1>Очередь модерации</h1>
    <div>
        <a href="dashboard.php" class="back-btn">📊 Дашборд</a>
        <a href="index.php" class="back-btn">🏠 На сайт</a>
    </div>
</header>

<main class="moderation-page">
    <div class="stats-bar">
        <span>⏳ На модерации: <strong><?php echo $totalPending; ?></strong></span>
        <span>📄 Страница <?php echo $page; ?> из <?php echo $totalPages; ?></span>
    </div>

    <div class="tracks-grid">
        <?php if (empty($pendingTracks)): ?>
            <div class="empty-state" style="grid-column: 1/-1; text-align: center; padding: 60px;">
                <h2>🎉 Все треки обработаны!</h2>
                <p style="color: #666; font-size: 18px;">Пока нет треков на модерации.</p>
            </div>
        <?php else: ?>
            <?php foreach ($pendingTracks as $track): ?>
            <div class="moderation-card">
                <div class="track-preview">
                    <div class="track-cover" 
                         style="background-image: url('<?php echo htmlspecialchars($track['cover_path'] ?? ''); ?>'); background-size: cover;">
                        <?php if (empty($track['cover_path'])): ?>
                            <div class="no-cover" style="font-size: 24px;">🎵</div>
                        <?php endif; ?>
                    </div>
                    <div class="track-info">
                        <h3><?php echo htmlspecialchars($track['title']); ?></h3>
                        <p class="author">@<?php echo htmlspecialchars($track['author_display']); ?></p>
                        <p class="date"><?php echo date('d.m.Y H:i', strtotime($track['upload_date'])); ?></p>
                        <?php if (!empty($track['description'])): ?>
                            <p style="font-size: 14px; color: #666; margin-top: 5px;"><?php echo htmlspecialchars(substr($track['description'], 0, 100)); ?>...</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="audio-preview" style="margin: 15px 0;">
                    <audio controls style="width: 100%;">
                        <source src="<?php echo htmlspecialchars($track['audio_path']); ?>" type="audio/mpeg">
                        Браузер не поддерживает аудио.
                    </audio>
                </div>
                
                <div class="moderation-actions">
                    <form method="post" action="moderate_track.php" style="display:inline;">
                        <input type="hidden" name="track_id" value="<?php echo $track['id']; ?>">
                        <button type="submit" name="action" value="approve" class="btn-approve">✅ Одобрить</button>
                    </form>
                    <form method="post" action="moderate_track.php" style="display:inline;">
                        <input type="hidden" name="track_id" value="<?php echo $track['id']; ?>">
                        <button type="submit" name="action" value="reject" class="btn-reject" 
                                onclick="return confirm('Отклонить трек «<?php echo addslashes(htmlspecialchars($track['title'])); ?>»?\nФайлы будут удалены.')">
                            ❌ Отклонить
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

  
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>">« Предыдущая</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <a href="?page=<?php echo $i; ?>" <?php echo $i == $page ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>">Следующая »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</main>
</body>
</html>
