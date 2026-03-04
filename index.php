<?php
require_once 'db.php'; 

$host = '127.0.0.1';
$dbname = 'maxusic_bd';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SinFY</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="note.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tiny5&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
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
                        <a href="profile.php" class="dropdown-item">Профиль</a>
                        <a href="settings.php" class="dropdown-item">Настройки</a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item">Выйти</a>
                    <?php else: ?>
                        <a href="login.php" class="dropdown-item">Войти/Регистрация</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <div class="main-layout">
        <aside class="genres-sidebar">
            <h3>Жанры</h3>
            <ul class="checkbox-list">
                <li><label><input type="checkbox" name="genre" value="rock"> Rock</label></li>
                <li><label><input type="checkbox" name="genre" value="pop"> Pop</label></li>
                <li><label><input type="checkbox" name="genre" value="hiphop"> Hip-Hop</label></li>
                <li><label><input type="checkbox" name="genre" value="electronic"> Electronic</label></li>
                <li><label><input type="checkbox" name="genre" value="jazz"> Jazz</label></li>
            </ul>
        </aside>
        
        <main class="music-content">
            <h2>Музыка</h2>
            
        
            <?php if (!empty($_SESSION['user_id'])): 
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
            ?>
                <?php if (!empty($myTracks)): ?>
                <h3 style="margin: 20px 0 10px 0; color: #d32f2f;">🎵 Твои треки</h3>
                <div class="tracks-grid">
                    <?php foreach ($myTracks as $track): ?>
                        <a href="track.php?id=<?php echo $track['id']; ?>" class="track-link">
                            <div class="track-card">
                                <?php 
                                $hasCover = !empty($track['cover_path']) && file_exists($track['cover_path']);
                                ?>
                                <div class="track-cover" 
                                     style="<?php echo $hasCover ? 'background-image: url(' . htmlspecialchars($track['cover_path']) . '); background-size: cover; background-position: center;' : ''; ?>"
                                     <?php echo !$hasCover ? 'class="no-cover"' : ''; ?>>
                                </div>
                                
                                <div class="track-info">
                                    <h3><?php echo htmlspecialchars($track['title']); ?></h3>
                                    <p class="track-author"><?php echo htmlspecialchars($track['author_login'] ?? $track['author']); ?></p>
                                    <p class="track-meta"><?php echo date('d.m.Y', strtotime($track['upload_date'])); ?></p>
                                    <audio controls preload="metadata" class="track-player">
                                        <source src="<?php echo htmlspecialchars($track['audio_path']); ?>" type="audio/mpeg">
                                    </audio>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>

  
            <h3 style="margin-top: 40px;">📻 Новые треки</h3>
            <div class="tracks-grid">
                <?php
            
                $stmt = $pdo->query('
                    SELECT t.*, 
                           COALESCE(u.login, t.author) as author_display
                    FROM tracks t 
                    LEFT JOIN users u ON t.user_id = u.id 
                    ORDER BY t.upload_date DESC 
                    LIMIT 20
                ');
                
                if ($stmt->rowCount() === 0): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #666;">
                        <p>Треков пока нет</p>
                        <?php if (!empty($_SESSION['user_id'])): ?>
                            <a href="upload.php" class="upload-btn">Загрузить первый!</a>
                        <?php endif; ?>
                    </div>
                <?php else: 
                    while ($track = $stmt->fetch(PDO::FETCH_ASSOC)): 
                        $hasCover = !empty($track['cover_path']) && file_exists($track['cover_path']);
                ?>
                  
                        <a href="track.php?id=<?php echo $track['id']; ?>" class="track-link">
                            <div class="track-card">
                                <div class="track-cover" 
                                     style="<?php echo $hasCover ? 'background-image: url(' . htmlspecialchars($track['cover_path']) . '); background-size: cover; background-position: center;' : ''; ?>"
                                     <?php echo !$hasCover ? 'class="no-cover"' : ''; ?>>
                                </div>
                                
                                <div class="track-info">
                                    <h3><?php echo htmlspecialchars($track['title']); ?></h3>
                                    <p class="track-author"><?php echo htmlspecialchars($track['author_display']); ?></p>
                                    <p class="track-meta">
                                        <?php echo date('d.m.Y', strtotime($track['upload_date'])); ?> • 
                                        <?php echo $track['plays'] ?? 0; ?> просл.
                                    </p>
                                    <audio controls preload="metadata" class="track-player">
                                        <source src="<?php echo htmlspecialchars($track['audio_path']); ?>" type="audio/mpeg">
                                    </audio>
                                </div>
                            </div>
                        </a>
                <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <footer class="footer">
        <div class="footer-inner">
            <span>© 2026 SinFY
            </span>
            <a href="support.html" class="footer-link">Поддержка</a>
        </div>
        <script src="scriptIndex.js"></script>
    </footer>
</body>
</html>
