<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Please log in to follow artists.']);
    exit;
}

$user_id = getCurrentUserId();

$data = json_decode(file_get_contents('php://input'), true);
$artist_id = $data['artist_id'] ?? 0;
$action = $data['action'] ?? '';

try {
    if (!$artist_id || !in_array($action, ['follow', 'unfollow'])) {
        throw new Exception('Invalid request.');
    }
    
    $artist = fetchOne(
        "SELECT user_id FROM users WHERE user_id = ? AND role = 'artist' AND status = 'active'",
        [$artist_id],
        'i'
    );
    
    if (!$artist) {
        throw new Exception('Artist not found.');
    }
    
    if ($user_id == $artist_id) {
        throw new Exception('You cannot follow yourself.');
    }
    
    if ($action === 'follow') {
        $existing = fetchOne(
            "SELECT follow_id FROM follows WHERE follower_id = ? AND followed_id = ?",
            [$user_id, $artist_id],
            'ii'
        );
        
        if ($existing) {
            throw new Exception('You are already following this artist.');
        }
        
        executeQuery(
            "INSERT INTO follows (follower_id, followed_id, created_at) VALUES (?, ?, NOW())",
            [$user_id, $artist_id],
            'ii'
        );
    } else {
        executeQuery(
            "DELETE FROM follows WHERE follower_id = ? AND followed_id = ?",
            [$user_id, $artist_id],
            'ii'
        );
    }
    
    $follower_count = fetchOne(
        "SELECT COUNT(*) as count FROM follows WHERE followed_id = ?",
        [$artist_id],
        'i'
    )['count'];
    
    echo json_encode([
        'success' => true,
        'action' => $action,
        'follower_count' => $follower_count
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 