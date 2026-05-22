<?php
require_once 'config.php';
requireAuth();
require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (isDatabaseAvailable()) {
        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user && password_verify($current_password, $user['password_hash'])) {
            $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
            $update_stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_hash, $user_id);
            if ($update_stmt->execute()) {
                $success = "Password changed successfully!";
            } else {
                $error = "Failed to update password.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}

// Handle email change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_email'])) {
    $new_email = trim($_POST['new_email'] ?? '');
    
    if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (isDatabaseAvailable()) {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_stmt->bind_param("si", $new_email, $user_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "This email is already in use.";
        } else {
            $update_stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_email, $user_id);
            if ($update_stmt->execute()) {
                $_SESSION['email'] = $new_email;
                $success = "Email updated successfully!";
            } else {
                $error = "Failed to update email.";
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
    <title>Settings - VishwaCollab</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
        .settings-container { max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        .settings-card { background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 2rem; margin-bottom: 2rem; }
        .settings-card h2 { color: #202124; margin-bottom: 1.5rem; border-bottom: 2px solid #e0e0e0; padding-bottom: 0.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #202124; }
        .form-control { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; }
        .form-control:focus { border-color: #1a73e8; outline: none; box-shadow: 0 0 0 3px rgba(26,115,232,0.2); }
        .btn { padding: 0.7rem 1.8rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; font-size: 1rem; }
        .btn-primary { background: #1a73e8; color: white; }
        .btn-primary:hover { background: #0d47a1; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #2e7d32; }
        .alert-error { background: #ffebee; color: #c62828; border-left: 4px solid #c62828; }
    </style>
</head>
<body>
    <header style="background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15); padding: 1rem 5%;">
        <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1400px; margin: 0 auto;">
            <a href="index.php" style="font-size: 2rem; color: #202124; text-decoration: none; font-weight: 700;"><span style="color: #1a73e8;">Vishwa</span>Collab</a>
            <nav>
                <a href="<?php echo getUserRole(); ?>-dashboard.php" class="btn btn-primary">Back to Dashboard</a>
            </nav>
        </div>
    </header>

    <div class="settings-container">
        <h1 style="color: #202124; margin-bottom: 2rem;">Account Settings</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="settings-card">
            <h2><i class="fas fa-key"></i> Change Password</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                </div>
                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
            </form>
        </div>

        <div class="settings-card">
            <h2><i class="fas fa-envelope"></i> Change Email</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Current Email</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" disabled>
                </div>
                <div class="form-group">
                    <label>New Email</label>
                    <input type="email" name="new_email" class="form-control" required>
                </div>
                <button type="submit" name="change_email" class="btn btn-primary">Change Email</button>
            </form>
        </div>

        <div class="settings-card">
            <h2><i class="fas fa-user-cog"></i> Account Information</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
            <p><strong>Role:</strong> <?php echo ucfirst(getUserRole()); ?></p>
            <p><strong>User ID:</strong> <?php echo htmlspecialchars($_SESSION['user_id'] ?? ''); ?></p>
        </div>
    </div>
</body>
</html>


