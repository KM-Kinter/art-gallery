<?php
require_once 'config.php';
require_once 'includes/db.php';

// Get admin user ID
$admin = fetchOne("SELECT user_id FROM users WHERE role = 'admin' LIMIT 1");
if (!$admin) {
    die("No admin user found in the database.");
}

$admin_id = $admin['user_id'];

// Get all images from the uploads/artworks directory
$images = glob('uploads/artworks/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

// Get existing images from database to avoid duplicates
$existing_images = fetchAll("SELECT file_path FROM artworks");
$existing_paths = array_column($existing_images, 'file_path');

$imported = 0;
$skipped = 0;

foreach ($images as $image) {
    // Convert Windows path to Unix style for consistency
    $image = str_replace('\\', '/', $image);
    
    // Skip if image already exists in database
    if (in_array($image, $existing_paths)) {
        echo "Skipping {$image} - already exists in database<br>";
        $skipped++;
        continue;
    }
    
    // Get image info
    $info = pathinfo($image);
    $title = str_replace('_', ' ', $info['filename']);
    $title = ucwords($title);
    
    try {
        // Insert artwork into database
        executeQuery(
            "INSERT INTO artworks (title, description, file_path, artist_id, status, created_at, updated_at) 
             VALUES (?, ?, ?, ?, 'approved', NOW(), NOW())",
            [$title, "Imported artwork", $image, $admin_id],
            'sssi'
        );
        
        echo "Imported {$image} successfully<br>";
        $imported++;
    } catch (Exception $e) {
        echo "Error importing {$image}: " . $e->getMessage() . "<br>";
    }
}

echo "<br>Import completed:<br>";
echo "- Imported: {$imported} images<br>";
echo "- Skipped: {$skipped} images<br>";
echo "<br><a href='admin/dashboard.php'>Return to Dashboard</a>";
?> 