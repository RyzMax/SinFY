<?php
require 'db.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit;
}

$trackId = $_POST['track_id'] ?? 0;
$action  = $_POST['action'] ?? '';

if (!$trackId || !is_numeric($trackId)) {
    header('Location: dashboard.php');
    exit;
}


$stmt = $pdo->prepare('SELECT * FROM tracks WHERE id = ?');
$stmt->execute([$trackId]);
$track = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$track) {
    header('Location: dashboard.php');
    exit;
}

if ($action === 'approve') {
    $stmt = $pdo->prepare('UPDATE tracks SET is_approved = 1 WHERE id = ?');
    $stmt->execute([$trackId]);
} elseif ($action === 'reject' || $action === 'delete') {
  
    if (!empty($track['audio_path']) && file_exists($track['audio_path'])) {
        unlink($track['audio_path']);
    }
    if (!empty($track['cover_path']) && file_exists($track['cover_path'])) {
        unlink($track['cover_path']);
    }
    $stmt = $pdo->prepare('DELETE FROM tracks WHERE id = ?');
    $stmt->execute([$trackId]);
}

header('Location: dashboard.php');
exit;
