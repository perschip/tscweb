<?php
// Include database connection and auth check
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Check for login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Username is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    // If no validation errors, attempt login
    if (empty($errors)) {
        try {
            $query = "SELECT id, username, password, role FROM users WHERE username = :username LIMIT 1";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                loginUser($user['id'], $user['username'], $user['role']);
                
                // Redirect to dashboard
                header('Location: index.php');
                exit;
            } else {
                // Invalid credentials
                $errors[] = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Tristate Cards</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .login-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .login-header {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .login-logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .login-subtext {
            font-size: 14px;
            opacity: 0.8;
        }
        .login-body {
            padding: 30px;
        }
        .btn-login {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            border: none;
            width: 100%;
            padding: 10px;
        }
        .input-group-text {
            background-color: transparent;
            border-right: none;
        }
        .form-control.login-input {
            border-left: none;
        }
        .form-control.login-input:focus {
            box-shadow: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="login-header">
                <div class="login-logo">Tristate Cards</div>
                <div class="login-subtext">Admin Portal</div>
            </div>
            <div class="login-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="login.php">
                    <div class="mb-4">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" class="form-control login-input" id="username" name="username" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                   required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control login-input" id="password" name="password" required>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                        <label class="form-check-label" for="remember_me">Remember me</label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i> Login
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-4">
                    <a href="#" class="text-decoration-none">Forgot your password?</a>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3 text-muted">
            <small>&copy; 2025 Tristate Cards. All rights reserved.</small>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>