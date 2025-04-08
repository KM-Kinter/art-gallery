<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Please log in to comment.']);
    exit;
}

$user_id = getCurrentUserId();
$username = getCurrentUsername();

$data = json_decode(file_get_contents('php://input'), true);
$artwork_id = $data['artwork_id'] ?? 0;
$comment = trim($data['comment'] ?? '');

try {
    if (!$artwork_id || empty($comment)) {
        throw new Exception('Please enter a comment.');
    }
    
    if (strlen($comment) > 1000) {
        throw new Exception('Comment is too long. Maximum length is 1000 characters.');
    }
    
    $artwork = fetchOne(
        "SELECT artwork_id FROM artworks WHERE artwork_id = ? AND status = 'approved'",
        [$artwork_id],
        'i'
    );
    
    if (!$artwork) {
        throw new Exception('Artwork not found.');
    }
    
    executeQuery(
        "INSERT INTO artwork_comments (user_id, artwork_id, comment, created_at) 
         VALUES (?, ?, ?, NOW())",
        [$user_id, $artwork_id, $comment],
        'iis'
    );
    
    $user = fetchOne(
        "SELECT up.avatar FROM user_profiles up WHERE up.user_id = ?",
        [$user_id],
        'i'
    );
    
    echo json_encode([
        'success' => true,
        'username' => $username,
        'user_avatar' => $user['avatar'] ?: DEFAULT_AVATAR,
        'comment' => $comment
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 