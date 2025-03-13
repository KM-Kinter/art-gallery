<?php
require_once 'config.php';
require_once 'includes/db.php';

// Get featured artworks
$featured_artworks = fetchAll(
    "SELECT a.*, u.username, u.full_name, c.name as category_name,
            (SELECT AVG(rating) FROM ratings r WHERE r.artwork_id = a.artwork_id) as avg_rating
     FROM artworks a 
     JOIN users u ON a.artist_id = u.user_id 
     JOIN categories c ON a.category_id = c.category_id 
     WHERE a.status = 'approved'
     ORDER BY (SELECT AVG(rating) FROM ratings r WHERE r.artwork_id = a.artwork_id) DESC, a.upload_date DESC
     LIMIT 6"
);

// Get featured artists
$featured_artists = fetchAll(
    "SELECT u.*, 
            (SELECT COUNT(*) FROM artworks a WHERE a.artist_id = u.user_id AND a.status = 'approved') as artwork_count,
            (SELECT AVG(r.rating) 
             FROM ratings r 
             JOIN artworks a ON r.artwork_id = a.artwork_id 
             WHERE a.artist_id = u.user_id) as avg_rating
     FROM users u 
     WHERE u.role = 'artist' AND u.status = 'active'
     HAVING artwork_count > 0
     ORDER BY avg_rating DESC, artwork_count DESC
     LIMIT 4"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Online Art Gallery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('images/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            margin-bottom: 50px;
        }
        .feature-card {
            border: none;
            transition: transform 0.3s ease;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .feature-card:hover {
            transform: translateY(-10px);
        }
        .artwork-image {
            height: 250px;
            object-fit: cover;
        }
        .artist-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            margin: 0 auto 15px;
        }
        .rating-stars {
            color: #ffc107;
        }
        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .cta-section {
            background: #f8f9fa;
            padding: 80px 0;
            margin: 50px 0;
        }
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 mb-4">Welcome to <?php echo SITE_NAME; ?></h1>
            <p class="lead mb-5">Discover amazing artworks from talented artists around the world</p>
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="gallery.php" class="btn btn-primary btn-lg px-4 gap-3">
                    <i class="fas fa-images me-2"></i> Browse Gallery
                </a>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-user-plus me-2"></i> Join Now
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Featured Artworks -->
    <section class="container mb-5">
        <h2 class="text-center mb-4">Featured Artworks</h2>
        <div class="row">
            <?php foreach ($featured_artworks as $artwork): ?>
                <div class="col-md-4">
                    <div class="card feature-card">
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
                            <div class="rating-stars mb-2">
                                <?php
                                $rating = round($artwork['avg_rating'] ?? 0);
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating ? '★' : '☆';
                                }
                                ?>
                            </div>
                            <a href="artwork.php?id=<?php echo $artwork['artwork_id']; ?>" 
                               class="btn btn-outline-primary w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="gallery.php" class="btn btn-primary">View All Artworks</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="cta-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-paint-brush"></i>
                    </div>
                    <h3>Share Your Art</h3>
                    <p>Create your artist profile and showcase your artwork to a global audience</p>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Rate & Comment</h3>
                    <p>Engage with the community by rating artworks and sharing your thoughts</p>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Connect with Artists</h3>
                    <p>Follow your favorite artists and stay updated with their latest works</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Artists -->
    <section class="container mb-5">
        <h2 class="text-center mb-4">Featured Artists</h2>
        <div class="row">
            <?php foreach ($featured_artists as $artist): ?>
                <div class="col-md-3">
                    <div class="card feature-card text-center">
                        <div class="card-body">
                            <img src="<?php echo $artist['profile_image'] ?? 'images/default-avatar.png'; ?>" 
                                 class="artist-image" 
                                 alt="<?php echo htmlspecialchars($artist['full_name']); ?>">
                            <h5 class="card-title mb-2">
                                <?php echo htmlspecialchars($artist['full_name']); ?>
                            </h5>
                            <div class="rating-stars mb-2">
                                <?php
                                $rating = round($artist['avg_rating'] ?? 0);
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating ? '★' : '☆';
                                }
                                ?>
                            </div>
                            <p class="card-text">
                                <small class="text-muted">
                                    <?php echo $artist['artwork_count']; ?> artworks
                                </small>
                            </p>
                            <a href="artist.php?id=<?php echo $artist['user_id']; ?>" 
                               class="btn btn-outline-primary">View Profile</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="artists.php" class="btn btn-primary">View All Artists</a>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 