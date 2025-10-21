<?php
session_start();

require_once('database/init.php');

// Check if user is logged in
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Get user role
$user_role = $_SESSION['role'];

// Check if user has access to user management (only admin can delete users)
if ($user_role !== 'admin') {
    $_SESSION['error'] = 'You do not have permission to delete users!';
    header('Location: user.php');
    exit();
}

// Include database connection
include 'database/db.php';

// Check if database connection exists
if (!isset($koneksi)) {
    die('Database connection failed!');
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = 'Invalid user ID!';
    header('Location: user.php');
    exit();
}

$user_id = (int)$_GET['id'];

// Validate user ID
if ($user_id <= 0) {
    $_SESSION['error'] = 'Invalid user ID!';
    header('Location: user.php');
    exit();
}

// Prevent self-deletion
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
    $_SESSION['error'] = 'You cannot delete your own account!';
    header('Location: user.php');
    exit();
}

// Handle confirmation
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    // First, get user data before deletion for logging
    $user_query = mysqli_prepare($koneksi, "SELECT id, first_name, last_name, email, role, created_at FROM users WHERE id = ?");
    
    if (!$user_query) {
        $_SESSION['error'] = 'Database query preparation failed: ' . mysqli_error($koneksi);
        header('Location: user.php');
        exit();
    }
    
    mysqli_stmt_bind_param($user_query, "i", $user_id);
    mysqli_stmt_execute($user_query);
    $result = mysqli_stmt_get_result($user_query);
    
    if (mysqli_num_rows($result) == 0) {
        $_SESSION['error'] = 'User not found!';
        header('Location: user.php');
        exit();
    }
    
    $user_data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($user_query);
    
    // Start transaction
    mysqli_autocommit($koneksi, false);
    
    try {
        // Check if audit_log table exists before logging
        $table_check = mysqli_query($koneksi, "SHOW TABLES LIKE 'audit_log'");
        if (mysqli_num_rows($table_check) > 0) {
            // Log the deletion
            $log_query = mysqli_prepare($koneksi, "INSERT INTO audit_log (action, table_name, record_id, old_values, performed_by, performed_at) VALUES (?, ?, ?, ?, ?, NOW())");
            if ($log_query) {
                $action = 'DELETE';
                $table_name = 'users';
                $old_values = json_encode($user_data);
                $performed_by = $_SESSION['user_id'];
                mysqli_stmt_bind_param($log_query, "ssisi", $action, $table_name, $user_id, $old_values, $performed_by);
                mysqli_stmt_execute($log_query);
                mysqli_stmt_close($log_query);
            }
        }
        
        // Delete user
        $delete_query = mysqli_prepare($koneksi, "DELETE FROM users WHERE id = ?");
        if (!$delete_query) {
            throw new Exception('Failed to prepare delete query: ' . mysqli_error($koneksi));
        }
        
        mysqli_stmt_bind_param($delete_query, "i", $user_id);
        
        if (mysqli_stmt_execute($delete_query)) {
            $affected_rows = mysqli_stmt_affected_rows($delete_query);
            mysqli_stmt_close($delete_query);
            
            if ($affected_rows > 0) {
                // Commit transaction
                mysqli_commit($koneksi);
                mysqli_autocommit($koneksi, true);
                
                $_SESSION['success'] = 'User "' . htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']) . '" has been deleted successfully!';
                header('Location: user.php');
                exit();
            } else {
                throw new Exception('No user was deleted. User may not exist.');
            }
        } else {
            throw new Exception('Failed to delete user: ' . mysqli_stmt_error($delete_query));
        }
        
    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($koneksi);
        mysqli_autocommit($koneksi, true);
        $_SESSION['error'] = $e->getMessage();
        header('Location: user.php');
        exit();
    }
}

// Get user data for confirmation
$user_query = mysqli_prepare($koneksi, "SELECT id, first_name, last_name, email, role, created_at FROM users WHERE id = ?");

if (!$user_query) {
    $_SESSION['error'] = 'Database query preparation failed: ' . mysqli_error($koneksi);
    header('Location: user.php');
    exit();
}

mysqli_stmt_bind_param($user_query, "i", $user_id);
mysqli_stmt_execute($user_query);
$result = mysqli_stmt_get_result($user_query);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'User not found!';
    mysqli_stmt_close($user_query);
    header('Location: user.php');
    exit();
}

$user_data = mysqli_fetch_assoc($result);
mysqli_stmt_close($user_query);

include 'layout/header.php';
?>

<div id="layoutSidenav">
    <?php include 'layout/sidebar.php'; ?>
    
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Delete User</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="user.php">User Management</a></li>
                    <li class="breadcrumb-item active">Delete User</li>
                </ol>
                
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-xl-6">
                        <div class="card mb-4 border-danger">
                            <div class="card-header bg-danger text-white">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Confirm User Deletion</strong>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-danger d-flex align-items-center" role="alert">
                                    <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                                    <div>
                                        <strong>Warning!</strong> This action cannot be undone. 
                                        All data associated with this user will be permanently deleted.
                                    </div>
                                </div>
                                
                                <h5 class="mb-3">Are you sure you want to delete this user?</h5>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <table class="table table-bordered">
                                            <tr>
                                                <td><strong>User ID:</strong></td>
                                                <td><?php echo htmlspecialchars($user_data['id']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Name:</strong></td>
                                                <td><?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email:</strong></td>
                                                <td><?php echo htmlspecialchars($user_data['email']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Role:</strong></td>
                                                <td>
                                                    <span class="badge bg-<?php echo ($user_data['role'] == 'admin') ? 'danger' : (($user_data['role'] == 'manager') ? 'warning' : 'primary'); ?>">
                                                        <?php echo htmlspecialchars(ucfirst($user_data['role'])); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Created:</strong></td>
                                                <td><?php echo htmlspecialchars(date('d M Y H:i', strtotime($user_data['created_at']))); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <i class="fas fa-user-times text-danger mb-3" style="font-size: 3rem;"></i>
                                                <h6 class="card-title">User will be deleted</h6>
                                                <p class="card-text small text-muted">
                                                    This action is permanent and cannot be reversed.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Make sure you have backed up any important data before proceeding.
                                        </small>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <a href="user.php" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-1"></i> Cancel
                                        </a>
                                        <a href="?id=<?php echo $user_id; ?>&confirm=yes" class="btn btn-danger" id="deleteBtn">
                                            <i class="fas fa-trash me-1"></i> Yes, Delete User
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Warning -->
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>What happens when you delete this user?</strong>
                            </div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    <li>The user account will be permanently removed from the system</li>
                                    <li>The user will no longer be able to log in</li>
                                    <li>All personal information will be deleted</li>
                                    <li>Any data associated with this user may be affected</li>
                                    <li>This action will be logged in the audit trail (if enabled)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <?php include 'layout/footer.php'; ?>
    </div>
</div>

<style>
#layoutSidenav {
    display: flex;
    width: 100%;
}

#layoutSidenav_content {
    flex: 1;
    min-width: 0;
}

.badge {
    font-size: 0.75rem;
}

.btn-group .btn {
    min-width: 120px;
}

.card-header {
    font-weight: 600;
}

.table td:first-child {
    width: 30%;
    font-weight: 600;
    background-color: #f8f9fa;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>

<script>
// Add final confirmation before deletion
document.getElementById('deleteBtn').addEventListener('click', function(e) {
    e.preventDefault();
    
    const userName = '<?php echo addslashes($user_data['first_name'] . ' ' . $user_data['last_name']); ?>';
    const confirmation = confirm(`Are you absolutely sure you want to delete user "${userName}"?\n\nThis action cannot be undone!`);
    
    if (confirmation) {
        // Double confirmation for extra safety
        const doubleConfirmation = confirm('Last chance! Click OK to permanently delete this user, or Cancel to abort.');
        
        if (doubleConfirmation) {
            window.location.href = this.href;
        }
    }
});

// Add keyboard shortcut for quick navigation
document.addEventListener('keydown', function(e) {
    // Escape key to cancel
    if (e.key === 'Escape') {
        window.location.href = 'user.php';
    }
});
</script>