<?php
require_once 'config.php';
require_once 'includes/db.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$user = fetchOne(
    "SELECT * FROM users WHERE user_id = ?",
    [getCurrentUserId()],
    'i'
);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $full_name = trim($_POST['full_name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        
        // Handle profile image upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_image'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception('Only JPG, PNG and GIF images are allowed.');
            }
            
            $max_size = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $max_size) {
                throw new Exception('File size must be less than 5MB.');
            }
            
            $filename = uniqid('profile_') . '_' . basename($file['name']);
            $upload_path = 'uploads/profiles/' . $filename;
            
            // Create directory if it doesn't exist
            if (!file_exists('uploads/profiles')) {
                mkdir('uploads/profiles', 0755, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Delete old profile image if exists
                if (!empty($user['profile_image']) && file_exists($user['profile_image'])) {
                    unlink($user['profile_image']);
                }
                
                executeQuery(
                    "UPDATE users SET profile_image = ? WHERE user_id = ?",
                    [$upload_path, getCurrentUserId()],
                    'si'
                );
                $user['profile_image'] = $upload_path;
            }
        }
        
        // Update other profile information
        executeQuery(
            "UPDATE users SET full_name = ?, bio = ? WHERE user_id = ?",
            [$full_name, $bio, getCurrentUserId()],
            'ssi'
        );
        
        $user['full_name'] = $full_name;
        $user['bio'] = $bio;
        
        $success = "Profile updated successfully.";
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Include header
$pageTitle = "Edit Profile";
include 'includes/header.php';
?>

<div class="container mt-5">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Profile</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="text-center mb-4">
                            <img src="<?= $user['profile_image'] ?? 'images/default-avatar.png' ?>" 
                                 class="rounded-circle mb-3" 
                                 style="width: 150px; height: 150px; object-fit: cover;"
                                 alt="Profile Picture">
                            <div class="mb-3">
                                <label for="profile_image" class="form-label">Change Profile Picture</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" 
                                       accept="image/jpeg,image/png,image/gif">
                                <div class="form-text">
                                    Maximum file size: 5MB. Allowed formats: JPG, PNG, GIF
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" 
                                   value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            <div class="form-text">Username cannot be changed</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                            <div class="form-text">Tell others about yourself</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" 
                                   value="<?= htmlspecialchars($user['email']) ?>" disabled>
                            <div class="form-text">Email cannot be changed</div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 