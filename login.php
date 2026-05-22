<?php
require_once 'config.php';
require_once 'db_connect.php';

if (isLoggedIn()) {
    redirectToDashboard();
}

// Handle user login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $userType = $_POST['userType'] ?? '';
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!isDatabaseAvailable()) {
        $error = "Database not available. Please run init_db.php.";
    } else {
        $missingTables = function_exists('getMissingTables') ? getMissingTables(['users']) : [];
        if (!empty($missingTables)) {
            $error = "Required tables (" . implode(', ', $missingTables) . ") are missing. Please run init_db.php or import vishwacollab_sample_data.sql via phpMyAdmin.";
        } else {
        // Check user in database
        $stmt = $conn->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (!empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
                // Optional: enforce selected userType to match account role
                if (!empty($userType) && $userType !== $user['role']) {
                    $error = "Selected role does not match your account role (" . htmlspecialchars($user['role']) . ").";
                } else {
                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];
                    $success = "Login successful! Taking you to your profile...";
                    echo "<script>setTimeout(function(){ window.location.href = 'profile.php'; }, 1000);</script>";
                }
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Account not found.";
        }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - VishwaCollab</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header Styles */
        header {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
        }

        .logo i {
            margin-right: 10px;
            font-size: 2rem;
        }

        .nav-links {
            display: flex;
            list-style: none;
        }

        .nav-links li {
            margin-left: 1.5rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s;
        }

        .nav-links a:hover {
            opacity: 0.8;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .user-avatar:hover {
            transform: scale(1.05);
        }

        .user-info {
            color: white;
            line-height: 1.2;
        }

        .user-role {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .user-dropdown {
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            background: white;
            border-radius: 8px;
            min-width: 200px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            display: none;
            z-index: 10;
        }

        .user-dropdown.show {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.75rem 1rem;
            color: #202124;
            text-decoration: none;
            transition: background 0.2s;
        }

        .dropdown-item:hover {
            background: #f5f7fa;
            color: #1a73e8;
        }

        .dropdown-divider {
            height: 1px;
            background: #e0e0e0;
            margin: 0.25rem 0;
        }

        .session-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #f8f9ff;
            border: 1px solid #d2e3fc;
            padding: 0.85rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.25rem;
            font-size: 0.95rem;
        }

        .session-indicator .indicator-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #c62828;
            animation: pulse 2s infinite;
        }

        .session-indicator.active .indicator-dot {
            background: #2e7d32;
        }

        @keyframes pulse {
            0% { transform: scale(0.9); opacity: 0.7; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.7; }
        }

        .role-helper {
            margin: 0.5rem 0 1.25rem;
            padding: 0.85rem 1rem;
            background: #f0f7ff;
            border: 1px solid #90caf9;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #0d47a1;
        }

        .password-group {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            cursor: pointer;
            color: #666;
            font-size: 1rem;
        }

        .password-toggle:hover {
            color: #1a73e8;
        }

        .login-button {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .button-loader {
            width: 18px;
            height: 18px;
            border: 3px solid rgba(255,255,255,0.5);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: none;
        }

        .button-loader.show {
            display: inline-block;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Login Container */
        .login-container {
            max-width: 500px;
            margin: 3rem auto;
            padding: 0 1rem;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            transition: transform 0.3s;
            width: 100%;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .login-card h2 {
            text-align: center;
            margin-bottom: 0.5rem;
            color: #1a73e8;
            font-size: 1.8rem;
        }

        .login-card > p {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #666;
        }

        /* Form Styles */
        .user-type-selector {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 5px;
            background: #f9f9f9;
        }

        .user-type-selector label {
            flex: 1;
            text-align: center;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .user-type-selector input[type="radio"] {
            display: none;
        }

        .user-type-selector input[type="radio"]:checked + span {
            background: #1a73e8;
            color: white;
        }

        .user-type-selector span {
            display: block;
            padding: 8px 12px;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #444;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border 0.3s;
        }

        .form-group input:focus {
            border-color: #1a73e8;
            outline: none;
            box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2);
        }

        .login-button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 115, 232, 0.3);
        }

        .signup-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }

        .signup-link a {
            color: #1a73e8;
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        /* Messages */
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #c62828;
        }

        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #2e7d32;
        }

        /* Footer */
        footer {
            background: #1a237e;
            color: white;
            text-align: center;
            padding: 2rem 1rem;
            margin-top: auto;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            list-style: none;
            margin: 1rem 0;
        }

        .footer-links li {
            margin: 0 1rem;
        }

        .footer-links a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .footer-links a:hover {
            opacity: 0.8;
        }

        .copyright {
            margin-top: 1rem;
            opacity: 0.8;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                text-align: center;
            }

            .nav-links {
                margin-top: 1rem;
                flex-wrap: wrap;
                justify-content: center;
            }

            .nav-links li {
                margin: 0.5rem;
            }

            .user-type-selector {
                flex-direction: column;
            }

            .user-type-selector label {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body data-session-keepalive="90000">
    <!-- Header with Navigation -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <i class="fas fa-handshake"></i>
                VishwaCollab
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#jobs-section">Jobs</a></li>
                <li><a href="index.php#companies-section">Companies</a></li>
                <li><a href="index.php#students-section">Students</a></li>
                <li><a href="index.php#tpo-section">TPO</a></li>
                <li><a href="login.php" style="background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 4px;">Login</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            </ul>
            <?php if (isLoggedIn()): ?>
                <div class="user-profile" id="userProfile">
                    <div class="user-avatar" onclick="toggleUserDropdown()">
                        <?php echo strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($_SESSION['role'] ?? 'user'); ?></div>
                    </div>
                    <div class="user-dropdown" id="userDropdown">
                        <a href="<?php echo getDashboardPath(); ?>" class="dropdown-item">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <a href="settings.php" class="dropdown-item">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-outline">Login</a>
                    <a href="signup.php" class="btn btn-primary">Sign Up</a>
                </div>
            <?php endif; ?>
        </nav>
    </header>

    <!-- Login Form -->
    <div class="login-container">
        <div class="login-card">
            <h2>Welcome Back</h2>
            <p>Sign in to your VishwaCollab account</p>

            <div class="session-indicator <?php echo isLoggedIn() ? 'active' : ''; ?>"
                 id="sessionIndicator"
                 data-session-indicator
                 data-auto-redirect="<?php echo isLoggedIn() ? 'false' : 'true'; ?>"
                 data-idle-text="Session idle. Enter your details to activate."
                 data-error-text="Unable to verify session. Please refresh.">
                <span class="indicator-dot"></span>
                <span id="sessionIndicatorText" data-session-text>
                    <?php echo isLoggedIn() ? 'Active session detected. Redirecting you shortly.' : 'Session idle. Enter your details to activate.'; ?>
                </span>
            </div>

            <!-- PHP Error/Success Messages -->
            <?php 
            // Initialize variables to prevent warnings
            $error = isset($error) ? $error : '';
            $success = isset($success) ? $success : '';
            
            if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm" novalidate>
                <div class="user-type-selector">
                    <label>
                        <input type="radio" name="userType" value="student" checked>
                        <span><i class="fas fa-user-graduate"></i> Student</span>
                    </label>
                    <label>
                        <input type="radio" name="userType" value="company">
                        <span><i class="fas fa-building"></i> Company</span>
                    </label>
                    <label>
                        <input type="radio" name="userType" value="tpo">
                        <span><i class="fas fa-user-tie"></i> TPO</span>
                    </label>
                </div>

                <div class="role-helper" id="roleHelper">
                    Students can access personalized jobs, resumes, and interview tracking.
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="emailInput" required placeholder="Enter your email">
                </div>

                <div class="form-group password-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="passwordInput" required placeholder="Enter your password">
                    <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>

                <button type="submit" class="login-button" id="loginButton">
                    <span>Sign In</span>
                    <span class="button-loader" id="loginLoader"></span>
                </button>
            </form>
            
            <p class="signup-link">Don't have an account? <a href="signup.php">Sign up here</a></p>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="logo">
                <i class="fas fa-handshake"></i>
                VishwaCollab
            </div>
            <ul class="footer-links">
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="privacy.php">Privacy Policy</a></li>
                <li><a href="terms.php">Terms of Service</a></li>
            </ul>
            <p class="copyright">&copy; 2023 VishwaCollab. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/session-helper.js" defer></script>
    <script>
        const dropdown = document.getElementById('userDropdown');
        const userProfile = document.getElementById('userProfile');

        function toggleUserDropdown() {
            if (dropdown) {
                dropdown.classList.toggle('show');
            }
        }

        document.addEventListener('click', (event) => {
            if (userProfile && dropdown && !userProfile.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        const roleDescriptions = {
            student: 'Students can explore job feeds, submit applications, and manage resumes.',
            company: 'Companies can post openings, manage applicants, and schedule interviews.',
            tpo: 'TPOs can evaluate student data, coordinate drives, and view placement analytics.'
        };

        const roleHelper = document.getElementById('roleHelper');
        document.querySelectorAll('input[name="userType"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                const role = e.target.value;
                roleHelper.textContent = roleDescriptions[role] || '';
            });
        });

        const togglePasswordBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('passwordInput');
        if (togglePasswordBtn && passwordInput) {
            togglePasswordBtn.addEventListener('click', () => {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                togglePasswordBtn.innerHTML = isPassword ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
            });
        }

        const sessionIndicator = document.getElementById('sessionIndicator');
        const sessionIndicatorText = document.getElementById('sessionIndicatorText');
        const emailInput = document.getElementById('emailInput');

        if (emailInput && sessionIndicatorText) {
            emailInput.addEventListener('input', () => {
                if (emailInput.value.trim().length > 3) {
                    sessionIndicatorText.textContent = 'Great! We will link your secure session as soon as you sign in.';
                } else {
                    sessionIndicatorText.textContent = 'Session idle. Enter your details to activate.';
                }
            });
        }

        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');
        const loginLoader = document.getElementById('loginLoader');

        if (loginForm) {
            loginForm.addEventListener('submit', () => {
                sessionIndicator.classList.add('active');
                sessionIndicatorText.textContent = 'Securing your session...';
                loginButton.setAttribute('disabled', 'disabled');
                loginLoader.classList.add('show');
            });
        }
    </script>
</body>
</html>