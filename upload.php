<?php
require 'db.php'; 

// Проверка авторизации
if (empty($_SESSION['user_id'])) {
    $_SESSION['auth_error'] = 'Для загрузки треков нужно войти в аккаунт';
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$host = '127.0.0.1';
$dbname = 'maxusic_bd';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Валидация
    if (strlen($title) < 2) {
        $message = 'Название трека должно быть не короче 2 символов';
    } elseif (strlen($author) < 2) {
        $message = 'Имя автора должно быть не короче 2 символов';
    } else {
        // Папки для файлов
        $uploads_dir = 'uploads/';
        $audio_dir = $uploads_dir . 'audio/';
        $covers_dir = $uploads_dir . 'covers/';

        // Создаём папки
        if (!file_exists($uploads_dir)) mkdir($uploads_dir, 0777, true);
        if (!file_exists($audio_dir)) mkdir($audio_dir, 0777, true);
        if (!file_exists($covers_dir)) mkdir($covers_dir, 0777, true);

        $audio_path = '';
        $cover_path = '';

        // Загрузка аудио
        if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
            $audio_size = $_FILES['audio']['size'];
            $audio_ext = strtolower(pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION));
            
            // Проверка формата и размера (до 50MB)
            if (in_array($audio_ext, ['mp3', 'wav', 'ogg', 'm4a']) && $audio_size <= 50 * 1024 * 1024) {
                $audio_name = $userId . '_' . time() . '_' . uniqid() . '.' . $audio_ext;
                $audio_path = $audio_dir . $audio_name;
                
                if (move_uploaded_file($_FILES['audio']['tmp_name'], $audio_path)) {
                    // Аудио успешно сохранено
                } else {
                    $message = 'Ошибка сохранения аудио файла';
                }
            } else {
                $message = 'Неверный формат или размер аудио (макс. 50MB, MP3/WAV/OGG/M4A)';
            }
        } else {
            $message = 'Выберите аудио файл';
        }

        // Загрузка обложки
        if (empty($message) && isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $cover_size = $_FILES['cover']['size'];
            $cover_ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
            
            // Проверка формата и размера (до 5MB)
            if (in_array($cover_ext, ['jpg', 'jpeg', 'png', 'webp']) && $cover_size <= 5 * 1024 * 1024) {
                $cover_name = $userId . '_' . time() . '_' . uniqid() . '.' . $cover_ext;
                $cover_path = $covers_dir . $cover_name;
                move_uploaded_file($_FILES['cover']['tmp_name'], $cover_path);
            }
        }

        // Сохраняем в БД с user_id
        if (empty($message) && $audio_path) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO tracks (user_id, title, author, description, audio_path, cover_path) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$userId, $title, $author, $description, $audio_path, $cover_path]);
                
                $message = "✅ Трек '<strong>$title</strong>' успешно загружен!";
            } catch (PDOException $e) {
                $message = 'Ошибка сохранения в БД: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Загрузка трека - Maxusic</title>
    <link rel="stylesheet" href="upload.css">
    <link rel="icon" href="note.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tiny5&display=swap" rel="stylesheet">
</head>
<body>
<header class="header">
    <h1>Загрузка трека</h1>
    <div class="header-right">
        <a href="profile.php" class="upload-btn">Мой профиль</a>
        <a href="index.php" class="back-link">← На главную</a>
    </div>
</header>

<main class="page">
    <?php if ($message): ?>
        <div class="message success">
            <?php echo $message; ?>
            <div style="margin-top: 15px;">
                <a href="upload.php" class="btn-clear">Загрузить ещё</a>
                <a href="profile.php" class="btn-clear secondary">Посмотреть профиль</a>
            </div>
        </div>
    <?php else: ?>
        <form class="upload-form" method="post" enctype="multipart/form-data" action="upload.php">
           
            <div class="form-group">
                <label for="title">Название трека <span class="required">*</span></label>
                <input type="text" id="title" name="title" required maxlength="100">
            </div>

            <div class="form-group">
                <label for="author">Автор / исполнитель <span class="required">*</span></label>
                <input type="text" id="author" name="author" required maxlength="100">
            </div>

            <div class="form-group">
                <label for="description">Описание</label>
                <textarea id="description" name="description" rows="4" 
                          placeholder="Краткое описание, настроение, теги..." maxlength="500"></textarea>
            </div>

            <div class="form-group">
                <label for="audio">Файл музыки <span class="required">*</span></label>
                <input type="file" id="audio" name="audio" accept="audio/*" required>
                <small>MP3, WAV, OGG, M4A — до 50MB</small>
            </div>

            <div class="form-group preview-group">
                <div class="preview-left">
                    <div class="form-group">
                        <label for="cover">Обложка (картинка)</label>
                        <input type="file" id="cover" name="cover" accept="image/*">
                        <small>JPG, PNG, WebP — до 5MB</small>
                    </div>
                </div>

                <div class="preview-right">
                    <span class="preview-label">Предпросмотр карточки трека</span>
                    <div class="track-card">
                        <img id="coverPreview" src="" alt="Обложка">
                        <div class="track-info">
                            <h2 id="previewTitle">Название трека</h2>
                            <p id="previewAuthor" class="track-author">Автор / Исполнитель</p>
                            <p id="previewDescription" class="track-description">
                                Здесь будет краткое описание трека.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit">Загрузить трек</button>
            </div>
        </form>
    <?php endif; ?>
</main>

<script src="upload.js"></script>
</body>
</html>
