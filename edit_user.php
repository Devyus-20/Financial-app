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

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = 'Invalid user ID!';
    header('Location: user.php');
    exit();
}

$user_id = (int)$_GET['id'];
$success_msg = '';
$error_msg = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = trim($_POST['password']);
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($role)) {
        $error_msg = 'All fields except password are required!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'Invalid email format!';
    } elseif (!in_array($role, ['admin', 'manager', 'user'])) {
        $error_msg = 'Invalid role selected!';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error_msg = 'Password must be at least 6 characters long!';
    } else {
        // Check if email already exists for other users
        $check_email = mysqli_prepare($koneksi, "SELECT id FROM users WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($check_email, "si", $email, $user_id);
        mysqli_stmt_execute($check_email);
        $result = mysqli_stmt_get_result($check_email);
        
        if (mysqli_num_rows($result) > 0) {
            $error_msg = 'Email already exists for another user!';
        } else {
            // Check if updated_at column exists
            $columns_query = mysqli_query($koneksi, "DESCRIBE users");
            $columns = [];
            while ($column = mysqli_fetch_array($columns_query)) {
                $columns[] = $column['Field'];
            }
            
            $has_updated_at = in_array('updated_at', $columns);
            
            // Update user data
            if (!empty($password)) {
                // Update with new password - always hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                if ($has_updated_at) {
                    $update_query = mysqli_prepare($koneksi, "UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, password_hash = ?, updated_at = NOW() WHERE id = ?");
                    mysqli_stmt_bind_param($update_query, "sssssi", $first_name, $last_name, $email, $role, $hashed_password, $user_id);
                } else {
                    $update_query = mysqli_prepare($koneksi, "UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, password_hash = ? WHERE id = ?");
                    mysqli_stmt_bind_param($update_query, "sssssi", $first_name, $last_name, $email, $role, $hashed_password, $user_id);
                }
            } else {
                // Update without changing password
                if ($has_updated_at) {
                    $update_query = mysqli_prepare($koneksi, "UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, updated_at = NOW() WHERE id = ?");
                    mysqli_stmt_bind_param($update_query, "ssssi", $first_name, $last_name, $email, $role, $user_id);
                } else {
                    $update_query = mysqli_prepare($koneksi, "UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ? WHERE id = ?");
                    mysqli_stmt_bind_param($update_query, "ssssi", $first_name, $last_name, $email, $role, $user_id);
                }
            }
            
            if (mysqli_stmt_execute($update_query)) {
                $success_msg = 'User updated successfully!';
                
                // Log password update for security audit
                if (!empty($password)) {
                    error_log("Password updated for user ID: " . $user_id . " (" . $email . ") by admin ID: " . $_SESSION['id']);
                }
                
                // Redirect after 2 seconds
                header("refresh:2;url=user.php");
            } else {
                $error_msg = 'Error updating user: ' . mysqli_error($koneksi);
            }
            
            mysqli_stmt_close($update_query);
        }
        mysqli_stmt_close($check_email);
    }
}

// Get user data
$user_query = mysqli_prepare($koneksi, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($user_query, "i", $user_id);
mysqli_stmt_execute($user_query);
$result = mysqli_stmt_get_result($user_query);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'User not found!';
    header('Location: user.php');
    exit();
}

$user_data = mysqli_fetch_array($result);
mysqli_stmt_close($user_query);

include 'layout/header.php';
?>

<div id="layoutSidenav">
    <?php include 'layout/sidebar.php'; ?>
    
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Edit User</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="user.php">User Management</a></li>
                    <li class="breadcrumb-item active">Edit User</li>
                </ol>
                
                <div class="row">
                    <div class="col-lg-8 col-xl-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-user-edit me-1"></i>
                                Edit User: <?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($success_msg)): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <?php echo $success_msg; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($error_msg)): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <?php echo $error_msg; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" action="" id="editUserForm">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="first_name" class="form-label">First Name *</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                                   value="<?php echo htmlspecialchars($user_data['first_name']); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="last_name" class="form-label">Last Name *</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                                   value="<?php echo htmlspecialchars($user_data['last_name']); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role *</label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="">Select Role</option>
                                            <option value="admin" <?php echo ($user_data['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                            <option value="manager" <?php echo ($user_data['role'] == 'manager') ? 'selected' : ''; ?>>Manager</option>
                                            <option value="user" <?php echo ($user_data['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">New Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" 
                                                   placeholder="Leave blank to keep current password" minlength="6">
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                            </button>
                                        </div>
                                        <div class="form-text" id="passwordHelp">Minimum 6 characters. Leave blank to keep current password.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                                   placeholder="Confirm new password">
                                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                                <i class="fas fa-eye" id="toggleConfirmPasswordIcon"></i>
                                            </button>
                                        </div>
                                        <div class="form-text" id="confirmPasswordHelp">Re-enter the new password to confirm.</div>
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="user.php" class="btn btn-secondary me-md-2">
                                            <i class="fas fa-arrow-left me-1"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary" id="updateBtn">
                                            <i class="fas fa-save me-1"></i> Update User
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-xl-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-info-circle me-1"></i>
                                User Information
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>User ID:</strong></td>
                                        <td><?php echo $user_data['id']; ?></td>
                                    </tr>
                                    <?php if (isset($user_data['created_at']) && !empty($user_data['created_at'])): ?>
                                    <tr>
                                        <td><strong>Created:</strong></td>
                                        <td><?php echo date('d M Y H:i', strtotime($user_data['created_at'])); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (isset($user_data['updated_at']) && !empty($user_data['updated_at'])): ?>
                                    <tr>
                                        <td><strong>Last Updated:</strong></td>
                                        <td><?php echo date('d M Y H:i', strtotime($user_data['updated_at'])); ?></td>
                                    </tr>
                                    <?php else: ?>
                                    <tr>
                                        <td><strong>Last Updated:</strong></td>
                                        <td>Never</td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td><strong>Current Role:</strong></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($user_data['role'] == 'admin') ? 'danger' : (($user_data['role'] == 'manager') ? 'warning' : 'primary'); ?>">
                                                <?php echo ucfirst($user_data['role']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                                
                                <?php if (!empty($user_data['last_login'])): ?>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        Last login: <?php echo date('d M Y H:i', strtotime($user_data['last_login'])); ?>
                                    </small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-shield-alt me-1"></i>
                                Security Note
                            </div>
                            <div class="card-body">
                                <p class="card-text small text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    When updating a user's password, it will be securely hashed and stored. The user will be able to login with the new password immediately.
                                </p>
                                <p class="card-text small text-muted mb-0">
                                    <i class="fas fa-history me-1"></i>
                                    All password changes are logged for security audit purposes.
                                </p>
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

.form-label {
    font-weight: 600;
    color: #495057;
}

.badge {
    font-size: 0.75rem;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    font-weight: 600;
}

.is-invalid {
    border-color: #dc3545;
}

.is-valid {
    border-color: #198754;
}

.text-danger {
    color: #dc3545 !important;
}

.text-success {
    color: #198754 !important;
}

.input-group .btn-outline-secondary {
    border-color: #ced4da;
}

.input-group .btn-outline-secondary:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
}

.input-group .btn-outline-secondary:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.input-group .form-control:focus + .btn-outline-secondary {
    border-color: #86b7fe;
}

.password-toggle-btn {
    cursor: pointer;
    user-select: none;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>

<script>
// Auto hide success message after 5 seconds
setTimeout(function() {
    const successAlert = document.querySelector('.alert-success');
    if (successAlert) {
        successAlert.style.transition = 'opacity 0.5s';
        successAlert.style.opacity = '0';
        setTimeout(() => successAlert.remove(), 500);
    }
}, 5000);

// Password validation and confirmation
document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    const passwordHelp = document.getElementById('passwordHelp');
    const confirmPasswordHelp = document.getElementById('confirmPasswordHelp');
    const form = document.getElementById('editUserForm');
    const updateBtn = document.getElementById('updateBtn');

    // Password toggle functionality
    const togglePassword = document.getElementById('togglePassword');
    const togglePasswordIcon = document.getElementById('togglePasswordIcon');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const toggleConfirmPasswordIcon = document.getElementById('toggleConfirmPasswordIcon');

    // Toggle password visibility
    togglePassword.addEventListener('click', function() {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        
        // Toggle icon
        if (type === 'text') {
            togglePasswordIcon.classList.remove('fa-eye');
            togglePasswordIcon.classList.add('fa-eye-slash');
        } else {
            togglePasswordIcon.classList.remove('fa-eye-slash');
            togglePasswordIcon.classList.add('fa-eye');
        }
    });

    // Toggle confirm password visibility
    toggleConfirmPassword.addEventListener('click', function() {
        const type = confirmPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordField.setAttribute('type', type);
        
        // Toggle icon
        if (type === 'text') {
            toggleConfirmPasswordIcon.classList.remove('fa-eye');
            toggleConfirmPasswordIcon.classList.add('fa-eye-slash');
        } else {
            toggleConfirmPasswordIcon.classList.remove('fa-eye-slash');
            toggleConfirmPasswordIcon.classList.add('fa-eye');
        }
    });

    function validatePassword() {
        const password = passwordField.value;
        const confirmPassword = confirmPasswordField.value;
        let isValid = true;

        // Reset classes
        passwordField.classList.remove('is-invalid', 'is-valid');
        confirmPasswordField.classList.remove('is-invalid', 'is-valid');
        
        // Check password length if not empty
        if (password.length > 0) {
            if (password.length < 6) {
                passwordField.classList.add('is-invalid');
                passwordHelp.textContent = 'Password must be at least 6 characters long.';
                passwordHelp.className = 'form-text text-danger';
                isValid = false;
            } else {
                passwordField.classList.add('is-valid');
                passwordHelp.textContent = 'Password length is valid.';
                passwordHelp.className = 'form-text text-success';
            }

            // Check password confirmation
            if (confirmPassword.length > 0) {
                if (password !== confirmPassword) {
                    confirmPasswordField.classList.add('is-invalid');
                    confirmPasswordHelp.textContent = 'Passwords do not match.';
                    confirmPasswordHelp.className = 'form-text text-danger';
                    isValid = false;
                } else {
                    confirmPasswordField.classList.add('is-valid');
                    confirmPasswordHelp.textContent = 'Passwords match.';
                    confirmPasswordHelp.className = 'form-text text-success';
                }
            }
        } else {
            // Reset help text when password is empty
            passwordHelp.textContent = 'Minimum 6 characters. Leave blank to keep current password.';
            passwordHelp.className = 'form-text';
            confirmPasswordHelp.textContent = 'Re-enter the new password to confirm.';
            confirmPasswordHelp.className = 'form-text';
        }

        return isValid;
    }

    // Add event listeners
    passwordField.addEventListener('input', validatePassword);
    confirmPasswordField.addEventListener('input', validatePassword);

    // Form submission validation
    form.addEventListener('submit', function(e) {
        const password = passwordField.value;
        const confirmPassword = confirmPasswordField.value;

        // If password is provided, both fields must match and be valid
        if (password.length > 0) {
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                passwordField.focus();
                return false;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match.');
                confirmPasswordField.focus();
                return false;
            }
        }

        // Show loading state
        updateBtn.disabled = true;
        updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';
    });

    // Reset form state if back button is used
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            updateBtn.disabled = false;
            updateBtn.innerHTML = '<i class="fas fa-save me-1"></i> Update User';
        }
    });
});
</script>