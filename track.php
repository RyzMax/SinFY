<?php
require_once 'db.php';
$pdo = getDb();
session_start();

$trackId = (int)($_GET['id'] ?? 0);
if (!$trackId) die('Трек не найден');

function getCurrentUser($pdo) {
    if (empty($_SESSION['user_id'])) return null;
    $stmt = $pdo->prepare('SELECT id, login, avatar FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function buildChildren($lookup, $parentId) {
    if (!is_array($lookup) || empty($lookup)) {
        return [];
    }
    
    $children = [];
    foreach ($lookup as $id => $comment) {
        if (isset($comment['parent_id']) && $comment['parent_id'] == $parentId) {
            $replies = buildChildren($lookup, $id);
            $comment['replies'] = $replies;
            $comment['replies_count'] = count($replies);
            $children[] = $comment;
        }
    }
    return $children;
}

function buildCommentTree($pdo, $trackId) {
    if (!$pdo) return [];
    
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, COALESCE(u.login, c.username) as username_display, 
                   COALESCE(u.avatar, 'avatar.jpg') as commenter_avatar,
                   COALESCE(u.id, 0) as user_id
            FROM comments c LEFT JOIN users u ON c.user_id = u.id 
            WHERE c.track_id = ? AND c.is_approved = 1
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$trackId]);
        $allComments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("buildCommentTree error: " . $e->getMessage());
        return [];
    }
    
    if (empty($allComments) || !is_array($allComments)) {
        return [];
    }
    
    $lookup = [];
    foreach ($allComments as $comment) {
        if (!isset($comment['id'])) continue;
        
        $id = (int)$comment['id'];
        $parentId = ($comment['parent_id'] === '' || $comment['parent_id'] === null || $comment['parent_id'] == '0') 
            ? 0 : (int)$comment['parent_id'];
        
        $lookup[$id] = array_merge($comment, [
            'parent_id' => $parentId,
            'replies' => [],
            'replies_count' => 0
        ]);
    }
    
    if (empty($lookup)) {
        return [];
    }
    
    $tree = [];
    foreach ($lookup as $id => $comment) {
        if (($comment['parent_id'] ?? 0) == 0) {
            $comment['replies'] = buildChildren($lookup, $id);
            $comment['replies_count'] = count($comment['replies']);
            $tree[] = $comment;
        }
    }
    
    return $tree;
}

function renderComment($comment, $currentUser, $trackId, $depth = 0) {
    $repliesCount = count($comment['replies'] ?? []);
    $isOwnComment = $currentUser && $currentUser['id'] == ($comment['user_id'] ?? 0);
    ?>
    <div class="comment-item depth-<?php echo $depth; ?> <?php echo $isOwnComment ? 'my-comment' : ''; ?>" 
         data-comment-id="<?php echo $comment['id']; ?>">
        
        <div class="comment-header">
            <img src="<?php echo htmlspecialchars($comment['commenter_avatar']); ?>" 
                 class="comment-avatar" alt="Аватар" loading="lazy">
            <div class="comment-info">
                <strong><?php echo htmlspecialchars($comment['username'] ?? $comment['username_display']); ?></strong>
                <?php if ($isOwnComment): ?>
                    <span class="own-label">(это вы)</span>
                <?php endif; ?>
                <span class="comment-date">
                    <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?>
                </span>
            </div>
        </div>
        
        <div class="comment-text">
            <?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?>
        </div>
        
        <?php if ($repliesCount > 0): ?>
            <div class="replies-count"><?php echo $repliesCount; ?> ответов</div>
        <?php endif; ?>
        
        <div class="comment-actions">
            <button class="reply-btn" onclick="showReplyForm(<?php echo $comment['id']; ?>)">
                💬 Ответить
            </button>
            
            <?php if ($isOwnComment): ?>
                <button class="delete-btn" onclick="deleteComment(<?php echo $comment['id']; ?>, <?php echo $trackId; ?>)">
                    🗑️ Удалить
                </button>
            <?php endif; ?>
            
            <?php if ($currentUser && $currentUser['id'] != ($comment['user_id'] ?? 0)): ?>
                <form method="POST" style="display: inline-block;" 
                      onsubmit="return confirm('🚨 Пожаловаться на комментарий?\n\nДействие нельзя отменить!')">
                    <input type="hidden" name="report_comment_id" value="<?php echo $comment['id']; ?>">
                    <button type="submit" class="report-btn">🚨 Пожаловаться</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="reply-form" id="reply-<?php echo $comment['id']; ?>" style="display: none;">
            <form method="POST" class="reply-form-inner">
                <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                <textarea name="comment_text" placeholder="Ответ @<?php echo htmlspecialchars($comment['username'] ?? $comment['username_display']); ?>..." 
                          required rows="3" maxlength="1000"></textarea>
                <div class="reply-buttons">
                    <button type="submit" class="comment-btn small">💬 Ответить</button>
                    <button type="button" class="cancel-btn" onclick="hideReplyForm(<?php echo $comment['id']; ?>)">
                        ❌ Отмена
                    </button>
                </div>
            </form>
        </div>
        

        <?php if (!empty($comment['replies'])): ?>
            <div class="replies-container">
                <?php foreach ($comment['replies'] as $reply): ?>
                    <?php renderComment($reply, $currentUser, $trackId, $depth + 1); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}


$currentUser = getCurrentUser($pdo);
$commentError = null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_comment_id'])) {
        $deleteId = (int)$_POST['delete_comment_id'];
        if ($currentUser) {
            $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?")
                ->execute([$deleteId, $currentUser['id']]);
        }
        header("Location: track.php?id=$trackId#comments");
        exit;
    }
    
    if (isset($_POST['report_comment_id'])) {
        $reportId = (int)$_POST['report_comment_id'];
        if ($currentUser) {
            $pdo->prepare("INSERT IGNORE INTO reports (comment_id, user_id, created_at) VALUES (?, ?, NOW())")
                ->execute([$reportId, $currentUser['id']]);
        }
        header("Location: track.php?id=$trackId#comments");
        exit;
    }
    
    if (isset($_POST['comment_text'])) {
        $commentText = trim($_POST['comment_text']);
        if (strlen($commentText) >= 3 && strlen($commentText) <= 1000) {
            $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
            $userId = $currentUser ? $currentUser['id'] : null;
            $username = $currentUser ? $currentUser['login'] : trim($_POST['username'] ?? 'Гость');
            
            $pdo->prepare("
                INSERT INTO comments (track_id, parent_id, user_id, username, comment_text, is_approved, created_at) 
                VALUES (?, ?, ?, ?, ?, 1, NOW())
            ")->execute([$trackId, $parentId, $userId, $username, $commentText]);
            
            header("Location: track.php?id=$trackId#comments");
            exit;
        } else {
            $commentError = 'Комментарий: 3-1000 символов';
        }
    }
}


try {
    $stmt = $pdo->prepare('
        SELECT t.*, 
               COALESCE(u.login, t.author) as author_display,
               u.login as author_login, 
               u.avatar as author_avatar, 
               u.about as author_about
        FROM tracks t LEFT JOIN users u ON t.user_id = u.id 
        WHERE t.id = ?
    ');
    $stmt->execute([$trackId]);
    $track = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$track) die('Трек не найден');
    
    $comments = buildCommentTree($pdo, $trackId);
} catch (Exception $e) {
    die('Ошибка: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($track['title'] ?? 'Трек'); ?> - SinFY</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="../assets/images/note.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Tiny5&display=swap" rel="stylesheet">
    
    <style>
     
        .comment-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px solid #eee;
        }
        
        .reply-btn, .delete-btn, .report-btn {
            padding: 8px 18px;
            border: none;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .reply-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .reply-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6); }
        
        .delete-btn {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
        }
        .delete-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255, 107, 107, 0.6); }
        
        .report-btn {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 152, 0, 0.4);
        }
        .report-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255, 152, 0, 0.6); }
        
        .reply-form textarea { 
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; 
            resize: vertical; font-family: inherit; font-size: 15px; min-height: 80px; 
        }
        .reply-buttons { display: flex; gap: 10px; margin-top: 12px; }
        .comment-btn.small { 
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); 
            color: white; border: none; padding: 10px 20px; border-radius: 20px; 
            font-size: 14px; cursor: pointer; flex: 1;
        }
        .cancel-btn { 
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); 
            color: #333; border: none; padding: 10px 20px; border-radius: 20px; 
            font-size: 14px; cursor: pointer; flex: 1;
        }
        
        .comment-item { 
            background: white; padding: 24px; border-radius: 16px; margin-bottom: 20px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s ease;
        }
        .comment-item:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.12); transform: translateY(-2px); }
        .comment-item.depth-1 { margin-left: 40px; background: linear-gradient(90deg, #f8f9ff 0%, white 100%); border-left: 4px solid #667eea; }
        .comment-item.depth-2 { margin-left: 70px; background: linear-gradient(90deg, #f0f4ff 0%, #f8f9ff 100%); border-left: 4px solid #4facfe; }
        .comment-avatar { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .replies-count { color: #667eea; font-weight: 600; font-size: 14px; margin: 12px 0; padding: 4px 12px; background: rgba(102, 126, 234, 0.1); border-radius: 20px; display: inline-block; }
        .player-card {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 16px;
    align-items: center;
    background: linear-gradient(135deg, #1f1c2c 0%, #928dab 100%);
    padding: 16px 20px;
    border-radius: 18px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    color: #fff;
    margin-bottom: 24px;
}

.player-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.player-cover {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    background-size: cover;
    background-position: center;
    background-color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
}

.player-info {
    display: flex;
    flex-direction: column;
}
.player-title {
    font-weight: 600;
    font-size: 16px;
}
.player-artist {
    font-size: 13px;
    opacity: 0.8;
}

.player-center {
    padding: 0 10px;
}

.player-controls {
    display: grid;
    grid-template-columns: 32px auto 1fr auto;
    gap: 8px;
    align-items: center;
}

.player-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: #ffffff22;
    color: #fff;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: 0.2s;
}
.player-btn:hover {
    background: #ffffff44;
    transform: scale(1.05);
}

#seek {
    width: 100%;
    accent-color: #ffca28;
    cursor: pointer;
}
.player-time {
    font-size: 12px;
    opacity: 0.9;
}

.player-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
}

.player-volume {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 14px;
}
#volume {
    width: 80px;
    accent-color: #ffca28;
    cursor: pointer;
}

.download-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 13px;
    background: #ffca28;
    color: #1f1c2c;
    text-decoration: none;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    transition: 0.2s;
}
.download-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.35);
}
.like-btn {
    width: 56px; height: 56px; 
    border-radius: 50%; border: none;
    background: #f0f0f0; color: #666;
    font-size: 24px; cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.like-btn:hover { transform: scale(1.1); }
.like-btn.liked {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52) !important;
    color: white !important;
    box-shadow: 0 6px 20px rgba(255,107,107,0.4);
}
.likes-section { 
    margin: 30px 0; padding: 25px; 
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 20px; border: 2px solid #e9ecef;
    display: flex; align-items: center; gap: 20px;
    max-width: 400px;
 }


@media (max-width: 700px) {
    .player-card {
        grid-template-columns: 1fr;
        row-gap: 12px;
    }
    .player-right {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}

    </style>
</head>

<body>
<header class="header">
    <a href="index.php" class="back-btn">← Все треки</a>
    <h1>SinFY</h1>
    <div class="header-right">
        <?php if ($currentUser): ?>
            <a href="upload.php" class="upload-btn">Загрузить</a>
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
                        <a href="dashboard.php" class="dropdown-item">📊 Дашборд</a>
                        <a href="admin_reports.php" class="dropdown-item">🔍 Модерация комментариев</a>
                    <?php endif; ?>
                    <a href="profile.php" class="dropdown-item">👤 Профиль</a>
                    <a href="settings.php" class="dropdown-item">⚙️ Настройки</a>
                    <div class="dropdown-divider"></div>
                    <a href="../logout.php" class="dropdown-item">🚪 Выйти</a>
                <?php else: ?>
                    <a href="login.php" class="dropdown-item">🔐 Войти/Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </div>
</header>

<main class="track-page">
    <section class="track-main">
        <div class="track-artwork <?php echo empty($track['cover_path']) ? 'no-cover' : ''; ?>" 
             style="<?php echo !empty($track['cover_path']) ? 'background-image: url(' . htmlspecialchars($track['cover_path']) . ');' : ''; ?>">
            <?php if (empty($track['cover_path'])): ?><div class="no-cover">🎵</div><?php endif; ?>
        </div>
        <div class="track-details">
            <h1><?php echo htmlspecialchars($track['title']); ?></h1>
            <p class="track-author"><?php echo htmlspecialchars($track['author_display']); ?></p>
            <?php if (!empty($track['description'])): ?>
                <p class="track-description"><?php echo nl2br(htmlspecialchars($track['description'])); ?></p>
            <?php endif; ?>
            <div class="track-meta">
                <span>⏱️ <?php echo date('d.m.Y H:i', strtotime($track['upload_date'])); ?></span>
                <span>▶️ <?php echo (int)($track['plays'] ?? 0); ?> просл.</span>
            </div>
            <div class="player-card">


    <div class="player-center">
        <audio id="audio-player" src="<?php echo htmlspecialchars($track['audio_path']); ?>"></audio>

        <div class="player-controls">
            <button id="play-pause" class="player-btn">▶️</button>
            <span id="current-time" class="player-time">0:00</span>
            <input id="seek" type="range" min="0" value="0" step="0.1">
            <span id="duration" class="player-time">0:00</span>
        </div>
    </div>

    <div class="player-right">
        <div class="player-volume">
            <span>🔊</span>
            <input id="volume" type="range" min="0" max="1" step="0.01" value="1">
        </div>

    
        <a href="<?php echo htmlspecialchars($track['audio_path']); ?>" 
           download="<?php echo htmlspecialchars($track['title'] . '.mp3'); ?>" 
           class="download-btn">
            ⬇️ Скачать
        </a>
    </div>
</div>

        </div>
    </section>
<div class="likes-section">
    <?php 
    $isLikedUser = false;  
    $likesCount = 0;       
    ?>
    
    <button class="like-btn <?php echo $isLikedUser ? 'liked' : ''; ?>" 
            data-track-id="<?php echo $track['id']; ?>"
            onclick="toggleLike(<?php echo $track['id']; ?>)">
        ❤️
    </button>
    
   <span class="likes-count">
<?php 

$countReal = $pdo->query("SELECT COUNT(*) FROM likes WHERE track_id = {$track['id']}")->fetchColumn();
echo number_format($countReal);
?>
</span>


    <span style="color: #666;">лайков</span>
</div>
    <?php if ($track['user_id'] && !empty($track['author_login'])): ?>
    <section class="track-author-section">
        <h2>Автор трека</h2>
        <a href="profile.php?user=<?php echo $track['user_id']; ?>" class="author-card">
            <div class="author-avatar" style="background-image: url('<?php echo htmlspecialchars($track['author_avatar'] ?? 'avatar.jpg'); ?>');"></div>
            <div class="author-info">
                <h3>@<?php echo htmlspecialchars($track['author_login']); ?></h3>
                <p><?php echo htmlspecialchars($track['author_about'] ?? 'Инди-музыкант'); ?></p>
            </div>
        </a>
    </section>
    <?php endif; ?>

    <div class="comments-section" id="comments">
        <h2 style="margin-bottom: 25px; color: #333;">💬 Комментарии (<?php echo count($comments); ?>)</h2>
        
        <?php if ($commentError): ?>
            <div class="error-msg"><?php echo htmlspecialchars($commentError); ?></div>
        <?php endif; ?>
        
        <form method="POST" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 30px;">
            <?php if ($currentUser): ?>
                <textarea name="comment_text" placeholder="💭 Напишите комментарий..." rows="3" required maxlength="1000"></textarea>
            <?php else: ?>
                <input type="text" name="username" placeholder="Ваше имя" maxlength="50" required 
                       style="width: 100%; margin-bottom: 12px; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                <textarea name="comment_text" placeholder="💭 Ваш комментарий..." rows="3" required maxlength="1000"></textarea>
            <?php endif; ?>
            <button type="submit" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 14px 28px; border-radius: 25px; font-size: 16px; font-weight: 600; cursor: pointer; width: 100%; margin-top: 15px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                💬 Опубликовать комментарий
            </button>
        </form>
        
        <div class="comments-list">
            <?php if (empty($comments)): ?>
                <div class="no-comments" style="text-align: center; color: #666; padding: 60px 20px; font-style: italic;">
                    Пока нет комментариев. Будьте первым! 👆
                </div>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <?php renderComment($comment, $currentUser, $trackId); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
function showReplyForm(commentId) {
    const form = document.getElementById('reply-' + commentId);
    if (form) {
        form.style.display = 'block';
        const textarea = form.querySelector('textarea');
        if (textarea) {
            textarea.focus();
            textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}

function hideReplyForm(commentId) {
    document.getElementById('reply-' + commentId).style.display = 'none';
}

function deleteComment(commentId, trackId) {
    if (confirm('🗑️ Удалить ваш комментарий?\n\nЭто действие нельзя отменить!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_comment_id';
        input.value = commentId;
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const userBtn = document.getElementById('userBtn');
    const userDropdown = document.getElementById('userDropdown');
    
    if (userBtn && userDropdown) {
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
        });
        
        document.addEventListener('click', function() {
            userDropdown.classList.remove('active');
        });
    }
});


document.addEventListener('DOMContentLoaded', () => {
    const audio   = document.getElementById('audio-player');
    const playBtn = document.getElementById('play-pause');
    const seek    = document.getElementById('seek');
    const volume  = document.getElementById('volume');
    const curTime = document.getElementById('current-time');
    const durTime = document.getElementById('duration');

    if (!audio) {
        console.log('Audio element not found');
        return;
    }

    let isDragging = false;

    function formatTime(sec) {
        const m = Math.floor(sec / 60);
        const s = Math.floor(sec % 60).toString().padStart(2, '0');
        return `${m}:${s}`;
    }


    audio.addEventListener('loadedmetadata', () => {
        seek.max = audio.duration;
        durTime.textContent = formatTime(audio.duration);
    });


    audio.addEventListener('timeupdate', () => {
        if (!isDragging) {
            seek.value = audio.currentTime;
            curTime.textContent = formatTime(audio.currentTime);
        }
    });


    seek.addEventListener('mousedown', () => isDragging = true);
    document.addEventListener('mouseup', () => isDragging = false);
    
    seek.addEventListener('input', () => {
        audio.currentTime = seek.value;
        curTime.textContent = formatTime(seek.value);
    });


    volume.addEventListener('input', () => {
        audio.volume = volume.value;
    });


    playBtn.addEventListener('click', async () => {
        try {
            if (audio.paused) {
                await audio.play();
                playBtn.textContent = '⏸️';
            } else {
                audio.pause();
                playBtn.textContent = '▶️';
            }
        } catch (err) {
            console.log('Play error:', err);
        }
    });
});
function toggleLike(trackId) {
    if (!<?php echo isset($currentUser) ? 'true' : 'false'; ?>) {
        alert('🔐 Войдите для лайков!');
        return;
    }
    
    const btn = event.target;
    const countEl = document.querySelector('.likes-count');
    const wasLiked = btn.classList.contains('liked');
    
    fetch('like.php', {  
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'track_id=' + trackId
    })
    .then(response => response.text())
    .then(text => {
        console.log('Ответ:', text);  
        const data = JSON.parse(text);
        
        if (data.error) {
            alert('❌ ' + data.error);
            return;
        }
        
        btn.classList.toggle('liked', data.liked);
        countEl.textContent = data.count.toLocaleString();
        
        if (data.liked && !wasLiked) {
            btn.style.transform = 'scale(1.3)';
            setTimeout(() => btn.style.transform = '', 200);
        }
    })
    .catch(err => {
        console.error('Like error:', err);
        alert('❌ Ошибка');
    });
}

</script>
</body>
</html>
