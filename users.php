<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug database connection
try {
    $conn = getConnection();
    // echo "MySQL Server Info: " . $conn->server_info . "<br>";
    // echo "MySQL Client Info: " . $conn->client_info . "<br>";
    
    $test_connection = fetchOne("SELECT 1 as test");
    // echo "Database connection test result: " . ($test_connection['test'] ?? 'failed') . "<br>";
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "<br>";
}

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    $status = $_POST['status'] ?? '';
    
    try {
        if ($action === 'change_status' && !empty($user_id) && !empty($status)) {
            if (!in_array($status, ['active', 'inactive', 'banned'])) {
                throw new Exception("Invalid status.");
            }
            
            executeQuery(
                "UPDATE users SET status = ? WHERE user_id = ? AND user_id != ?",
                [$status, $user_id, getCurrentUserId()],
                'sii'
            );
            
            $success = "User status updated successfully.";
        } elseif ($action === 'delete' && !empty($user_id)) {
            if ($user_id == getCurrentUserId()) {
                throw new Exception("You cannot delete your own account.");
            }
            
            executeQuery(
                "DELETE FROM users WHERE user_id = ?",
                [$user_id],
                'i'
            );
            
            $success = "User deleted successfully.";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$page = max(1, $_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

try {
    $total_users = fetchOne(
        "SELECT COUNT(*) as count FROM users WHERE user_id != ?",
        [getCurrentUserId()],
        'i'
    );
    
    // echo "SQL Debug - Total Users Query:<br>";
    // echo "Current User ID: " . getCurrentUserId() . "<br>";
    // echo "Query Result: " . print_r($total_users, true) . "<br>";
    
    $total_users = $total_users['count'] ?? 0;
    // echo "Total users found: " . $total_users . "<br>";
    
    $total_pages = ceil($total_users / $limit);
    
    $users = fetchAll(
        "SELECT u.*, 
                (SELECT COUNT(*) FROM artworks WHERE artist_id = u.user_id AND status = 'approved') as artwork_count
         FROM users u 
         WHERE u.user_id != ?
         ORDER BY u.created_at DESC 
         LIMIT ? OFFSET ?",
        [getCurrentUserId(), $limit, $offset],
        'iii'
    );
    
    // echo "SQL Debug - Users Query:<br>";
    // echo "Parameters: user_id=" . getCurrentUserId() . ", limit=" . $limit . ", offset=" . $offset . "<br>";
    // echo "Number of users fetched: " . count($users) . "<br>";
    
    if (empty($users)) {
        echo "No users found in the database<br>";
        echo "SQL Error (if any): " . ($conn->error ?? 'None') . "<br>";
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}

$pageTitle = "Manage Users";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Art Gallery</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; background: #f4f4f4; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .back-btn { display: inline-block; padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .back-btn:hover { background: #0056b3; }
        .alert { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        tr:hover { background: #f5f5f5; }
        .user-info { display: flex; align-items: center; }
        .avatar { width: 32px; height: 32px; border-radius: 50%; margin-right: 10px; object-fit: cover; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; color: white; }
        .badge-admin { background: #dc3545; }
        .badge-artist { background: #007bff; }
        .badge-user { background: #6c757d; }
        .badge-active { background: #28a745; }
        .badge-banned { background: #dc3545; }
        .actions { position: relative; }
        .action-menu { display: none; position: absolute; right: 0; background: white; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); z-index: 1000; }
        .action-menu.show { display: block; }
        .action-btn { display: block; width: 100%; padding: 8px 16px; border: none; background: none; text-align: left; cursor: pointer; color: #333; text-decoration: none; }
        .action-btn:hover { background: #f8f9fa; }
        .action-btn.danger { color: #dc3545; }
        .action-btn.success { color: #28a745; }
        .pagination { display: flex; justify-content: center; list-style: none; margin-top: 20px; }
        .page-link { display: inline-block; padding: 8px 12px; margin: 0 4px; border: 1px solid #ddd; color: #007bff; text-decoration: none; }
        .page-link.active { background: #007bff; color: white; border-color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Manage Users</h1>
            <a href="http://localhost/projekt/admin/dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($users)): ?>
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Artworks</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <img src="<?= htmlspecialchars($user['profile_image'] ?? 'images/default-avatar.png') ?>" 
                                         class="avatar" 
                                         alt="<?= htmlspecialchars($user['username']) ?>">
                                    <div>
                                        <div><strong><?= htmlspecialchars($user['username']) ?></strong></div>
                                        <div style="color: #666;"><?= htmlspecialchars($user['full_name']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-<?= $user['role'] ?>"><?= ucfirst(htmlspecialchars($user['role'])) ?></span>
                            </td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="badge badge-<?= $user['status'] ?>"><?= ucfirst(htmlspecialchars($user['status'])) ?></span>
                            </td>
                            <td><?= (int)$user['artwork_count'] ?></td>
                            <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                            <td class="actions">
                                <div class="action-menu" id="menu-<?= $user['user_id'] ?>">
                                    <a href="profile.php?id=<?= $user['user_id'] ?>" class="action-btn">View Profile</a>
                                    <?php if ($user['status'] !== 'active'): ?>
                                        <form method="POST">
                                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                            <input type="hidden" name="action" value="change_status">
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="action-btn success">Activate</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($user['status'] !== 'banned'): ?>
                                        <form method="POST">
                                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                            <input type="hidden" name="action" value="change_status">
                                            <input type="hidden" name="status" value="banned">
                                            <button type="submit" class="action-btn danger">Ban</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <button onclick="toggleMenu(<?= $user['user_id'] ?>)" class="action-btn">Actions ▼</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="page-link <?= $page === $i ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-danger">No users found.</div>
        <?php endif; ?>
    </div>

    <script>
        function toggleMenu(userId) {
            const menus = document.querySelectorAll('.action-menu');
            menus.forEach(menu => menu.classList.remove('show'));
            
            const menu = document.getElementById('menu-' + userId);
            menu.classList.toggle('show');
        }

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.matches('.action-btn')) {
                const menus = document.querySelectorAll('.action-menu');
                menus.forEach(menu => menu.classList.remove('show'));
            }
        });
    </script>
</body>
</html> 