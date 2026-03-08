<?php
require_once 'db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $genres = $_POST['genres'] ?? []; 
    
    if (empty($title) || empty($author)) {
        $error = 'Заполните название и автора!';
    } elseif (!empty($_FILES['audio']['name']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
        $audioTmp = $_FILES['audio']['tmp_name'];
        $audioExt = strtolower(pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($audioExt, ['mp3', 'wav', 'ogg'])) {
            $error = 'Только MP3, WAV, OGG';
        } else {
            $audioName = uniqid('audio_') . '.' . $audioExt;
            $audioPath = 'uploads/audio/' . $audioName;

            if (!is_dir('uploads/audio')) mkdir('uploads/audio', 0777, true);
            if (!is_dir('uploads/covers')) mkdir('uploads/covers', 0777, true);
            
            if (move_uploaded_file($audioTmp, $audioPath)) {
     
                $coverPath = null;
                if (!empty($_FILES['cover']['name']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
                    $coverTmp = $_FILES['cover']['tmp_name'];
                    $coverExt = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
                    
                    if (in_array($coverExt, ['jpg', 'jpeg', 'png'])) {
                        $coverName = uniqid('cover_') . '.' . $coverExt;
                        $coverPath = 'uploads/covers/' . $coverName;
                        move_uploaded_file($coverTmp, $coverPath);
                    }
                }
                
        
                $genresJson = !empty($genres) ? json_encode($genres) : null;
                
                $stmt = $pdo->prepare("
                    INSERT INTO tracks (user_id, title, author, description, genres, audio_path, cover_path, upload_date, is_approved) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 0)
                ");
                $stmt->execute([$userId, $title, $author, $description, $genresJson, $audioPath, $coverPath]);
                
                $success = '✅ Трек загружен! Ожидает модерации.';
            } else {
                $error = 'Ошибка сохранения аудио';
            }
        }
    } else {
        $error = 'Загрузите аудиофайл (MP3, WAV, OGG)';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Загрузить трек - Maxusic</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/note.png">
    <link href="https://fonts.googleapis.com/css2?family=Tiny5&display=swap" rel="stylesheet">
    <style>
        .upload-page { max-width: 600px; margin: 0 auto; padding: 20px; }
        .upload-container { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 12px; font-size: 16px; box-sizing: border-box; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #d32f2f; }
        .genres-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 12px; margin-top: 12px; }
        .genre-tag { display: flex; align-items: center; gap: 8px; padding: 12px; background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 20px; cursor: pointer; transition: all 0.2s; }
        .genre-tag:hover { background: #d32f2f; color: white; border-color: #d32f2f; }
        .genre-tag input:checked + span { color: #d32f2f; font-weight: 600; }
        .upload-btn { width: 100%; background: linear-gradient(135deg, #d32f2f, #b71c1c); color: white; border: none; padding: 16px; border-radius: 25px; font-size: 18px; font-weight: 600; cursor: pointer; transition: transform 0.2s; }
        .upload-btn:hover { transform: translateY(-2px); }
        .success-msg, .error-msg { padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; font-weight: 600; }
        .success-msg { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-msg { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .upload-another-btn { display: inline-block; background: #28a745; color: white; padding: 12px 24px; border-radius: 25px; text-decoration: none; margin-top: 20px; }
        @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<header class="header">
    <a href="index.php" class="back-btn">← На главную</a>
    <h1>SinFY</h1>
</header>

<main class="upload-page">
    <div class="upload-container">
        <?php if (!empty($success)): ?>
            <div class="success-msg"><?php echo htmlspecialchars($success); ?></div>
            <a href="upload.php" class="upload-another-btn">📤 Загрузить ещё один</a>
            <a href="index.php" class="upload-another-btn" style="background: #007bff; margin-left: 10px;">🏠 На главную</a>
        <?php else: ?>
            <h2>📤 Загрузить новый трек</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
              
                <div class="form-row">
                    <div class="form-group">
                        <label>🎵 Название трека</label>
                        <input type="text" name="title" required 
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                               placeholder="Например: Ночной город">
                    </div>
                    <div class="form-group">
                        <label>👤 Исполнитель</label>
                        <input type="text" name="author" required 
                               value="<?php echo htmlspecialchars($_POST['author'] ?? ''); ?>"
                               placeholder="Твоё имя или псевдоним">
                    </div>
                </div>

          
                <div class="form-group">
                    <label>📝 Описание (опционально)</label>
                    <textarea name="description" rows="3" 
                              placeholder="О треке, настроение, история создания..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

      
                <div class="form-group">
                    <label>🎼 Жанры (выберите до 5)</label>
                    <div class="genres-grid">
                        <label class="genre-tag"><input type="checkbox" name="genres[]" value="rock"><span>Rock</span></label>
                        <label class="genre-tag"><input type="checkbox" name="genres[]" value="pop"><span>Pop</span></label>
                        <label class="genre-tag"><input type="checkbox" name="genres[]" value="hiphop"><span>Hip-Hop</span></label>
                        <label class="genre-tag"><input type="checkbox" name="genres[]" value="electronic"><span>Electronic</span></label>
                        <label class="genre-tag"><input type="checkbox" name="genres[]" value="jazz"><span>Jazz</span></label>
                        <label class="genre-tag"><input type="checkbox" name="genres[]" value="rap"><span>Rap</span></label>
                        <label class="genre-tag"><input type="checkbox" name="genres[]" value="rnb"><span>R&B</span></label>
                        <label class="genre-tag"><input type="checkbox" name="genres[]" value="techno"><span>Techno</span></label>
                        <label class="genre-tag"><input type="checkbox" name="genres[]" value="classical"><span>Classical</span></label>
                        <label class="genre-tag"><input type="checkbox" name="genres[]" value="metal"><span>Metal</span></label>
                    </div>
                </div>

         
                <div class="form-group">
                    <label>🎧 Аудиофайл (MP3, WAV, OGG)</label>
                    <input type="file" name="audio" accept="audio/*" required>
                    <small style="color: #666;">Максимум 50MB</small>
                </div>

        
                <div class="form-group">
                    <label>🖼️ Обложка (JPG, PNG) — опционально</label>
                    <input type="file" name="cover" accept="image/jpeg,image/png">
                    <small style="color: #666;">Рекомендуемый размер: 500x500px</small>
                </div>

                <button type="submit" class="upload-btn">🚀 Загрузить трек</button>
            </form>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
