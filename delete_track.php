<?php
require 'db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$trackId = $_POST['track_id'] ?? 0;

if (!$trackId || !is_numeric($trackId)) {
    $_SESSION['error'] = 'Неверный ID трека';
    header('Location: profile.php');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM tracks WHERE id = ? AND user_id = ?');
    $stmt->execute([$trackId, $userId]);
    $track = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$track) {
        $_SESSION['error'] = 'Трек не найден или не принадлежит вам';
        header('Location: profile.php');
        exit;
    }

   
    if (file_exists($track['audio_path'])) {
        unlink($track['audio_path']);
    }
    if (file_exists($track['cover_path'])) {
        unlink($track['cover_path']);
    }

   
    $stmt = $pdo->prepare('DELETE FROM tracks WHERE id = ? AND user_id = ?');
    $stmt->execute([$trackId, $userId]);

    $_SESSION['success'] = 'Трек успешно удалён';
} catch (Exception $e) {
    $_SESSION['error'] = 'Ошибка удаления: ' . $e->getMessage();
}

header('Location: profile.php');
exit;
?>
