<?php
require_once '../config.php';
require_once '../includes/db.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle user status changes
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
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get users with pagination
$page = max(1, $_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

$total_users = fetchOne(
    "SELECT COUNT(*) as count FROM users WHERE user_id != ?",
    [getCurrentUserId()],
    'i'
)['count'];

$total_pages = ceil($total_users / $limit);

$users = fetchAll(
    "SELECT u.*, 
            (SELECT COUNT(*) FROM artworks WHERE artist_id = u.user_id AND status = 'approved') as artwork_count,
            (SELECT COUNT(*) FROM follows WHERE followed_id = u.user_id) as follower_count
     FROM users u 
     WHERE u.user_id != ?
     ORDER BY u.created_at DESC 
     LIMIT ? OFFSET ?",
    [getCurrentUserId(), $limit, $offset],
    'iii'
);

// Include header
$pageTitle = "Manage Users";
include '../includes/header.php';
?>

<link rel="stylesheet" href="css/admin.css">

<div class="admin-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manage Users</h1>
        <div>
            <a href="dashboard.php" class="admin-btn admin-btn-outline">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <div class="admin-card">
        <div class="admin-card-header">
            <h5 class="mb-0">All Users</h5>
        </div>
        <div class="admin-card-body">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Artworks</th>
                            <th>Followers</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= $user['profile_image'] ?? '../images/default-avatar.png'; ?>" 
                                             class="rounded-circle me-2" 
                                             style="width: 32px; height: 32px; object-fit: cover;"
                                             alt="<?= htmlspecialchars($user['username']) ?>">
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($user['username']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($user['full_name']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="admin-badge admin-badge-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'artist' ? 'primary' : 'secondary') ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="admin-badge admin-badge-<?= $user['status'] === 'active' ? 'success' : ($user['status'] === 'banned' ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($user['status']) ?>
                                    </span>
                                </td>
                                <td><?= $user['artwork_count'] ?></td>
                                <td><?= $user['follower_count'] ?></td>
                                <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="../profile.php?id=<?= $user['user_id'] ?>">
                                                    <i class="fas fa-user me-2"></i>View Profile
                                                </a>
                                            </li>
                                            <?php if ($user['status'] !== 'active'): ?>
                                                <li>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                        <input type="hidden" name="action" value="change_status">
                                                        <input type="hidden" name="status" value="active">
                                                        <button type="submit" class="dropdown-item text-success">
                                                            <i class="fas fa-check me-2"></i>Activate
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($user['status'] !== 'inactive'): ?>
                                                <li>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                        <input type="hidden" name="action" value="change_status">
                                                        <input type="hidden" name="status" value="inactive">
                                                        <button type="submit" class="dropdown-item text-warning">
                                                            <i class="fas fa-pause me-2"></i>Deactivate
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($user['status'] !== 'banned'): ?>
                                                <li>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                        <input type="hidden" name="action" value="change_status">
                                                        <input type="hidden" name="status" value="banned">
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="fas fa-ban me-2"></i>Ban
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <nav aria-label="Users pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $page === $i ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 