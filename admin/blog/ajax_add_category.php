<?php
// Include database connection
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if request is POST and name is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    
    // Validate name
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Category name is required']);
        exit;
    }
    
    // Generate slug
    $slug = createSlug($name);
    
    try {
        // Check if category already exists
        $check_query = "SELECT id FROM blog_categories WHERE name = :name OR slug = :slug LIMIT 1";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->bindParam(':name', $name);
        $check_stmt->bindParam(':slug', $slug);
        $check_stmt->execute();
        $existing = $check_stmt->fetch();
        
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'Category already exists', 'id' => $existing['id']]);
            exit;
        }
        
        // Insert new category
        $insert_query = "INSERT INTO blog_categories (name, slug) VALUES (:name, :slug)";
        $insert_stmt = $pdo->prepare($insert_query);
        $insert_stmt->bindParam(':name', $name);
        $insert_stmt->bindParam(':slug', $slug);
        $insert_stmt->execute();
        
        $category_id = $pdo->lastInsertId();
        
        echo json_encode(['success' => true, 'message' => 'Category added successfully', 'id' => $category_id, 'name' => $name, 'slug' => $slug]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>