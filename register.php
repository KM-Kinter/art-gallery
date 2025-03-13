<?php
require_once 'config.php';
require_once 'includes/db.php';

// Initialize variables
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $role = $_POST['role'] ?? 'user';
    
    // Validate input
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($full_name)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!in_array($role, ['user', 'artist'])) {
        $error = "Invalid role selected.";
    } else {
        try {
            // Check if username already exists
            $existing = fetchOne(
                "SELECT user_id FROM users WHERE username = ? OR email = ?",
                [$username, $email],
                'ss'
            );
            
            if ($existing) {
                $error = "Username or email already exists.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                executeQuery(
                    "INSERT INTO users (username, email, password, full_name, role, bio, status, created_at) 
                     VALUES (?, ?, ?, ?, ?, '', 'active', NOW())",
                    [$username, $email, $hashed_password, $full_name, $role],
                    'sssss'
                );
                
                $success = "Registration successful! Redirecting to login...";
                
                // Redirect to login page immediately
                header("Location: login.php");
                exit;
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = "An error occurred. Please try again later.";
        }
    }
}

// Include header
$pageTitle = "Register";
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Register</h2>
                    
                    <?php if ($error): ?>
                        <?= displayError($error) ?>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <?= displaySuccess($success) ?>
                    <?php endif; ?>
                    
                    <form method="POST" action="register.php">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= htmlspecialchars($username ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($email ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?= htmlspecialchars($full_name ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   minlength="8" required>
                            <div class="form-text">Password must be at least 8 characters long.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" 
                                   name="confirm_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Account Type</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="user" <?= ($role ?? '') === 'user' ? 'selected' : '' ?>>Regular User</option>
                                <option value="artist" <?= ($role ?? '') === 'artist' ? 'selected' : '' ?>>Artist</option>
                            </select>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Register</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 