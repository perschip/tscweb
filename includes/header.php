<?php
// Get site settings
$site_name = getSetting('site_name', 'Tristate Cards');
$site_description = getSetting('site_description', 'Your trusted source for sports cards, collectibles, and memorabilia');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo htmlspecialchars($site_name); ?></title>
    
    <!-- Meta tags -->
    <meta name="description" content="<?php echo htmlspecialchars($site_description); ?>">
    <meta name="keywords" content="sports cards, trading cards, collectibles, memorabilia, card breaks, eBay listings, Whatnot">
    
    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:title" content="<?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?><?php echo htmlspecialchars($site_name); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($site_description); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    <meta property="og:image" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]"; ?>/assets/images/og-image.jpg">
    
    <!-- Favicon -->
    <link rel="icon" href="favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/apple-touch-icon.png">
    
    <!-- CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #0275d8;
            --secondary-color: #6c757d;
            --accent-color: #fd7e14;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f9f9f9;
            color: var(--dark-color);
        }

        .navbar {
            background-color: var(--dark-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--light-color);
        }

        .nav-link {
            color: var(--light-color);
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: var(--accent-color);
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }

        .hero-section h1 {
            font-weight: 700;
            font-size: 2.5rem;
        }

        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .whatnot-status {
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            background-color: rgba(0, 123, 255, 0.1);
            border: 1px solid rgba(0, 123, 255, 0.3);
        }

        .whatnot-live {
            background-color: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .whatnot-upcoming {
            background-color: rgba(0, 123, 255, 0.1);
            border: 1px solid rgba(0, 123, 255, 0.3);
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .status-live {
            background-color: #28a745;
            animation: pulse 1.5s infinite;
        }

        .status-upcoming {
            background-color: #007bff;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .footer {
            background-color: var(--dark-color);
            color: var(--light-color);
            padding: 2rem 0;
            margin-top: 3rem;
        }

        .footer a {
            color: var(--light-color);
            transition: color 0.3s;
        }

        .footer a:hover {
            color: var(--accent-color);
            text-decoration: none;
        }

        .footer-heading {
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--accent-color);
        }

        .social-links a {
            display: inline-block;
            margin-right: 15px;
            font-size: 1.5rem;
        }

        /* eBay listings customization */
        #auction-nudge-items {
            margin-top: 1.5rem;
        }

        .an-item {
            transition: transform 0.3s;
        }

        .an-item:hover {
            transform: translateY(-5px);
        }

        .an-price {
            color: var(--primary-color);
            font-weight: 600;
        }

        .an-listings-header {
            display: none;
        }

        .auction-nudge-customizations.auctions-remaining {
            color: var(--secondary-color);
            font-size: 0.9rem;
        }
        
        /* Testimonials */
        .testimonial {
            position: relative;
            padding-left: 1.5rem;
        }
        
        .testimonial:before {
            content: '"';
            font-size: 3rem;
            position: absolute;
            left: 0;
            top: -1rem;
            opacity: 0.2;
            font-family: serif;
        }
        
        .testimonial-text {
            font-style: italic;
            margin-bottom: 0.5rem;
        }
        
        .testimonial-author {
            font-weight: 600;
            text-align: right;
        }
        
        
    </style>
    
    <!-- Google Analytics -->
    <?php if ($ga_id = getSetting('google_analytics_id')): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo htmlspecialchars($ga_id); ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?php echo htmlspecialchars($ga_id); ?>');
    </script>
    <?php endif; ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                Tristate Cards
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'about.php' ? 'active' : ''; ?>" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'contact.php' ? 'active' : ''; ?>" href="contact.php">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>