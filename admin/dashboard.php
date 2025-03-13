<?php
require_once '../config.php';
require_once '../includes/db.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get statistics
$stats = [
    'total_users' => fetchOne("SELECT COUNT(*) as count FROM users")['count'],
    'total_artists' => fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'artist'")['count'],
    'total_artworks' => fetchOne("SELECT COUNT(*) as count FROM artworks WHERE status = 'approved'")['count'],
    'pending_artworks' => fetchOne("SELECT COUNT(*) as count FROM artworks WHERE status = 'pending'")['count']
];

// Get recent artworks pending approval
$pending_artworks = fetchAll(
    "SELECT a.*, u.username, u.full_name, c.name as category_name
     FROM artworks a 
     JOIN users u ON a.artist_id = u.user_id 
     JOIN categories c ON a.category_id = c.category_id
     WHERE a.status = 'pending' 
     ORDER BY a.upload_date DESC"
);

// Get recent users
$recent_users = fetchAll(
    "SELECT * FROM users 
     ORDER BY created_at DESC 
     LIMIT 5"
);

// Get recent comments
$recent_comments = fetchAll(
    "SELECT c.*, u.username, u.full_name, a.title as artwork_title 
     FROM comments c 
     JOIN users u ON c.user_id = u.user_id 
     JOIN artworks a ON c.artwork_id = a.artwork_id 
     ORDER BY c.created_at DESC 
     LIMIT 5"
);

// Handle artwork approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $artwork_id = $_POST['artwork_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'approve' || $action === 'reject') {
            $status = $action === 'approve' ? 'approved' : 'rejected';
            executeQuery(
                "UPDATE artworks SET status = ? WHERE artwork_id = ?",
                [$status, $artwork_id],
                'si'
            );
            $success = "Artwork has been " . $status;
            
            // Refresh pending artworks list
            $pending_artworks = fetchAll(
                "SELECT a.*, u.username, u.full_name, c.name as category_name
                 FROM artworks a 
                 JOIN users u ON a.artist_id = u.user_id 
                 JOIN categories c ON a.category_id = c.category_id
                 WHERE a.status = 'pending' 
                 ORDER BY a.upload_date DESC"
            );
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = "Admin Dashboard";
include '../includes/header.php';
?>

<link rel="stylesheet" href="css/admin.css">

<div class="admin-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Admin Dashboard</h1>
        <div>
            <a href="users.php" class="admin-btn admin-btn-outline me-2">
                <i class="fas fa-users me-2"></i>Manage Users
            </a>
            <a href="../index.php" class="admin-btn admin-btn-primary">
                <i class="fas fa-home me-2"></i>View Site
            </a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <h3><?= $stats['total_users'] ?></h3>
                <p>Total Users</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h3><?= $stats['total_artists'] ?></h3>
                <p>Artists</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h3><?= $stats['total_artworks'] ?></h3>
                <p>Approved Artworks</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h3><?= $stats['pending_artworks'] ?></h3>
                <p>Pending Artworks</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pending Artworks -->
        <div class="col-md-6 mb-4">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="mb-0">Pending Artworks</h5>
                </div>
                <div class="admin-card-body">
                    <?php if (empty($pending_artworks)): ?>
                        <p class="text-muted mb-0">No artworks pending approval.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Preview</th>
                                        <th>Title</th>
                                        <th>Artist</th>
                                        <th>Category</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_artworks as $artwork): ?>
                                        <tr>
                                            <td>
                                                <img src="../<?= htmlspecialchars($artwork['file_path']) ?>" 
                                                     class="img-thumbnail" 
                                                     style="width: 50px; height: 50px; object-fit: cover;"
                                                     alt="<?= htmlspecialchars($artwork['title']) ?>">
                                            </td>
                                            <td>
                                                <a href="../artwork.php?id=<?= $artwork['artwork_id'] ?>" 
                                                   class="text-decoration-none">
                                                    <?= htmlspecialchars($artwork['title']) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($artwork['full_name']) ?></td>
                                            <td><?= htmlspecialchars($artwork['category_name']) ?></td>
                                            <td><?= date('M j, Y', strtotime($artwork['upload_date'])) ?></td>
                                            <td>
                                                <form method="POST" class="d-inline me-2">
                                                    <input type="hidden" name="artwork_id" value="<?= $artwork['artwork_id'] ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="admin-btn admin-btn-primary btn-sm">
                                                        <i class="fas fa-check me-1"></i>Approve
                                                    </button>
                                                </form>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="artwork_id" value="<?= $artwork['artwork_id'] ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="admin-btn admin-btn-outline btn-sm text-danger">
                                                        <i class="fas fa-times me-1"></i>Reject
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="col-md-6 mb-4">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="mb-0">Recent Users</h5>
                </div>
                <div class="admin-card-body">
                    <?php if (empty($recent_users)): ?>
                        <p class="text-muted mb-0">No recent users.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_users as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= $user['profile_image'] ?? '../images/default-avatar.png' ?>" 
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
                                            <td>
                                                <span class="admin-badge admin-badge-<?= $user['status'] === 'active' ? 'success' : ($user['status'] === 'banned' ? 'danger' : 'warning') ?>">
                                                    <?= ucfirst($user['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Comments -->
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="mb-0">Recent Comments</h5>
                </div>
                <div class="admin-card-body">
                    <?php if (empty($recent_comments)): ?>
                        <p class="text-muted mb-0">No recent comments.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Artwork</th>
                                        <th>Comment</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_comments as $comment): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="fw-bold"><?= htmlspecialchars($comment['username']) ?></div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($comment['artwork_title']) ?></td>
                                            <td><?= htmlspecialchars($comment['content']) ?></td>
                                            <td><?= date('M j, Y', strtotime($comment['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 