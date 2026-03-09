<?php
function toggleLike($pdo, $userId, $trackId) {
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND track_id = ?");
    $stmt->execute([$userId, $trackId]);
    
    if ($stmt->fetch()) {
        $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND track_id = ?")->execute([$userId, $trackId]);
    } else {
        $pdo->prepare("INSERT INTO likes (user_id, track_id) VALUES (?, ?)")->execute([$userId, $trackId]);
    }
}
function isLiked($pdo, $userId, $trackId) {
    $stmt = $pdo->prepare("SELECT 1 FROM likes WHERE user_id = ? AND track_id = ?");
    $stmt->execute([$userId, $trackId]);
    return $stmt->fetch() !== false;
}


function getUserLikes($pdo, $userId, $limit = 20) {
    $sql = "
        SELECT t.*, 
               COALESCE(u.login, t.author) as author_login,
               t.cover_path as cover_image  -- ✅ ТОЛЬКО cover_path!
        FROM likes l 
        JOIN tracks t ON l.track_id = t.id 
        LEFT JOIN users u ON t.user_id = u.id
        WHERE l.user_id = ? AND t.is_approved = 1
        ORDER BY l.created_at DESC 
        LIMIT " . (int)$limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLikesCount($pdo, $trackId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE track_id = ?");
    $stmt->execute([$trackId]);
    return (int)$stmt->fetchColumn();
}
?>
