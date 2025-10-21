<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Reset Password Form" />
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

        .forgot-card {
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
            align-items: flex-start;
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

        .alert-success {
            background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
            color: var(--success-color);
        }

        .alert-info {
            background: linear-gradient(135deg, #bee3f8 0%, #90cdf4 100%);
            color: var(--info-color);
        }

        .info-text {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: rgba(248, 250, 252, 0.8);
            border-radius: 8px;
            border-left: 4px solid var(--info-color);
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

        .form-control.is-valid {
            border-color: var(--success-color);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2348bb78' d='m2.3 6.73.94-.94 1.96 1.96 3.13-3.13.94.94-4.07 4.07z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
        }

        .form-control.is-invalid {
            border-color: var(--error-color);
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

        .btn-outline-success {
            background: transparent;
            color: var(--success-color);
            border: 1px solid var(--success-color);
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
        }

        .btn-outline-success:hover {
            background: var(--success-color);
            color: white;
        }

        .success-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .card-footer {
            background: rgba(248, 250, 252, 0.8);
            padding: 1.5rem;
            text-align: center;
            border-top: 1px solid rgba(226, 232, 240, 0.5);
        }

        .footer-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: var(--accent-color);
        }

        /* Loading Animation */
        .btn-loading {
            position: relative;
            pointer-events: none;
            color: transparent;
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
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Email validation indicator */
        .email-status {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            display: none;
        }

        .email-status.valid {
            color: var(--success-color);
            display: block;
        }

        .email-status.invalid {
            color: var(--error-color);
            display: block;
        }

        /* Reset link highlight */
        .reset-link-box {
            background: rgba(72, 187, 120, 0.1);
            border: 1px solid rgba(72, 187, 120, 0.3);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .reset-link-box p {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-dark);
        }

        /* Responsive Design */
        @media (max-width: 576px) {
            .main-container {
                padding: 10px;
            }
            
            .forgot-card {
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

            .success-actions {
                flex-direction: column;
            }
        }

        /* Success state animation */
        .success-icon {
            animation: successPulse 0.6s ease-out;
        }

        @keyframes successPulse {
            0% { transform: scale(0.8); opacity: 0; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="forgot-card">
            <div class="card-header">
                <div class="header-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h1 class="header-title">Lupa Password?</h1>
                <p class="header-subtitle">Jangan khawatir, kami akan membantu Anda</p>
            </div>
            
            <div class="card-body">
                <!-- Alert Error (Demo) -->
                <div class="alert alert-danger" style="display: none;" id="errorAlert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Oops!</strong><br>
                        <span id="errorMessage"></span>
                    </div>
                </div>
                
                <!-- Alert Success (Demo) -->
                <div class="alert alert-success" style="display: none;" id="successAlert">
                    <i class="fas fa-check-circle success-icon"></i>
                    <div>
                        <strong>Berhasil!</strong><br>
                        <span id="successMessage">Link reset password telah dikirim ke email Anda. Silakan cek email untuk melanjutkan.</span>
                        <div class="reset-link-box" id="resetLinkBox" style="display: none;">
                            <p><strong>Link Reset Password (untuk testing):</strong></p>
                            <a href="#" class="btn btn-outline-success" id="resetLink" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Buka Link Reset
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Form Section -->
                <div id="formSection">
                    <div class="info-text">
                        <i class="fas fa-info-circle"></i>
                        Masukkan alamat email Anda dan kami akan mengirimkan link untuk mereset password.
                    </div>
                    
                    <form id="forgotPasswordForm">
                        <div class="form-group">
                            <label class="form-label" for="inputEmail">
                                <i class="fas fa-envelope"></i> Alamat Email
                            </label>
                            <input class="form-control" name="inputEmail" id="inputEmail" type="email"
                                placeholder="masukkan@email.com" required />
                            <div class="email-status" id="emailStatus">
                                <i class="fas fa-check-circle"></i> Format email valid
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                Kembali ke Login
                            </a>
                            <button class="btn btn-primary" type="submit" name="resetPasswordForm" id="submitBtn">
                                <i class="fas fa-paper-plane"></i>
                                Kirim Link Reset
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Success Actions -->
                <div id="successActions" style="display: none;">
                    <div class="success-actions">
                        <a href="#" class="btn btn-secondary" onclick="resetForm()">
                            <i class="fas fa-redo"></i> Kirim Lagi
                        </a>
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-home"></i> Kembali ke Login
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card-footer">
                <a href="#" class="footer-link">
                    Belum punya akun? Daftar sekarang!
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Email validation
        function validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Real-time email validation
        document.getElementById('inputEmail').addEventListener('input', function() {
            const email = this.value.trim();
            const emailStatus = document.getElementById('emailStatus');
            
            if (email.length === 0) {
                this.classList.remove('is-valid', 'is-invalid');
                emailStatus.style.display = 'none';
                return;
            }
            
            if (validateEmail(email)) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                emailStatus.className = 'email-status valid';
                emailStatus.innerHTML = '<i class="fas fa-check-circle"></i> Format email valid';
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
                emailStatus.className = 'email-status invalid';
                emailStatus.innerHTML = '<i class="fas fa-times-circle"></i> Format email tidak valid';
            }
        });

        // Form submission
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('inputEmail').value.trim();
            const submitBtn = document.getElementById('submitBtn');
            
            // Validate email
            if (!email) {
                showError('Email tidak boleh kosong.');
                return;
            }
            
            if (!validateEmail(email)) {
                showError('Format email tidak valid.');
                return;
            }
            
            // Show loading state
            submitBtn.classList.add('btn-loading');
            submitBtn.textContent = 'Mengirim...';
            
            // Simulate API call
            setTimeout(() => {
                // Reset button state
                submitBtn.classList.remove('btn-loading');
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Link Reset';
                
                // Show success with demo reset link
                const demoResetLink = `reset_password.php?token=demo_token_${Date.now()}`;
                showSuccess(email, demoResetLink);
            }, 2000);
        });

        // Show error message
        function showError(message) {
            const errorAlert = document.getElementById('errorAlert');
            const errorMessage = document.getElementById('errorMessage');
            const successAlert = document.getElementById('successAlert');
            
            errorMessage.textContent = message;
            errorAlert.style.display = 'flex';
            successAlert.style.display = 'none';
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                errorAlert.style.display = 'none';
            }, 5000);
        }

        // Show success message
        function showSuccess(email, resetLink = null) {
            const formSection = document.getElementById('formSection');
            const successAlert = document.getElementById('successAlert');
            const successActions = document.getElementById('successActions');
            const errorAlert = document.getElementById('errorAlert');
            const resetLinkBox = document.getElementById('resetLinkBox');
            const resetLinkElement = document.getElementById('resetLink');
            
            // Hide form and error
            formSection.style.display = 'none';
            errorAlert.style.display = 'none';
            
            // Show success
            successAlert.style.display = 'flex';
            successActions.style.display = 'block';
            
            // Show demo reset link if provided
            if (resetLink) {
                resetLinkBox.style.display = 'block';
                resetLinkElement.href = resetLink;
            }
        }

        // Reset form function
        function resetForm() {
            const formSection = document.getElementById('formSection');
            const successAlert = document.getElementById('successAlert');
            const successActions = document.getElementById('successActions');
            const emailInput = document.getElementById('inputEmail');
            const emailStatus = document.getElementById('emailStatus');
            
            // Reset form
            emailInput.value = '';
            emailInput.classList.remove('is-valid', 'is-invalid');
            emailStatus.style.display = 'none';
            
            // Show form, hide success
            formSection.style.display = 'block';
            successAlert.style.display = 'none';
            successActions.style.display = 'none';
            
            // Focus email input
            emailInput.focus();
        }

        // Focus management
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('inputEmail').focus();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // ESC key to reset form when in success state
            if (e.key === 'Escape' && document.getElementById('successAlert').style.display === 'flex') {
                resetForm();
            }
        });
    </script>
</body>
</html>