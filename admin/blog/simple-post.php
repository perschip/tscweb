<?php
// Include database connection
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = trim($_POST['title']);
    $content = $_POST['content'];
    $status = $_POST['status'] ?? 'published';
    
    // Create slug
    $slug = createSlug($title);
    
    // Generate excerpt
    $excerpt = generateExcerpt($content, 160);
    
    // Use excerpt as meta description
    $meta_description = $excerpt;
    
    // Insert post
    try {
        $query = "INSERT INTO blog_posts (title, slug, content, excerpt, meta_description, status, created_at, updated_at) 
                  VALUES (:title, :slug, :content, :excerpt, :meta_description, :status, NOW(), NOW())";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':slug', $slug);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':excerpt', $excerpt);
        $stmt->bindParam(':meta_description', $meta_description);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        
        $post_id = $pdo->lastInsertId();
        
        // Success message
        $success = "Blog post created successfully! Post ID: $post_id";
    } catch (PDOException $e) {
        // Error message
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Page title
$page_title = 'Simple Blog Post Form';

// Include header
include_once '../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Simple Blog Post Form</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                            <div class="mt-2">
                                <a href="list.php" class="btn btn-sm btn-primary">View All Posts</a>
                                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-sm btn-outline-primary">Add Another Post</a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                        <div class="mb-3">
                            <label for="title" class="form-label">Post Title *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Post Content *</label>
                            <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="published" selected>Published</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Create Post</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-muted">
                    <a href="list.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?>