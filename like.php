<?php
session_start();
header('Content-Type: application/json');


require_once 'db.php';


global $pdo;  
if (!isset($pdo)) {

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=maxusic_bd", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo json_encode(['error' => 'БД ошибка']);
        exit;
    }
}

if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Войдите!']);
    exit;
}

$trackId = (int)$_POST['track_id'] ?? 0;
if (!$trackId) {
    echo json_encode(['error' => 'Нет трека']);
    exit;
}


$check = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND track_id = ?");
$check->execute([$_SESSION['user_id'], $trackId]);
$userLiked = $check->rowCount() > 0;

if ($userLiked) {
    $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND track_id = ?")->execute([$_SESSION['user_id'], $trackId]);
    $liked = false;
} else {
    $pdo->prepare("INSERT INTO likes (user_id, track_id) VALUES (?, ?)")->execute([$_SESSION['user_id'], $trackId]);
    $liked = true;
}

$count = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE track_id = ?");
$count->execute([$trackId]);
$totalLikes = $count->fetchColumn();

echo json_encode([
    'liked' => $liked,
    'count' => (int)$totalLikes
]);
?>
