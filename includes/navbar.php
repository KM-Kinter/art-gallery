<?php
// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?= SITE_URL ?>">
            <i class="fas fa-palette me-2"></i><?= SITE_NAME ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'gallery.php' ? 'active' : '' ?>" 
                       href="<?= SITE_URL ?>/gallery.php">
                        <i class="fas fa-images me-1"></i>Gallery
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'artists.php' ? 'active' : '' ?>" 
                       href="<?= SITE_URL ?>/artists.php">
                        <i class="fas fa-users me-1"></i>Artists
                    </a>
                </li>
                <?php if (isArtist()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page === 'upload.php' ? 'active' : '' ?>" 
                           href="<?= SITE_URL ?>/upload.php">
                            <i class="fas fa-upload me-1"></i>Upload
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                           data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?= htmlspecialchars(getCurrentUsername()) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (isAdmin()): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= SITE_URL ?>/admin/dashboard.php">
                                        <i class="fas fa-tachometer-alt me-1"></i>Admin Dashboard
                                    </a>
                                </li>
                            <?php elseif (isArtist()): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= SITE_URL ?>/artist/dashboard.php">
                                        <i class="fas fa-tachometer-alt me-1"></i>Artist Dashboard
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item" href="<?= SITE_URL ?>/edit-profile.php">
                                    <i class="fas fa-user-edit me-1"></i>Edit Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= SITE_URL ?>/settings.php">
                                    <i class="fas fa-cog me-1"></i>Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= SITE_URL ?>/logout.php">
                                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page === 'login.php' ? 'active' : '' ?>" 
                           href="<?= SITE_URL ?>/login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page === 'register.php' ? 'active' : '' ?>" 
                           href="<?= SITE_URL ?>/register.php">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav> 