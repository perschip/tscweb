<?php
// Include database connection and auth check
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Function to remove emojis and special characters
function removeEmojis($text) {
    // Pattern to match emojis and other special characters
    $pattern = '/[\x{1F600}-\x{1F64F}|\x{1F300}-\x{1F5FF}|\x{1F680}-\x{1F6FF}|\x{2600}-\x{26FF}|\x{2700}-\x{27BF}]/u';
    return preg_replace($pattern, '', $text);
}

// Check if API key is being saved
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_api_settings') {
    $whatnot_username = trim($_POST['whatnot_username']);
    $whatnot_api_key = trim($_POST['whatnot_api_key']);
    $whatnot_api_secret = trim($_POST['whatnot_api_secret']);
    $auto_check_interval = (int)$_POST['auto_check_interval'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($whatnot_username)) {
        $errors[] = 'Whatnot username is required';
    }
    
    // Save settings if no errors
    if (empty($errors)) {
        try {
            // Begin transaction
            $pdo->beginTransaction();
            
            // Check if settings exist
            $check_query = "SELECT COUNT(*) as count FROM site_settings WHERE setting_key IN ('whatnot_username', 'whatnot_api_key', 'whatnot_api_secret', 'whatnot_check_interval')";
            $check_stmt = $pdo->prepare($check_query);
            $check_stmt->execute();
            $settings_exist = $check_stmt->fetch()['count'] > 0;
            
            // Define settings to update
            $settings = [
                'whatnot_username' => $whatnot_username,
                'whatnot_api_key' => $whatnot_api_key,
                'whatnot_api_secret' => $whatnot_api_secret,
                'whatnot_check_interval' => $auto_check_interval
            ];
            
            foreach ($settings as $key => $value) {
                if ($settings_exist) {
                    // Update existing setting
                    $update_query = "UPDATE site_settings SET setting_value = :value WHERE setting_key = :key";
                    $update_stmt = $pdo->prepare($update_query);
                    $update_stmt->bindParam(':key', $key);
                    $update_stmt->bindParam(':value', $value);
                    $update_stmt->execute();
                } else {
                    // Insert new setting
                    $insert_query = "INSERT INTO site_settings (setting_key, setting_value) VALUES (:key, :value)";
                    $insert_stmt = $pdo->prepare($insert_query);
                    $insert_stmt->bindParam(':key', $key);
                    $insert_stmt->bindParam(':value', $value);
                    $insert_stmt->execute();
                }
            }
            
            // Commit transaction
            $pdo->commit();
            
            // Set success message
            $_SESSION['success_message'] = 'Whatnot API settings saved successfully!';
            
            // Redirect to refresh the page
            header('Location: settings.php');
            exit;
            
        } catch (PDOException $e) {
            // Rollback transaction
            $pdo->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Check if manual stream update is being saved
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_stream') {
    // Clean input data - remove emojis and special characters
    $is_live = isset($_POST['is_live']) && $_POST['is_live'] === '1' ? 1 : 0;
    $stream_title = removeEmojis(trim($_POST['stream_title'])); // Clean emojis from title
    $stream_url = trim($_POST['stream_url']);
    $scheduled_time = !empty($_POST['scheduled_time']) ? $_POST['scheduled_time'] : null;
    
    // Validate inputs
    $errors = [];
    
    if (empty($stream_title)) {
        $errors[] = 'Stream title is required';
    }
    
    if ($is_live && empty($stream_url)) {
        $errors[] = 'Stream URL is required for live streams';
    }
    
    if (!$is_live && empty($scheduled_time)) {
        $errors[] = 'Scheduled time is required for upcoming streams';
    }
    
    // Save stream status if no errors
    if (empty($errors)) {
        try {
            // Insert stream status
            $query = "INSERT INTO whatnot_status (is_live, stream_title, stream_url, scheduled_time, last_checked) 
                      VALUES (:is_live, :stream_title, :stream_url, :scheduled_time, NOW())";
                      
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':is_live', $is_live, PDO::PARAM_INT);
            $stmt->bindParam(':stream_title', $stream_title);
            $stmt->bindParam(':stream_url', $stream_url);
            $stmt->bindParam(':scheduled_time', $scheduled_time);
            $stmt->execute();
            
            // Set success message
            $_SESSION['success_message'] = 'Stream status updated successfully!';
            
            // Redirect to refresh the page
            header('Location: settings.php');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Check if ending stream
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['end_stream'])) {
    try {
        // Get current status
        $status_query = "SELECT * FROM whatnot_status ORDER BY id DESC LIMIT 1";
        $status_stmt = $pdo->prepare($status_query);
        $status_stmt->execute();
        $currentStatus = $status_stmt->fetch();
        
        if ($currentStatus && $currentStatus['is_live']) {
            // Clean title from emojis
            $stream_title = removeEmojis($currentStatus['stream_title']);
            
            // Insert new status with is_live set to 0
            $query = "INSERT INTO whatnot_status (is_live, stream_title, stream_url, scheduled_time, last_checked) 
                      VALUES (0, :stream_title, '', NULL, NOW())";
                      
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':stream_title', $stream_title);
            $stmt->execute();
            
            // Set success message
            $_SESSION['success_message'] = 'Stream ended successfully!';
        }
        
        // Redirect to refresh the page
        header('Location: settings.php');
        exit;
        
    } catch (PDOException $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
    }
}

// Get current Whatnot API settings
$settings_query = "SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('whatnot_username', 'whatnot_api_key', 'whatnot_api_secret', 'whatnot_check_interval')";
$settings_stmt = $pdo->prepare($settings_query);
$settings_stmt->execute();
$settings_result = $settings_stmt->fetchAll();

$settings = [];
foreach ($settings_result as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Get current Whatnot status
try {
    $status_query = "SELECT * FROM whatnot_status ORDER BY id DESC LIMIT 1";
    $status_stmt = $pdo->prepare($status_query);
    $status_stmt->execute();
    $whatnot_status = $status_stmt->fetch();
} catch (PDOException $e) {
    // If table doesn't exist, set status to null
    $whatnot_status = null;
    $errors[] = 'Status table error: ' . $e->getMessage();
}

// Include header
$page_title = 'Whatnot Integration Settings';
include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Whatnot Integration Settings</h1>
            </div>
            
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i> Important Information</h5>
                    <p>Your database is not properly configured to handle emoji characters. Please avoid using emojis in your stream titles.</p>
                    <p>To fix this permanently, update your database by running this SQL command:</p>
                    <pre>ALTER TABLE whatnot_status CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</pre>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <!-- API Settings -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold">Whatnot API Settings</h6>
                        </div>
                        <div class="card-body">
                            <form action="settings.php" method="post">
                                <input type="hidden" name="action" value="save_api_settings">
                                
                                <div class="mb-3">
                                    <label for="whatnot_username" class="form-label">Whatnot Username *</label>
                                    <input type="text" class="form-control" id="whatnot_username" name="whatnot_username" 
                                           value="<?php echo isset($settings['whatnot_username']) ? htmlspecialchars($settings['whatnot_username']) : ''; ?>" required>
                                    <div class="form-text">Your Whatnot username (without the @ symbol)</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="whatnot_api_key" class="form-label">API Key</label>
                                    <input type="text" class="form-control" id="whatnot_api_key" name="whatnot_api_key" 
                                           value="<?php echo isset($settings['whatnot_api_key']) ? htmlspecialchars($settings['whatnot_api_key']) : ''; ?>">
                                    <div class="form-text">Get this from your Whatnot developer account</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="whatnot_api_secret" class="form-label">API Secret</label>
                                    <input type="password" class="form-control" id="whatnot_api_secret" name="whatnot_api_secret" 
                                           value="<?php echo isset($settings['whatnot_api_secret']) ? htmlspecialchars($settings['whatnot_api_secret']) : ''; ?>">
                                    <div class="form-text">Keep this secret and secure</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="auto_check_interval" class="form-label">Auto-Check Interval (minutes)</label>
                                    <input type="number" class="form-control" id="auto_check_interval" name="auto_check_interval" min="5" 
                                           value="<?php echo isset($settings['whatnot_check_interval']) ? htmlspecialchars($settings['whatnot_check_interval']) : '15'; ?>">
                                    <div class="form-text">How often to check for stream status updates (minimum 5 minutes)</div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save API Settings</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <!-- Manual Stream Update -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold">Manual Stream Update</h6>
                        </div>
                        <div class="card-body">
                            <?php if ($whatnot_status): ?>
                                <div class="alert <?php echo $whatnot_status['is_live'] ? 'alert-success' : 'alert-info'; ?> mb-4">
                                    <strong>Current Status:</strong> 
                                    <?php if ($whatnot_status['is_live']): ?>
                                        <span class="badge bg-success">LIVE</span> with "<?php echo htmlspecialchars($whatnot_status['stream_title']); ?>"
                                    <?php elseif ($whatnot_status['scheduled_time'] && strtotime($whatnot_status['scheduled_time']) > time()): ?>
                                        <span class="badge bg-info">UPCOMING</span> "<?php echo htmlspecialchars($whatnot_status['stream_title']); ?>" on <?php echo date('F j, Y \a\t g:i A', strtotime($whatnot_status['scheduled_time'])); ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">OFFLINE</span>
                                    <?php endif; ?>
                                    <div class="small mt-1">Last updated: <?php echo date('F j, Y \a\t g:i A', strtotime($whatnot_status['last_checked'])); ?></div>
                                </div>
                            <?php endif; ?>

                            <form action="settings.php" method="post">
                                <input type="hidden" name="action" value="update_stream">
                                
                                <div class="mb-3">
                                    <label class="form-label d-block">Stream Status</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="is_live" id="status_live" value="1" <?php echo (isset($_POST['is_live']) && $_POST['is_live'] === '1') ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="status_live">Live Now</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="is_live" id="status_upcoming" value="0" <?php echo (!isset($_POST['is_live']) || $_POST['is_live'] === '0') ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="status_upcoming">Upcoming/Offline</label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="stream_title" class="form-label">Stream Title * <small class="text-danger">(no emojis please)</small></label>
                                    <input type="text" class="form-control" id="stream_title" name="stream_title" 
                                           value="<?php echo isset($_POST['stream_title']) ? htmlspecialchars($_POST['stream_title']) : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3 live-field">
                                    <label for="stream_url" class="form-label">Stream URL</label>
                                    <input type="url" class="form-control" id="stream_url" name="stream_url" 
                                           value="<?php echo isset($_POST['stream_url']) ? htmlspecialchars($_POST['stream_url']) : ''; ?>">
                                    <div class="form-text">The URL where viewers can watch your stream</div>
                                </div>
                                
                                <div class="mb-3 upcoming-field">
                                    <label for="scheduled_time" class="form-label">Scheduled Time</label>
                                    <input type="datetime-local" class="form-control" id="scheduled_time" name="scheduled_time" 
                                           value="<?php echo isset($_POST['scheduled_time']) ? htmlspecialchars($_POST['scheduled_time']) : ''; ?>">
                                </div>
                                
                                <button type="submit" class="btn btn-success">Update Stream Status</button>
                                
                                <?php if ($whatnot_status && $whatnot_status['is_live']): ?>
                                    <button type="submit" name="end_stream" value="1" class="btn btn-outline-danger ms-2" 
                                            onclick="return confirm('Are you sure you want to end the current stream?');">
                                        End Current Stream
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
// Toggle fields based on stream status
document.addEventListener('DOMContentLoaded', function() {
    const streamStatus = document.querySelectorAll('input[name="is_live"]');
    const liveFields = document.querySelectorAll('.live-field');
    const upcomingFields = document.querySelectorAll('.upcoming-field');
    
    function toggleFields() {
        const isLive = document.querySelector('#status_live').checked;
        
        liveFields.forEach(field => {
            field.style.display = isLive ? 'block' : 'none';
        });
        
        upcomingFields.forEach(field => {
            field.style.display = isLive ? 'none' : 'block';
        });
    }
    
    // Set initial state
    toggleFields();
    
    // Add event listeners
    streamStatus.forEach(radio => {
        radio.addEventListener('change', toggleFields);
    });
});
</script>