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

// Check if user has access to user management
if (!in_array($user_role, ['admin', 'manager'])) {
    header('Location: dashboard.php');
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

// Get user data
$user_query = mysqli_prepare($koneksi, "SELECT id, first_name, last_name, email, role, created_at, updated_at, last_login FROM users WHERE id = ?");

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

// Check if current user can view this user's details
$is_current_user = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id);
$can_view_full_details = ($user_role == 'admin' || $is_current_user);

// If manager, can only view non-admin users and own profile
if ($user_role == 'manager' && !$is_current_user && $user_data['role'] == 'admin') {
    $_SESSION['error'] = 'You do not have permission to view admin user details!';
    header('Location: user.php');
    exit();
}

include 'layout/header.php';
?>

<div id="layoutSidenav">
    <?php include 'layout/sidebar.php'; ?>
    
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">User Details</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="user.php">User Management</a></li>
                    <li class="breadcrumb-item active">View User</li>
                </ol>
                
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <!-- User Profile Card -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-user me-2"></i>
                                    <strong>User Profile</strong>
                                    <?php if ($is_current_user) { ?>
                                        <span class="badge bg-light text-dark ms-2">Your Profile</span>
                                    <?php } ?>
                                </div>
                                <div class="btn-group" role="group">
                                    <?php if ($user_role == 'admin' || ($user_role == 'manager' && $user_data['role'] != 'admin') || $is_current_user) { ?>
                                    <a href="edit_user.php?id=<?php echo $user_id; ?>" class="btn btn-light btn-sm">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                    <?php } ?>
                                    <a href="user.php" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-arrow-left me-1"></i> Back
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <div class="user-avatar mb-3">
                                            <i class="fas fa-user-circle text-primary" style="font-size: 6rem;"></i>
                                        </div>
                                        <h5 class="mb-1"><?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></h5>
                                        <span class="badge bg-<?php echo ($user_data['role'] == 'admin') ? 'danger' : (($user_data['role'] == 'manager') ? 'warning' : 'primary'); ?> mb-3">
                                            <?php echo htmlspecialchars(ucfirst($user_data['role'])); ?>
                                        </span>
                                        
                                        <?php if ($user_data['role'] == 'admin') { ?>
                                        <div class="alert alert-info small">
                                            <i class="fas fa-crown me-1"></i>
                                            Administrator
                                        </div>
                                        <?php } elseif ($user_data['role'] == 'manager') { ?>
                                        <div class="alert alert-warning small">
                                            <i class="fas fa-users me-1"></i>
                                            Manager
                                        </div>
                                        <?php } else { ?>
                                        <div class="alert alert-primary small">
                                            <i class="fas fa-user me-1"></i>
                                            Standard User
                                        </div>
                                        <?php } ?>
                                    </div>
                                    
                                    <div class="col-md-8">
                                        <h6 class="text-muted mb-3">Personal Information</h6>
                                        
                                        <table class="table table-borderless">
                                            <tr>
                                                <td class="fw-bold" style="width: 30%;">
                                                    <i class="fas fa-id-card me-2 text-muted"></i>User ID:
                                                </td>
                                                <td><?php echo htmlspecialchars($user_data['id']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">
                                                    <i class="fas fa-user me-2 text-muted"></i>First Name:
                                                </td>
                                                <td><?php echo htmlspecialchars($user_data['first_name']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">
                                                    <i class="fas fa-user me-2 text-muted"></i>Last Name:
                                                </td>
                                                <td><?php echo htmlspecialchars($user_data['last_name']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">
                                                    <i class="fas fa-envelope me-2 text-muted"></i>Email:
                                                </td>
                                                <td>
                                                    <a href="mailto:<?php echo htmlspecialchars($user_data['email']); ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($user_data['email']); ?>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">
                                                    <i class="fas fa-shield-alt me-2 text-muted"></i>Role:
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo ($user_data['role'] == 'admin') ? 'danger' : (($user_data['role'] == 'manager') ? 'warning' : 'primary'); ?>">
                                                        <?php echo htmlspecialchars(ucfirst($user_data['role'])); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Account Information Card -->
                        <div class="card mb-4">
                            <div class="card-header bg-secondary text-white">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Account Information</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td class="fw-bold">
                                                    <i class="fas fa-calendar-plus me-2 text-muted"></i>Account Created:
                                                </td>
                                                <td><?php echo date('d M Y, H:i', strtotime($user_data['created_at'])); ?></td>
                                            </tr>
                                            <?php if (isset($user_data['updated_at']) && $user_data['updated_at']) { ?>
                                            <tr>
                                                <td class="fw-bold">
                                                    <i class="fas fa-calendar-edit me-2 text-muted"></i>Last Updated:
                                                </td>
                                                <td><?php echo date('d M Y, H:i', strtotime($user_data['updated_at'])); ?></td>
                                            </tr>
                                            <?php } ?>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <?php if (isset($user_data['last_login']) && $user_data['last_login'] && $can_view_full_details) { ?>
                                            <tr>
                                                <td class="fw-bold">
                                                    <i class="fas fa-sign-in-alt me-2 text-muted"></i>Last Login:
                                                </td>
                                                <td><?php echo date('d M Y, H:i', strtotime($user_data['last_login'])); ?></td>
                                            </tr>
                                            <?php } ?>
                                            <tr>
                                                <td class="fw-bold">
                                                    <i class="fas fa-chart-line me-2 text-muted"></i>Account Status:
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i>Active
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Permissions Card -->
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <i class="fas fa-key me-2"></i>
                                <strong>User Permissions</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                        $permissions = [];
                                        
                                        if ($user_data['role'] == 'admin') {
                                            $permissions = [
                                                'Full system access',
                                                'User management (create, edit, delete)',
                                                'System configuration',
                                                'View all reports',
                                                'Data backup and restore',
                                                'Audit log access'
                                            ];
                                        } elseif ($user_data['role'] == 'manager') {
                                            $permissions = [
                                                'Limited system access',
                                                'User management (view, edit non-admin users)',
                                                'View reports',
                                                'Manage team data',
                                                'Export data'
                                            ];
                                        } else {
                                            $permissions = [
                                                'Basic system access',
                                                'View own profile',
                                                'Edit own profile',
                                                'Access assigned features'
                                            ];
                                        }
                                        ?>
                                        
                                        <ul class="list-group list-group-flush">
                                            <?php foreach ($permissions as $permission) { ?>
                                            <li class="list-group-item d-flex align-items-center">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <?php echo htmlspecialchars($permission); ?>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <?php if ($user_role == 'admin') { ?>
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <i class="fas fa-tools me-2"></i>
                                <strong>Admin Actions</strong>
                            </div>
                            <div class="card-body">
                                <div class="btn-group" role="group">
                                    <a href="edit_user.php?id=<?php echo $user_id; ?>" class="btn btn-warning">
                                        <i class="fas fa-edit me-1"></i> Edit User
                                    </a>
                                    
                                    <!-- <?php if (!$is_current_user) { ?>
                                    <a href="reset_password.php?id=<?php echo $user_id; ?>" class="btn btn-info"
                                       onclick="return confirm('Are you sure you want to reset password for this user?')">
                                        <i class="fas fa-key me-1"></i> Reset Password
                                    </a> -->
                                    
                                    <a href="delete_user.php?id=<?php echo $user_id; ?>" class="btn btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this user?')">
                                        <i class="fas fa-trash me-1"></i> Delete User
                                    </a>
                                    <?php } ?>
                                </div>
                                
                                <?php if ($is_current_user) { ?>
                                <div class="alert alert-info mt-3 mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Note:</strong> You cannot delete or reset password for your own account.
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php } ?>
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

.user-avatar {
    margin-bottom: 1rem;
}

.badge {
    font-size: 0.75rem;
}

.table td {
    padding: 0.5rem 0;
    border: none;
}

.table td:first-child {
    font-weight: 600;
    color: #6c757d;
    width: 40%;
}

.card-header {
    font-weight: 600;
}

.list-group-item {
    border-left: none;
    border-right: none;
    padding-left: 0;
    padding-right: 0;
}

.btn-group .btn {
    margin-right: 0.5rem;
}

@media (max-width: 768px) {
    .btn-group {
        display: flex;
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 0.5rem;
        margin-right: 0;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>

<script>
// Add keyboard shortcut for quick navigation
document.addEventListener('keydown', function(e) {
    // Escape key to go back
    if (e.key === 'Escape') {
        window.location.href = 'user.php';
    }
    
    // E key to edit (if edit button exists)
    if (e.key === 'e' || e.key === 'E') {
        const editBtn = document.querySelector('a[href*="edit_user.php"]');
        if (editBtn) {
            window.location.href = editBtn.href;
        }
    }
});

// Add smooth scrolling for any internal links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>
