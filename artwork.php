<?php
require_once 'config.php';
require_once 'includes/db.php';

// Get artwork ID from URL
$artwork_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get artwork details
$artwork = fetchOne(
    "SELECT a.*, u.username, u.full_name, u.profile_image as artist_image, c.name as category_name,
            (SELECT AVG(rating) FROM ratings r WHERE r.artwork_id = a.artwork_id) as avg_rating,
            (SELECT COUNT(*) FROM ratings r WHERE r.artwork_id = a.artwork_id) as rating_count
     FROM artworks a 
     JOIN users u ON a.artist_id = u.user_id 
     JOIN categories c ON a.category_id = c.category_id 
     WHERE a.artwork_id = ? AND a.status != 'pending'",
    [$artwork_id],
    'i'
);

if (!$artwork) {
    header('Location: gallery.php');
    exit;
}

// Handle rating submission
if (isset($_POST['rating']) && isset($_SESSION['user_id'])) {
    $rating = (int)$_POST['rating'];
    if ($rating >= 1 && $rating <= 5) {
        executeQuery(
            "INSERT INTO ratings (artwork_id, user_id, rating) 
             VALUES (?, ?, ?) 
             ON DUPLICATE KEY UPDATE rating = ?",
            [$artwork_id, $_SESSION['user_id'], $rating, $rating],
            'iiii'
        );
        header("Location: artwork.php?id=$artwork_id");
        exit;
    }
}

// Handle comment submission
if (isset($_POST['comment']) && isset($_SESSION['user_id'])) {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        executeQuery(
            "INSERT INTO comments (artwork_id, user_id, content) VALUES (?, ?, ?)",
            [$artwork_id, $_SESSION['user_id'], $comment],
            'iis'
        );
        header("Location: artwork.php?id=$artwork_id");
        exit;
    }
}

// Get user's rating if logged in
$user_rating = null;
if (isset($_SESSION['user_id'])) {
    $rating_result = fetchOne(
        "SELECT rating FROM ratings WHERE artwork_id = ? AND user_id = ?",
        [$artwork_id, $_SESSION['user_id']],
        'ii'
    );
    if ($rating_result) {
        $user_rating = $rating_result['rating'];
    }
}

// Get comments with user info
$comments = fetchAll(
    "SELECT c.*, u.username, u.full_name, u.profile_image
     FROM comments c
     JOIN users u ON c.user_id = u.user_id
     WHERE c.artwork_id = ?
     ORDER BY c.created_at DESC",
    [$artwork_id],
    'i'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($artwork['title']); ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .artwork-image {
            max-height: 600px;
            object-fit: contain;
        }
        .rating-stars {
            color: #ffc107;
            font-size: 1.5rem;
            cursor: pointer;
        }
        .rating-stars.readonly {
            cursor: default;
        }
        .comment-avatar {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }
        .artist-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container my-5">
        <div class="row">
            <!-- Artwork Image -->
            <div class="col-md-8">
                <img src="<?php echo htmlspecialchars($artwork['file_path']); ?>" 
                     class="img-fluid artwork-image" 
                     alt="<?php echo htmlspecialchars($artwork['title']); ?>">
            </div>

            <!-- Artwork Details -->
            <div class="col-md-4">
                <h1 class="mb-3"><?php echo htmlspecialchars($artwork['title']); ?></h1>
                
                <!-- Artist Info -->
                <div class="d-flex align-items-center mb-4">
                    <img src="<?php echo $artwork['artist_image'] ?? 'images/default-avatar.png'; ?>" 
                         class="artist-image me-3" 
                         alt="<?php echo htmlspecialchars($artwork['full_name']); ?>">
                    <div>
                        <h5 class="mb-1">
                            <a href="artist.php?id=<?php echo $artwork['artist_id']; ?>" 
                               class="text-decoration-none">
                                <?php echo htmlspecialchars($artwork['full_name']); ?>
                            </a>
                        </h5>
                        <span class="badge bg-primary"><?php echo htmlspecialchars($artwork['category_name']); ?></span>
                    </div>
                </div>

                <!-- Rating -->
                <div class="mb-4">
                    <h5>Rating</h5>
                    <div class="rating-stars <?php echo isset($_SESSION['user_id']) ? '' : 'readonly'; ?>" id="rating-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star" data-rating="<?php echo $i; ?>">
                                <?php echo $i <= round($artwork['avg_rating']) ? '★' : '☆'; ?>
                            </span>
                        <?php endfor; ?>
                    </div>
                    <small class="text-muted">
                        <?php echo number_format($artwork['avg_rating'], 1); ?> 
                        (<?php echo $artwork['rating_count']; ?> ratings)
                    </small>
                </div>

                <!-- Creation Date -->
                <div class="mb-4">
                    <h5>Created On</h5>
                    <p><?php echo date('F j, Y', strtotime($artwork['creation_date'])); ?></p>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <h5>Description</h5>
                    <p><?php echo nl2br(htmlspecialchars($artwork['description'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="row mt-5">
            <div class="col-md-8">
                <h3 class="mb-4">Comments</h3>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Comment Form -->
                    <form method="POST" class="mb-4">
                        <div class="mb-3">
                            <textarea class="form-control" 
                                      name="comment" 
                                      rows="3" 
                                      placeholder="Share your thoughts about this artwork..."
                                      required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Post Comment</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">
                        Please <a href="login.php">login</a> to comment on this artwork.
                    </div>
                <?php endif; ?>

                <!-- Comments List -->
                <?php foreach ($comments as $comment): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <img src="<?php echo $comment['profile_image'] ?? 'images/default-avatar.png'; ?>" 
                                     class="comment-avatar me-3" 
                                     alt="<?php echo htmlspecialchars($comment['username']); ?>">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($comment['full_name']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo date('F j, Y g:i A', strtotime($comment['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Rating Form (Hidden) -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <form id="rating-form" method="POST" class="d-none">
            <input type="hidden" name="rating" id="rating-input">
        </form>
    <?php endif; ?>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <script>
            // Rating functionality
            const ratingStars = document.querySelectorAll('.rating-stars .star');
            const ratingForm = document.getElementById('rating-form');
            const ratingInput = document.getElementById('rating-input');
            let userRating = <?php echo $user_rating ?? 'null'; ?>;

            function updateStars(rating) {
                ratingStars.forEach((star, index) => {
                    star.textContent = index < rating ? '★' : '☆';
                });
            }

            if (userRating) {
                updateStars(userRating);
            }

            ratingStars.forEach(star => {
                star.addEventListener('mouseover', () => {
                    const rating = star.dataset.rating;
                    updateStars(rating);
                });

                star.addEventListener('mouseout', () => {
                    updateStars(userRating || 0);
                });

                star.addEventListener('click', () => {
                    const rating = star.dataset.rating;
                    userRating = rating;
                    ratingInput.value = rating;
                    ratingForm.submit();
                });
            });
        </script>
    <?php endif; ?>
</body>
</html> 