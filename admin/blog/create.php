<?php
// Include database connection
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = trim($_POST['title']);
    $slug = createSlug($title);
    $content = $_POST['content'];
    $excerpt = trim($_POST['excerpt']);
    $meta_description = trim($_POST['meta_description']);
    $status = $_POST['status'];
    $featured_image = '';
    
    // Validate inputs
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'Post title is required';
    }
    
    if (empty($content)) {
        $errors[] = 'Post content is required';
    }
    
    if (empty($excerpt)) {
        // Generate excerpt from content if empty
        $excerpt = generateExcerpt($content, 160);
    }
    
    if (empty($meta_description)) {
        // Use excerpt as meta description if empty
        $meta_description = $excerpt;
    }
    
    // Check if a file was uploaded
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = $_FILES['featured_image']['name'];
        $file_tmp = $_FILES['featured_image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Check if the file type is allowed
        if (in_array($file_ext, $allowed)) {
            // Generate unique filename
            $new_file_name = uniqid('post_') . '.' . $file_ext;
            $upload_path = '../../uploads/blog/' . $new_file_name;
            
            // Move the file to the uploads directory
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $featured_image = '/uploads/blog/' . $new_file_name;
            } else {
                $errors[] = 'Failed to upload image';
            }
        } else {
            $errors[] = 'Only JPG, JPEG, PNG, and GIF files are allowed';
        }
    }
    
    // Check for duplicate slug
    $check_query = "SELECT COUNT(*) as count FROM blog_posts WHERE slug = :slug";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(':slug', $slug);
    $check_stmt->execute();
    $row = $check_stmt->fetch();
    
    if ($row['count'] > 0) {
        // Add unique identifier to make slug unique
        $slug = $slug . '-' . date('mdY');
    }
    
    // If no errors, insert post
    if (empty($errors)) {
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Insert into blog_posts table
            $query = "INSERT INTO blog_posts (title, slug, content, excerpt, meta_description, featured_image, status, created_at, updated_at) 
                      VALUES (:title, :slug, :content, :excerpt, :meta_description, :featured_image, :status, NOW(), NOW())";
            
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':excerpt', $excerpt);
            $stmt->bindParam(':meta_description', $meta_description);
            $stmt->bindParam(':featured_image', $featured_image);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            
            // Get the ID of the inserted post
            $post_id = $pdo->lastInsertId();
            
            // Process categories if provided
            if (isset($_POST['categories']) && is_array($_POST['categories'])) {
                foreach ($_POST['categories'] as $category_id) {
                    $cat_query = "INSERT INTO blog_post_categories (post_id, category_id) VALUES (:post_id, :category_id)";
                    $cat_stmt = $pdo->prepare($cat_query);
                    $cat_stmt->bindParam(':post_id', $post_id);
                    $cat_stmt->bindParam(':category_id', $category_id);
                    $cat_stmt->execute();
                }
            }
            
            // Process tags if provided
            if (!empty($_POST['tags'])) {
                $tags = explode(',', $_POST['tags']);
                
                foreach ($tags as $tag_name) {
                    $tag_name = trim($tag_name);
                    
                    if (!empty($tag_name)) {
                        // Check if tag exists
                        $tag_check_query = "SELECT id FROM blog_tags WHERE name = :name";
                        $tag_check_stmt = $pdo->prepare($tag_check_query);
                        $tag_check_stmt->bindParam(':name', $tag_name);
                        $tag_check_stmt->execute();
                        $tag = $tag_check_stmt->fetch();
                        
                        if ($tag) {
                            $tag_id = $tag['id'];
                        } else {
                            // Create a new tag
                            $tag_slug = createSlug($tag_name);
                            $tag_insert_query = "INSERT INTO blog_tags (name, slug) VALUES (:name, :slug)";
                            $tag_insert_stmt = $pdo->prepare($tag_insert_query);
                            $tag_insert_stmt->bindParam(':name', $tag_name);
                            $tag_insert_stmt->bindParam(':slug', $tag_slug);
                            $tag_insert_stmt->execute();
                            $tag_id = $pdo->lastInsertId();
                        }
                        
                        // Associate tag with post
                        $post_tag_query = "INSERT INTO blog_post_tags (post_id, tag_id) VALUES (:post_id, :tag_id)";
                        $post_tag_stmt = $pdo->prepare($post_tag_query);
                        $post_tag_stmt->bindParam(':post_id', $post_id);
                        $post_tag_stmt->bindParam(':tag_id', $tag_id);
                        $post_tag_stmt->execute();
                    }
                }
            }
            
            // Commit transaction
            $pdo->commit();
            
            // Redirect to the blog post list with success message
            $_SESSION['success_message'] = 'Blog post created successfully!';
            header('Location: list.php');
            exit;
            
        } catch (PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all categories
$categories_query = "SELECT id, name FROM blog_categories ORDER BY name ASC";
$categories_stmt = $pdo->prepare($categories_query);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll();

// Include header
$page_title = 'Create Blog Post';
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Create Blog Post</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="list.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>

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
                    <form action="create.php" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Main Content Fields -->
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="content" class="form-label">Content *</label>
                                    <textarea class="form-control editor" id="content" name="content" rows="15" required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="excerpt" class="form-label">Excerpt</label>
                                    <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?php echo isset($_POST['excerpt']) ? htmlspecialchars($_POST['excerpt']) : ''; ?></textarea>
                                    <div class="form-text">A short summary of the post. If left empty, it will be generated from the content.</div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <!-- Sidebar Fields -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Publishing</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="published" <?php echo (isset($_POST['status']) && $_POST['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                                                <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">Publish Post</button>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Featured Image</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="featured_image" class="form-label">Upload Image</label>
                                            <input class="form-control" type="file" id="featured_image" name="featured_image">
                                            <div class="form-text">Recommended size: 1200x630 pixels</div>
                                        </div>
                                        <div id="imagePreview" class="mt-2 d-none">
                                            <img src="" alt="Image Preview" class="img-fluid img-thumbnail">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Categories</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (count($categories) > 0): ?>
                                            <div class="mb-3">
                                                <?php foreach ($categories as $category): ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="categories[]" value="<?php echo $category['id']; ?>" id="category-<?php echo $category['id']; ?>">
                                                        <label class="form-check-label" for="category-<?php echo $category['id']; ?>">
                                                            <?php echo htmlspecialchars($category['name']); ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p>No categories found. <a href="../categories/create.php">Create one</a>.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Tags</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <input type="text" class="form-control" id="tags" name="tags" value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>" placeholder="Enter tags separated by commas">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">SEO Settings</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="meta_description" class="form-label">Meta Description</label>
                                            <textarea class="form-control" id="meta_description" name="meta_description" rows="3"><?php echo isset($_POST['meta_description']) ? htmlspecialchars($_POST['meta_description']) : ''; ?></textarea>
                                            <div class="form-text">If left empty, the excerpt will be used. Recommended length: 150-160 characters.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
// Initialize TinyMCE
tinymce.init({
    selector: '.editor',
    plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
    toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
    height: 500
});

// Image preview
document.getElementById('featured_image').addEventListener('change', function(event) {
    const preview = document.getElementById('imagePreview');
    const file = event.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.querySelector('img').src = e.target.result;
            preview.classList.remove('d-none');
        };
        
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('d-none');
    }
});
</script>