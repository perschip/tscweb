<?php
// This is an emergency admin access file
// Place this file in your root directory and access it via browser
// IMPORTANT: Delete this file after use!

// Check for form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Display database connection info and test connection
    if (isset($_POST['action']) && $_POST['action'] === 'test_connection') {
        $db_host = $_POST['db_host'];
        $db_name = $_POST['db_name'];
        $db_user = $_POST['db_user'];
        $db_pass = $_POST['db_pass'];
        
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<div class='alert alert-success'>Connection successful!</div>";
            
            // Check if users table exists
            try {
                $stmt = $pdo->query("SELECT 1 FROM users LIMIT 1");
                echo "<div class='alert alert-success'>Users table exists.</div>";
                
                // Check for admin user
                $stmt = $pdo->query("SELECT * FROM users WHERE username = 'admin'");
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($admin) {
                    echo "<div class='alert alert-success'>Admin user found in database.</div>";
                    echo "<pre>".print_r($admin, true)."</pre>";
                } else {
                    echo "<div class='alert alert-warning'>Admin user not found. Creating default admin user...</div>";
                    
                    // Insert default admin
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, role) 
                                         VALUES ('admin', 'admin@example.com', :password, 'Admin', 'User', 'admin')");
                    
                    // Generate password hash
                    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
                    $stmt->bindParam(':password', $password_hash);
                    $stmt->execute();
                    
                    echo "<div class='alert alert-success'>Admin user created successfully!</div>";
                }
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>Users table doesn't exist. Error: " . $e->getMessage() . "</div>";
                
                // Option to create users table
                echo "<form method='post' action=''>";
                echo "<input type='hidden' name='action' value='create_users_table'>";
                echo "<input type='hidden' name='db_host' value='".htmlspecialchars($db_host)."'>";
                echo "<input type='hidden' name='db_name' value='".htmlspecialchars($db_name)."'>";
                echo "<input type='hidden' name='db_user' value='".htmlspecialchars($db_user)."'>";
                echo "<input type='hidden' name='db_pass' value='".htmlspecialchars($db_pass)."'>";
                echo "<button type='submit' class='btn btn-primary'>Create Users Table</button>";
                echo "</form>";
            }
            
            // Check if login_logs table exists
            try {
                $stmt = $pdo->query("SELECT 1 FROM login_logs LIMIT 1");
                echo "<div class='alert alert-success'>Login_logs table exists.</div>";
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>Login_logs table doesn't exist. Error: " . $e->getMessage() . "</div>";
                
                // Option to create login_logs table
                echo "<form method='post' action=''>";
                echo "<input type='hidden' name='action' value='create_login_logs_table'>";
                echo "<input type='hidden' name='db_host' value='".htmlspecialchars($db_host)."'>";
                echo "<input type='hidden' name='db_name' value='".htmlspecialchars($db_name)."'>";
                echo "<input type='hidden' name='db_user' value='".htmlspecialchars($db_user)."'>";
                echo "<input type='hidden' name='db_pass' value='".htmlspecialchars($db_pass)."'>";
                echo "<button type='submit' class='btn btn-primary'>Create Login_logs Table</button>";
                echo "</form>";
            }
            
            // Add button to start admin session
            echo "<div class='mt-4'>";
            echo "<h3>Access Admin Panel</h3>";
            echo "<form method='post' action=''>";
            echo "<input type='hidden' name='action' value='start_admin_session'>";
            echo "<button type='submit' class='btn btn-success'>Start Admin Session</button>";
            echo "</form>";
            echo "</div>";
            
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Connection failed: " . $e->getMessage() . "</div>";
        }
    }
    
    // Create users table if requested
    if (isset($_POST['action']) && $_POST['action'] === 'create_users_table') {
        $db_host = $_POST['db_host'];
        $db_name = $_POST['db_name'];
        $db_user = $_POST['db_user'];
        $db_pass = $_POST['db_pass'];
        
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create users table
            $sql = "CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                first_name VARCHAR(50),
                last_name VARCHAR(50),
                role ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            
            $pdo->exec($sql);
            echo "<div class='alert alert-success'>Users table created successfully!</div>";
            
            // Insert default admin user
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, role) 
                                 VALUES ('admin', 'admin@example.com', :password, 'Admin', 'User', 'admin')");
            
            // Generate password hash
            $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $password_hash);
            $stmt->execute();
            
            echo "<div class='alert alert-success'>Default admin user created!</div>";
            echo "<div class='alert alert-info'>Username: admin<br>Password: admin123</div>";
            
            // Add button to start admin session
            echo "<div class='mt-4'>";
            echo "<h3>Access Admin Panel</h3>";
            echo "<form method='post' action=''>";
            echo "<input type='hidden' name='action' value='start_admin_session'>";
            echo "<button type='submit' class='btn btn-success'>Start Admin Session</button>";
            echo "</form>";
            echo "</div>";
            
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error creating users table: " . $e->getMessage() . "</div>";
        }
    }
    
    // Create login_logs table if requested
    if (isset($_POST['action']) && $_POST['action'] === 'create_login_logs_table') {
        $db_host = $_POST['db_host'];
        $db_name = $_POST['db_name'];
        $db_user = $_POST['db_user'];
        $db_pass = $_POST['db_pass'];
        
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create login_logs table
            $sql = "CREATE TABLE login_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                username VARCHAR(50) NULL,
                ip_address VARCHAR(45) NOT NULL,
                success TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            $pdo->exec($sql);
            echo "<div class='alert alert-success'>Login_logs table created successfully!</div>";
            
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Error creating login_logs table: " . $e->getMessage() . "</div>";
        }
    }
    
    // Start admin session if requested
    if (isset($_POST['action']) && $_POST['action'] === 'start_admin_session') {
        session_start();
        
        // Set session variables
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['user_role'] = 'admin';
        $_SESSION['logged_in_time'] = time();
        
        echo "<div class='alert alert-success'>Admin session started successfully!</div>";
        echo "<div class='mt-3'><a href='admin/index.php' class='btn btn-primary'>Go to Admin Dashboard</a></div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Admin Access</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 5px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Emergency Admin Access</h1>
        
        <div class="warning">
            <strong>IMPORTANT:</strong> This is an emergency file to help troubleshoot admin login issues. Delete this file after use for security reasons!
        </div>
        
        <?php if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !(isset($_POST['action']) && in_array($_POST['action'], ['test_connection', 'create_users_table', 'create_login_logs_table', 'start_admin_session']))): ?>
            <h3>Step 1: Test Database Connection</h3>
            <form method="post" action="">
                <input type="hidden" name="action" value="test_connection">
                <div class="mb-3">
                    <label for="db_host" class="form-label">Database Host</label>
                    <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                </div>
                <div class="mb-3">
                    <label for="db_name" class="form-label">Database Name</label>
                    <input type="text" class="form-control" id="db_name" name="db_name" required>
                </div>
                <div class="mb-3">
                    <label for="db_user" class="form-label">Database Username</label>
                    <input type="text" class="form-control" id="db_user" name="db_user" required>
                </div>
                <div class="mb-3">
                    <label for="db_pass" class="form-label">Database Password</label>
                    <input type="password" class="form-control" id="db_pass" name="db_pass">
                </div>
                <button type="submit" class="btn btn-primary">Test Connection</button>
            </form>
        <?php endif; ?>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>