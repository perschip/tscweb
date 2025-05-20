<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eBay Listings Test</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS for eBay Listings -->
    <style>
        .an-item {
            transition: transform 0.3s;
            margin-bottom: 15px;
        }
        
        .an-item:hover {
            transform: translateY(-5px);
        }
        
        .an-price {
            color: #0275d8;
            font-weight: 600;
        }
        
        .an-listings-header {
            margin-bottom: 20px;
        }
        
        #auction-nudge-items img {
            max-width: 100%;
            height: auto;
        }
        
        .auction-nudge-customisations {
            margin-top: 10px;
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">eBay Listings Test</h1>
        
        <div class="card">
            <div class="card-body">
                <h2 class="card-title mb-4">Current eBay Listings</h2>
                
                <!-- Debug info -->
                <div class="alert alert-info mb-4">
                    <h5>Instructions:</h5>
                    <p>If your eBay listings aren't showing up, try these fixes:</p>
                    <ol>
                        <li>Make sure your eBay seller ID is correct (currently set to: <strong>tristate_cards</strong>)</li>
                        <li>Try using a different target ID (each page using Auction Nudge should have a unique target ID)</li>
                        <li>Check browser console for any JavaScript errors (press F12 to open developer tools)</li>
                        <li>Make sure your website allows scripts from auctionnudge.com</li>
                    </ol>
                </div>
                
                <!-- eBay Listings Option 1 (Original) -->
                <h5>Original Code:</h5>
                <div id="ebay-listings-1">
                    <script type="text/javascript" src="https://www.auctionnudge.com/feed/item/js/theme/responsive/page/init/img_size/120/cats_output/dropdown/search_box/1/user_profile/1/blank/1/show_logo/1/lang/english/SellerID/tristate_cards/siteid/0/MaxEntries/6/target/4c9be4bc1"></script>
                    <div id="auction-nudge-4c9be4bc1"></div>
                </div>
                
                <hr class="my-5">
                
                <!-- eBay Listings Option 2 (Fixed) -->
                <h5>Fixed Code (New Target ID):</h5>
                <div id="ebay-listings-2">
                    <script type="text/javascript" src="https://www.auctionnudge.com/feed/item/js/theme/responsive/page/init/img_size/120/cats_output/dropdown/search_box/1/user_profile/1/blank/1/show_logo/1/lang/english/SellerID/tristate_cards/siteid/0/MaxEntries/6/target/tristatecards123"></script>
                    <div id="auction-nudge-tristatecards123"></div>
                </div>
                
                <hr class="my-5">
                
                <!-- eBay Listings Option 3 (Alternative) -->
                <h5>Alternative Approach (Iframe):</h5>
                <div id="ebay-listings-3">
                    <iframe style="width:100%;border:none;min-height:500px;" src="https://www.auctionnudge.com/embed/item/responsive/SellerID/tristate_cards/siteid/0/theme/responsive/MaxEntries/6/img_size/120"></iframe>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-body">
                <h4>Auction Nudge Instructions</h4>
                <p>If none of the above options work, try these steps:</p>
                <ol>
                    <li>Visit <a href="https://www.auctionnudge.com/tools/your-ebay-items" target="_blank">Auction Nudge</a> and create a new feed</li>
                    <li>Enter your eBay username and configure display options</li>
                    <li>Copy the generated code and replace the existing code on your page</li>
                    <li>Make sure to verify your eBay seller ID is exactly as it appears on eBay</li>
                </ol>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>