<?php
require_once '../config.php';
require_once '../includes/db.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'You must be logged in to follow artists']);
    exit;
}

// Get POST data
$artist_id = (int)($_POST['artist_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$artist_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid artist ID']);
    exit;
}

try {
    if ($action === 'follow') {
        // Check if already following
        $existing = fetchOne(
            "SELECT * FROM follows WHERE follower_id = ? AND followed_id = ?",
            [getCurrentUserId(), $artist_id],
            'ii'
        );
        
        if (!$existing) {
            executeQuery(
                "INSERT INTO follows (follower_id, followed_id, created_at) VALUES (?, ?, NOW())",
                [getCurrentUserId(), $artist_id],
                'ii'
            );
            echo json_encode(['success' => true, 'action' => 'follow']);
        }
    } elseif ($action === 'unfollow') {
        executeQuery(
            "DELETE FROM follows WHERE follower_id = ? AND followed_id = ?",
            [getCurrentUserId(), $artist_id],
            'ii'
        );
        echo json_encode(['success' => true, 'action' => 'unfollow']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 