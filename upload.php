<?php
require_once 'config.php';
require_once 'includes/db.php';

// Check if user is logged in and is an artist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'artist') {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Get categories for the form
$categories = fetchAll("SELECT * FROM categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = $_POST['description'] ?? '';
    $creation_date = $_POST['creation_date'] ?? '';

    // Validation
    if (empty($title) || empty($category_id) || empty($description) || empty($creation_date)) {
        $error = 'Please fill in all required fields';
    } elseif (!isset($_FILES['artwork_image']) || $_FILES['artwork_image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select an image to upload';
    } else {
        $file = $_FILES['artwork_image'];
        
        // Validate file type
        $file_type = mime_content_type($file['tmp_name']);
        if (!in_array($file_type, ALLOWED_IMAGE_TYPES)) {
            $error = 'Invalid file type. Please upload a JPEG, PNG, or GIF image';
        }
        // Validate file size
        elseif ($file['size'] > MAX_FILE_SIZE) {
            $error = 'File is too large. Maximum size is ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB';
        } else {
            // Create upload directory if it doesn't exist
            if (!file_exists(UPLOAD_PATH)) {
                mkdir(UPLOAD_PATH, 0777, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $filepath = UPLOAD_PATH . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Insert artwork into database
                $sql = "INSERT INTO artworks (title, artist_id, category_id, description, image_path, creation_date) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $result = executeQuery(
                    $sql,
                    [$title, $_SESSION['user_id'], $category_id, $description, $filepath, $creation_date],
                    'siisss'
                );

                if ($result) {
                    $success = 'Artwork uploaded successfully! It will be reviewed by our staff.';
                } else {
                    $error = 'Failed to save artwork details';
                    // Clean up uploaded file
                    unlink($filepath);
                }
            } else {
                $error = 'Failed to upload image';
            }
        }
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
    <style>
        .upload-container {
            max-width: 800px;
            margin: 40px auto;
        }
        .image-preview {
            max-width: 100%;
            max-height: 300px;
            margin-top: 10px;
            display: none;
        }
        #drop-zone {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        #drop-zone:hover, #drop-zone.dragover {
            border-color: #0d6efd;
            background: #e9ecef;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <div class="container upload-container">
        <h1 class="mb-4">Upload Artwork</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
                <br>
                <a href="my-artworks.php">View your artworks</a>
            </div>
        <?php else: ?>
            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="title" class="form-label">Title *</label>
                    <input type="text" 
                           class="form-control" 
                           id="title" 
                           name="title" 
                           required>
                    <div class="invalid-feedback">Please provide a title</div>
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">Category *</label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Please select a category</div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description *</label>
                    <textarea class="form-control" 
                              id="description" 
                              name="description" 
                              rows="4" 
                              required></textarea>
                    <div class="invalid-feedback">Please provide a description</div>
                </div>

                <div class="mb-3">
                    <label for="creation_date" class="form-label">Creation Date *</label>
                    <input type="date" 
                           class="form-control" 
                           id="creation_date" 
                           name="creation_date" 
                           required>
                    <div class="invalid-feedback">Please specify when this artwork was created</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Artwork Image *</label>
                    <div id="drop-zone" onclick="document.getElementById('artwork_image').click();">
                        <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                        <p class="mb-0">Click or drag and drop your image here</p>
                        <small class="text-muted">Maximum file size: <?php echo MAX_FILE_SIZE / 1024 / 1024; ?>MB</small>
                        <input type="file" 
                               class="d-none" 
                               id="artwork_image" 
                               name="artwork_image" 
                               accept="image/*" 
                               required>
                    </div>
                    <img id="image-preview" class="image-preview" alt="Preview">
                    <div class="invalid-feedback">Please select an image</div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">Upload Artwork</button>
                    <a href="my-artworks.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Image preview
        const imageInput = document.getElementById('artwork_image');
        const imagePreview = document.getElementById('image-preview');
        const dropZone = document.getElementById('drop-zone');

        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    dropZone.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        });

        // Drag and drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('dragover');
        }

        function unhighlight(e) {
            dropZone.classList.remove('dragover');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            imageInput.files = files;
            
            if (files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    dropZone.style.display = 'none';
                }
                reader.readAsDataURL(files[0]);
            }
        }
    </script>
</body>
</html> 