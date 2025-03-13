<?php
require_once 'config.php';
require_once 'includes/db.php';

// Get search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build query
$where_clause = "WHERE u.role = 'artist'";
if (!empty($search)) {
    $search = '%' . $search . '%';
    $where_clause .= " AND (u.username LIKE ? OR u.full_name LIKE ?)";
}

// Get total artists count
$total_count = fetchOne(
    "SELECT COUNT(*) as count FROM users u $where_clause",
    !empty($search) ? [$search, $search] : [],
    !empty($search) ? 'ss' : ''
)['count'];

$total_pages = ceil($total_count / $per_page);

// Get artists with their stats
$artists = fetchAll(
    "SELECT u.*, 
            (SELECT COUNT(*) FROM artworks WHERE artist_id = u.user_id) as artwork_count,
            (SELECT AVG(r.rating) FROM ratings r 
             JOIN artworks a ON r.artwork_id = a.artwork_id 
             WHERE a.artist_id = u.user_id) as avg_rating
     FROM users u 
     $where_clause
     ORDER BY artwork_count DESC, u.full_name
     LIMIT ? OFFSET ?",
    !empty($search) ? [$search, $search, $per_page, $offset] : [$per_page, $offset],
    !empty($search) ? 'ssii' : 'ii'
);

// Page title and header
$pageTitle = "Artists - " . SITE_NAME;
include 'includes/header.php';
?>

<div class="container-fluid py-5">
    <!-- Search Section -->
    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form method="GET" class="input-group">
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           value="<?= htmlspecialchars($search) ?>"
                           placeholder="Search artists by name...">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Artists Grid -->
    <div class="container">
        <?php if (empty($artists)): ?>
            <div class="text-center">
                <h3>No artists found</h3>
                <?php if (!empty($search)): ?>
                    <p>Try different search terms or <a href="artists.php">view all artists</a></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($artists as $artist): ?>
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card artist-card h-100">
                            <div class="card-body text-center">
                                <img src="<?= $artist['profile_image'] ?? 'images/default-avatar.png' ?>" 
                                     class="artist-image mb-3" 
                                     alt="<?= htmlspecialchars($artist['full_name']) ?>"
                                     style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; margin: 0 auto;">
                                
                                <h5 class="card-title">
                                    <a href="artist.php?id=<?= $artist['user_id'] ?>" 
                                       class="text-decoration-none">
                                        <?= htmlspecialchars($artist['full_name']) ?>
                                    </a>
                                </h5>
                                
                                <div class="rating-stars mb-2" style="color: #ffc107;">
                                    <?php
                                    $rating = round($artist['avg_rating'] ?? 0);
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $rating ? '★' : '☆';
                                    }
                                    ?>
                                </div>
                                
                                <p class="card-text">
                                    <small class="text-muted">
                                        <?= $artist['artwork_count'] ?? 0 ?> artworks
                                    </small>
                                </p>
                                
                                <?php if (!empty($artist['bio'])): ?>
                                    <p class="card-text small">
                                        <?php 
                                        $bio = htmlspecialchars($artist['bio']);
                                        echo strlen($bio) > 100 ? substr($bio, 0, 100) . '...' : $bio;
                                        ?>
                                    </p>
                                <?php endif; ?>
                                
                                <a href="artist.php?id=<?= $artist['user_id'] ?>" 
                                   class="btn btn-outline-primary">
                                    View Profile
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Artists navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $page === $i ? 'active' : '' ?>">
                                <a class="page-link" 
                                   href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.artist-card {
    transition: transform 0.3s ease;
}
.artist-card:hover {
    transform: translateY(-10px);
}
.artist-image {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 50%;
    margin: 0 auto;
}
.rating-stars {
    color: #ffc107;
}
</style>

<?php include 'includes/footer.php'; ?> 