<?php
require_once 'config.php';
require_once 'db_connect.php';

if (isLoggedIn()) {
    redirectToDashboard();
}

// Handle user registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $userType = $_POST['userType'];
    $name = ucfirst(explode('@', $email)[0]);
    
    // Basic validation
    if ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!in_array($userType, ['student','company','tpo'], true)) {
        $error = "Invalid user type.";
    } elseif (!isDatabaseAvailable()) {
        $error = "Database not available. Please run init_db.php.";
    } else {
        $requiredTables = ['users', 'students', 'companies'];
        $missingTables = function_exists('getMissingTables') ? getMissingTables($requiredTables) : [];

        if (!empty($missingTables)) {
            $error = "Required tables (" . implode(', ', $missingTables) . ") are missing. Please run init_db.php or import vishwacollab_sample_data.sql via phpMyAdmin.";
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = "An account with this email already exists.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $passwordHash, $userType);
                if ($stmt->execute()) {
                    $userId = $stmt->insert_id;
                    // Seed role-specific row for convenience
                    if ($userType === 'student') {
                        $seed = $conn->prepare("INSERT INTO students (user_id, name, email, course, skills, cgpa) VALUES (?, ?, ?, '', '', 0.0)");
                        $seed->bind_param("iss", $userId, $name, $email);
                        $seed->execute();
                    } elseif ($userType === 'company') {
                        $seed = $conn->prepare("INSERT INTO companies (user_id, name, email, industry, location) VALUES (?, ?, ?, '', '')");
                        $seed->bind_param("iss", $userId, $name, $email);
                        $seed->execute();
                    }
                    $_SESSION['user_id'] = (int)$userId;
                    $_SESSION['email'] = $email;
                    $_SESSION['name'] = $name;
                $_SESSION['role'] = $userType;
                $success = "Account created successfully! Let's finish your profile...";
                echo "<script>setTimeout(function(){ window.location.href = 'profile.php?welcome=1'; }, 1000);</script>";
                } else {
                    $error = "Failed to create account. Please try again.";
                }
            }
        }
    }
}

// Resume upload handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['resume'])) {
    $uploadDir = 'uploads/resumes/';
    
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $file = $_FILES['resume'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    // Validate file
    if ($fileError === 0) {
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'doc', 'docx'];
        
        if (in_array($fileExt, $allowedExts)) {
            if ($fileSize < 5 * 1024 * 1024) { // 5MB limit
                $newFileName = uniqid() . '_' . $fileName;
                $fileDestination = $uploadDir . $newFileName;
                
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    $uploadSuccess = "Resume uploaded successfully!";
                } else {
                    $uploadError = "Failed to upload resume. Please try again.";
                }
            } else {
                $uploadError = "File size must be less than 5MB.";
            }
        } else {
            $uploadError = "Invalid file type. Please upload PDF, DOC, or DOCX files only.";
        }
    } else {
        $uploadError = "Error uploading file. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - VishwaCollab</title>
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

        .password-hint {
            font-size: 0.85rem;
            color: #555;
            margin-top: 0.25rem;
        }

        .password-hint span {
            font-weight: 600;
        }

        .signup-button {
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

        .signup-progress {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .progress-pill {
            flex: 1;
            text-align: center;
            padding: 0.75rem;
            border-radius: 999px;
            border: 1px solid #d2e3fc;
            color: #0d47a1;
            font-size: 0.9rem;
            background: #f8f9ff;
        }

        .progress-pill.active {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            color: white;
            border-color: transparent;
            box-shadow: 0 8px 18px rgba(26, 115, 232, 0.25);
        }

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 1rem;
            flex: 1;
            display: flex;
            gap: 2rem;
            align-items: flex-start;
        }

        /* Sign Up Container */
        .signup-container {
            flex: 1;
            max-width: 500px;
        }

        /* Resume Upload Container */
        .resume-container {
            flex: 1;
            max-width: 500px;
        }

        .signup-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            transition: transform 0.3s;
        }

        .signup-card:hover {
            transform: translateY(-5px);
        }

        .signup-card h2 {
            text-align: center;
            margin-bottom: 0.5rem;
            color: #1a73e8;
            font-size: 1.8rem;
        }

        .signup-card > p {
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

        .signup-button {
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

        .signup-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 115, 232, 0.3);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }

        .login-link a {
            color: #1a73e8;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* Resume Upload Styles */
        .resume-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            transition: transform 0.3s;
        }

        .resume-card:hover {
            transform: translateY(-5px);
        }

        .resume-card h2 {
            text-align: center;
            margin-bottom: 0.5rem;
            color: #1a73e8;
            font-size: 1.8rem;
        }

        .resume-card > p {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #666;
        }

        .file-upload-area {
            border: 2px dashed #1a73e8;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            background: #f8f9ff;
            transition: all 0.3s;
            cursor: pointer;
            margin-bottom: 1.5rem;
        }

        .file-upload-area:hover {
            background: #e8f0fe;
            border-color: #0d47a1;
        }

        .file-upload-area.dragover {
            background: #e8f0fe;
            border-color: #0d47a1;
            transform: scale(1.02);
        }

        .file-upload-icon {
            font-size: 3rem;
            color: #1a73e8;
            margin-bottom: 1rem;
        }

        .file-upload-text {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .file-upload-subtext {
            color: #666;
            font-size: 0.9rem;
        }

        .file-input {
            display: none;
        }

        .upload-button {
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
            margin-bottom: 1rem;
        }

        .upload-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 115, 232, 0.3);
        }

        .upload-button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .file-info {
            background: #f0f7ff;
            border: 1px solid #1a73e8;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: none;
        }

        .file-info.show {
            display: block;
        }

        .file-name {
            font-weight: 600;
            color: #1a73e8;
            margin-bottom: 0.5rem;
        }

        .file-size {
            color: #666;
            font-size: 0.9rem;
        }

        .remove-file {
            background: #ff4444;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }

        .remove-file:hover {
            background: #cc0000;
        }

        /* Profile Display Styles */
        .profile-display {
            display: none;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            margin-top: 2rem;
            transition: transform 0.3s;
        }

        .profile-display.show {
            display: block;
        }

        .profile-display:hover {
            transform: translateY(-5px);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e0e0e0;
        }

        .profile-header h2 {
            color: #1a73e8;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .profile-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-section {
            background: #f8f9ff;
            border-radius: 8px;
            padding: 1.5rem;
            border-left: 4px solid #1a73e8;
        }

        .info-section h3 {
            color: #1a73e8;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .info-section h3 i {
            margin-right: 0.5rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #444;
            flex: 1;
        }

        .info-value {
            color: #666;
            flex: 2;
            text-align: right;
        }

        .user-type-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .user-type-badge.student {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .user-type-badge.company {
            background: #e3f2fd;
            color: #1976d2;
        }

        .user-type-badge.tpo {
            background: #fff3e0;
            color: #f57c00;
        }

        .resume-info {
            background: #f0f7ff;
            border: 1px solid #1a73e8;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .resume-info h4 {
            color: #1a73e8;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .resume-info h4 i {
            margin-right: 0.5rem;
        }

        .profile-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .profile-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .profile-btn.primary {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            color: white;
        }

        .profile-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 115, 232, 0.3);
        }

        .profile-btn.secondary {
            background: #f5f5f5;
            color: #666;
            border: 1px solid #ddd;
        }

        .profile-btn.secondary:hover {
            background: #e0e0e0;
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

            .main-container {
                flex-direction: column;
                gap: 1rem;
            }

            .signup-container,
            .resume-container {
                max-width: 100%;
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
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php" style="background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 4px;">Sign Up</a></li>
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

    <!-- Main Container with Sign Up and Resume Upload -->
    <div class="main-container">
        <!-- Sign Up Form -->
        <div class="signup-container">
            <div class="signup-card">
                <h2>Create Account</h2>
                <p>Join VishwaCollab as Student, Company, or TPO</p>

                <div class="session-indicator"
                     id="signupSessionIndicator"
                     data-session-indicator
                     data-auto-redirect="<?php echo isLoggedIn() ? 'false' : 'true'; ?>"
                     data-idle-text="Session idle. Complete the steps below to activate."
                     data-error-text="Unable to verify session. Please refresh.">
                    <span class="indicator-dot"></span>
                    <span id="signupSessionText" data-session-text>
                        <?php echo isLoggedIn() ? 'Active session detected. Redirecting you shortly.' : 'Session idle. Complete the steps below to activate.'; ?>
                    </span>
                </div>

                <div class="signup-progress">
                    <div class="progress-pill active" id="stepAccount">1. Account</div>
                    <div class="progress-pill" id="stepSecurity">2. Security</div>
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

                <form method="POST" action="" id="signupForm" novalidate>
                <div class="user-type-selector">
                    <label>
                        <input type="radio" name="userType" value="student" <?php echo (!isset($_GET['type']) || $_GET['type'] === 'student') ? 'checked' : ''; ?>>
                        <span><i class="fas fa-user-graduate"></i> Student</span>
                    </label>
                    <label>
                        <input type="radio" name="userType" value="company" <?php echo (isset($_GET['type']) && $_GET['type'] === 'company') ? 'checked' : ''; ?>>
                        <span><i class="fas fa-building"></i> Company</span>
                    </label>
                    <label>
                        <input type="radio" name="userType" value="tpo" <?php echo (isset($_GET['type']) && $_GET['type'] === 'tpo') ? 'checked' : ''; ?>>
                        <span><i class="fas fa-user-tie"></i> TPO</span>
                    </label>
                </div>

                    <div class="role-helper" id="signupRoleHelper">
                        Students can explore curated jobs, submit applications, and track interviews.
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="signupEmail" required placeholder="Enter your email">
                    </div>

                    <div class="form-group password-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="signupPassword" required placeholder="Enter password">
                        <button type="button" class="password-toggle" id="toggleSignupPassword" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <div class="password-hint" id="passwordStrength">
                        Strength: <span>Waiting for input</span>
                    </div>

                    <div class="form-group password-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" name="confirm_password" id="signupConfirmPassword" required placeholder="Confirm password">
                        <button type="button" class="password-toggle" id="toggleConfirmPassword" aria-label="Toggle confirm password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <button type="submit" class="signup-button" id="signupButton">
                        <span>Create Account</span>
                        <span class="button-loader" id="signupLoader"></span>
                    </button>
                </form>
                
                <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>

        <!-- Resume Upload Block -->
        <div class="resume-container">
            <div class="resume-card">
                <h2>Upload Resume</h2>
                <p>Upload your resume to get started with job applications</p>

                <!-- Resume Upload Messages -->
                <?php 
                $uploadError = isset($uploadError) ? $uploadError : '';
                $uploadSuccess = isset($uploadSuccess) ? $uploadSuccess : '';
                
                if ($uploadError): ?>
                    <div class="error-message"><?php echo $uploadError; ?></div>
                <?php endif; ?>

                <?php if ($uploadSuccess): ?>
                    <div class="success-message"><?php echo $uploadSuccess; ?></div>
                <?php endif; ?>

                <form id="resumeForm" method="POST" action="" enctype="multipart/form-data">
                    <div class="file-upload-area" id="fileUploadArea">
                        <div class="file-upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="file-upload-text">Click to upload or drag and drop</div>
                        <div class="file-upload-subtext">PDF, DOC, DOCX (Max 5MB)</div>
                        <input type="file" name="resume" id="resumeFile" class="file-input" accept=".pdf,.doc,.docx" required>
                    </div>

                    <div class="file-info" id="fileInfo">
                        <div class="file-name" id="fileName"></div>
                        <div class="file-size" id="fileSize"></div>
                        <button type="button" class="remove-file" id="removeFile">Remove File</button>
                    </div>

                    <button type="submit" class="upload-button" id="uploadButton" disabled>Upload Resume</button>
                </form>

                <div class="login-link">
                    <small>Supported formats: PDF, DOC, DOCX (Max 5MB)</small>
                </div>
            </div>
        </div>

        <!-- Profile Display Section -->
        <div class="profile-display" id="profileDisplay">
            <div class="profile-header">
                <h2><i class="fas fa-user-check"></i> Profile Created Successfully!</h2>
                <p>Your account has been created. Here's your profile information:</p>
            </div>

            <div class="profile-info">
                <div class="info-section">
                    <h3><i class="fas fa-user"></i> Account Information</h3>
                    <div class="info-item">
                        <span class="info-label">User Type:</span>
                        <span class="info-value">
                            <span class="user-type-badge" id="profileUserType">Student</span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value" id="profileEmail">user@example.com</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Account Status:</span>
                        <span class="info-value" style="color: #2e7d32; font-weight: 600;">✓ Active</span>
                    </div>
                </div>

                <div class="info-section">
                    <h3><i class="fas fa-calendar-alt"></i> Account Details</h3>
                    <div class="info-item">
                        <span class="info-label">Created:</span>
                        <span class="info-value" id="profileCreatedDate">Just now</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Profile ID:</span>
                        <span class="info-value" id="profileId">#VC-001</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Next Steps:</span>
                        <span class="info-value" style="color: #1a73e8; font-weight: 600;">Complete Profile</span>
                    </div>
                </div>
            </div>

            <div class="resume-info" id="resumeInfo" style="display: none;">
                <h4><i class="fas fa-file-alt"></i> Resume Information</h4>
                <div class="info-item">
                    <span class="info-label">File Name:</span>
                    <span class="info-value" id="resumeFileName">resume.pdf</span>
                </div>
                <div class="info-item">
                    <span class="info-label">File Size:</span>
                    <span class="info-value" id="resumeFileSize">2.5 MB</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status:</span>
                    <span class="info-value" style="color: #2e7d32; font-weight: 600;">✓ Uploaded</span>
                </div>
            </div>

            <div class="profile-actions">
                <a href="login.php" class="profile-btn primary">
                    <i class="fas fa-sign-in-alt"></i> Go to Login
                </a>
                <button class="profile-btn secondary" onclick="hideProfile()">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
            </div>
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
        // File upload functionality
        const fileUploadArea = document.getElementById('fileUploadArea');
        const resumeFile = document.getElementById('resumeFile');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const uploadButton = document.getElementById('uploadButton');
        const removeFileBtn = document.getElementById('removeFile');

        // Click to upload
        fileUploadArea.addEventListener('click', () => {
            resumeFile.click();
        });

        // Drag and drop functionality
        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });

        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('dragover');
        });

        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                resumeFile.files = files;
                handleFileSelect();
            }
        });

        // File selection handler
        resumeFile.addEventListener('change', handleFileSelect);

        function handleFileSelect() {
            const file = resumeFile.files[0];
            if (file) {
                // Validate file type
                const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid file (PDF, DOC, or DOCX)');
                    resumeFile.value = '';
                    return;
                }

                // Validate file size (5MB)
                const maxSize = 5 * 1024 * 1024; // 5MB in bytes
                if (file.size > maxSize) {
                    alert('File size must be less than 5MB');
                    resumeFile.value = '';
                    return;
                }

                // Show file info
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                fileInfo.classList.add('show');
                uploadButton.disabled = false;
            }
        }

        // Remove file
        removeFileBtn.addEventListener('click', () => {
            resumeFile.value = '';
            fileInfo.classList.remove('show');
            uploadButton.disabled = true;
        });

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Utility helpers for resume preview
        function updateResumeInfo(file) {
            document.getElementById('resumeFileName').textContent = file.name;
            document.getElementById('resumeFileSize').textContent = formatFileSize(file.size);
        }

        // Called from the "Edit Profile" button in the success panel.
        function hideProfile() {
            const profile = document.getElementById('profileDisplay');
            if (profile) profile.style.display = 'none';

            const signupContainer = document.querySelector('.signup-container');
            if (signupContainer) signupContainer.style.display = '';

            const resumeContainer = document.querySelector('.resume-container');
            if (resumeContainer) resumeContainer.style.display = '';

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>