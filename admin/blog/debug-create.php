<?php
// DEBUGGING VERSION OF CREATE.PHP
// This file will help identify why the form isn't submitting

// Start by logging that the file was accessed
error_log('CREATE.PHP - File accessed at ' . date('Y-m-d H:i:s'));

// Include database connection and helper functions
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Initialize variables
$post_categories = [];
$post_tags = '';
$title = '';
$content = '';
$excerpt = '';
$meta_description = '';
$status = 'published'; 
$featured_image = '';

// Logging what $_POST contains
error_log('CREATE.PHP - POST data: ' . print_r($_POST, true));
error_log('CREATE.PHP - REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);

// Check if form is submitted - use a direct and simple approach
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('CREATE.PHP - Form submitted');
    
    // Get basic form data - just title and content for testing
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? $_POST['content'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : 'draft';
    
    error_log('CREATE.PHP - Received title: ' . $title);
    error_log('CREATE.PHP - Received content length: ' . strlen($content));
    
    // Simple validation
    if (empty($title)) {
        error_log('CREATE.PHP - Title is empty');
        $errors[] = 'Post title is required';
    }
    
    if (empty($content)) {
        error_log('CREATE.PHP - Content is empty');
        $errors[] = 'Post content is required';
    }
    
    // If we have title and content, try to insert
    if (!empty($title) && !empty($content)) {
        error_log('CREATE.PHP - Title and content are valid, attempting insert');
        
        try {
            // Create a very basic post with minimum required fields
            $slug = createSlug($title);
            $excerpt = generateExcerpt($content, 160);
            
            error_log('CREATE.PHP - Generated slug: ' . $slug);
            
            // Insert in the simplest way possible
            $query = "INSERT INTO blog_posts (title, slug, content, excerpt, status, created_at, updated_at) 
                      VALUES (:title, :slug, :content, :excerpt, :status, NOW(), NOW())";
            
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':excerpt', $excerpt);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            
            error_log('CREATE.PHP - Insert successful, last insert ID: ' . $pdo->lastInsertId());
            
            // If we got here, it worked!
            $_SESSION['success_message'] = 'Blog post created successfully!';
            
            // Redirect to list page
            error_log('CREATE.PHP - Redirecting to list.php');
            header('Location: list.php');
            exit;
            
        } catch (PDOException $e) {
            // Log the database error
            error_log('CREATE.PHP - Database error: ' . $e->getMessage());
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all categories
try {
    $categories_query = "SELECT id, name FROM blog_categories ORDER BY name ASC";
    $categories_stmt = $pdo->prepare($categories_query);
    $categories_stmt->execute();
    $categories = $categories_stmt->fetchAll();
} catch (PDOException $e) {
    error_log('CREATE.PHP - Error fetching categories: ' . $e->getMessage());
    $categories = [];
}

// Page variables
$page_title = 'Create Blog Post';
$use_tinymce = true;

// Include header
include_once '../includes/header.php';
?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <!-- SIMPLIFIED FORM FOR TESTING -->
        <form id="blogPostForm" action="debug-create.php" method="post">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Content *</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($content); ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Publishing</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="published" selected>Published</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                            
                            <!-- THREE DIFFERENT BUTTONS TO TEST SUBMISSION -->
                            <button type="submit" class="btn btn-primary w-100 mb-2">Standard Submit Button</button>
                            
                            <button type="button" class="btn btn-success w-100 mb-2" onclick="document.getElementById('blogPostForm').submit();">JavaScript Submit Button</button>
                            
                            <a href="#" class="btn btn-warning w-100" onclick="submitForm(); return false;">Alternative Submit Method</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Include admin footer -->
<?php include_once '../includes/footer.php'; ?>

<script>
// Simple form submission function
function submitForm() {
    console.log('Alternative submit clicked');
    
    // Get form values
    var title = document.getElementById('title').value;
    var content = document.getElementById('content').value;
    
    console.log('Title: ' + title);
    console.log('Content length: ' + content.length);
    
    // Create a form dynamically
    var form = document.createElement('form');
    form.method = 'post';
    form.action = 'debug-create.php';
    
    // Add title
    var titleInput = document.createElement('input');
    titleInput.type = 'hidden';
    titleInput.name = 'title';
    titleInput.value = title;
    form.appendChild(titleInput);
    
    // Add content
    var contentInput = document.createElement('input');
    contentInput.type = 'hidden';
    contentInput.name = 'content';
    contentInput.value = content;
    form.appendChild(contentInput);
    
    // Add status
    var statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'status';
    statusInput.value = document.getElementById('status').value;
    form.appendChild(statusInput);
    
    // Submit the form
    document.body.appendChild(form);
    console.log('Submitting alternative form');
    form.submit();
    document.body.removeChild(form);
}

// Check for TinyMCE initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Document loaded');
    
    // Check if TinyMCE exists every second for 10 seconds
    var checkCount = 0;
    var checkInterval = setInterval(function() {
        if (typeof tinymce !== 'undefined') {
            console.log('TinyMCE exists!');
            clearInterval(checkInterval);
            
            console.log('TinyMCE instances:', tinymce.editors);
            
            // Check if content editor instance exists
            if (tinymce.get('content')) {
                console.log('Content editor exists');
            } else {
                console.log('Content editor does NOT exist');
            }
        } else {
            console.log('TinyMCE not found yet...');
        }
        
        checkCount++;
        if (checkCount >= 10) {
            clearInterval(checkInterval);
            console.log('Stopped checking for TinyMCE');
        }
    }, 1000);
});
</script>