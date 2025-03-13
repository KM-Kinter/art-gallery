<?php
require_once '../config.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Please log in to follow artists.']);
    exit;
}

// Get current user ID
$user_id = getCurrentUserId();

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$artist_id = $data['artist_id'] ?? 0;
$action = $data['action'] ?? '';

try {
    // Validate input
    if (!$artist_id || !in_array($action, ['follow', 'unfollow'])) {
        throw new Exception('Invalid request.');
    }
    
    // Check if artist exists and is actually an artist
    $artist = fetchOne(
        "SELECT user_id FROM users WHERE user_id = ? AND role = 'artist' AND status = 'active'",
        [$artist_id],
        'i'
    );
    
    if (!$artist) {
        throw new Exception('Artist not found.');
    }
    
    // Check if user is trying to follow themselves
    if ($user_id == $artist_id) {
        throw new Exception('You cannot follow yourself.');
    }
    
    if ($action === 'follow') {
        // Check if already following
        $existing = fetchOne(
            "SELECT follow_id FROM follows WHERE follower_id = ? AND followed_id = ?",
            [$user_id, $artist_id],
            'ii'
        );
        
        if ($existing) {
            throw new Exception('You are already following this artist.');
        }
        
        // Add follow relationship
        executeQuery(
            "INSERT INTO follows (follower_id, followed_id, created_at) VALUES (?, ?, NOW())",
            [$user_id, $artist_id],
            'ii'
        );
    } else {
        // Remove follow relationship
        executeQuery(
            "DELETE FROM follows WHERE follower_id = ? AND followed_id = ?",
            [$user_id, $artist_id],
            'ii'
        );
    }
    
    // Get updated follower count
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