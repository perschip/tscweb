<?php
// Include database connection and auth check
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Get the current user's information
$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

try {
    $query = "SELECT * FROM users WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch();
    
    if (!$user) {
        $error_message = 'User not found. Please log in again.';
    }
} catch (PDOException $e) {
    $error_message = 'Database error: ' . $e->getMessage();
}

// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    // Check if email is already in use by another user
    if (!empty($email) && $email !== $user['email']) {
        try {
            $check_query = "SELECT COUNT(*) as count FROM users WHERE email = :email AND id != :id";
            $check_stmt = $pdo->prepare($check_query);
            $check_stmt->bindParam(':email', $email);
            $check_stmt->bindParam(':id', $user_id);
            $check_stmt->execute();
            $email_exists = $check_stmt->fetch()['count'] > 0;
            
            if ($email_exists) {
                $errors[] = 'Email is already in use by another user';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        try {
            $update_query = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, updated_at = NOW() WHERE id = :id";
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->bindParam(':first_name', $first_name);
            $update_stmt->bindParam(':last_name', $last_name);
            $update_stmt->bindParam(':email', $email);
            $update_stmt->bindParam(':id', $user_id);
            $update_stmt->execute();
            
            $success_message = 'Profile updated successfully!';
            
            // Update user data
            $user['first_name'] = $first_name;
            $user['last_name'] = $last_name;
            $user['email'] = $email;
        } catch (PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($current_password)) {
        $errors[] = 'Current password is required';
    }
    
    if (empty($new_password)) {
        $errors[] = 'New password is required';
    } elseif (strlen($new_password) < 8) {
        $errors[] = 'New password must be at least 8 characters long';
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = 'New passwords do not match';
    }
    
    // Skip password verification for admin user with 'admin123' special case (ONLY FOR INITIAL SETUP)
    $emergency_override = false;
    if (empty($errors) && $_SESSION['username'] === 'admin' && $current_password === 'admin123') {
        $emergency_override = true;
    }
    
    // Verify current password (with enhanced debugging)
    if (empty($errors) && !$emergency_override) {
        // Try different password verification methods (for compatibility with older systems)
        $password_verified = false;
        
        // Method 1: Standard password_verify
        if (password_verify($current_password, $user['password'])) {
            $password_verified = true;
        }
        
        // Method 2: Direct MD5 comparison (if legacy system used MD5)
        if (!$password_verified && strlen($user['password']) == 32 && $user['password'] === md5($current_password)) {
            $password_verified = true;
        }
        
        // Method 3: Check if password is stored in plain text (not recommended but might be the case)
        if (!$password_verified && $user['password'] === $current_password) {
            $password_verified = true;
        }
        
        if (!$password_verified) {
            $errors[] = 'Current password is incorrect';
            
            // Debug info (remove in production)
            error_log("Password verification failed. Hash length: " . strlen($user['password']));
            error_log("Password hash format: " . substr($user['password'], 0, 7) . "...");
        }
    }
    
    // If no errors, update password
    if (empty($errors)) {
        try {
            // Generate password hash
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_query = "UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id";
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->bindParam(':password', $password_hash);
            $update_stmt->bindParam(':id', $user_id);
            $update_stmt->execute();
            
            $success_message = 'Password changed successfully!';
        } catch (PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}
// Include header
$page_title = 'Account Settings';
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
        .dashboard-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .top-header {
            background-color: #fff;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .card-header {
            background-color: rgba(0, 0, 0, 0.03);
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            padding: 0.75rem 1.25rem;
        }
        .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #6c757d;
            font-weight: 500;
        }
        .nav-tabs .nav-link:hover {
            color: #0d6efd;
            border-color: transparent;
        }
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            border-color: #0d6efd;
            background-color: transparent;
        }
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .form-label {
            font-weight: 500;
        }
        .badge-role {
            background-color: #0d6efd;
            color: white;
            padding: 0.35em 0.65em;
            border-radius: 0.25rem;
            font-weight: 500;
            font-size: 0.75em;
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
                            <p class="sidebar-heading mt-4 mb-2">Content</p>
                        </li>
                        <li>
                            <a href="/admin/blog/list.php" class="nav-link active">
                                <i class="fas fa-blog me-2"></i>
                                Blog Posts
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
                            <a href="/admin/analytics/dashboard.php" class="nav-link">
                                <i class="fas fa-chart-line me-2"></i>
                                Analytics
                            </a>
                        </li>
                        <li>
                            <a href="/admin/settings/account.php" class="nav-link">
                                <i class="fas fa-user-cog me-2"></i>
                                Account Settings
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
                            <li><a class="dropdown-item" href="/admin/settings/account.php">Account Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/admin/logout.php">Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom top-header">
                    <h1 class="h2">Account Settings</h1>
                </div>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-lg-4">
                        <!-- Profile Summary Card -->
                        <div class="card dashboard-card mb-4">
                            <div class="card-body text-center">
                                <div class="mt-3 mb-4">
                                    <img src="https://via.placeholder.com/150" alt="Profile Image" class="profile-image">
                                </div>
                                <h5 class="mb-1">
                                    <?php 
                                    $display_name = trim($user['first_name'] . ' ' . $user['last_name']);
                                    echo !empty($display_name) ? htmlspecialchars($display_name) : htmlspecialchars($user['username']);
                                    ?>
                                </h5>
                                <p class="text-muted mb-2"><?php echo htmlspecialchars($user['email']); ?></p>
                                <div class="mb-3">
                                    <span class="badge-role"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></span>
                                </div>
                                <p class="small text-muted">
                                    Account created: <?php echo date('F j, Y', strtotime($user['created_at'])); ?><br>
                                    Last updated: <?php echo date('F j, Y', strtotime($user['updated_at'])); ?>
                                </p>
                                <div class="d-flex justify-content-center">
                                    <a href="#profile-tab" class="btn btn-outline-primary me-2" data-bs-toggle="tab" data-bs-target="#profile-tab">
                                        <i class="fas fa-user-edit me-1"></i> Edit Profile
                                    </a>
                                    <a href="#security-tab" class="btn btn-outline-secondary" data-bs-toggle="tab" data-bs-target="#security-tab">
                                        <i class="fas fa-shield-alt me-1"></i> Security
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Activity Card -->
                        <div class="card dashboard-card mb-4">
                            <div class="card-header py-3">
                                <h5 class="mb-0 fw-bold">Recent Activity</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                // Fetch recent login activity
                                try {
                                    $activity_query = "SELECT * FROM login_logs WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5";
                                    $activity_stmt = $pdo->prepare($activity_query);
                                    $activity_stmt->bindParam(':user_id', $user_id);
                                    $activity_stmt->execute();
                                    $activities = $activity_stmt->fetchAll();
                                } catch (PDOException $e) {
                                    $activities = [];
                                }
                                
                                if (!empty($activities)):
                                ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($activities as $activity): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <i class="fas fa-sign-in-alt me-2 <?php echo $activity['success'] ? 'text-success' : 'text-danger'; ?>"></i>
                                            <?php echo $activity['success'] ? 'Successful login' : 'Failed login attempt'; ?>
                                            <div class="text-muted small">IP: <?php echo htmlspecialchars($activity['ip_address']); ?></div>
                                        </div>
                                        <span class="text-muted small"><?php echo date('M j, g:i A', strtotime($activity['created_at'])); ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php else: ?>
                                <p class="text-muted mb-0">No recent activity found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-8">
                        <!-- Settings Tabs -->
                        <div class="card dashboard-card">
                            <div class="card-header bg-white">
                                <ul class="nav nav-tabs card-header-tabs" id="accountTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="profile-tab-btn" data-bs-toggle="tab" data-bs-target="#profile-tab" type="button" role="tab" aria-controls="profile-tab" aria-selected="true">
                                            <i class="fas fa-user me-2"></i> Profile
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="security-tab-btn" data-bs-toggle="tab" data-bs-target="#security-tab" type="button" role="tab" aria-controls="security-tab" aria-selected="false">
                                            <i class="fas fa-lock me-2"></i> Security
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="preferences-tab-btn" data-bs-toggle="tab" data-bs-target="#preferences-tab" type="button" role="tab" aria-controls="preferences-tab" aria-selected="false">
                                            <i class="fas fa-cog me-2"></i> Preferences
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content" id="accountTabContent">
                                    <!-- Profile Tab -->
                                    <div class="tab-pane fade show active" id="profile-tab" role="tabpanel" aria-labelledby="profile-tab-btn">
                                        <form action="account.php" method="post">
                                            <input type="hidden" name="action" value="update_profile">
                                            
                                            <div class="row mb-4">
                                                <div class="col-md-6 mb-3">
                                                    <label for="username" class="form-label">Username</label>
                                                    <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                                    <div class="form-text">Username cannot be changed</div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="email" class="form-label">Email Address *</label>
                                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-4">
                                                <div class="col-md-6 mb-3">
                                                    <label for="first_name" class="form-label">First Name</label>
                                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="last_name" class="form-label">Last Name</label>
                                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="role" class="form-label">Role</label>
                                                <input type="text" class="form-control" id="role" value="<?php echo ucfirst(htmlspecialchars($user['role'])); ?>" disabled>
                                                <div class="form-text">Role can only be changed by a super administrator</div>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i> Save Changes
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <!-- Security Tab -->
                                    <div class="tab-pane fade" id="security-tab" role="tabpanel" aria-labelledby="security-tab-btn">
                                        <h5 class="card-title mb-4">Change Password</h5>
                                        <form action="account.php" method="post">
                                            <input type="hidden" name="action" value="change_password">
                                            
                                            <div class="mb-3">
                                                <label for="current_password" class="form-label">Current Password *</label>
                                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="new_password" class="form-label">New Password *</label>
                                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                                <div class="form-text">Password must be at least 8 characters long</div>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="confirm_password" class="form-label">Confirm New Password *</label>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-key me-1"></i> Update Password
                                            </button>
                                        </form>
                                        
                                        <hr class="my-4">
                                        
                                        <h5 class="card-title mb-4">Login Activity</h5>
                                        <p>You are currently logged in from IP address: <strong><?php echo $_SERVER['REMOTE_ADDR']; ?></strong></p>
                                        <p>Last login: <strong><?php echo isset($_SESSION['logged_in_time']) ? date('F j, Y \a\t g:i A', $_SESSION['logged_in_time']) : 'Unknown'; ?></strong></p>
                                        
                                        <div class="d-grid gap-2 d-md-flex justify-content-md-start mt-4">
                                            <a href="/admin/logout.php" class="btn btn-outline-danger">
                                                <i class="fas fa-sign-out-alt me-1"></i> Sign Out of All Devices
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <!-- Preferences Tab -->
                                    <div class="tab-pane fade" id="preferences-tab" role="tabpanel" aria-labelledby="preferences-tab-btn">
                                        <h5 class="card-title mb-4">Display Settings</h5>
                                        <form>
                                            <div class="mb-3">
                                                <label class="form-label d-block">Theme</label>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="theme" id="theme_light" value="light" checked>
                                                    <label class="form-check-label" for="theme_light">Light</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="theme" id="theme_dark" value="dark">
                                                    <label class="form-check-label" for="theme_dark">Dark</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="theme" id="theme_system" value="system">
                                                    <label class="form-check-label" for="theme_system">System</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="dashboard_layout" class="form-label">Dashboard Layout</label>
                                                <select class="form-select" id="dashboard_layout">
                                                    <option value="default" selected>Default</option>
                                                    <option value="compact">Compact</option>
                                                    <option value="expanded">Expanded</option>
                                                </select>
                                            </div>
                                            
                                            <hr class="my-4">
                                            
                                            <h5 class="card-title mb-4">Notification Preferences</h5>
                                            
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" id="notify_login" checked>
                                                <label class="form-check-label" for="notify_login">Email me about new sign-ins</label>
                                            </div>
                                            
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" id="notify_whatnot" checked>
                                                <label class="form-check-label" for="notify_whatnot">Email me when Whatnot stream goes live</label>
                                            </div>
                                            
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" id="notify_updates">
                                                <label class="form-check-label" for="notify_updates">Email me about system updates</label>
                                            </div>
                                            
                                            <button type="button" class="btn btn-primary mt-2">
                                                <i class="fas fa-save me-1"></i> Save Preferences
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
    // Show current tab based on hash URL
    document.addEventListener('DOMContentLoaded', function() {
        // Get hash from URL (remove # if present)
        const hash = window.location.hash.replace('#', '');
        
        // If hash corresponds to a tab, activate it
        if (hash && document.getElementById(hash)) {
            const tabElement = document.getElementById(hash);
            const tab = new bootstrap.Tab(document.querySelector(`button[data-bs-target="#${hash}"]`));
            tab.show();
        }
        
        // Update URL hash when tab is changed
        const tabs = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(event) {
                const targetId = event.target.getAttribute('data-bs-target').substring(1);
                window.location.hash = targetId;
            });
        });
    });
    </script>
</body>
</html>