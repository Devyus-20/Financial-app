<?php
session_start();
require 'database/db.php';

$servername = "localhost";
$database = "zakat";
$username = "root";
$password = "";

$koneksi = mysqli_connect($servername, $username, $password, $database);
if (!$koneksi) {
    die("Connection failed: " . mysqli_connect_error());
}

// Deteksi apakah dari admin
$isFromAdmin = isset($_GET['from']) && $_GET['from'] === 'admin';
$isAdminLoggedIn = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Batasi akses ke ?from=admin hanya untuk admin yang sudah login
if ($isFromAdmin && !$isAdminLoggedIn) {
    die("Akses ditolak. Hanya admin yang dapat mendaftarkan admin.");
}

$errors = [];
$success = '';
$formData = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'role' => 'manager'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data
    $firstName = trim($_POST["first_name"] ?? '');
    $lastName = trim($_POST["lastName"] ?? ''); 
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["inputPassword"] ?? '';
    $passwordConfirm = $_POST["inputPasswordConfirm"] ?? '';
    $role = trim($_POST["role"] ?? 'manager');
    
    // Store form data for repopulation
    $formData = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'role' => $role
    ];
    
    // Comprehensive validation
    
    // First name validation
    if (empty($firstName)) {
        $errors[] = 'First name is required.';
    } elseif (strlen($firstName) < 2 || strlen($firstName) > 50) {
        $errors[] = 'First name must be between 2-50 characters.';
    } elseif (!preg_match("/^[a-zA-Z\s'-]+$/u", $firstName)) {
        $errors[] = 'First name contains invalid characters.';
    }
    
    // Last name validation
    if (empty($lastName)) {
        $errors[] = 'Last name is required.';
    } elseif (strlen($lastName) < 2 || strlen($lastName) > 50) {
        $errors[] = 'Last name must be between 2-50 characters.';
    } elseif (!preg_match("/^[a-zA-Z\s'-]+$/u", $lastName)) {
        $errors[] = 'Last name contains invalid characters.';
    }
    
    // Email validation
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (strlen($email) > 255) {
        $errors[] = 'Email address is too long.';
    }
    
    // Password validation with 2025 best practices
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } else {
        $passwordErrors = [];
        
        if (strlen($password) < 8) {
            $passwordErrors[] = 'be at least 8 characters long';
        }
        if (strlen($password) > 128) {
            $passwordErrors[] = 'be no more than 128 characters long';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $passwordErrors[] = 'contain at least one lowercase letter';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $passwordErrors[] = 'contain at least one uppercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $passwordErrors[] = 'contain at least one number';
        }
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
            $passwordErrors[] = 'contain at least one special character';
        }
        
        if (!empty($passwordErrors)) {
            $errors[] = 'Password must ' . implode(', ', $passwordErrors) . '.';
        }
    }
    
    // Confirm password validation
    if (empty($passwordConfirm)) {
        $errors[] = 'Please confirm your password.';
    } elseif ($password !== $passwordConfirm) {
        $errors[] = 'Passwords do not match.';
    }
    
    // Role validation
    $allowedRoles = ['admin', 'manager', 'user'];
    if (!in_array($role, $allowedRoles)) {
        $role = 'manager'; // Default fallback
    }
    
    // Role permission check
    if ($isFromAdmin && !in_array($role, ['admin', 'manager'])) {
        $errors[] = 'Invalid role selected.';
    } elseif (!$isFromAdmin && $role !== 'manager') {
        $role = 'manager'; // Force manager role for non-admin registration
    }
    
    // Check if email already exists (using prepared statement)
    if (empty($errors)) {
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $errors[] = 'Email address is already registered. Please use a different email.';
        }
        mysqli_stmt_close($stmt);
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash password with current best practices
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user using prepared statement
        $stmt = mysqli_prepare($koneksi, "INSERT INTO users (first_name, last_name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, "sssss", $firstName, $lastName, $email, $hashedPassword, $role);
        
        if (mysqli_stmt_execute($stmt)) {
            $userId = mysqli_insert_id($koneksi);
            
            // Log successful registration
            error_log("New user registered - ID: $userId, Email: $email, Role: $role");
            
            $success = 'Registration successful! You can now login with your credentials.';
            
            // Clear form data on success
            $formData = [
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'role' => 'manager'
            ];
            
            // Redirect after successful registration
            if ($isFromAdmin) {
                header("Location: register.php?success=1&from=admin");
            } else {
                header("Location: register.php?success=1");
            }
            exit();
        } else {
            $errors[] = 'Registration failed. Please try again.';
            error_log("Registration failed for email: $email - " . mysqli_error($koneksi));
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Handle success message from redirect
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success = 'Registration successful! You can now login with your credentials.';
}

mysqli_close($koneksi);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Register - Zakat System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --shadow-soft: 0 20px 40px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 30px 60px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--primary-gradient);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(120, 219, 255, 0.3) 0%, transparent 50%);
            animation: backgroundShift 15s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes backgroundShift {
            0%, 100% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.1) rotate(180deg); }
        }

        /* Floating particles */
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            pointer-events: none;
            animation: float 6s ease-in-out infinite;
        }

        .particle:nth-child(1) { width: 4px; height: 4px; top: 20%; left: 20%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 6px; height: 6px; top: 60%; left: 80%; animation-delay: 2s; }
        .particle:nth-child(3) { width: 3px; height: 3px; top: 80%; left: 40%; animation-delay: 4s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .registration-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
        }

        .registration-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: var(--shadow-soft);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: slideUp 0.8s ease-out;
        }

        .registration-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s linear infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .card-header h3 {
            font-weight: 600;
            font-size: 1.5rem;
            position: relative;
            z-index: 1;
            margin: 0;
        }

        .card-header .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .card-body {
            padding: 2.5rem;
        }

        /* Form Styles */
        .form-floating {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-floating input,
        .form-floating select {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-floating input:focus,
        .form-floating select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: rgba(255, 255, 255, 0.95);
            transform: translateY(-2px);
        }

        .form-floating label {
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Alert Styles */
        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
            animation: alertSlide 0.5s ease-out;
        }

        @keyframes alertSlide {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(75, 192, 192, 0.1), rgba(75, 192, 192, 0.2));
            color: #0f5132;
            border-left: 4px solid #0f5132;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(220, 53, 69, 0.2));
            color: #842029;
            border-left: 4px solid #842029;
        }

        /* Password Strength Indicator */
        .password-strength {
            margin-top: 0.5rem;
        }

        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background: #e9ecef;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .strength-fill {
            height: 100%;
            border-radius: 2px;
            transition: all 0.3s ease;
            background: linear-gradient(90deg, #ff6b6b, #feca57, #48dbfb, #0abde3, #00d2d3);
        }

        /* Button Styles */
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        /* Footer */
        .card-footer {
            background: rgba(248, 249, 250, 0.8);
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            text-align: center;
        }

        .card-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .card-footer a:hover {
            color: #764ba2;
            text-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
        }

        /* Password Requirements */
        .password-requirements {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .password-requirements ul {
            margin-bottom: 0;
            padding-left: 1.2rem;
        }

        .password-requirements li {
            margin-bottom: 0.25rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        /* Loading States */
        .btn-loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            margin-left: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 576px) {
            .registration-container {
                padding: 1rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .card-header {
                padding: 1.5rem;
            }
            
            .card-header h3 {
                font-size: 1.25rem;
            }
        }

        /* Input validation states */
        .is-invalid {
            border-color: #dc3545 !important;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .is-valid {
            border-color: #28a745 !important;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-gradient);
        }
    </style>
</head>
<body>
    <!-- Floating particles -->
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>

    <div class="registration-container">
        <div class="registration-card">
            <div class="card-header">
                <i class="fas fa-user-plus icon"></i>
                <h3>
                    <?php echo $isFromAdmin ? 'Create New User Account' : 'Create Your Account'; ?>
                </h3>
                <p class="mb-0 opacity-75">
                    <?php echo $isFromAdmin ? 'Add a new team member' : 'Join our financial management system'; ?>
                </p>
            </div>
            
            <div class="card-body">
                <!-- Success Message -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Error Messages -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form action="register.php<?php echo $isFromAdmin ? '?from=admin' : ''; ?>" method="post" id="registrationForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input class="form-control <?php echo !empty($errors) && empty($formData['first_name']) ? 'is-invalid' : ''; ?>" 
                                       name="first_name" id="first_name" type="text" 
                                       placeholder="Enter your first name" 
                                       value="<?php echo htmlspecialchars($formData['first_name']); ?>" 
                                       required maxlength="50" />
                                <label for="first_name"><i class="fas fa-user me-2"></i>First name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input class="form-control <?php echo !empty($errors) && empty($formData['last_name']) ? 'is-invalid' : ''; ?>" 
                                       name="lastName" id="lastName" type="text" 
                                       placeholder="Enter your last name" 
                                       value="<?php echo htmlspecialchars($formData['last_name']); ?>" 
                                       required maxlength="50" />
                                <label for="lastName"><i class="fas fa-user me-2"></i>Last name</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-floating">
                        <input class="form-control <?php echo !empty($errors) && empty($formData['email']) ? 'is-invalid' : ''; ?>" 
                               name="email" id="email" type="email" 
                               placeholder="name@example.com" 
                               value="<?php echo htmlspecialchars($formData['email']); ?>" 
                               required maxlength="255" />
                        <label for="email"><i class="fas fa-envelope me-2"></i>Email address</label>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input class="form-control" name="inputPassword" id="inputPassword" 
                                       type="password" placeholder="Create a password" 
                                       required minlength="8" maxlength="128" />
                                <label for="inputPassword"><i class="fas fa-lock me-2"></i>Password</label>
                            </div>
                            <div class="password-strength">
                                <div class="strength-bar">
                                    <div class="strength-fill" style="width: 0%"></div>
                                </div>
                                <small class="text-muted">Strength: <span id="strength-text">None</span></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input class="form-control" name="inputPasswordConfirm" id="inputPasswordConfirm" 
                                       type="password" placeholder="Confirm password" 
                                       required minlength="8" maxlength="128" />
                                <label for="inputPasswordConfirm"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                            </div>
                            <div class="mt-2">
                                <small id="password-match" class="text-muted"></small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Password Requirements -->
                    <div class="password-requirements">
                        <small class="text-muted">
                            <strong><i class="fas fa-shield-alt me-2"></i>Password Requirements:</strong>
                            <ul class="small mt-2">
                                <li>At least 8 characters long</li>
                                <li>One uppercase letter (A-Z)</li>
                                <li>One lowercase letter (a-z)</li>
                                <li>One number (0-9)</li>
                                <li>One special character (!@#$%^&*)</li>
                            </ul>
                        </small>
                    </div>

                    <!-- Role Field - hanya muncul jika dari admin -->
                    <?php if ($isFromAdmin): ?>
                        <div class="form-floating">
                            <select name="role" id="role" class="form-control" required>
                                <option value="manager" <?php echo $formData['role'] === 'manager' ? 'selected' : ''; ?>>Manager</option>
                                <option value="admin" <?php echo $formData['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <label for="role"><i class="fas fa-user-tag me-2"></i>Role</label>
                        </div>
                    <?php endif; ?>

                    <div class="d-grid">
                        <button class="btn btn-primary btn-lg" type="submit" id="submitBtn">
                            <span class="btn-text">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="card-footer">
                <div class="small">
                    <a href="<?php echo $isFromAdmin ? 'dashboard.php' : 'index.php'; ?>">
                        <i class="fas fa-arrow-left me-2"></i>
                        <?php echo $isFromAdmin ? 'Back to Dashboard' : 'Already have an account? Sign in'; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Password strength checker
    function checkPasswordStrength(password) {
        let strength = 0;
        let feedback = [];
        
        if (password.length >= 8) strength += 1;
        else feedback.push("8+ characters");
        
        if (/[a-z]/.test(password)) strength += 1;
        else feedback.push("lowercase letter");
        
        if (/[A-Z]/.test(password)) strength += 1;
        else feedback.push("uppercase letter");
        
        if (/[0-9]/.test(password)) strength += 1;
        else feedback.push("number");
        
        if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength += 1;
        else feedback.push("special character");
        
        return { strength, feedback };
    }
    
    // Real-time password validation with enhanced visual feedback
    document.getElementById('inputPassword').addEventListener('input', function() {
        const password = this.value;
        const result = checkPasswordStrength(password);
        const strengthFill = document.querySelector('.strength-fill');
        const strengthText = document.getElementById('strength-text');
        
        const percentage = (result.strength / 5) * 100;
        strengthFill.style.width = percentage + '%';
        
        // Enhanced color coding and feedback
        if (result.strength <= 1) {
            strengthFill.style.background = '#ff6b6b';
            strengthText.textContent = 'Very Weak';
            strengthText.className = 'text-danger fw-bold';
        } else if (result.strength <= 2) {
            strengthFill.style.background = '#feca57';
            strengthText.textContent = 'Weak';
            strengthText.className = 'text-warning fw-bold';
        } else if (result.strength <= 3) {
            strengthFill.style.background = '#48dbfb';
            strengthText.textContent = 'Good';
            strengthText.className = 'text-info fw-bold';
        } else if (result.strength <= 4) {
            strengthFill.style.background = '#0abde3';
            strengthText.textContent = 'Strong';
            strengthText.className = 'text-primary fw-bold';
        } else {
            strengthFill.style.background = '#00d2d3';
            strengthText.textContent = 'Very Strong';
            strengthText.className = 'text-success fw-bold';
        }
        
        // Add visual feedback to input
        if (result.strength >= 3) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else if (password.length > 0) {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-valid', 'is-invalid');
        }
    });
    
    // Enhanced password confirmation checker
    document.getElementById('inputPasswordConfirm').addEventListener('input', function() {
        const password = document.getElementById('inputPassword').value;
        const confirm = this.value;
        const matchText = document.getElementById('password-match');
        
        if (confirm === '') {
            matchText.textContent = '';
            matchText.className = 'text-muted';
            this.classList.remove('is-valid', 'is-invalid');
        } else if (password === confirm) {
            matchText.innerHTML = '<i class="fas fa-check-circle me-1"></i>Passwords match';
            matchText.className = 'text-success fw-bold';
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            matchText.innerHTML = '<i class="fas fa-times-circle me-1"></i>Passwords do not match';
            matchText.className = 'text-danger fw-bold';
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    });
    
    // Real-time form validation
    const form = document.getElementById('registrationForm');
    const inputs = form.querySelectorAll('input[required]');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });
    
    function validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        
        if (field.hasAttribute('required') && !value) {
            isValid = false;
        }
        
        if (field.type === 'email' && value && !isValidEmail(value)) {
            isValid = false;
        }
        
        if (field.name === 'first_name' || field.name === 'lastName') {
            if (value && (value.length < 2 || value.length > 50 || !/^[a-zA-Z\s'-]+$/.test(value))) {
                isValid = false;
            }
        }
        
        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
        }
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Enhanced form submission handling
    form.addEventListener('submit', function(e) {
        // Validate all fields before submission
        let isFormValid = true;
        
        inputs.forEach(input => {
            validateField(input);
            if (input.classList.contains('is-invalid')) {
                isFormValid = false;
            }
        });
        
        // Check password confirmation
        const password = document.getElementById('inputPassword').value;
        const confirmPassword = document.getElementById('inputPasswordConfirm').value;
        
        if (password !== confirmPassword) {
            isFormValid = false;
        }
        
        if (!isFormValid) {
            e.preventDefault();
            
            // Smooth scroll to first error
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            
            // Show error message
            showNotification('Please fix the form errors before submitting.', 'error');
            return;
        }
        
        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const spinner = submitBtn.querySelector('.spinner-border');
        
        submitBtn.disabled = true;
        submitBtn.classList.add('btn-loading');
        btnText.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Account...';
        spinner.classList.remove('d-none');
        
        // Add a slight delay for better UX
        setTimeout(() => {
            // Form will submit naturally
        }, 500);
    });
    
    // Notification system
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        `;
        
        notification.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
    
    // Auto-hide alerts with smooth animation
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            if (!alert.classList.contains('position-fixed')) {
                alert.style.transition = 'all 0.5s ease-out';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 500);
            }
        });
    }, 8000);
    
    // Enhanced keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.tagName === 'INPUT') {
            const inputs = Array.from(form.querySelectorAll('input, select'));
            const currentIndex = inputs.indexOf(e.target);
            const nextInput = inputs[currentIndex + 1];
            
            if (nextInput) {
                e.preventDefault();
                nextInput.focus();
            }
        }
    });
    
    // Add smooth transitions for form interactions
    const formElements = document.querySelectorAll('.form-control, .btn');
    formElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-1px)';
        });
        
        element.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Initialize tooltips for better UX
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
    
    // Add loading animation to page
    window.addEventListener('load', function() {
        const card = document.querySelector('.registration-card');
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);
    });
    
    // Add particle animation on successful registration
    if (document.querySelector('.alert-success')) {
        createSuccessParticles();
    }
    
    function createSuccessParticles() {
        for (let i = 0; i < 20; i++) {
            const particle = document.createElement('div');
            particle.style.cssText = `
                position: fixed;
                width: 6px;
                height: 6px;
                background: linear-gradient(45deg, #00d2d3, #48dbfb);
                border-radius: 50%;
                pointer-events: none;
                z-index: 1000;
                left: 50%;
                top: 50%;
                animation: explode 2s ease-out forwards;
                animation-delay: ${i * 0.1}s;
            `;
            document.body.appendChild(particle);
            
            setTimeout(() => particle.remove(), 2000);
        }
    }
    
    // Add CSS for particle explosion
    const style = document.createElement('style');
    style.textContent = `
        @keyframes explode {
            0% {
                transform: translate(-50%, -50%) scale(0);
                opacity: 1;
            }
            50% {
                opacity: 1;
            }
            100% {
                transform: translate(${Math.random() * 400 - 200}px, ${Math.random() * 400 - 200}px) scale(1);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>