<?php
require_once 'config.php';

// Initialize variables
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        try {
            // Get user from database
            $sql = "SELECT user_id, username, password, role, status, full_name FROM users WHERE username = ?";
            $user = fetchOne($sql, [$username], 's');
            
            if (!$user) {
                $error = "Invalid username or password.";
            } elseif ($user['status'] !== 'active') {
                $error = "Your account is not active. Please contact the administrator.";
            } elseif (!password_verify($password, $user['password'])) {
                $error = "Invalid username or password.";
            } else {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
                // Update last login time
                executeQuery(
                    "UPDATE users SET updated_at = NOW() WHERE user_id = ?",
                    [$user['user_id']],
                    'i'
                );
                
                $success = "Login successful! Redirecting...";
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                    exit;
                } elseif ($user['role'] === 'artist') {
                    header("Location: artist/dashboard.php");
                    exit;
                } else {
                    header("Location: gallery.php");
                    exit;
                }
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = "An error occurred. Please try again later.";
        }
    }
}

// Include header
$pageTitle = "Login";
include 'includes/header.php';
?>

<div class="container mt-5" style="min-height: 80vh;">
    <div class="row justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Login</h2>
                    
                    <?php if ($error): ?>
                        <?= displayError($error) ?>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <?= displaySuccess($success) ?>
                    <?php endif; ?>
                    
                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= htmlspecialchars($username ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Don't have an account? <a href="register.php">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 