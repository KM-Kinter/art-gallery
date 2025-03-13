<?php
require_once 'config.php';
require_once 'includes/db.php';

// Get filter parameters
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build query
$where_clause = "WHERE a.status = 'approved'";
if ($category_id > 0) {
    $where_clause .= " AND a.category_id = " . $category_id;
}

$order_clause = match($sort) {
    'oldest' => 'a.upload_date ASC',
    'title' => 'a.title ASC',
    'rating' => '(SELECT AVG(rating) FROM ratings r WHERE r.artwork_id = a.artwork_id) DESC',
    default => 'a.upload_date DESC'
};

// Get total artworks count
$total_count = fetchOne(
    "SELECT COUNT(*) as count FROM artworks a $where_clause"
)['count'];

$total_pages = ceil($total_count / $per_page);

// Get artworks
$artworks = fetchAll(
    "SELECT a.*, u.username, u.full_name, c.name as category_name,
            (SELECT AVG(rating) FROM ratings r WHERE r.artwork_id = a.artwork_id) as avg_rating,
            (SELECT COUNT(*) FROM comments cm WHERE cm.artwork_id = a.artwork_id) as comment_count
     FROM artworks a 
     JOIN users u ON a.artist_id = u.user_id 
     JOIN categories c ON a.category_id = c.category_id 
     $where_clause
     ORDER BY $order_clause
     LIMIT ? OFFSET ?",
    [$per_page, $offset],
    'ii'
);

// Get all categories for filter
$categories = fetchAll("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .artwork-card {
            transition: transform 0.3s ease;
            margin-bottom: 30px;
        }
        .artwork-card:hover {
            transform: translateY(-10px);
        }
        .artwork-image {
            height: 250px;
            object-fit: cover;
        }
        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .rating-stars {
            color: #ffc107;
        }
        .filter-section {
            background: #f8f9fa;
            padding: 20px 0;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Filter Section -->
    <section class="filter-section">
        <div class="container">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="category" class="form-label">Category</label>
                    <select name="category" id="category" class="form-select">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>"
                                    <?php echo $category_id == $category['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="sort" class="form-label">Sort By</label>
                    <select name="sort" id="sort" class="form-select">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>Title</option>
                        <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </form>
        </div>
    </section>

    <!-- Gallery -->
    <section class="container mb-5">
        <div class="row">
            <?php foreach ($artworks as $artwork): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card artwork-card">
                        <img src="<?php echo htmlspecialchars($artwork['file_path']); ?>" 
                             class="card-img-top artwork-image" 
                             alt="<?php echo htmlspecialchars($artwork['title']); ?>">
                        <span class="badge bg-primary category-badge">
                            <?php echo htmlspecialchars($artwork['category_name']); ?>
                        </span>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($artwork['title']); ?></h5>
                            <p class="card-text">
                                <small class="text-muted">
                                    By <a href="artist.php?id=<?php echo $artwork['artist_id']; ?>"
                                        class="text-decoration-none">
                                        <?php echo htmlspecialchars($artwork['full_name']); ?>
                                    </a>
                                </small>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="rating-stars">
                                    <?php
                                    $rating = round($artwork['avg_rating'] ?? 0);
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $rating ? '★' : '☆';
                                    }
                                    ?>
                                </div>
                                <small class="text-muted">
                                    <?php echo $artwork['comment_count']; ?> comments
                                </small>
                            </div>
                            <a href="artwork.php?id=<?php echo $artwork['artwork_id']; ?>" 
                               class="btn btn-outline-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Gallery navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo $category_id; ?>&sort=<?php echo $sort; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html> 