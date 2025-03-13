<?php
require_once '../config.php';
require_once '../includes/db.php';

// Check if user is logged in and is an artist
if (!isLoggedIn() || !isArtist()) {
    header('Location: ../login.php');
    exit;
}

// Get artist's statistics
$user_id = getCurrentUserId();
$stats = fetchOne(
    "SELECT 
        (SELECT COUNT(*) FROM artworks WHERE artist_id = ? AND status = 'approved') as approved_artworks,
        (SELECT COUNT(*) FROM artworks WHERE artist_id = ? AND status = 'pending') as pending_artworks,
        (SELECT COUNT(*) FROM follows WHERE artist_id = ?) as followers,
        (SELECT AVG(r.rating) 
         FROM ratings r 
         JOIN artworks a ON r.artwork_id = a.artwork_id 
         WHERE a.artist_id = ?) as avg_rating",
    [$user_id, $user_id, $user_id, $user_id],
    'iiii'
);

// Get recent artworks
$recent_artworks = fetchAll(
    "SELECT a.*, c.name as category_name,
            (SELECT AVG(rating) FROM ratings r WHERE r.artwork_id = a.artwork_id) as avg_rating,
            (SELECT COUNT(*) FROM comments WHERE artwork_id = a.artwork_id) as comment_count
     FROM artworks a 
     JOIN categories c ON a.category_id = c.category_id
     WHERE a.artist_id = ?
     ORDER BY a.upload_date DESC 
     LIMIT 5",
    [$user_id],
    'i'
);

// Get recent comments
$recent_comments = fetchAll(
    "SELECT c.*, a.title as artwork_title, u.username, u.profile_image
     FROM comments c
     JOIN artworks a ON c.artwork_id = a.artwork_id
     JOIN users u ON c.user_id = u.user_id
     WHERE a.artist_id = ?
     ORDER BY c.created_at DESC
     LIMIT 5",
    [$user_id],
    'i'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artist Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="mb-3">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
                <p class="text-muted">Manage your artworks and view your statistics</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="upload.php" class="btn btn-primary">
                    <i class="fas fa-upload me-2"></i>Upload New Artwork
                </a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="custom-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-image fa-2x text-primary mb-3"></i>
                        <h3 class="mb-2"><?php echo $stats['approved_artworks']; ?></h3>
                        <p class="text-muted mb-0">Approved Artworks</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="custom-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x text-warning mb-3"></i>
                        <h3 class="mb-2"><?php echo $stats['pending_artworks']; ?></h3>
                        <p class="text-muted mb-0">Pending Approval</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="custom-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x text-success mb-3"></i>
                        <h3 class="mb-2"><?php echo $stats['followers']; ?></h3>
                        <p class="text-muted mb-0">Followers</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="custom-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-star fa-2x text-warning mb-3"></i>
                        <h3 class="mb-2"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></h3>
                        <p class="text-muted mb-0">Average Rating</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Artworks -->
            <div class="col-md-8 mb-4">
                <div class="custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">Recent Artworks</h5>
                            <a href="my-artworks.php" class="btn btn-outline-primary btn-sm">View All</a>
                        </div>

                        <?php if (empty($recent_artworks)): ?>
                            <p class="text-muted">No artworks uploaded yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Rating</th>
                                            <th>Comments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_artworks as $artwork): ?>
                                            <tr>
                                                <td>
                                                    <a href="../artwork.php?id=<?php echo $artwork['artwork_id']; ?>"
                                                       class="text-decoration-none">
                                                        <?php echo htmlspecialchars($artwork['title']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($artwork['category_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $artwork['status'] === 'approved' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($artwork['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="rating-stars small">
                                                        <?php
                                                        $rating = round($artwork['avg_rating'] ?? 0);
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            echo $i <= $rating ? '★' : '☆';
                                                        }
                                                        ?>
                                                    </div>
                                                </td>
                                                <td><?php echo $artwork['comment_count']; ?></td>
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
            <div class="col-md-4 mb-4">
                <div class="custom-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Recent Comments</h5>

                        <?php if (empty($recent_comments)): ?>
                            <p class="text-muted">No comments yet.</p>
                        <?php else: ?>
                            <?php foreach ($recent_comments as $comment): ?>
                                <div class="d-flex mb-3">
                                    <img src="<?php echo $comment['profile_image'] ?? '../' . DEFAULT_AVATAR; ?>" 
                                         class="rounded-circle me-2" 
                                         style="width: 32px; height: 32px; object-fit: cover;"
                                         alt="<?php echo htmlspecialchars($comment['username']); ?>">
                                    <div>
                                        <div class="fw-bold">
                                            <?php echo htmlspecialchars($comment['username']); ?>
                                            <small class="text-muted">on</small>
                                            <a href="../artwork.php?id=<?php echo $comment['artwork_id']; ?>"
                                               class="text-decoration-none">
                                                <?php echo htmlspecialchars($comment['artwork_title']); ?>
                                            </a>
                                        </div>
                                        <p class="small text-muted mb-0">
                                            <?php echo htmlspecialchars($comment['content']); ?>
                                        </p>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y', strtotime($comment['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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