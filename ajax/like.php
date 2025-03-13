<?php
require_once '../config.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Please log in to like artworks.']);
    exit;
}

// Get current user ID
$user_id = getCurrentUserId();

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$artwork_id = $data['artwork_id'] ?? 0;
$action = $data['action'] ?? '';

try {
    // Validate input
    if (!$artwork_id || !in_array($action, ['like', 'unlike'])) {
        throw new Exception('Invalid request.');
    }
    
    // Check if artwork exists and is approved
    $artwork = fetchOne(
        "SELECT artwork_id FROM artworks WHERE artwork_id = ? AND status = 'approved'",
        [$artwork_id],
        'i'
    );
    
    if (!$artwork) {
        throw new Exception('Artwork not found.');
    }
    
    if ($action === 'like') {
        // Check if already liked
        $existing = fetchOne(
            "SELECT like_id FROM artwork_likes WHERE user_id = ? AND artwork_id = ?",
            [$user_id, $artwork_id],
            'ii'
        );
        
        if ($existing) {
            throw new Exception('You have already liked this artwork.');
        }
        
        // Add like
        executeQuery(
            "INSERT INTO artwork_likes (user_id, artwork_id, created_at) VALUES (?, ?, NOW())",
            [$user_id, $artwork_id],
            'ii'
        );
    } else {
        // Remove like
        executeQuery(
            "DELETE FROM artwork_likes WHERE user_id = ? AND artwork_id = ?",
            [$user_id, $artwork_id],
            'ii'
        );
    }
    
    // Get updated like count
    $like_count = fetchOne(
        "SELECT COUNT(*) as count FROM artwork_likes WHERE artwork_id = ?",
        [$artwork_id],
        'i'
    )['count'];
    
    echo json_encode([
        'success' => true,
        'action' => $action,
        'likes' => $like_count
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 