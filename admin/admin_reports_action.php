<?php
require_once '../db.php';
session_start();

if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    die('Доступ запрещен');
}

$reportId  = (int)($_POST['report_id'] ?? 0);
$commentId = (int)($_POST['comment_id'] ?? 0);
$action    = $_POST['action'] ?? '';

if ($reportId && $action) {
    if ($action === 'delete_comment' && $commentId) {
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$commentId]);
    }

    $stmt = $pdo->prepare("UPDATE reports SET resolved = 1 WHERE id = ?");
    $stmt->execute([$reportId]);
}

header('Location: admin_reports.php');
exit;
