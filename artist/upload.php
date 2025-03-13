<?php
require_once '../config.php';
require_once '../includes/db.php';

// Check if user is logged in and is an artist
if (!isLoggedIn() || !isArtist()) {
    header('Location: ../login.php');
    exit;
}

// Initialize variables
$error = '';
$success = '';

// Get categories for the form
$categories = fetchAll("SELECT * FROM categories ORDER BY name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    
    try {
        // Validate input
        if (empty($title)) {
            throw new Exception("Please enter a title for your artwork.");
        }
        
        if (empty($description)) {
            throw new Exception("Please provide a description for your artwork.");
        }
        
        if ($category_id <= 0) {
            throw new Exception("Please select a category.");
        }
        
        // Handle file upload
        if (!isset($_FILES['artwork']) || $_FILES['artwork']['error'] === UPLOAD_ERR_NO_FILE) {
            throw new Exception("Please select an image to upload.");
        }
        
        $file = $_FILES['artwork'];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error uploading file. Please try again.");
        }
        
        // Check file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            throw new Exception("Only JPG, PNG and GIF images are allowed.");
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('artwork_') . '.' . $extension;
        $upload_path = '../uploads/artworks/' . $filename;
        
        // Create directory if it doesn't exist
        if (!file_exists('../uploads/artworks')) {
            mkdir('../uploads/artworks', 0777, true);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            throw new Exception("Error saving file. Please try again.");
        }
        
        // Save to database
        executeQuery(
            "INSERT INTO artworks (title, description, file_path, artist_id, category_id, status, upload_date) 
             VALUES (?, ?, ?, ?, ?, 'pending', NOW())",
            [$title, $description, 'uploads/artworks/' . $filename, getCurrentUserId(), $category_id],
            'sssis'
        );
        
        $success = "Your artwork has been uploaded successfully and is pending approval.";
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Artwork - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .preview-image {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
            display: none;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Upload New Artwork</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select a category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['category_id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="artwork" class="form-label">Artwork Image</label>
                                <input type="file" class="form-control" id="artwork" name="artwork" 
                                       accept="image/jpeg,image/png,image/gif" required>
                                <div class="form-text">
                                    Allowed formats: JPG, PNG, GIF. Maximum file size: 5MB.
                                </div>
                                <img id="preview" class="preview-image mt-3">
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i>Upload Artwork
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Image preview
        document.getElementById('artwork').addEventListener('change', function(e) {
            const preview = document.getElementById('preview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        });
    </script>
</body>
</html> 