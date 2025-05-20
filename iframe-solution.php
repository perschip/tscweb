<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eBay Listings Iframe Version</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h1>eBay Listings - Iframe Version</h1>
        <p class="lead">This page demonstrates the iframe method of embedding eBay listings, which is less likely to be blocked by ad blockers.</p>
        
        <div class="card mb-4">
            <div class="card-body">
                <!-- Iframe Method for eBay Listings -->
                <h3 class="mb-3">Your eBay Listings</h3>
                <iframe style="width:100%;border:none;min-height:600px;" src="//www.auctionnudge.com/embed/item/responsive/SellerID/tristate_cards/siteid/0/theme/responsive/MaxEntries/12/img_size/120"></iframe>
                
                <div class="alert alert-info mt-3">
                    <h5>Why the iframe method works better:</h5>
                    <p>The iframe loads eBay listings directly from Auction Nudge's servers without using JavaScript that might be blocked by ad blockers.</p>
                </div>
            </div>
        </div>
        
        <div class="alert alert-primary">
            <h4>How to Use This on Your Site:</h4>
            <p>To add this to your main page, replace your current eBay listings code with this iframe code:</p>
            <pre class="bg-light p-3"><code>&lt;iframe style="width:100%;border:none;min-height:600px;" src="//www.auctionnudge.com/embed/item/responsive/SellerID/tristate_cards/siteid/0/theme/responsive/MaxEntries/12/img_size/120"&gt;&lt;/iframe&gt;</code></pre>
            <p>You can adjust the height, number of entries, and image size as needed.</p>
        </div>
        
        <p><a href="/" class="btn btn-primary">Return to Homepage</a></p>
    </div>
</body>
</html>