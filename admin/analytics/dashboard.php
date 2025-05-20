<?php
// Include database connection and auth check
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Get all view data
function getAnalyticsData($pdo, $period = 'month') {
    $timeConstraint = '';
    
    switch ($period) {
        case 'week':
            $timeConstraint = "WHERE visit_date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)";
            break;
        case 'month':
            $timeConstraint = "WHERE visit_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)";
            break;
        case 'year':
            $timeConstraint = "WHERE visit_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR)";
            break;
        case 'all':
            $timeConstraint = "";
            break;
        default:
            $timeConstraint = "WHERE visit_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)";
    }
    
    try {
        // Overall stats
        $visitsQuery = "SELECT 
                          COUNT(*) as total_visits,
                          COUNT(DISTINCT visitor_ip) as unique_visitors,
                          AVG(pages_viewed) as avg_pages,
                          AVG(time_on_site) as avg_time
                        FROM visits $timeConstraint";
                        
        $visitsStmt = $pdo->prepare($visitsQuery);
        $visitsStmt->execute();
        $visitsStats = $visitsStmt->fetch();
        
        // eBay clicks
        $ebayQuery = "SELECT COUNT(*) as total_clicks FROM ebay_clicks $timeConstraint";
        $ebayStmt = $pdo->prepare($ebayQuery);
        $ebayStmt->execute();
        $ebayClicks = $ebayStmt->fetch()['total_clicks'];
        
        // Whatnot clicks
        $whatnotQuery = "SELECT COUNT(*) as total_clicks FROM whatnot_clicks $timeConstraint";
        $whatnotStmt = $pdo->prepare($whatnotQuery);
        $whatnotStmt->execute();
        $whatnotClicks = $whatnotStmt->fetch()['total_clicks'];
        
        // Daily visits trend
        $dailyTrendQuery = "SELECT 
                               DATE_FORMAT(visit_date, '%Y-%m-%d') as date,
                               COUNT(*) as visits,
                               COUNT(DISTINCT visitor_ip) as unique_visitors
                             FROM visits 
                             $timeConstraint
                             GROUP BY DATE_FORMAT(visit_date, '%Y-%m-%d')
                             ORDER BY date ASC";
                             
        $dailyTrendStmt = $pdo->prepare($dailyTrendQuery);
        $dailyTrendStmt->execute();
        $dailyTrend = $dailyTrendStmt->fetchAll();
        
        // Referrer sources
        $referrerQuery = "SELECT 
                            CASE
                                WHEN referrer LIKE '%google.com%' THEN 'Google'
                                WHEN referrer LIKE '%facebook.com%' THEN 'Facebook'
                                WHEN referrer LIKE '%instagram.com%' THEN 'Instagram'
                                WHEN referrer LIKE '%twitter.com%' THEN 'Twitter'
                                WHEN referrer LIKE '%whatnot.com%' THEN 'Whatnot'
                                WHEN referrer LIKE '%ebay.com%' THEN 'eBay'
                                WHEN referrer = '' THEN 'Direct'
                                ELSE 'Other'
                            END as source,
                            COUNT(*) as count
                          FROM visits
                          $timeConstraint
                          GROUP BY source
                          ORDER BY count DESC";
                          
        $referrerStmt = $pdo->prepare($referrerQuery);
        $referrerStmt->execute();
        $referrers = $referrerStmt->fetchAll();
        
        return [
            'visits' => $visitsStats,
            'ebay_clicks' => $ebayClicks,
            'whatnot_clicks' => $whatnotClicks,
            'daily_trend' => $dailyTrend,
            'referrers' => $referrers
        ];
    } catch (PDOException $e) {
        // Return default values on error
        return [
            'visits' => [
                'total_visits' => 0,
                'unique_visitors' => 0,
                'avg_pages' => 0,
                'avg_time' => 0
            ],
            'ebay_clicks' => 0,
            'whatnot_clicks' => 0,
            'daily_trend' => [],
            'referrers' => []
        ];
    }
}

// Check if period filter was applied
$period = isset($_GET['period']) ? $_GET['period'] : 'month';
$validPeriods = ['week', 'month', 'year', 'all'];
if (!in_array($period, $validPeriods)) {
    $period = 'month';
}

// Get analytics data
try {
    $analyticsData = getAnalyticsData($pdo, $period);

    // Format daily trend data for chart
    $dates = [];
    $visits = [];
    $uniqueVisitors = [];

    foreach ($analyticsData['daily_trend'] as $day) {
        $dates[] = date('M j', strtotime($day['date']));
        $visits[] = $day['visits'];
        $uniqueVisitors[] = $day['unique_visitors'];
    }

    // Format referrer data for chart
    $referrerLabels = [];
    $referrerData = [];

    foreach ($analyticsData['referrers'] as $referrer) {
        $referrerLabels[] = $referrer['source'];
        $referrerData[] = $referrer['count'];
    }
} catch (Exception $e) {
    // Default values if anything fails
    $analyticsData = [
        'visits' => [
            'total_visits' => 0,
            'unique_visitors' => 0,
            'avg_pages' => 0,
            'avg_time' => 0
        ],
        'ebay_clicks' => 0,
        'whatnot_clicks' => 0
    ];
    $dates = [];
    $visits = [];
    $uniqueVisitors = [];
    $referrerLabels = [];
    $referrerData = [];
}

// Include header
$page_title = 'Analytics Dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Tristate Cards Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #212529;
            color: #fff;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 5px;
            border-radius: 4px;
        }
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .sidebar .nav-link.active {
            background-color: #0d6efd;
            color: #fff;
        }
        .sidebar-heading {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.5);
        }
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse p-0">
                <div class="d-flex flex-column p-3 h-100">
                    <a href="/admin/index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                        <span class="fs-4">Tristate Cards</span>
                    </a>
                    <hr>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="/admin/index.php" class="nav-link">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <p class="sidebar-heading mt-4 mb-2">Integrations</p>
                        </li>
                        <li>
                            <a href="/admin/whatnot/settings.php" class="nav-link">
                                <i class="fas fa-video me-2"></i>
                                Whatnot Integration
                            </a>
                        </li>
                        <li>
                            <p class="sidebar-heading mt-4 mb-2">System</p>
                        </li>
                        <li>
                            <a href="/admin/analytics/dashboard.php" class="nav-link active">
                                <i class="fas fa-chart-line me-2"></i>
                                Analytics
                            </a>
                        </li>
                    </ul>
                    <hr>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://via.placeholder.com/32" alt="Admin" width="32" height="32" class="rounded-circle me-2">
                            <strong><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?></strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                            <li><a class="dropdown-item" href="/admin/logout.php">Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Analytics Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="?period=week" class="btn btn-sm btn-outline-secondary <?php echo $period === 'week' ? 'active' : ''; ?>">Week</a>
                            <a href="?period=month" class="btn btn-sm btn-outline-secondary <?php echo $period === 'month' ? 'active' : ''; ?>">Month</a>
                            <a href="?period=year" class="btn btn-sm btn-outline-secondary <?php echo $period === 'year' ? 'active' : ''; ?>">Year</a>
                            <a href="?period=all" class="btn btn-sm btn-outline-secondary <?php echo $period === 'all' ? 'active' : ''; ?>">All Time</a>
                        </div>
                    </div>
                </div>

                <!-- Stats Overview -->
                <div class="row mt-4">
                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Total Visits</h6>
                                <div class="stat-value text-primary"><?php echo number_format($analyticsData['visits']['total_visits']); ?></div>
                                <div class="stat-label"><?php echo ucfirst($period); ?> total</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Unique Visitors</h6>
                                <div class="stat-value text-success"><?php echo number_format($analyticsData['visits']['unique_visitors']); ?></div>
                                <div class="stat-label"><?php echo ucfirst($period); ?> total</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Whatnot Clicks</h6>
                                <div class="stat-value text-danger"><?php echo number_format($analyticsData['whatnot_clicks']); ?></div>
                                <div class="stat-label"><?php echo ucfirst($period); ?> total</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title text-muted">eBay Clicks</h6>
                                <div class="stat-value text-warning"><?php echo number_format($analyticsData['ebay_clicks']); ?></div>
                                <div class="stat-label"><?php echo ucfirst($period); ?> total</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row">
                    <!-- Visitor Trend Chart -->
                    <div class="col-md-8 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Visitor Trend</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($dates)): ?>
                                <div class="alert alert-info">
                                    No visitor data available for the selected period. Data will appear here as visitors browse your site.
                                </div>
                                <?php else: ?>
                                <div class="chart-container">
                                    <canvas id="visitorTrendChart"></canvas>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Referrer Chart -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Traffic Sources</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($referrerLabels)): ?>
                                <div class="alert alert-info">
                                    No referrer data available for the selected period. Data will appear here as visitors arrive from different sources.
                                </div>
                                <?php else: ?>
                                <div class="chart-container">
                                    <canvas id="referrerChart"></canvas>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Behavior Stats -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">User Behavior</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <h6 class="small fw-bold">Pages Per Visit <span class="float-end"><?php echo number_format($analyticsData['visits']['avg_pages'], 1); ?></span></h6>
                                    <div class="progress mb-4">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo min(100, $analyticsData['visits']['avg_pages'] * 25); ?>%" aria-valuenow="<?php echo $analyticsData['visits']['avg_pages']; ?>" aria-valuemin="0" aria-valuemax="10"></div>
                                    </div>
                                    
                                    <h6 class="small fw-bold">Avg. Time on Site <span class="float-end"><?php echo formatTimeDuration($analyticsData['visits']['avg_time']); ?></span></h6>
                                    <div class="progress mb-4">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo min(100, ($analyticsData['visits']['avg_time'] / 300) * 100); ?>%" aria-valuenow="<?php echo $analyticsData['visits']['avg_time']; ?>" aria-valuemin="0" aria-valuemax="300"></div>
                                    </div>
                                    
                                    <h6 class="small fw-bold">Conversion Rate (Clicks/Visits) <span class="float-end"><?php echo number_format(($analyticsData['ebay_clicks'] + $analyticsData['whatnot_clicks']) / max(1, $analyticsData['visits']['total_visits']) * 100, 1); ?>%</span></h6>
                                    <div class="progress mb-4">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo min(100, (($analyticsData['ebay_clicks'] + $analyticsData['whatnot_clicks']) / max(1, $analyticsData['visits']['total_visits']) * 100) * 2); ?>%" aria-valuenow="<?php echo ($analyticsData['ebay_clicks'] + $analyticsData['whatnot_clicks']) / max(1, $analyticsData['visits']['total_visits']) * 100; ?>" aria-valuemin="0" aria-valuemax="50"></div>
                                    </div>
                                </div>
                                
                                <p class="mb-0 text-muted">
                                    <i class="fas fa-info-circle me-1"></i> 
                                    These metrics help you understand how visitors interact with your site. Higher pages per visit and time on site generally indicate more engaged users.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group mb-4">
                                    <a href="/admin/index.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        Go to Dashboard
                                        <span class="badge bg-primary rounded-pill"><i class="fas fa-arrow-right"></i></span>
                                    </a>
                                    <a href="/admin/whatnot/settings.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        Update Whatnot Settings
                                        <span class="badge bg-primary rounded-pill"><i class="fas fa-arrow-right"></i></span>
                                    </a>
                                    <a href="/" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank">
                                        View Website
                                        <span class="badge bg-primary rounded-pill"><i class="fas fa-external-link-alt"></i></span>
                                    </a>
                                </div>
                                
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">Analytics Tips</h6>
                                    <p class="mb-0">Track which eBay listings and Whatnot streams get the most clicks to optimize your content strategy. Higher engagement typically leads to more sales.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <script>
        <?php if (!empty($dates)): ?>
        // Visitor Trend Chart
        const visitorTrendChart = document.getElementById('visitorTrendChart').getContext('2d');
        
        new Chart(visitorTrendChart, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [
                    {
                        label: 'Total Visits',
                        data: <?php echo json_encode($visits); ?>,
                        backgroundColor: 'rgba(13, 110, 253, 0.05)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        pointBackgroundColor: 'rgba(13, 110, 253, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Unique Visitors',
                        data: <?php echo json_encode($uniqueVisitors); ?>,
                        backgroundColor: 'rgba(25, 135, 84, 0.05)',
                        borderColor: 'rgba(25, 135, 84, 1)',
                        pointBackgroundColor: 'rgba(25, 135, 84, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(25, 135, 84, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        <?php endif; ?>
        
        <?php if (!empty($referrerLabels)): ?>
        // Referrer Chart
        const referrerChart = document.getElementById('referrerChart').getContext('2d');
        
        new Chart(referrerChart, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($referrerLabels); ?>,
                datasets: [
                    {
                        data: <?php echo json_encode($referrerData); ?>,
                        backgroundColor: [
                            'rgba(13, 110, 253, 0.8)',
                            'rgba(25, 135, 84, 0.8)',
                            'rgba(220, 53, 69, 0.8)',
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(111, 66, 193, 0.8)',
                            'rgba(23, 162, 184, 0.8)',
                            'rgba(108, 117, 125, 0.8)',
                            'rgba(40, 167, 69, 0.8)'
                        ],
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                },
                cutout: '70%'
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>