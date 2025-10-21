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
$user_id = $_SESSION['user_id'] ?? null;

// Check if user has access to user management
if (!in_array($user_role, ['admin', 'manager'])) {
    header('Location: dashboard.php');
    exit();
}

include 'layout/header.php';
?>

<div id="layoutSidenav">
    <?php include 'layout/sidebar.php'; ?>
    
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">User Management</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">User Management</li>
                </ol>
                
                <?php
                // Include flash helper functions
                if (file_exists('functions/flash_helper.php')) {
                    include_once 'functions/flash_helper.php';
                    
                    // Migrate old flash messages for backward compatibility
                    migrateOldFlashMessages();
                    
                    // Display flash messages
                    displayFlashMessages();
                } else {
                    // Fallback to old method if helper file doesn't exist
                    if (isset($_SESSION['success'])) {
                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($_SESSION['success']) . '
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </div>';
                        unset($_SESSION['success']);
                    }
                    
                    if (isset($_SESSION['error'])) {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>' . htmlspecialchars($_SESSION['error']) . '
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </div>';
                        unset($_SESSION['error']);
                    }
                }
                ?>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-table me-1"></i>
                                    Data Users
                                </div>
                                <?php if ($user_role == 'admin') { ?>
                                <a href="add_user.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i> Add New User
                                </a>
                                <?php } ?>
                            </div>
                            <div class="card-body">
                                <?php
                                // Include database connection
                                include 'database/db.php';
                                
                                // Check if connection exists
                                if (!isset($koneksi)) {
                                    echo '<div class="alert alert-danger">Database connection failed!</div>';
                                } else {
                                    // Query to get users data
                                    $query = "SELECT id, first_name, last_name, email, role, created_at FROM users ORDER BY id ASC";
                                    $data = mysqli_query($koneksi, $query);
                                    
                                    if (!$data) {
                                        echo '<div class="alert alert-danger">Error: ' . mysqli_error($koneksi) . '</div>';
                                    } else {
                                        $user_count = mysqli_num_rows($data);
                                ?>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Total users: <?php echo $user_count; ?></small>
                                </div>
                                
                                <?php if ($user_count > 0) { ?>
                                <div class="table-responsive">
                                    <table id="datatablesSimple" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Created At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $no = 1;
                                            while($d = mysqli_fetch_assoc($data)) {
                                                $is_current_user = ($user_id && $user_id == $d['id']);
                                            ?>
                                            <tr<?php echo $is_current_user ? ' class="table-info"' : ''; ?>>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo htmlspecialchars($d['first_name']); ?></td>
                                                <td><?php echo htmlspecialchars($d['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($d['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo ($d['role'] == 'admin') ? 'danger' : (($d['role'] == 'manager') ? 'warning' : 'primary'); ?>">
                                                        <?php echo htmlspecialchars($d['role']); ?>
                                                    </span>
                                                    <?php if ($is_current_user) { ?>
                                                        <small class="text-muted">(You)</small>
                                                    <?php } ?>
                                                </td>
                                                <td><?php echo date('d M Y', strtotime($d['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="view_user.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-info" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        
                                                        <?php if ($user_role == 'admin' || ($user_role == 'manager' && $d['role'] != 'admin')) { ?>
                                                        <a href="edit_user.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-warning" title="Edit User">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php } ?>
                                                        
                                                        <?php if ($user_role == 'admin' && !$is_current_user) { ?>
                                                        <a href="delete_user.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-danger" 
                                                           title="Delete User"
                                                           onclick="return confirm('Are you sure you want to delete user: <?php echo addslashes($d['first_name'] . ' ' . $d['last_name']); ?>?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                        <?php } ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php 
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php } else { ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No users found in the database.
                                    </div>
                                <?php } ?>
                                
                                <?php
                                    }
                                }
                                ?>
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

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.badge {
    font-size: 0.75rem;
}

.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

.table-info {
    background-color: #d1ecf1 !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
<script src="assets/demo/chart-area-demo.js"></script>
<script src="assets/demo/chart-bar-demo.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
<script src="js/datatables-simple-demo.js"></script>

<script>
// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>