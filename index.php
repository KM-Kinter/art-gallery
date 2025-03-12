<?php
require_once 'config.php';
require_once 'includes/db.php';

// Get featured artworks
$featured_artworks = fetchAll(
    "SELECT a.*, u.username, u.full_name, c.name as category_name 
     FROM artworks a 
     JOIN users u ON a.artist_id = u.user_id 
     JOIN categories c ON a.category_id = c.category_id 
     WHERE a.status = 'featured' 
     ORDER BY a.upload_date DESC 
     LIMIT 6"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://source.unsplash.com/random/1920x1080/?art');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            margin-bottom: 40px;
        }
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?php echo SITE_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="gallery.php">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="exhibitions.php">Exhibitions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="artists.php">Artists</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">My Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero text-center">
        <div class="container">
            <h1 class="display-4 mb-4">Welcome to <?php echo SITE_NAME; ?></h1>
            <p class="lead mb-4">Discover amazing artworks from talented artists around the world</p>
            <a href="gallery.php" class="btn btn-light btn-lg">Explore Gallery</a>
        </div>
    </section>

    <!-- Featured Artworks -->
    <section class="container mb-5">
        <h2 class="text-center mb-4">Featured Artworks</h2>
        <div class="row">
            <?php foreach ($featured_artworks as $artwork): ?>
                <div class="col-md-4">
                    <div class="card artwork-card">
                        <img src="<?php echo htmlspecialchars($artwork['image_path']); ?>" 
                             class="card-img-top artwork-image" 
                             alt="<?php echo htmlspecialchars($artwork['title']); ?>">
                        <span class="badge bg-primary category-badge">
                            <?php echo htmlspecialchars($artwork['category_name']); ?>
                        </span>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($artwork['title']); ?></h5>
                            <p class="card-text">
                                <small class="text-muted">
                                    By <?php echo htmlspecialchars($artwork['full_name']); ?>
                                </small>
                            </p>
                            <a href="artwork.php?id=<?php echo $artwork['artwork_id']; ?>" 
                               class="btn btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p>Connecting artists and art lovers worldwide</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5>Follow Us</h5>
                    <a href="#" class="text-light me-3"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 