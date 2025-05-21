<?php
// Direct post submission script - an emergency solution for adding blog posts
// Save this as admin/blog/direct-submit.php

// Include database connection and essential files
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Initialize result variables
$success = false;
$message = '';

// Process post creation on form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize form data
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = $_POST['status'] ?? 'published';
    
    // Basic validation
    if (empty($title)) {
        $message = 'Error: Title is required';
    } elseif (empty($content)) {
        $message = 'Error: Content is required';
    } else {
        // Generate slug from title
        $slug = createSlug($title);
        
        // Check for duplicate slug
        try {
            $check_query = "SELECT COUNT(*) as count FROM blog_posts WHERE slug = :slug";
            $check_stmt = $pdo->prepare($check_query);
            $check_stmt->bindParam(':slug', $slug);
            $check_stmt->execute();
            $row = $check_stmt->fetch();
            
            if ($row['count'] > 0) {
                // Add unique identifier to make slug unique
                $slug = $slug . '-' . date('mdY');
            }
            
            // Generate excerpt
            $excerpt = generateExcerpt($content, 160);
            
            // Insert the post
            $query = "INSERT INTO blog_posts (title, slug, content, excerpt, meta_description, status, created_at, updated_at) 
                      VALUES (:title, :slug, :content, :excerpt, :meta_description, :status, NOW(), NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':excerpt', $excerpt);
            $stmt->bindParam(':meta_description', $excerpt); // Using excerpt for meta_description
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            
            $post_id = $pdo->lastInsertId();
            $success = true;
            $message = "Post created successfully! ID: $post_id";
            
            // Clear form data after successful submission
            $title = '';
            $content = '';
            
        } catch (Exception $e) {
            $message = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct Blog Post Submission</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            padding: 20px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .btn-primary {
            background-color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="mb-0">Direct Blog Post Submission</h3>
                        <p class="mb-0 small">Emergency tool for adding blog posts</p>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?> mb-4">
                                <?php echo htmlspecialchars($message); ?>
                                <?php if ($success): ?>
                                    <div class="mt-2">
                                        <a href="list.php" class="btn btn-sm btn-primary">View All Posts</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="title" class="form-label">Post Title *</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Post Content *</label>
                                <textarea class="form-control" id="content" name="content" rows="15" required><?php echo htmlspecialchars($content ?? ''); ?></textarea>
                                <small class="form-text text-muted">You can use HTML tags for formatting.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="published" selected>Published</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Create Post</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer">
                        <a href="list.php" class="btn btn-sm btn-outline-secondary">Back to Blog List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>