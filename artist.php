<?php
require_once 'config.php';
require_once 'includes/db.php';

// Get artist ID from URL
$artist_id = (int)($_GET['id'] ?? 0);

// Get artist data
$artist = fetchOne(
    "SELECT u.*, up.bio, up.avatar,
            (SELECT COUNT(*) FROM artworks WHERE artist_id = u.user_id AND status = 'approved') as artwork_count,
            (SELECT COUNT(*) FROM follows WHERE followed_id = u.user_id) as follower_count,
            (SELECT COUNT(*) FROM follows WHERE follower_id = u.user_id) as following_count
     FROM users u
     LEFT JOIN user_profiles up ON u.user_id = up.user_id
     WHERE u.user_id = ? AND u.role = 'artist'",
    [$artist_id],
    'i'
);

// If artist not found or not an artist, redirect to gallery
if (!$artist) {
    header('Location: gallery.php');
    exit;
}

// Get artist's approved artworks
$artworks = fetchAll(
    "SELECT a.*, 
            (SELECT COUNT(*) FROM artwork_likes WHERE artwork_id = a.artwork_id) as like_count,
            (SELECT COUNT(*) FROM artwork_comments WHERE artwork_id = a.artwork_id) as comment_count,
            (SELECT AVG(rating) FROM artwork_ratings WHERE artwork_id = a.artwork_id) as avg_rating,
            c.name as category_name
     FROM artworks a
     LEFT JOIN categories c ON a.category_id = c.category_id
     WHERE a.artist_id = ? AND a.status = 'approved'
     ORDER BY a.upload_date DESC",
    [$artist_id],
    'i'
);

// Check if current user follows this artist
$is_following = false;
if (isLoggedIn()) {
    $follow_check = fetchOne(
        "SELECT * FROM follows WHERE follower_id = ? AND followed_id = ?",
        [getCurrentUserId(), $artist_id],
        'ii'
    );
    $is_following = (bool)$follow_check;
}

// Handle follow/unfollow
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'follow') {
            if (!$is_following) {
                executeQuery(
                    "INSERT INTO follows (follower_id, followed_id, created_at) VALUES (?, ?, NOW())",
                    [getCurrentUserId(), $artist_id],
                    'ii'
                );
                $is_following = true;
                $artist['follower_count']++;
            }
        } elseif ($action === 'unfollow') {
            if ($is_following) {
                executeQuery(
                    "DELETE FROM follows WHERE follower_id = ? AND followed_id = ?",
                    [getCurrentUserId(), $artist_id],
                    'ii'
                );
                $is_following = false;
                $artist['follower_count']--;
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Include header
$pageTitle = htmlspecialchars($artist['full_name']) . " - Artist Profile";
include 'includes/header.php';
?>

<div class="container mt-5">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Artist Info -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <img src="<?= htmlspecialchars($artist['avatar'] ?? 'images/default-avatar.png') ?>" 
                         alt="<?= htmlspecialchars($artist['username']) ?>" 
                         class="rounded-circle mb-3"
                         style="width: 150px; height: 150px; object-fit: cover;">
                    
                    <h4 class="card-title"><?= htmlspecialchars($artist['full_name']) ?></h4>
                    <p class="text-muted">@<?= htmlspecialchars($artist['username']) ?></p>
                    
                    <div class="d-flex justify-content-around mb-4">
                        <div class="text-center">
                            <h5 class="mb-0"><?= $artist['artwork_count'] ?></h5>
                            <small class="text-muted">Artworks</small>
                        </div>
                        <div class="text-center">
                            <h5 class="mb-0"><?= $artist['follower_count'] ?></h5>
                            <small class="text-muted">Followers</small>
                        </div>
                        <div class="text-center">
                            <h5 class="mb-0"><?= $artist['following_count'] ?></h5>
                            <small class="text-muted">Following</small>
                        </div>
                    </div>
                    
                    <?php if (isLoggedIn() && getCurrentUserId() !== $artist_id): ?>
                        <form method="POST" class="d-grid mb-3">
                            <?php if ($is_following): ?>
                                <input type="hidden" name="action" value="unfollow">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-user-minus me-1"></i>Unfollow
                                </button>
                            <?php else: ?>
                                <input type="hidden" name="action" value="follow">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-1"></i>Follow
                                </button>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                    
                    <?php if (!empty($artist['bio'])): ?>
                        <div class="mt-3">
                            <h6 class="text-muted mb-2">About</h6>
                            <p class="card-text"><?= nl2br(htmlspecialchars($artist['bio'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Artist's Artworks -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0">Artworks</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($artworks)): ?>
                        <p class="text-muted text-center mb-0">No artworks yet.</p>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($artworks as $artwork): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100">
                                        <a href="artwork.php?id=<?= $artwork['artwork_id'] ?>" class="text-decoration-none">
                                            <img src="<?= htmlspecialchars($artwork['file_path']) ?>" 
                                                 class="card-img-top" 
                                                 alt="<?= htmlspecialchars($artwork['title']) ?>"
                                                 style="height: 200px; object-fit: cover;">
                                        </a>
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <a href="artwork.php?id=<?= $artwork['artwork_id'] ?>" 
                                                   class="text-decoration-none text-dark">
                                                    <?= htmlspecialchars($artwork['title']) ?>
                                                </a>
                                            </h6>
                                            <p class="card-text small text-muted">
                                                <?= htmlspecialchars($artwork['category_name']) ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="small text-muted">
                                                    <i class="fas fa-heart me-1"></i><?= $artwork['like_count'] ?>
                                                    <i class="fas fa-comment ms-2 me-1"></i><?= $artwork['comment_count'] ?>
                                                </div>
                                                <?php if ($artwork['avg_rating']): ?>
                                                    <div class="text-warning">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star<?= $i <= round($artwork['avg_rating']) ? '' : '-o' ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 