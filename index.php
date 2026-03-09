<?php
require_once 'db.php';

$currentUserId = $_SESSION['user_id'] ?? null;


$followingIds = [];
if ($currentUserId) {
    $stmtFol = $pdo->prepare('SELECT followed_id FROM follows WHERE follower_id = ?');
    $stmtFol->execute([$currentUserId]);
    $followingIds = $stmtFol->fetchAll(PDO::FETCH_COLUMN, 0);
}

$followingTracks = [];
if ($followingIds) {

    $in = implode(',', array_fill(0, count($followingIds), '?'));
    $paramsFollowing = $followingIds;

   
    if (!$isAdmin) {
        $whereFollow = "t.is_approved = 1 AND t.user_id IN ($in)";
    } else {
        $whereFollow = "t.user_id IN ($in)";
    }

    $sqlFollowing = "
        SELECT t.*, COALESCE(u.login, t.author) as author_display
        FROM tracks t
        LEFT JOIN users u ON t.user_id = u.id
        WHERE $whereFollow
        ORDER BY t.upload_date DESC
        LIMIT 20
    ";
    $stmtFT = $pdo->prepare($sqlFollowing);
    $stmtFT->execute($paramsFollowing);
    $followingTracks = $stmtFT->fetchAll(PDO::FETCH_ASSOC);
}


$whereConditions = [];
$params = [];

// Админ видит все треки
$isAdmin = !empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;


if (!$isAdmin) {
    $whereConditions[] = 't.is_approved = 1';
}


if (!empty($_GET['search'])) {
    $whereConditions[] = '(t.title LIKE ? OR t.author LIKE ? OR t.description LIKE ?)';
    $searchTerm = "%{$_GET['search']}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}


if (!empty($_GET['genres'])) {
    $genres = array_map('trim', explode(',', $_GET['genres']));
    $genreConditions = [];
    
    foreach ($genres as $genre) {
        $genreConditions[] = "(LOWER(t.title) LIKE LOWER(?) OR LOWER(t.description) LIKE LOWER(?) OR LOWER(t.genres) LIKE LOWER(?) OR LOWER(t.author) LIKE LOWER(?))";
        $genreTerm = "%{$genre}%";
        $params[] = $genreTerm;
        $params[] = $genreTerm;
        $params[] = $genreTerm;
        $params[] = $genreTerm;
    }
    
    if ($genreConditions) {
        $whereConditions[] = '(' . implode(' OR ', $genreConditions) . ')';
    }
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Твои треки (всегда показываем)
$myTracks = [];
if (!empty($_SESSION['user_id'])) {
    $stmtMy = $pdo->prepare('
        SELECT t.*, u.login as author_login 
        FROM tracks t 
        LEFT JOIN users u ON t.user_id = u.id 
        WHERE t.user_id = ? 
        ORDER BY t.upload_date DESC 
        LIMIT 6
    ');
    $stmtMy->execute([$_SESSION['user_id']]);
    $myTracks = $stmtMy->fetchAll(PDO::FETCH_ASSOC);
}

// Все треки с фильтрами
$stmt = $pdo->prepare("
    SELECT t.*, COALESCE(u.login, t.author) as author_display
    FROM tracks t 
    LEFT JOIN users u ON t.user_id = u.id 
    $whereClause
    ORDER BY t.upload_date DESC 
    LIMIT 20
");
$stmt->execute($params);
$allTracks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SinFY</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/note.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tiny5&display=swap" rel="stylesheet">
</head>
<body>
<header class="header">
    <h1>SinFY</h1>
    
    <div class="header-right">     
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="upload.php" class="upload-btn">+ Загрузить</a>
        <?php endif; ?>

        <div class="user-menu">
            <button class="user-icon" id="userBtn">
                <svg viewBox="0 0 24 24" width="24" height="24">
                    <circle cx="12" cy="7" r="4" fill="currentColor"/>
                    <path d="M20,19c0-3.87-3.13-7-7-7s-7,3.13-7,7" stroke="currentColor" stroke-width="2" fill="none"/>
                </svg>
            </button>
            
            <div class="user-dropdown" id="userDropdown">
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <?php if ($isAdmin): ?>
                        <a href="/admin/dashboard.php" class="dropdown-item">📊 Дашборд</a>
                        <a href="/admin/admin_reports.php" class="dropdown-item">🔍 Модерация комментариев</a>
                    <?php endif; ?>
                    <a href="profile.php" class="dropdown-item">👤 Профиль</a>
                    <a href="settings.php" class="dropdown-item">⚙️ Настройки</a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item">🚪 Выйти</a>
                <?php else: ?>
                    <a href="login.php" class="dropdown-item">🔐 Войти/Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<div class="main-layout">

    <aside class="genres-sidebar">
        <h3>🎵 Фильтры</h3>
        
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Поиск по названию..." 
                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        </div>
        
        <h4>Жанры</h4>
        <ul class="checkbox-list">
            <li><label><input type="checkbox" value="rock" <?php echo in_array('rock', explode(',', $_GET['genres'] ?? '')) ? 'checked' : ''; ?>> Rock</label></li>
            <li><label><input type="checkbox" value="pop" <?php echo in_array('pop', explode(',', $_GET['genres'] ?? '')) ? 'checked' : ''; ?>> Pop</label></li>
            <li><label><input type="checkbox" value="hiphop" <?php echo in_array('hiphop', explode(',', $_GET['genres'] ?? '')) ? 'checked' : ''; ?>> Hip-Hop</label></li>
            <li><label><input type="checkbox" value="electronic" <?php echo in_array('electronic', explode(',', $_GET['genres'] ?? '')) ? 'checked' : ''; ?>> Electronic</label></li>
            <li><label><input type="checkbox" value="jazz" <?php echo in_array('jazz', explode(',', $_GET['genres'] ?? '')) ? 'checked' : ''; ?>> Jazz</label></li>
            <li><label><input type="checkbox" value="rap" <?php echo in_array('rap', explode(',', $_GET['genres'] ?? '')) ? 'checked' : ''; ?>> Rap</label></li>
            <li><label><input type="checkbox" value="techno" <?php echo in_array('techno', explode(',', $_GET['genres'] ?? '')) ? 'checked' : ''; ?>> Techno</label></li>
            <li><label><input type="checkbox" value="rnb" <?php echo in_array('rnb', explode(',', $_GET['genres'] ?? '')) ? 'checked' : ''; ?>> R&B</label></li>
            <li><label><input type="checkbox" value="classical" <?php echo in_array('classical', explode(',', $_GET['genres'] ?? '')) ? 'checked' : ''; ?>> Classical</label></li>
            <li><label><input type="checkbox" value="metal" <?php echo in_array('metal', explode(',', $_GET['genres'] ?? '')) ? 'checked' : ''; ?>> Metal</label></li>
        </ul>
        
        <button id="clearFilters" class="clear-btn">🧹 Очистить</button>
    </aside>
    
    <main class="music-content">
        <h2>Музыка</h2>
        
    
        <?php if (!empty($_GET['search']) || !empty($_GET['genres'])): ?>
        <div class="active-filters">
            <?php if (!empty($_GET['search'])): ?>
                <span class="filter-tag">
                    🔍 <?php echo htmlspecialchars($_GET['search']); ?> 
                    <a href="index.php?genres=<?php echo htmlspecialchars($_GET['genres'] ?? ''); ?>" style="margin-left: 8px; color: #d32f2f;">✕</a>
                </span>
            <?php endif; ?>
            <?php if (!empty($_GET['genres'])): 
                $genres = array_map('trim', explode(',', $_GET['genres']));
                foreach ($genres as $genre): ?>
                <span class="filter-tag">
                    <?php echo ucfirst($genre); ?> 
                    <a href="index.php?search=<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>&genres=<?php 
                        echo htmlspecialchars(implode(',', array_diff($genres, [$genre]))); ?>" style="margin-left: 8px; color: #d32f2f;">✕</a>
                </span>
            <?php endforeach; endif; ?>
            <a href="index.php" class="clear-all" style="margin-left: 15px;">Очистить все</a>
        </div>
        <?php endif; ?>

    
        <?php if (!empty($myTracks)): ?>
        <h3 style="margin: 20px 0 10px 0; color: #d32f2f;">🎵 Твои треки</h3>
        <div class="tracks-grid">
            <?php foreach ($myTracks as $track): ?>
            <a href="track.php?id=<?php echo $track['id']; ?>" class="track-link">
                <div class="track-card">
                    <?php $hasCover = !empty($track['cover_path']) && file_exists($track['cover_path']); ?>
                    <div class="track-cover <?php echo !$hasCover ? 'no-cover' : ''; ?>" 
                         style="<?php echo $hasCover ? 'background-image: url(' . htmlspecialchars($track['cover_path']) . '); background-size: cover; background-position: center;' : ''; ?>">
                    </div>
                            <?php if (!$track['is_approved']): ?>
            <div class="moderation-badge">⏳ На модерации</div>
        <?php endif; ?>
                    <div class="track-info">
                        <h3><?php echo htmlspecialchars($track['title']); ?></h3>
                        
    
                        <?php 
                        $trackGenres = json_decode($track['genres'], true) ?? [];
                        if (!empty($trackGenres)): 
                        ?>
                        <div class="track-genres">
                            <?php foreach (array_slice($trackGenres, 0, 3) as $genre): ?>
                                <span class="genre-badge"><?php echo ucfirst($genre); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <p class="track-author"><?php echo htmlspecialchars($track['author_login'] ?? $track['author']); ?></p>
                        <p class="track-meta"><?php echo date('d.m.Y', strtotime($track['upload_date'])); ?></p>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if (!empty($followingTracks)): ?>
    <h3 style="margin: 30px 0 10px 0; color:#1976d2;">📡 Треки тех, на кого вы подписаны</h3>
    <div class="tracks-grid">
        <?php foreach ($followingTracks as $track): ?>
            <a href="track.php?id=<?php echo $track['id']; ?>" class="track-link">
                <div class="track-card">
                    <?php $hasCover = !empty($track['cover_path']) && file_exists($track['cover_path']); ?>
                    <div class="track-cover <?php echo !$hasCover ? 'no-cover' : ''; ?>"
                         style="<?php echo $hasCover ? 'background-image: url(' . htmlspecialchars($track['cover_path']) . '); background-size: cover; background-position: center;' : ''; ?>">
                    </div>
                    <div class="track-info">
                        <h3><?php echo htmlspecialchars($track['title']); ?></h3>
                        <p class="track-author"><?php echo htmlspecialchars($track['author_display']); ?></p>
                        <p class="track-meta">
                            <?php echo date('d.m.Y', strtotime($track['upload_date'])); ?> •
                            <?php echo (int)($track['plays'] ?? 0); ?> просл.
                        </p>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php elseif ($currentUserId && empty($followingIds)): ?>
    <p style="margin:20px 0; color:#666;">
        Вы еще ни на кого не подписаны. Зайдите в профили других пользователей и нажмите «Подписаться» 🙂
    </p>
<?php endif; ?>


        <h3 style="margin-top: 40px;"><?php echo empty($myTracks) ? '📻 Новые треки' : '📻 Все треки'; ?></h3>
        <div class="tracks-grid">
            <?php if (empty($allTracks)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 60px; color: #666;">
                    <?php if (!empty($_GET['search']) || !empty($_GET['genres'])): ?>
                        <h3>😔 Ничего не найдено</h3>
                        <p>Попробуйте другие фильтры</p>
                    <?php else: ?>
                        <h3>🎵 Пока пусто</h3>
                        <?php if (!empty($_SESSION['user_id'])): ?>
                            <a href="upload.php" class="upload-btn" style="display: inline-block; margin-top: 20px;">Загрузить первый трек!</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($allTracks as $track): ?>
                <a href="track.php?id=<?php echo $track['id']; ?>" class="track-link">
                    <div class="track-card">
                        <?php $hasCover = !empty($track['cover_path']) && file_exists($track['cover_path']); ?>
                        <div class="track-cover <?php echo !$hasCover ? 'no-cover' : ''; ?>" 
                             style="<?php echo $hasCover ? 'background-image: url(' . htmlspecialchars($track['cover_path']) . '); background-size: cover; background-position: center;' : ''; ?>">
                        </div>
                        <div class="track-info">
                            <h3><?php echo htmlspecialchars($track['title']); ?></h3>
                            
                        
                            <?php 
                            $trackGenres = json_decode($track['genres'], true) ?? [];
                            if (!empty($trackGenres)): 
                            ?>
                            <div class="track-genres">
                                <?php foreach (array_slice($trackGenres, 0, 3) as $genre): ?>
                                    <span class="genre-badge"><?php echo ucfirst($genre); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <p class="track-author"><?php echo htmlspecialchars($track['author_display']); ?></p>
                            <p class="track-meta">
                                <?php echo date('d.m.Y', strtotime($track['upload_date'])); ?> • 
                                <?php echo number_format($track['plays'] ?? 0); ?> просл.
                            </p>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<footer class="footer">
    <div class="footer-inner">
        <span>© 2026 SinFY</span>
        <a href="support.php" class="footer-link">Поддержка</a>
    </div>
</footer>

<script src="scriptIndex.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    const clearBtn = document.getElementById('clearFilters');
    
    function applyFilters() {
        const search = searchInput.value.trim();
        const genres = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        
        const url = new URL(window.location.pathname, window.location.origin);
        if (search) url.searchParams.set('search', search);
        if (genres.length > 0) {
            url.searchParams.set('genres', genres.join(','));
        }
        window.location.href = url.toString();
    }
    
    searchInput.addEventListener('keypress', e => {
        if (e.key === 'Enter') applyFilters();
    });
    
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 800);
    });
    
    checkboxes.forEach(cb => cb.addEventListener('change', applyFilters));
    
    if (clearBtn) {
        clearBtn.addEventListener('click', () => window.location.href = 'index.php');
    }
});
</script>
</body>
</html>
