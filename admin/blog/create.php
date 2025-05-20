<?php
// Include database connection
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

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
            
            // Make sure upload directory exists
            $upload_dir = '../../uploads/blog/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $upload_path = $upload_dir . $new_file_name;
            
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
try {
    $categories_query = "SELECT id, name FROM blog_categories ORDER BY name ASC";
    $categories_stmt = $pdo->prepare($categories_query);
    $categories_stmt->execute();
    $categories = $categories_stmt->fetchAll();
} catch (PDOException $e) {
    // If table doesn't exist, create it
    try {
        $create_table = "CREATE TABLE IF NOT EXISTS blog_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($create_table);
        
        $create_post_cat_table = "CREATE TABLE IF NOT EXISTS blog_post_categories (
            post_id INT NOT NULL,
            category_id INT NOT NULL,
            PRIMARY KEY (post_id, category_id)
        )";
        $pdo->exec($create_post_cat_table);
        
        // Create default category
        $default_cat = "INSERT INTO blog_categories (name, slug) VALUES ('Uncategorized', 'uncategorized')";
        $pdo->exec($default_cat);
        
        // Now get categories
        $categories_query = "SELECT id, name FROM blog_categories ORDER BY name ASC";
        $categories_stmt = $pdo->prepare($categories_query);
        $categories_stmt->execute();
        $categories = $categories_stmt->fetchAll();
    } catch (PDOException $e2) {
        $categories = [];
        $errors[] = "Could not create categories table: " . $e2->getMessage();
    }
}

// Include header
$page_title = 'Create Blog Post';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Tristate Cards Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            padding: 1rem 1.25rem;
            font-weight: 500;
        }
        #imagePreview {
            max-width: 100%;
            height: auto;
            max-height: 200px;
            border-radius: 0.25rem;
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
                            <li><a class="dropdown-item" href="/admin/logout.php">Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom top-header">
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
                                            <div id="imagePreviewContainer" class="mt-2 d-none">
                                                <img id="imagePreview" src="" alt="Image Preview" class="img-fluid">
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
                                                            <input class="form-check-input" type="checkbox" name="categories[]" value="<?php echo $category['id']; ?>" id="category-<?php echo $category['id']; ?>" <?php echo (isset($_POST['categories']) && is_array($_POST['categories']) && in_array($category['id'], $_POST['categories'])) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="category-<?php echo $category['id']; ?>">
                                                                <?php echo htmlspecialchars($category['name']); ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                                                    <i class="fas fa-plus-circle me-1"></i> Add New Category
                                                </button>
                                            <?php else: ?>
                                                <p>No categories found.</p>
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                                                    <i class="fas fa-plus-circle me-1"></i> Create Category
                                                </button>
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

    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="category_name" placeholder="Enter category name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveCategory">Save Category</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
    // Initialize TinyMCE
    tinymce.init({
        selector: '.editor',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount checklist mediaembed casechange export formatpainter pageembed linkchecker a11ychecker tinymcespellchecker permanentpen powerpaste advtable advcode editimage advtemplate mentions tableofcontents footnotes mergetags autocorrect typography inlinecss',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
        tinycomments_mode: 'embedded',
        tinycomments_author: 'Admin',
        mergetags_list: [
            { value: 'First.Name', title: 'First Name' },
            { value: 'Email', title: 'Email' },
        ],
        height: 500,
        images_upload_handler: function (blobInfo, success, failure) {
            // This is a simplified example. In production, implement proper file upload.
            setTimeout(function () {
                // Normally, you would upload the image to your server here and return the URL.
                failure('Image upload is not implemented in this demo.');
            }, 2000);
        }
    });

    // Image preview
    document.getElementById('featured_image').addEventListener('change', function(event) {
        const preview = document.getElementById('imagePreview');
        const previewContainer = document.getElementById('imagePreviewContainer');
        const file = event.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewContainer.classList.remove('d-none');
            };
            
            reader.readAsDataURL(file);
        } else {
            previewContainer.classList.add('d-none');
        }
    });

    // Save Category
    document.getElementById('saveCategory').addEventListener('click', function() {
        const categoryName = document.getElementById('category_name').value.trim();
        
        if (categoryName) {
            // Send AJAX request to save category
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'ajax_add_category.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);
                        if (response.success) {
                            // Add the new category to the list
                            const categoriesContainer = document.querySelector('.card-body .mb-3');
                            const newCategory = document.createElement('div');
                            newCategory.className = 'form-check';
                            newCategory.innerHTML = `
                                <input class="form-check-input" type="checkbox" name="categories[]" value="${response.id}" id="category-${response.id}" checked>
                                <label class="form-check-label" for="category-${response.id}">
                                    ${categoryName}
                                </label>
                            `;
                            categoriesContainer.appendChild(newCategory);
                            
                            // Close modal and clear input
                            document.getElementById('category_name').value = '';
                            bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
                        } else {
                            alert(response.message || 'Failed to add category');
                        }
                    } catch (e) {
                        console.error('Invalid JSON response', e);
                        alert('Error processing response');
                    }
                }
            };
            xhr.send('name=' + encodeURIComponent(categoryName));
        } else {
            alert('Please enter a category name');
        }
    });
    </script>
</body>
</html>