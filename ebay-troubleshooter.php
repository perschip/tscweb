<?php
// This file will help troubleshoot eBay listings integration issues
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eBay Listings Troubleshooter</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">eBay Listings Troubleshooter</h1>
        
        <div class="alert alert-info">
            <p><strong>What this tool does:</strong> Tests multiple approaches to displaying your eBay listings to help identify and fix integration issues.</p>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Configuration Information</h5>
            </div>
            <div class="card-body">
                <form id="config-form" class="mb-4">
                    <div class="mb-3">
                        <label for="seller-id" class="form-label">eBay Seller ID</label>
                        <input type="text" class="form-control" id="seller-id" value="tristate_cards">
                    </div>
                    <div class="mb-3">
                        <label for="max-entries" class="form-label">Maximum Entries</label>
                        <input type="number" class="form-control" id="max-entries" value="6" min="1" max="100">
                    </div>
                    <div class="mb-3">
                        <label for="img-size" class="form-label">Image Size (pixels)</label>
                        <input type="number" class="form-control" id="img-size" value="120" min="50" max="500">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Test Cases</button>
                </form>
                
                <div class="alert alert-warning">
                    <h5>Common Issues & Solutions:</h5>
                    <ol>
                        <li><strong>No listings appear</strong> - Verify your seller ID is exactly as it appears on eBay (case sensitive)</li>
                        <li><strong>Script errors</strong> - Check for JavaScript errors in the browser console (F12 → Console tab)</li>
                        <li><strong>Mixed content warnings</strong> - Make sure your site uses HTTPS if embedding the script on an HTTPS site</li>
                        <li><strong>Duplicate IDs</strong> - Each Auction Nudge embed must have a unique target ID</li>
                    </ol>
                </div>
            </div>
        </div>
        
        <!-- Test Case 1: Standard JavaScript Integration -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Test 1: Standard JavaScript Integration</h5>
            </div>
            <div class="card-body">
                <p>This is the standard JavaScript approach using Auction Nudge's script:</p>
                <div id="test-case-1">
                    <div id="standard-js-code" class="bg-light p-3 mb-3">
                        <pre><code>&lt;script type="text/javascript" src="https://www.auctionnudge.com/feed/item/js/theme/responsive/page/init/img_size/120/cats_output/dropdown/search_box/1/user_profile/1/blank/1/show_logo/1/lang/english/SellerID/tristate_cards/siteid/0/MaxEntries/6/target/test1"&gt;&lt;/script&gt;
&lt;div id="auction-nudge-test1"&gt;&lt;/div&gt;</code></pre>
                    </div>
                    
                    <div id="standard-js-result">
                        <script type="text/javascript" src="https://www.auctionnudge.com/feed/item/js/theme/responsive/page/init/img_size/120/cats_output/dropdown/search_box/1/user_profile/1/blank/1/show_logo/1/lang/english/SellerID/tristate_cards/siteid/0/MaxEntries/6/target/test1"></script>
                        <div id="auction-nudge-test1"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Test Case 2: Alternative Target ID -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Test 2: Alternative Target ID</h5>
            </div>
            <div class="card-body">
                <p>Sometimes the target ID needs to be altered. This test uses a different target ID:</p>
                <div id="test-case-2">
                    <div id="alt-target-code" class="bg-light p-3 mb-3">
                        <pre><code>&lt;script type="text/javascript" src="https://www.auctionnudge.com/feed/item/js/theme/responsive/page/init/img_size/120/cats_output/dropdown/search_box/1/user_profile/1/blank/1/show_logo/1/lang/english/SellerID/tristate_cards/siteid/0/MaxEntries/6/target/unique123"&gt;&lt;/script&gt;
&lt;div id="auction-nudge-unique123"&gt;&lt;/div&gt;</code></pre>
                    </div>
                    
                    <div id="alt-target-result">
                        <script type="text/javascript" src="https://www.auctionnudge.com/feed/item/js/theme/responsive/page/init/img_size/120/cats_output/dropdown/search_box/1/user_profile/1/blank/1/show_logo/1/lang/english/SellerID/tristate_cards/siteid/0/MaxEntries/6/target/unique123"></script>
                        <div id="auction-nudge-unique123"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Test Case 3: iFrame Integration -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Test 3: iFrame Integration</h5>
            </div>
            <div class="card-body">
                <p>If the JavaScript approach doesn't work, this iframe method is more reliable:</p>
                <div id="test-case-3">
                    <div id="iframe-code" class="bg-light p-3 mb-3">
                        <pre><code>&lt;iframe style="width:100%;border:none;min-height:500px;" src="https://www.auctionnudge.com/embed/item/responsive/SellerID/tristate_cards/siteid/0/theme/responsive/MaxEntries/6/img_size/120"&gt;&lt;/iframe&gt;</code></pre>
                    </div>
                    
                    <div id="iframe-result">
                        <iframe style="width:100%;border:none;min-height:500px;" src="https://www.auctionnudge.com/embed/item/responsive/SellerID/tristate_cards/siteid/0/theme/responsive/MaxEntries/6/img_size/120"></iframe>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Test Case 4: Classic Layout -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Test 4: Classic Layout</h5>
            </div>
            <div class="card-body">
                <p>Sometimes the responsive theme causes issues. This test uses the classic layout:</p>
                <div id="test-case-4">
                    <div id="classic-code" class="bg-light p-3 mb-3">
                        <pre><code>&lt;script type="text/javascript" src="https://www.auctionnudge.com/feed/item/js/theme/classic/page/init/img_size/120/cats_output/dropdown/search_box/1/user_profile/1/blank/1/show_logo/1/lang/english/SellerID/tristate_cards/siteid/0/MaxEntries/6/target/classic123"&gt;&lt;/script&gt;
&lt;div id="auction-nudge-classic123"&gt;&lt;/div&gt;</code></pre>
                    </div>
                    
                    <div id="classic-result">
                        <script type="text/javascript" src="https://www.auctionnudge.com/feed/item/js/theme/classic/page/init/img_size/120/cats_output/dropdown/search_box/1/user_profile/1/blank/1/show_logo/1/lang/english/SellerID/tristate_cards/siteid/0/MaxEntries/6/target/classic123"></script>
                        <div id="auction-nudge-classic123"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="alert alert-primary">
            <h5>Next Steps:</h5>
            <p>Based on the tests above:</p>
            <ol>
                <li>Choose the integration method that works best (ideally Test 1 or 2)</li>
                <li>Copy the corresponding code</li>
                <li>Paste it into your website's main index.php file</li>
                <li>Make sure to use a unique target ID for each page that uses Auction Nudge</li>
            </ol>
            <p>If none of the tests display listings, verify that you have active listings on eBay and that the seller ID is correct.</p>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update test cases when form is submitted
        document.getElementById('config-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const sellerId = document.getElementById('seller-id').value;
            const maxEntries = document.getElementById('max-entries').value;
            const imgSize = document.getElementById('img-size').value;
            
            // Clear existing test cases
            document.getElementById('test-case-1').innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p>Loading...</p></div>';
            document.getElementById('test-case-2').innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p>Loading...</p></div>';
            document.getElementById('test-case-3').innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p>Loading...</p></div>';
            document.getElementById('test-case-4').innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p>Loading...</p></div>';
            
            // Rebuild test cases with new values
            setTimeout(() => {
                // Test 1: Standard JavaScript
                const standardJsCode = `<script type="text/javascript" src="https://www.auctionnudge.com/feed/item/js/theme/responsive/page/init/img_size/${imgSize}/cats_output/dropdown/search_box/1/user_profile/1/blank/1/show_logo/1/lang/english/SellerID/${sellerId}/siteid/0/MaxEntries/${maxEntries}/target/test1"><\/script>
<div id="auction-nudge-test1"></div>`;
                
                const standardJsResult = `<script type="text/javascript" src="https://www.auctionnudge.com/feed/item/js/theme/responsive/page/init/img_size/${imgSize}/cats_output/dropdown/search_box/1/user_profile/1/blank/1/show_logo/1/lang/english/SellerID/${sellerId}/siteid/0/MaxEntries/${maxEntries}/target/test1"><\/script>
<div id="auction-nudge-test1"></div>`;
                
                document.getElementById('test-case-1').innerHTML = `
                    <div id="standard-js-code" class="bg-light p-3 mb-3">
                        <pre><code>${standardJsCode.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</code></pre>
                    </div>
                    <div id="standard-js-result">${standardJsResult}</div>
                `;
                
                // Test 2: Alternative Target ID
                const altTargetCode = `<script type="text/javascript" src="https://www.auctionnudge.com/feed/item/js/theme/responsive/page/init/img_size/${imgSize}/cats_output/dropdown/search_box/1/user_profile/1/blank/1/show_logo/1/lang/english/SellerID/${sellerId}/siteid/0/MaxEntries/${maxEntries}/target/unique123"><\/script>
<div id="auction-nudge-unique123"></div>`;
                
                const altTargetResult = `<script type="text/javascript" src="https://www.auctionnudge.com/feed/item/js/theme/responsive/page/init/img_size/${imgSize}/cats_output/dropdown/search_box/1/user_profile/1/blank/1/show_logo/1/lang/english/SellerID/${sellerId}/siteid/0/MaxEntries/${maxEntries}/target/unique123"><\/script>
<div id="auction-nudge-unique123"></div>`;
                
                document.getElementById('test-case-2').innerHTML = `
                    <div id="alt-target-code" class="bg-light p-3 mb-3">
                        <pre><code>${altTargetCode.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</code></pre>
                    </div>
                    <div id="alt-target-result">${altTargetResult}</div>
                `;
                
                // Test 3: iFrame
                const iframeCode = `<iframe style="width:100%;border:none;min-height:500px;" src="https://www.auctionnudge.com/embed/item/responsive/SellerID/${sellerId}/siteid/0/theme/responsive/MaxEntries/${maxEntries}/img_size/${imgSize}"></iframe>`;
                
                document.getElementById('test-case-3').innerHTML = `
                    <div id="iframe-code" class="bg-light p-3 mb-3">
                        <pre><code>${iframeCode.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</code></pre>
                    </div>
                    <div id="iframe-result">
                        <iframe style="width:100%;border:none;min-height:500px;" src="https://www.auctionnudge.com/embed/item/responsive/SellerID/${sellerId}/siteid/0/theme/responsive/MaxEntries/${maxEntries}/img_size/${imgSize}"></iframe>
                    </div>
                `;
                
                // Test 4: Classic Layout
                const classicCode = `<script type="text/javascript" src="https://www.auctionnudge.com/feed/item/js/theme/classic/page/init/img_size/${imgSize}/cats_output/dropdown/search_box/1/user_profile/1/blank/1/show_logo/1/lang/english/SellerID/${sellerId}/siteid/0/MaxEntries/${maxEntries}/target/classic123"><\/script>
<div id="auction-nudge-classic123"></div>`;
                
                const classicResult = `<script type="text/javascript" src="https://www.auctionnudge.com/feed/item/js/theme/classic/page/init/img_size/${imgSize}/cats_output/dropdown/search_box/1/user_profile/1/blank/1/show_logo/1/lang/english/SellerID/${sellerId}/siteid/0/MaxEntries/${maxEntries}/target/classic123"><\/script>
<div id="auction-nudge-classic123"></div>`;
                
                document.getElementById('test-case-4').innerHTML = `
                    <div id="classic-code" class="bg-light p-3 mb-3">
                        <pre><code>${classicCode.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</code></pre>
                    </div>
                    <div id="classic-result">${classicResult}</div>
                `;
            }, 1000);
        });
    </script>
</body>
</html>