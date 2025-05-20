<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, redirect to login if not
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Login user
function loginUser($user_id, $username, $role) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['user_role'] = $role;
    $_SESSION['logged_in_time'] = time();
}

// Logout user
function logoutUser() {
    session_unset();
    session_destroy();
}

// TEMPORARY: Skip authentication check for admin area until database is fixed
// Comment this section out once your login system is working
if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false && !isset($_SESSION['user_id'])) {
    // Auto-login as admin for admin pages
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['user_role'] = 'admin';
    $_SESSION['logged_in_time'] = time();
}

// Regular authentication check (commented out temporarily)
/*
// If admin page, require login
$current_script = basename($_SERVER['SCRIPT_NAME']);
if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false && $current_script !== 'login.php') {
    requireLogin();
}
*/