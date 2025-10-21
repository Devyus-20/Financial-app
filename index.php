<?php
session_start();
require 'database/init.php';

// Redirect if already logged in
if (isset($_SESSION['id']) && isset($_SESSION['role'])) {
    if (trim(strtolower($_SESSION['role'])) === 'admin' || trim(strtolower($_SESSION['role'])) === 'manager') {
        header('Location: dashboard.php');
        exit();
    } else {
        header('Location: index.php');
        exit();
    }
}

$loginError = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $loginError = 'Email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $loginError = 'Please enter a valid email address.';
    } else {
        // Use prepared statement for security
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Hybrid password verification
            $passwordValid = false;
            $needsRehash = false;
            
            // Check if password starts with $2y$ (bcrypt hash)
            if (substr($user['password_hash'], 0, 4) === '$2y$') {
                // It's a hashed password, use password_verify
                $passwordValid = password_verify($password, $user['password_hash']);
            } else {
                // It's a plain text password, do direct comparison
                $passwordValid = ($password === $user['password_hash']);
                $needsRehash = true; // Flag to rehash this password
            }

            if ($passwordValid) {
                // If password was plain text, hash it now
                if ($needsRehash) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = mysqli_prepare($koneksi, "UPDATE users SET password_hash = ? WHERE id = ?");
                    mysqli_stmt_bind_param($updateStmt, "si", $newHash, $user['id']);
                    mysqli_stmt_execute($updateStmt);
                    mysqli_stmt_close($updateStmt);
                    
                    // Log the rehashing for security audit
                    error_log("Password rehashed for user ID: " . $user['id'] . " (" . $email . ")");
                }
                
                // Regenerate session ID untuk security
                session_regenerate_id(true);
                
                // Simpan data session
                $_SESSION['id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = trim($user['role']); // Remove extra spaces
                $_SESSION['login_time'] = time();

                // Debug - untuk melihat role yang tersimpan (hapus di production)
                error_log("User login - ID: " . $user['id'] . ", Role: " . $user['role']);
                
                // Update last login time
                $update_login = mysqli_prepare($koneksi, "UPDATE users SET last_login = NOW() WHERE id = ?");
                mysqli_stmt_bind_param($update_login, "i", $user['id']);
                mysqli_stmt_execute($update_login);
                mysqli_stmt_close($update_login);
                
                // Redirect berdasarkan role dengan pengecekan yang lebih ketat
                $user_role = trim(strtolower($user['role']));
                if ($user_role === 'admin' || $user_role === 'manager') {
                    header('Location: dashboard.php');
                    exit();
                } else {
                    // Jika role adalah 'user' atau role lainnya
                    header('Location: dashboard.php'); // Ganti dengan halaman yang sesuai
                    exit();
                }
            } else {
                $loginError = 'Invalid email or password.';
                // Log failed login attempt
                error_log("Failed login attempt for email: " . $email);
            }
        } else {
            $loginError = 'Invalid email or password.';
            // Log failed login attempt
            error_log("Failed login attempt for non-existent email: " . $email);
        }
        
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Login - Welcome Back</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="modern-login-body">
    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="bg-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
        </div>
    </div>

    <div class="login-container">
        <div class="login-wrapper">
            <!-- Left Side - Welcome Section -->
            <div class="welcome-section">
                <div class="welcome-content">
                    <div class="brand-logo">
                        <div class="logo-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h1>Welcome Back</h1>
                    </div>
                    <p class="welcome-text">
                        Sign in to your account to continue your journey with us. 
                        Experience seamless access to all your favorite features.
                    </p>
                    <div class="feature-highlights">
                        <div class="feature-item">
                            <i class="fas fa-lock"></i>
                            <span>Secure Authentication</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-lightning-bolt"></i>
                            <span>Fast & Reliable</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Mobile Optimized</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="form-section">
                <div class="form-container">
                    <div class="form-header">
                        <h2>Sign In</h2>
                        <p>Enter your credentials to access your account</p>
                    </div>

                    <?php if (!empty($loginError)): ?>
                        <div class="error-alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?php echo htmlspecialchars($loginError); ?></span>
                            <button type="button" class="close-alert">&times;</button>
                        </div>
                    <?php endif; ?>
                    
                    <form action="" method="post" id="loginForm" class="login-form">
                        <div class="input-group">
                            <div class="input-wrapper">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" name="email" id="inputEmail" 
                                       placeholder="Enter your email" required
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
                                <label for="inputEmail">Email Address</label>
                            </div>
                        </div>

                        <div class="input-group">
                            <div class="input-wrapper">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" name="password" id="inputPassword" 
                                       placeholder="Enter your password" required />
                                <label for="inputPassword">Password</label>
                                <button type="button" class="toggle-password" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-options">
                            <div class="remember-me">
                                <input type="checkbox" id="inputRememberPassword" 
                                       name="inputRememberPassword" value="1" />
                                <label for="inputRememberPassword">Remember me</label>
                            </div>
                            <!-- <a href="reset_password_form.php" class="forgot-password">
                                Forgot Password?
                            </a> -->
                        </div>

                        <button type="submit" class="login-btn" id="loginBtn">
                            <span class="btn-text">Sign In</span>
                            <span class="btn-spinner">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </form>

                    <div class="form-footer">
                        <p>Don't have an account? 
                            <a href="register.php">Create one here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    
    <script>
    // Toggle password visibility
    function togglePassword() {
        const passwordInput = document.getElementById('inputPassword');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

    // Form validation and loading state
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const email = document.getElementById('inputEmail').value.trim();
        const password = document.getElementById('inputPassword').value.trim();
        const loginBtn = document.getElementById('loginBtn');
        const btnText = loginBtn.querySelector('.btn-text');
        const spinner = loginBtn.querySelector('.btn-spinner');
        
        // Basic validation
        if (!email || !password) {
            e.preventDefault();
            showNotification('Please fill in all fields.', 'error');
            return false;
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            showNotification('Please enter a valid email address.', 'error');
            document.getElementById('inputEmail').focus();
            return false;
        }
        
        // Show loading state
        loginBtn.disabled = true;
        loginBtn.classList.add('loading');
        btnText.style.opacity = '0';
        spinner.style.display = 'inline-block';
    });

    // Custom notification function
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // Close error alert
    document.querySelectorAll('.close-alert').forEach(button => {
        button.addEventListener('click', function() {
            this.parentElement.style.opacity = '0';
            this.parentElement.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                this.parentElement.remove();
            }, 300);
        });
    });
    
    // Auto hide error messages after 10 seconds
    setTimeout(function() {
        const errorAlert = document.querySelector('.error-alert');
        if (errorAlert) {
            errorAlert.style.opacity = '0';
            errorAlert.style.transform = 'translateY(-10px)';
            setTimeout(() => errorAlert.remove(), 300);
        }
    }, 10000);
    
    // Reset form state if back button is used
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            const loginBtn = document.getElementById('loginBtn');
            const btnText = loginBtn.querySelector('.btn-text');
            const spinner = loginBtn.querySelector('.btn-spinner');
            
            loginBtn.disabled = false;
            loginBtn.classList.remove('loading');
            btnText.style.opacity = '1';
            spinner.style.display = 'none';
        }
    });

    // Input focus animations
    document.querySelectorAll('.input-wrapper input').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (this.value === '') {
                this.parentElement.classList.remove('focused');
            }
        });
        
        // Check if input has value on page load
        if (input.value !== '') {
            input.parentElement.classList.add('focused');
        }
    });
    </script>
    
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        line-height: 1.6;
        color: #333;
        overflow-x: hidden;
    }

    .modern-login-body {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
    }

    /* Animated Background */
    .animated-bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 1;
    }

    .bg-shapes {
        position: relative;
        width: 100%;
        height: 100%;
    }

    .shape {
        position: absolute;
        border-radius: 50%;
        opacity: 0.1;
        animation: float 20s infinite linear;
    }

    .shape-1 {
        width: 80px;
        height: 80px;
        background: #fff;
        top: 10%;
        left: 10%;
        animation-delay: 0s;
    }

    .shape-2 {
        width: 60px;
        height: 60px;
        background: #fff;
        top: 70%;
        left: 80%;
        animation-delay: 5s;
    }

    .shape-3 {
        width: 100px;
        height: 100px;
        background: #fff;
        top: 30%;
        left: 70%;
        animation-delay: 10s;
    }

    .shape-4 {
        width: 40px;
        height: 40px;
        background: #fff;
        top: 80%;
        left: 20%;
        animation-delay: 15s;
    }

    @keyframes float {
        0% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
        100% { transform: translateY(0px) rotate(360deg); }
    }

    /* Login Container */
    .login-container {
        position: relative;
        z-index: 2;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .login-wrapper {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        width: 100%;
        max-width: 1100px;
        min-height: 600px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        animation: slideUp 0.8s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Welcome Section */
    .welcome-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 40px;
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .welcome-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.3;
    }

    .welcome-content {
        position: relative;
        z-index: 1;
    }

    .brand-logo {
        margin-bottom: 30px;
    }

    .logo-icon {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        backdrop-filter: blur(10px);
    }

    .logo-icon i {
        font-size: 36px;
        color: white;
    }

    .welcome-section h1 {
        font-size: 42px;
        font-weight: 700;
        margin-bottom: 20px;
        line-height: 1.2;
    }

    .welcome-text {
        font-size: 18px;
        opacity: 0.9;
        margin-bottom: 40px;
        line-height: 1.6;
    }

    .feature-highlights {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 16px;
        opacity: 0.9;
    }

    .feature-item i {
        width: 24px;
        font-size: 18px;
    }

    /* Form Section */
    .form-section {
        padding: 60px 40px;
        display: flex;
        align-items: center;
    }

    .form-container {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }

    .form-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .form-header h2 {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 10px;
    }

    .form-header p {
        color: #666;
        font-size: 16px;
    }

    /* Error Alert */
    .error-alert {
        background: linear-gradient(135deg, #ff6b6b, #ee5a24);
        color: white;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
        animation: slideIn 0.3s ease-out;
        box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .error-alert i {
        font-size: 18px;
        flex-shrink: 0;
    }

    .close-alert {
        position: absolute;
        top: 8px;
        right: 12px;
        background: none;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
        padding: 4px;
        line-height: 1;
        opacity: 0.8;
        transition: opacity 0.2s ease;
    }

    .close-alert:hover {
        opacity: 1;
    }

    /* Form Styles */
    .login-form {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .input-group {
        position: relative;
    }

    .input-wrapper {
        position: relative;
        background: #f8f9fa;
        border-radius: 12px;
        border: 2px solid transparent;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .input-wrapper.focused {
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .input-wrapper input {
        width: 100%;
        padding: 20px 20px 20px 50px;
        border: none;
        background: transparent;
        font-size: 16px;
        font-weight: 500;
        color: #1a1a1a;
        outline: none;
        transition: all 0.3s ease;
    }

    .input-wrapper input::placeholder {
        color: transparent;
    }

    .input-wrapper label {
        position: absolute;
        top: 50%;
        left: 50px;
        transform: translateY(-50%);
        color: #666;
        font-size: 16px;
        font-weight: 500;
        pointer-events: none;
        transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .input-wrapper.focused label,
    .input-wrapper input:not(:placeholder-shown) + label {
        top: 16px;
        font-size: 12px;
        font-weight: 600;
        color: #667eea;
        transform: translateY(0);
    }

    .input-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        font-size: 18px;
        transition: color 0.3s ease;
    }

    .input-wrapper.focused .input-icon {
        color: #667eea;
    }

    .toggle-password {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #999;
        cursor: pointer;
        padding: 4px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .toggle-password:hover {
        color: #667eea;
        background: rgba(102, 126, 234, 0.1);
    }

    /* Form Options */
    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 8px;
    }

    .remember-me {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .remember-me input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #667eea;
    }

    .remember-me label {
        font-size: 14px;
        color: #666;
        cursor: pointer;
    }

    .forgot-password {
        color: #667eea;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: color 0.2s ease;
    }

    .forgot-password:hover {
        color: #5a67d8;
        text-decoration: underline;
    }

    /* Login Button */
    .login-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 18px 32px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        margin-top: 16px;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .login-btn:active {
        transform: translateY(0);
    }

    .login-btn.loading {
        pointer-events: none;
    }

    .btn-spinner {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .btn-text {
        transition: opacity 0.3s ease;
    }

    /* Form Footer */
    .form-footer {
        text-align: center;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #e0e0e0;
    }

    .form-footer p {
        color: #666;
        font-size: 14px;
    }

    .form-footer a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s ease;
    }

    .form-footer a:hover {
        color: #5a67d8;
        text-decoration: underline;
    }

    /* Notification */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 16px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    }

    .notification.show {
        transform: translateX(0);
    }

    .notification.error {
        border-left: 4px solid #ff6b6b;
    }

    .notification.success {
        border-left: 4px solid #51cf66;
    }

    .notification i {
        font-size: 18px;
    }

    .notification.error i {
        color: #ff6b6b;
    }

    .notification.success i {
        color: #51cf66;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .login-wrapper {
            grid-template-columns: 1fr;
            max-width: 500px;
        }

        .welcome-section {
            padding: 40px 30px;
            text-align: center;
        }

        .welcome-section h1 {
            font-size: 32px;
        }

        .welcome-text {
            font-size: 16px;
        }

        .feature-highlights {
            flex-direction: row;
            justify-content: center;
            flex-wrap: wrap;
        }

        .feature-item {
            flex-direction: column;
            text-align: center;
            gap: 8px;
        }

        .form-section {
            padding: 40px 30px;
        }

        .form-header h2 {
            font-size: 28px;
        }

        .form-options {
            flex-direction: column;
            gap: 16px;
            align-items: flex-start;
        }
    }

    @media (max-width: 480px) {
        .login-container {
            padding: 10px;
        }

        .welcome-section,
        .form-section {
            padding: 30px 20px;
        }

        .notification {
            top: 10px;
            right: 10px;
            left: 10px;
            transform: translateY(-100%);
        }

        .notification.show {
            transform: translateY(0);
        }
    }
    </style>
</body>

</html>