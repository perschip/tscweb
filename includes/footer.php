<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5 class="footer-heading">Tristate Cards</h5>
                <p>Your trusted source for sports cards, collectibles, and memorabilia. Find us on eBay and Whatnot for the best deals!</p>
            </div>
            <div class="col-md-4">
                <h5 class="footer-heading">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="https://www.whatnot.com/user/<?php echo htmlspecialchars(getSetting('whatnot_username', 'tristate_cards')); ?>" target="_blank">Whatnot</a></li>
                    <li><a href="https://www.ebay.com/usr/<?php echo htmlspecialchars(getSetting('ebay_seller_id', 'tristate_cards')); ?>" target="_blank">eBay Store</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5 class="footer-heading">Contact Us</h5>
                <p><i class="fas fa-envelope me-2"></i> <?php echo getSetting('contact_email', 'info@tristatecards.com'); ?></p>
                <p><i class="fas fa-phone me-2"></i> <?php echo getSetting('contact_phone', '(201) 555-1234'); ?></p>
                <p><i class="fas fa-map-marker-alt me-2"></i> <?php echo getSetting('contact_address', 'Hoffman, New Jersey, US'); ?></p>
                <div class="social-links mt-3">
                    <a href="<?php echo getSetting('social_instagram', '#'); ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="<?php echo getSetting('social_twitter', '#'); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                    <a href="<?php echo getSetting('social_youtube', '#'); ?>" target="_blank"><i class="fab fa-youtube"></i></a>
                    <a href="<?php echo getSetting('social_facebook', '#'); ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12 text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<!-- JavaScript Dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

</body>
</html>