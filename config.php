<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'art_gallery');

// Application configuration
define('SITE_NAME', 'Art Gallery');
define('SITE_URL', 'http://localhost/projekt');
define('UPLOAD_DIR', __DIR__ . '/uploads/artworks/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);
define('DEFAULT_AVATAR', '/images/default-avatar.png');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Include database connection
require_once __DIR__ . '/includes/db.php';

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isArtist() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'artist';
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

function redirectTo($path) {
    header("Location: " . SITE_URL . $path);
    exit();
}

function displayError($message) {
    return "<div class='alert alert-danger'>" . htmlspecialchars($message) . "</div>";
}

function displaySuccess($message) {
    return "<div class='alert alert-success'>" . htmlspecialchars($message) . "</div>";
}

function validateImage($file) {
    $errors = [];
    
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = "File is too large. Maximum size is " . (MAX_FILE_SIZE / 1024 / 1024) . "MB";
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        $errors[] = "Invalid file type. Allowed types: " . implode(', ', ALLOWED_EXTENSIONS);
    }
    
    return $errors;
}

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// Create logs directory if it doesn't exist
if (!file_exists(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0777, true);
}

// File paths configuration
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('ARTWORKS_PATH', UPLOAD_PATH . 'artworks/');
define('PROFILES_PATH', UPLOAD_PATH . 'profiles/');
define('IMAGES_PATH', __DIR__ . '/images/');

// Default images
define('HERO_BG', 'images/hero-bg.jpg');

// File upload configuration
define('ALLOWED_IMAGE_TYPES', [
    'image/jpeg',
    'image/png',
    'image/gif'
]);

// Pagination settings
define('ITEMS_PER_PAGE', 12);

// Time zone
date_default_timezone_set('Europe/Warsaw');

// Function to get and clear flash message
function getFlashMessage() {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
} 