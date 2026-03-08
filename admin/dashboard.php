<?php
require '../db.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit;
}



$totalTracks = $pdo->query('SELECT COUNT(*) FROM tracks')->fetchColumn();
$todayTracks = $pdo->query("SELECT COUNT(*) FROM tracks WHERE DATE(upload_date) = CURDATE()")->fetchColumn();
$totalUsers = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$approvedTracks = $pdo->query('SELECT COUNT(*) FROM tracks WHERE is_approved = 1')->fetchColumn();
$pendingTracks = $pdo->query('SELECT COUNT(*) FROM tracks WHERE is_approved = 0')->fetchColumn();



$uploadsByDay = $pdo->query("
    SELECT DATE(upload_date) as day, COUNT(*) as count 
    FROM tracks 
    GROUP BY DATE(upload_date) 
    ORDER BY day DESC 
    LIMIT 7
")->fetchAll(PDO::FETCH_ASSOC);


$genresRaw = $pdo->query("
    SELECT 
        CASE 
            WHEN genres LIKE '%\"rock\"%' THEN 'Rock'
            WHEN genres LIKE '%\"pop\"%' THEN 'Pop'
            WHEN genres LIKE '%\"hiphop\"%' OR genres LIKE '%\"rap\"%' THEN 'Hip-Hop'
            WHEN genres LIKE '%\"electronic\"%' OR genres LIKE '%\"techno\"%' THEN 'Electronic'
            WHEN genres LIKE '%\"jazz\"%' THEN 'Jazz'
            WHEN genres LIKE '%\"rnb\"%' THEN 'R&B'
            WHEN genres LIKE '%\"classical\"%' THEN 'Classical'
            WHEN genres LIKE '%\"metal\"%' THEN 'Metal'
            ELSE 'Other'
        END as genre,
        COUNT(*) as count
    FROM tracks 
    WHERE genres IS NOT NULL AND genres != 'null' AND genres != '[]'
    GROUP BY genre 
    ORDER BY count DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);


$where = [];
$params = [];
if (!empty($_GET['date_from'])) {
    $where[] = "DATE(t.upload_date) >= ?";
    $params[] = $_GET['date_from'];
}
if (!empty($_GET['date_to'])) {
    $where[] = "DATE(t.upload_date) <= ?";
    $params[] = $_GET['date_to'];
}
if (!empty($_GET['search'])) {
    $where[] = "(t.title LIKE ? OR t.description LIKE ? OR COALESCE(u.login, t.author) LIKE ?)";
    $search = "%" . $_GET['search'] . "%";
    $params[] = $search; $params[] = $search; $params[] = $search;
}
if (!empty($_GET['status'])) {
    $where[] = "t.is_approved = ?";
    $params[] = (int)$_GET['status'];
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';


$stmtLast = $pdo->prepare("
    SELECT t.*, COALESCE(u.login, t.author) as author_display, u.avatar
    FROM tracks t
    LEFT JOIN users u ON t.user_id = u.id
    $whereClause
    ORDER BY t.upload_date DESC 
    LIMIT 20
");
$stmtLast->execute($params);
$lastTracks = $stmtLast->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Дашборд - Maxusic</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" href="assets/images/note.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400..700&family=Tiny5&display=swap" rel="stylesheet">
</head>
<script>
window.dashboardData = {
    uploadsByDay: <?php echo json_encode(array_reverse($uploadsByDay)); ?>,
    genres: <?php echo json_encode($genresRaw); ?>
};
</script>
<body>
    <header class="header">
        <h1>Дашборд</h1>
        <div>
            <a href="admin_reports.php" class="back-btn">Модерация</a>
            <a href="../index.php" class="back-btn">На сайт</a>
        </div>
    </header>

    <div class="dashboard">
       
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Всего треков</h3>
                <span class="stat-number"><?php echo number_format($totalTracks); ?></span>
            </div>
            <div class="stat-card <?php echo $todayTracks > 0 ? 'positive' : ''; ?>">
                <h3>Загрузок сегодня</h3>
                <span class="stat-number"><?php echo $todayTracks; ?></span>
            </div>
            <div class="stat-card">
                <h3>Пользователей</h3>
                <span class="stat-number"><?php echo number_format($totalUsers); ?></span>
            </div>
            <div class="stat-card">
    <h3>Одобренных</h3>
    <span class="stat-number"><?php echo $approvedTracks; ?></span>
</div>
<div class="stat-card danger">
    <h3>На модерации</h3>
    <span class="stat-number"><?php echo $pendingTracks; ?></span>
</div>
        </div>

       
        <div class="filters-row">
            <form method="get" class="filters-form">
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>">
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>">
                <input type="text" name="search" placeholder="Поиск по названию/автору..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <select name="status">
                    <option value="">Все статусы</option>
                    <option value="1" <?php echo ($_GET['status'] ?? '') == '1' ? 'selected' : ''; ?>>✅ Одобренные</option>
                    <option value="0" <?php echo ($_GET['status'] ?? '') == '0' ? 'selected' : ''; ?>>⏳ На модерации</option>
                </select>
                <button type="submit" class="btn-filter">🔍 Фильтровать</button>
                <a href="dashboard.php" class="btn-clear">Очистить</a>
            </form>
        </div>

     
        <div class="charts-row">
            <div class="chart-card">
                <h3>📈 Загрузки за неделю</h3>
                <canvas id="uploadsChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>🎵 Популярные жанры</h3>
                <canvas id="genresChart"></canvas>
            </div>
        </div>

     
        <div class="table-card">
            <h3>🎼 Последние треки <?php echo !empty($where) ? '(фильтр активен)' : ''; ?></h3>
            <?php if (empty($lastTracks)): ?>
                <p class="empty-state">Треков по текущим фильтрам не найдено</p>
            <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Обложка</th>
                            <th>Название</th>
                            <th>Автор</th>
                            <th>Дата</th>
                            <th>Просл.</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lastTracks as $track): ?>
                        <tr>
                            <td>
                                <?php if (!empty($track['cover_path']) && file_exists($track['cover_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($track['cover_path']); ?>" width="40" style="border-radius: 4px;">
                                <?php else: ?>
                                    🎵
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars(substr($track['title'], 0, 30)); ?><?php echo strlen($track['title']) > 30 ? '...' : ''; ?></td>
                            <td><?php echo htmlspecialchars($track['author_display']); ?></td>
                            <td><?php echo date('d.m', strtotime($track['upload_date'])); ?></td>
                            <td><?php echo number_format($track['plays'] ?? 0); ?></td>
                            <td>
                                <span class="status <?php echo $track['is_approved'] ? 'approved' : 'pending'; ?>">
                                    <?php echo $track['is_approved'] ? '✅' : '⏳'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="track.php?id=<?php echo $track['id']; ?>" class="btn-small" target="_blank">👁️</a>
                                    <form method="post" action="moderate_track.php" style="display:inline;">
                                        <input type="hidden" name="track_id" value="<?php echo $track['id']; ?>">
                                        <?php if (!$track['is_approved']): ?>
                                            <button type="submit" name="action" value="approve" class="btn-small approve">✅</button>
                                        <?php endif; ?>
                                        <button type="submit" name="action" value="delete" class="btn-small delete" 
                                                onclick="return confirm('Удалить «<?php echo htmlspecialchars($track['title']); ?>»?')">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    window.dashboardData = {
        uploadsByDay: <?php echo json_encode(array_reverse($uploadsByDay)); ?>,
        genres: <?php echo json_encode($genresRaw); ?>
    };
    </script>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>
