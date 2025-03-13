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

// Get current user settings
$settings = fetchOne(
    "SELECT * FROM user_profiles WHERE user_id = ?",
    [getCurrentUserId()],
    'i'
);

// If no settings exist, create default settings
if (!$settings) {
    executeQuery(
        "INSERT INTO user_profiles (user_id, email_notifications, public_profile) VALUES (?, 1, 1)",
        [getCurrentUserId()],
        'i'
    );
    $settings = [
        'email_notifications' => 1,
        'public_profile' => 1
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $public_profile = isset($_POST['public_profile']) ? 1 : 0;
        
        executeQuery(
            "UPDATE user_profiles SET email_notifications = ?, public_profile = ? WHERE user_id = ?",
            [$email_notifications, $public_profile, getCurrentUserId()],
            'iii'
        );
        
        $settings['email_notifications'] = $email_notifications;
        $settings['public_profile'] = $public_profile;
        
        $success = "Settings updated successfully.";
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Include header
$pageTitle = "Settings";
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
                    <h5 class="card-title mb-0">Account Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <h6 class="mb-3">Notifications</h6>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="email_notifications" 
                                       name="email_notifications" <?= $settings['email_notifications'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="email_notifications">
                                    Email Notifications
                                </label>
                                <div class="form-text">
                                    Receive email notifications about new comments, likes, and followers.
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="mb-3">Privacy</h6>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="public_profile" 
                                       name="public_profile" <?= $settings['public_profile'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="public_profile">
                                    Public Profile
                                </label>
                                <div class="form-text">
                                    Allow other users to view your profile and artworks.
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="mb-3">Account</h6>
                        <div class="mb-3">
                            <a href="#" class="btn btn-outline-danger" data-bs-toggle="modal" 
                               data-bs-target="#deleteAccountModal">
                                <i class="fas fa-trash-alt me-1"></i>Delete Account
                            </a>
                            <div class="form-text text-danger">
                                This action cannot be undone. All your data will be permanently deleted.
                            </div>
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

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete your account? This action cannot be undone.</p>
                <p>All your data will be permanently deleted, including:</p>
                <ul>
                    <li>Your profile information</li>
                    <li>Your artworks and comments</li>
                    <li>Your likes and ratings</li>
                    <li>Your followers and following relationships</li>
                </ul>
                <form id="deleteAccountForm" method="POST" action="delete-account.php">
                    <div class="mb-3">
                        <label for="password" class="form-label">Enter your password to confirm</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="deleteAccountForm" class="btn btn-danger">
                    <i class="fas fa-trash-alt me-1"></i>Delete Account
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 