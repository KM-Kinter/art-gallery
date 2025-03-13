<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Art Gallery - Discover and share amazing artworks">
    <title><?= $pageTitle ?? 'Welcome' ?> - <?= SITE_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= SITE_URL ?>/css/style.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/images/favicon.png">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <main class="main-content">
        <!-- Content will be injected here -->
    </main>
</body>
</html> 