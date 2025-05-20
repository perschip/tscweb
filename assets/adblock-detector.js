// AdBlock Detector
(function() {
    // Create a bait element that ad blockers typically target
    function createBait() {
        const bait = document.createElement('div');
        bait.setAttribute('class', 'ad-banner ad adsbox ad-placement ad-container');
        bait.setAttribute('id', 'ad-detector');
        bait.style.position = 'absolute';
        bait.style.height = '1px';
        bait.style.width = '1px';
        bait.style.left = '-10000px';
        bait.style.top = '-10000px';
        document.body.appendChild(bait);
        return bait;
    }

    // Check if the bait element is hidden (which would indicate an ad blocker is active)
    function checkAdBlocker() {
        const bait = createBait();
        
        setTimeout(function() {
            let adBlockDetected = false;
            
            // Method 1: Check if the element has been hidden or removed
            if (bait.offsetParent === null || 
                bait.offsetHeight === 0 || 
                bait.offsetWidth === 0 || 
                bait.clientHeight === 0 || 
                bait.clientWidth === 0) {
                adBlockDetected = true;
            }
            
            // Method 2: Check computed style
            const computed = window.getComputedStyle(bait);
            if (computed && (computed.display === 'none' || 
                             computed.visibility === 'hidden' || 
                             computed.opacity === '0')) {
                adBlockDetected = true;
            }
            
            // Clean up the bait
            if (bait.parentNode) {
                bait.parentNode.removeChild(bait);
            }
            
            // Store adblock status in a global variable
            window.adBlockDetected = adBlockDetected;
            
            // Check eBay listings if adblock is detected
            if (adBlockDetected) {
                checkEbayListings();
            }
            
        }, 100);
    }

    // Check for eBay listing issues
    function checkEbayListings() {
        // Wait for Auction Nudge to attempt to load
        setTimeout(function() {
            const ebayContainer = document.getElementById('ebay-listings');
            if (!ebayContainer) return;
            
            const auctionNudgeElements = ebayContainer.querySelectorAll('[id^="auction-nudge-"]');
            let listingsLoaded = false;

            // Check if any auction nudge elements exist and have content
            auctionNudgeElements.forEach(element => {
                if (element.innerHTML.trim() !== '' && element.querySelectorAll('.an-item').length > 0) {
                    listingsLoaded = true;
                }
            });

            // If no listings were loaded, show error message
            if (!listingsLoaded) {
                showEbayErrorMessage(ebayContainer);
            }
        }, 3000); // Allow 3 seconds for Auction Nudge to load
    }

    // Show error message for eBay listings
    function showEbayErrorMessage(container) {
        // Create error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-warning';
        errorDiv.innerHTML = `
            <h5 class="mb-3"><i class="fas fa-exclamation-triangle me-2"></i> eBay Listings Blocked</h5>
            <p>We've detected that you're using an ad blocker which is preventing our eBay listings from displaying properly.</p>
            <p class="mb-0">To view our current listings, please consider temporarily disabling your ad blocker for this site, or visit our <a href="https://www.ebay.com/usr/tristate_cards" target="_blank" class="alert-link">eBay store directly <i class="fas fa-external-link-alt fa-xs"></i></a>.</p>
        `;
        
        // Find the auction nudge elements and hide them
        const auctionNudgeElements = container.querySelectorAll('[id^="auction-nudge-"]');
        auctionNudgeElements.forEach(element => {
            element.style.display = 'none';
        });
        
        // Empty the container and add our error message
        container.appendChild(errorDiv);
    }

    // Initialize detection after the page has loaded
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(checkAdBlocker, 1);
    } else {
        document.addEventListener('DOMContentLoaded', checkAdBlocker);
    }
    
    // Add global checker function that can be called from other scripts
    window.checkEbayListings = checkEbayListings;
})();