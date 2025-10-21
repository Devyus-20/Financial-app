<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Reset Password" />
    <meta name="author" content="" />
    <title>Reset Password - Aplikasi Zakat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-color: #f093fb;
            --accent-color: #4facfe;
            --text-dark: #2d3748;
            --text-light: #718096;
            --border-color: #e2e8f0;
            --success-color: #48bb78;
            --error-color: #f56565;
            --warning-color: #ed8936;
            --info-color: #4299e1;
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="gradient" cx="50%" cy="50%" r="50%"><stop offset="0%" style="stop-color:rgba(255,255,255,0.1);stop-opacity:1" /><stop offset="100%" style="stop-color:rgba(255,255,255,0);stop-opacity:0" /></radialGradient></defs><circle cx="20" cy="20" r="2" fill="url(%23gradient)"><animate attributeName="cy" values="20;80;20" dur="3s" repeatCount="indefinite"/></circle><circle cx="80" cy="80" r="2" fill="url(%23gradient)"><animate attributeName="cy" values="80;20;80" dur="4s" repeatCount="indefinite"/></circle><circle cx="50" cy="50" r="1.5" fill="url(%23gradient)"><animate attributeName="cx" values="50;20;50" dur="5s" repeatCount="indefinite"/></circle></svg>') repeat;
            opacity: 0.1;
            animation: float 20s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 480px;
            width: 100%;
            animation: slideUp 0.6s ease-out;
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

        .card-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .card-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
        }

        .header-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .header-subtitle {
            opacity: 0.9;
            font-size: 0.95rem;
            font-weight: 300;
        }

        .card-body {
            padding: 2rem;
        }

        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-danger {
            background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
            color: var(--error-color);
        }

        .alert-info {
            background: linear-gradient(135deg, #bee3f8 0%, #90cdf4 100%);
            color: var(--info-color);
        }

        .alert-success {
            background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
            color: var(--success-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            color: var(--text-dark);
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }

        .strength-bar {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak .strength-fill {
            width: 33%;
            background: #f56565;
        }

        .strength-medium .strength-fill {
            width: 66%;
            background: #ed8936;
        }

        .strength-strong .strength-fill {
            width: 100%;
            background: #48bb78;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            border: 2px solid var(--border-color);
            border-radius: 4px;
            cursor: pointer;
        }

        .form-check-input:checked {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-label {
            color: var(--text-light);
            font-size: 0.9rem;
            cursor: pointer;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: var(--text-light);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--border-color);
            color: var(--text-dark);
        }

        .card-footer {
            background: rgba(248, 250, 252, 0.8);
            padding: 1.5rem;
            text-align: center;
            border-top: 1px solid rgba(226, 232, 240, 0.5);
        }

        .footer-text {
            color: var(--text-light);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .footer-icon {
            color: var(--success-color);
        }

        /* Responsive Design */
        @media (max-width: 576px) {
            .main-container {
                padding: 10px;
            }
            
            .reset-card {
                border-radius: 16px;
            }
            
            .card-header, .card-body {
                padding: 1.5rem;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 1rem;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Loading Animation */
        .btn-loading {
            position: relative;
            pointer-events: none;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 16px;
            height: 16px;
            margin: -8px 0 0 -8px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="reset-card">
            <div class="card-header">
                <div class="header-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h1 class="header-title">Buat Password Baru</h1>
                <p class="header-subtitle">Masukkan password baru yang aman untuk akun Anda</p>
            </div>
            
            <div class="card-body">
                <!-- Alert Error (Demo) -->
                <div class="alert alert-danger" style="display: none;" id="errorAlert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="errorMessage"></span>
                </div>
                
                <!-- Alert Info (Demo) -->
                <div class="alert alert-info" id="infoAlert">
                    <i class="fas fa-info-circle"></i>
                    <span>Halo <strong>John Doe</strong>, silakan buat password baru untuk akun: <strong>john@example.com</strong></span>
                </div>
                
                <form id="resetForm">
                    <input type="hidden" name="token" value="demo_token_123">
                    
                    <div class="form-group">
                        <label class="form-label" for="newPassword">
                            <i class="fas fa-lock"></i> Password Baru
                        </label>
                        <div class="password-wrapper">
                            <input class="form-control" name="newPassword" id="newPassword" type="password"
                                placeholder="Masukkan password baru (min. 8 karakter)" required minlength="8" />
                            <button type="button" class="password-toggle" onclick="togglePassword('newPassword')">
                                <i class="fas fa-eye" id="newPasswordToggle"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength" style="display: none;">
                            <div class="strength-bar">
                                <div class="strength-fill"></div>
                            </div>
                            <span class="strength-text">Kekuatan password: <span id="strengthLevel">Lemah</span></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="confirmPassword">
                            <i class="fas fa-lock"></i> Konfirmasi Password
                        </label>
                        <div class="password-wrapper">
                            <input class="form-control" name="confirmPassword" id="confirmPassword" type="password"
                                placeholder="Ulangi password baru" required minlength="8" />
                            <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                                <i class="fas fa-eye" id="confirmPasswordToggle"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="showPassword">
                        <label class="form-check-label" for="showPassword">
                            Tampilkan kedua password
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <a href="#" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Kembali ke Login
                        </a>
                        <button class="btn btn-primary" type="submit" name="resetPasswordSubmit" id="submitBtn">
                            <i class="fas fa-save"></i>
                            Simpan Password Baru
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="card-footer">
                <div class="footer-text">
                    <i class="fas fa-shield-alt footer-icon"></i>
                    Password Anda akan dienkripsi dengan aman menggunakan teknologi terkini
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const toggle = document.getElementById(fieldId + 'Toggle');
            
            if (field.type === 'password') {
                field.type = 'text';
                toggle.className = 'fas fa-eye-slash';
            } else {
                field.type = 'password';
                toggle.className = 'fas fa-eye';
            }
        }

        // Show/hide both passwords
        document.getElementById('showPassword').addEventListener('change', function() {
            const newPassword = document.getElementById('newPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            const newToggle = document.getElementById('newPasswordToggle');
            const confirmToggle = document.getElementById('confirmPasswordToggle');
            
            if (this.checked) {
                newPassword.type = 'text';
                confirmPassword.type = 'text';
                newToggle.className = 'fas fa-eye-slash';
                confirmToggle.className = 'fas fa-eye-slash';
            } else {
                newPassword.type = 'password';
                confirmPassword.type = 'password';
                newToggle.className = 'fas fa-eye';
                confirmToggle.className = 'fas fa-eye';
            }
        });

        // Password strength checker
        function checkPasswordStrength(password) {
            const strengthIndicator = document.getElementById('passwordStrength');
            const strengthLevel = document.getElementById('strengthLevel');
            
            if (password.length === 0) {
                strengthIndicator.style.display = 'none';
                return;
            }
            
            strengthIndicator.style.display = 'block';
            
            let score = 0;
            
            // Length check
            if (password.length >= 8) score++;
            if (password.length >= 12) score++;
            
            // Character variety
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            
            const strengthBar = strengthIndicator.querySelector('.strength-bar');
            
            if (score <= 2) {
                strengthBar.className = 'strength-bar strength-weak';
                strengthLevel.textContent = 'Lemah';
                strengthLevel.style.color = '#f56565';
            } else if (score <= 4) {
                strengthBar.className = 'strength-bar strength-medium';
                strengthLevel.textContent = 'Sedang';
                strengthLevel.style.color = '#ed8936';
            } else {
                strengthBar.className = 'strength-bar strength-strong';
                strengthLevel.textContent = 'Kuat';
                strengthLevel.style.color = '#48bb78';
            }
        }

        // Password match validation
        function validatePasswordMatch() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const confirmField = document.getElementById('confirmPassword');
            
            if (confirmPassword.length > 0) {
                if (newPassword !== confirmPassword) {
                    confirmField.setCustomValidity('Password tidak cocok');
                    confirmField.style.borderColor = '#f56565';
                } else {
                    confirmField.setCustomValidity('');
                    confirmField.style.borderColor = '#48bb78';
                }
            } else {
                confirmField.setCustomValidity('');
                confirmField.style.borderColor = '#e2e8f0';
            }
        }

        // Event listeners
        document.getElementById('newPassword').addEventListener('input', function() {
            checkPasswordStrength(this.value);
            validatePasswordMatch();
        });

        document.getElementById('confirmPassword').addEventListener('input', validatePasswordMatch);

        // Form submission
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Validate form
            if (newPassword.length < 8) {
                showError('Password minimal 8 karakter');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showError('Konfirmasi password tidak cocok');
                return;
            }
            
            if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(newPassword)) {
                showError('Password harus mengandung huruf besar, huruf kecil, dan angka');
                return;
            }
            
            // Show loading state
            submitBtn.classList.add('btn-loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            
            // Simulate API call
            setTimeout(() => {
                showSuccess('Password berhasil direset! Mengalihkan ke halaman login...');
                setTimeout(() => {
                    // Redirect to login page
                    window.location.href = 'index.php';
                }, 2000);
            }, 1500);
        });

        // Utility functions
        function showError(message) {
            const errorAlert = document.getElementById('errorAlert');
            const errorMessage = document.getElementById('errorMessage');
            const infoAlert = document.getElementById('infoAlert');
            
            errorMessage.textContent = message;
            errorAlert.style.display = 'flex';
            infoAlert.style.display = 'none';
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                errorAlert.style.display = 'none';
                infoAlert.style.display = 'flex';
            }, 5000);
        }

        function showSuccess(message) {
            const errorAlert = document.getElementById('errorAlert');
            const infoAlert = document.getElementById('infoAlert');
            
            // Create success alert
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success';
            successAlert.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
            `;
            
            // Replace existing alerts
            errorAlert.style.display = 'none';
            infoAlert.style.display = 'none';
            infoAlert.parentNode.insertBefore(successAlert, infoAlert);
        }

        // Focus management
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('newPassword').focus();
        });
    </script>
</body>
</html>