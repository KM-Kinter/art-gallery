    </main>
    
    <footer class="footer mt-auto py-3 bg-dark text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>About <?= SITE_NAME ?></h5>
                    <p>Discover and share amazing artworks from talented artists around the world.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= SITE_URL ?>/gallery.php" class="text-white">Gallery</a></li>
                        <li><a href="<?= SITE_URL ?>/artists.php" class="text-white">Artists</a></li>
                        <?php if (!isLoggedIn()): ?>
                            <li><a href="<?= SITE_URL ?>/register.php" class="text-white">Register</a></li>
                            <li><a href="<?= SITE_URL ?>/login.php" class="text-white">Login</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i>contact@artgallery.com</li>
                        <li><i class="fas fa-phone me-2"></i>+1 234 567 890</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i>123 Art Street, City</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="<?= SITE_URL ?>/js/main.js"></script>
</body>
</html> 