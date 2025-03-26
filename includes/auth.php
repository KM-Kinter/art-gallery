<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current username
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Check if user is artist
function isArtist() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'artist';
}

// Get user data
function getUserData($user_id) {
    return fetchOne(
        "SELECT u.*, up.bio, up.avatar 
         FROM users u 
         LEFT JOIN user_profiles up ON u.user_id = up.user_id 
         WHERE u.user_id = ?",
        [$user_id],
        'i'
    );
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login');
        exit;
    }
}

// Require admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/gallery');
        exit;
    }
}

// Require artist
function requireArtist() {
    requireLogin();
    if (!isArtist()) {
        header('Location: ' . SITE_URL . '/gallery');
        exit;
    }
} 