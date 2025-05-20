<?php
// Include database connection and helper functions
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if Whatnot status needs update
if (time() - strtotime(getSetting('whatnot_last_check', date('Y-m-d H:i:s'))) > 60 * (int)getSetting('whatnot_check_interval', 15)) {
    checkWhatnotStatus();
    updateSetting('whatnot_last_check', date('Y-m-d H:i:s'));
}

// Get current Whatnot status
try {
    $status_query = "SELECT * FROM whatnot_status ORDER BY id DESC LIMIT 1";
    $status_stmt = $pdo->prepare($status_query);
    $status_stmt->execute();
    $whatnot_status = $status_stmt->fetch();
} catch (PDOException $e) {
    $whatnot_status = null;
}

// Get site settings
$site_name = getSetting('site_name', 'Tristate Cards');
$site_description = getSetting('site_description', 'Your trusted source for sports cards, collectibles, and memorabilia');

// Include header
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section text-center">
    <div class="container">
        <h1>Tristate Cards</h1>
        <p class="lead">Discover our latest eBay listings and Whatnot streams</p>
    </div>
</section>

<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <!-- eBay Listings Section -->
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">Current eBay Listings</h2>
                    <div id="ebay-listings">
                        <!-- CORRECTED: Using protocol-relative URL to help avoid ad blockers -->
                        <script type="text/javascript" src="https://www.auctionnudge.com/feed/item/js/theme/responsive/page/init/img_size/120/cats_output/dropdown/search_box/1/user_profile/1/blank/1/show_logo/1/lang/english/SellerID/tristate_cards/siteid/0/MaxEntries/6/target/4c9be4bc1"></script><div id="auction-nudge-4c9be4bc1"></div>
                    </div>
                </div>
            </div>

            <!-- Featured Cards Section -->
            <h2 class="mt-5 mb-4">Featured Cards</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <img src="https://via.placeholder.com/600x400" class="card-img-top" alt="Featured Card">
                        <div class="card-body">
                            <h5 class="card-title">Premium Baseball Collection</h5>
                            <p class="card-text">Explore our handpicked selection of rare baseball cards from legendary players and rising stars.</p>
                            <a href="#" class="btn btn-primary">View Collection</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <img src="https://via.placeholder.com/600x400" class="card-img-top" alt="Featured Card">
                        <div class="card-body">
                            <h5 class="card-title">Basketball Rookie Cards</h5>
                            <p class="card-text">Find valuable rookie cards from the biggest names in basketball, perfect for collectors and investors.</p>
                            <a href="#" class="btn btn-primary">View Collection</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Whatnot Status -->
            <?php if ($whatnot_status): ?>
                <?php if ($whatnot_status['is_live']): ?>
                    <!-- Live Stream Status -->
                    <div class="whatnot-status whatnot-live">
                        <h4><span class="status-indicator status-live"></span> LIVE NOW!</h4>
                        <div class="mb-3">
                            <p class="fw-bold mb-1"><?php echo htmlspecialchars($whatnot_status['stream_title']); ?></p>
                            <p class="mb-0">Join us for amazing pulls!</p>
                        </div>
                        <a href="<?php echo htmlspecialchars($whatnot_status['stream_url']); ?>" class="btn btn-success btn-sm" target="_blank" 
                           onclick="logWhatnotClick(<?php echo $whatnot_status['id']; ?>)">Watch Live</a>
                    </div>
                <?php elseif ($whatnot_status['scheduled_time'] && strtotime($whatnot_status['scheduled_time']) > time()): ?>
                    <!-- Upcoming Stream Status -->
                    <div class="whatnot-status whatnot-upcoming">
                        <h4><span class="status-indicator status-upcoming"></span> Next Stream</h4>
                        <div class="mb-3">
                            <p class="fw-bold mb-1"><?php echo htmlspecialchars($whatnot_status['stream_title']); ?></p>
                            <p class="mb-0"><?php echo date('F j, Y \a\t g:i A', strtotime($whatnot_status['scheduled_time'])); ?></p>
                        </div>
                        <a href="https://www.whatnot.com/user/<?php echo htmlspecialchars(getSetting('whatnot_username', 'tristate_cards')); ?>" class="btn btn-primary btn-sm" target="_blank"
                           onclick="logWhatnotClick(<?php echo $whatnot_status['id']; ?>)">Follow on Whatnot</a>
                    </div>
                <?php else: ?>
                    <!-- Default Whatnot Promo -->
                    <div class="whatnot-status">
                        <h4>Find Us on Whatnot</h4>
                        <p>Follow us on Whatnot for live card breaks, exclusive deals, and more!</p>
                        <a href="https://www.whatnot.com/user/<?php echo htmlspecialchars(getSetting('whatnot_username', 'tristate_cards')); ?>" class="btn btn-primary btn-sm" target="_blank"
                           onclick="logWhatnotClick(0)">Follow on Whatnot</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Default Whatnot Promo -->
                <div class="whatnot-status">
                    <h4>Find Us on Whatnot</h4>
                    <p>Follow us on Whatnot for live card breaks, exclusive deals, and more!</p>
                    <a href="https://www.whatnot.com/user/<?php echo htmlspecialchars(getSetting('whatnot_username', 'tristate_cards')); ?>" class="btn btn-primary btn-sm" target="_blank"
                       onclick="logWhatnotClick(0)">Follow on Whatnot</a>
                </div>
            <?php endif; ?>

            <!-- Newsletter Signup -->
            <div class="card mt-4">
                <div class="card-body">
                    <h4 class="card-title">Stay Updated</h4>
                    <p>Subscribe to our newsletter for updates on new listings and upcoming streams.</p>
                    <form id="newsletter-form">
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Your email address" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>

            <!-- Social Links -->
            <div class="card mt-4">
                <div class="card-body">
                    <h4 class="card-title">Connect With Us</h4>
                    <div class="social-links mt-3">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                    </div>
                </div>
            </div>
            
            <!-- Recent Testimonials -->
            <div class="card mt-4">
                <div class="card-body">
                    <h4 class="card-title">Customer Testimonials</h4>
                    <div class="testimonial">
                        <p class="testimonial-text">"Tristate Cards always delivers quality cards and an amazing experience. Their card breaks are the best!"</p>
                        <p class="testimonial-author">- Mike R.</p>
                    </div>
                    <hr>
                    <div class="testimonial">
                        <p class="testimonial-text">"Fast shipping, great communication, and amazing pulls. I'm a customer for life!"</p>
                        <p class="testimonial-author">- Sarah T.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Track Whatnot clicks -->
<script>
function logWhatnotClick(streamId) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'track_click.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('type=whatnot&stream_id=' + streamId);
}

// Add eBay click tracking to Auction Nudge items
document.addEventListener('DOMContentLoaded', function() {
    // Wait for Auction Nudge to load (it loads asynchronously)
    setTimeout(function() {
        // Find all listing links from Auction Nudge
        const ebayLinks = document.querySelectorAll('#auction-nudge-homepage123 a[href*="ebay.com/itm/"]');
        
        // Add click tracking to each link
        ebayLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                // Extract listing ID from URL
                const url = new URL(link.href);
                const pathParts = url.pathname.split('/');
                const listingId = pathParts[pathParts.length - 1];
                
                // Log the click
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'track_click.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send('type=ebay&listing_id=' + listingId);
            });
        });
    }, 3000); // Give Auction Nudge 3 seconds to load
});

// Newsletter form submission
document.getElementById('newsletter-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = this.querySelector('input[type="email"]').value;
    
    // Here you would typically send this to your backend
    alert('Thanks for subscribing! We\'ll keep you updated with the latest news.');
    this.reset();
});
</script>

<?php include 'includes/footer.php'; ?>