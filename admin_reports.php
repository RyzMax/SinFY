<?php
require_once 'db.php';
session_start();

if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die('❌ Доступ запрещён. Только для администраторов.');
}


$successMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['report_id'])) {
    $reportId = (int)$_POST['report_id'];
    $commentId = (int)$_POST['comment_id'];
    $action = $_POST['action'];
    
    try {
        if ($action === 'delete_comment' && $commentId) {
            $stmtDel = $pdo->prepare("DELETE FROM comments WHERE id = ?");
            $stmtDel->execute([$commentId]);
            $successMsg = "✅ Комментарий #$commentId удалён!";
        }
        
 
        $stmt = $pdo->prepare("UPDATE reports SET resolved = 1 WHERE id = ?");
        $stmt->execute([$reportId]);
        $successMsg = $successMsg ?: "✅ Жалоба #$reportId обработана.";
        
    } catch (Exception $e) {
        $errorMsg = "❌ Ошибка: " . $e->getMessage();
    }
}


$stmt = $pdo->query("
    SELECT r.*, c.comment_text, c.username, c.track_id, t.title AS track_title, t.audio_path
    FROM reports r
    JOIN comments c ON r.comment_id = c.id
    JOIN tracks t ON c.track_id = t.id
    WHERE r.resolved = 0
    ORDER BY r.created_at DESC
");
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Жалобы на комментарии - SinFY Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .admin-table th, .admin-table td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        .admin-table th { background: #d32f2f; color: white; }
        .admin-table tr:nth-child(even) { background: #f9f9f9; }
        .admin-table tr:hover { background: #fff3cd; }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; margin: 2px; text-decoration: none; display: inline-block; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-danger:hover { background: #c82333; }
        .success-msg { background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin: 15px 0; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin: 15px 0; }
        .comment-preview { max-height: 100px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 4px; }
        .track-link { color: #007bff; text-decoration: none; }
        .track-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="admin-container">
        <header style="margin-bottom: 30px;">
            <h1>🔧 Жалобы на комментарии</h1>
            <a href="index.php" class="btn">🏠 На главную</a>
            <a href="admin.php" class="btn btn-success">📋 Все жалобы</a>
        </header>

        <?php if ($successMsg): ?>
            <div class="success-msg"><?php echo $successMsg; ?></div>
        <?php endif; ?>
        <?php if (isset($errorMsg)): ?>
            <div class="error-msg"><?php echo $errorMsg; ?></div>
        <?php endif; ?>

        <?php if (empty($reports)): ?>
            <div style="text-align: center; padding: 50px; color: #666;">
                <h2>🎉 Жалоб нет!</h2>
                <p>Все комментарии в порядке.</p>
            </div>
        <?php else: ?>
            <p><strong><?php echo count($reports); ?> необработанных жалоб</strong></p>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID жалобы</th>
                        <th>Трек</th>
                        <th>Автор коммента</th>
                        <th>Комментарий</th>
                        <th>Кто пожаловался</th>
                        <th>Дата жалобы</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $rep): ?>
                        <tr>
                            <td><strong>#<?php echo $rep['id']; ?></strong></td>
                            <td>
                                <a href="track.php?id=<?php echo $rep['track_id']; ?>" 
                                   class="track-link" target="_blank">
                                   <?php echo htmlspecialchars($rep['track_title']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($rep['username']); ?></td>
                            <td>
                                <div class="comment-preview">
                                    <?php echo nl2br(htmlspecialchars(substr($rep['comment_text'], 0, 200))); ?>
                                    <?php if (strlen($rep['comment_text']) > 200): ?>...<?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo $rep['user_id'] ? 'Пользователь #' . $rep['user_id'] : 'Гость'; ?></td>
                            <td><?php echo date('d.m.Y H:i', strtotime($rep['created_at'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="report_id" value="<?php echo $rep['id']; ?>">
                                    <input type="hidden" name="comment_id" value="<?php echo $rep['comment_id']; ?>">
                                    <button type="submit" name="action" value="resolve" class="btn btn-success">✅ Оставить</button>
                                    <button type="submit" name="action" value="delete_comment" 
                                            class="btn btn-danger" 
                                            onclick="return confirm('Удалить комментарий №<?php echo $rep['comment_id']; ?>?\n\nЭто действие нельзя отменить!');">
                                        🗑️ Удалить комментарий
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
