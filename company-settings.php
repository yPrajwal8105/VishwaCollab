<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole() !== 'company') {
    header("Location: login.php");
    exit();
}
require_once 'db_connect.php';

$company_id = (int)$_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password']) && isDatabaseAvailable()) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request token.';
    } else {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (strlen($new) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $error = 'Password confirmation does not match.';
        } else {
            $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ? AND role = 'company' LIMIT 1");
            $stmt->bind_param("i", $company_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$user || !password_verify($current, $user['password_hash'])) {
                $error = 'Current password is incorrect.';
            } else {
                $new_hash = password_hash($new, PASSWORD_BCRYPT);
                $update_stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ? AND role = 'company'");
                $update_stmt->bind_param("si", $new_hash, $company_id);
                if ($update_stmt->execute()) {
                    $success = 'Password updated successfully.';
                    logActivity($conn, $company_id, 'company', 'password_changed', 'user', $company_id, 'Password changed from settings');
                    createCompanyNotification($conn, $company_id, 'Security Update', 'Your account password was changed successfully.', 'success');
                } else {
                    $error = 'Unable to update password.';
                }
                $update_stmt->close();
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
    <title>Company Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--primary:#0f766e;--shadow:0 4px 12px rgba(0,0,0,0.15);}
        *{box-sizing:border-box;font-family:'Segoe UI',sans-serif;}body{margin:0;background:#f5f7fa;}
        .dashboard-container{display:flex;min-height:100vh;}
        .sidebar{width:250px;background:#fff;box-shadow:var(--shadow);position:fixed;height:100vh;overflow-y:auto;}
        .sidebar-header{padding:2rem 1.5rem;background:var(--primary);color:#fff;}
        .sidebar-menu a{display:flex;align-items:center;padding:1rem 1.5rem;color:#202124;text-decoration:none;border-left:4px solid transparent;}
        .sidebar-menu a:hover,.sidebar-menu a.active{background:#fce4ec;border-left-color:var(--primary);color:var(--primary);}
        .main-content{flex:1;margin-left:250px;padding:2rem;}
        .card{background:#fff;border-radius:10px;box-shadow:var(--shadow);padding:1.3rem;}
        .form-group{margin-bottom:1rem;}
        label{display:block;margin-bottom:0.4rem;font-weight:600;}
        input{width:100%;padding:0.7rem;border:1px solid #ddd;border-radius:6px;}
        .btn{border:none;background:var(--primary);color:#fff;padding:0.65rem 1rem;border-radius:6px;cursor:pointer;}
        .alert{margin-bottom:1rem;padding:0.8rem 1rem;border-radius:8px;}
        .alert-success{background:#e6f4ea;color:#137333;}.alert-error{background:#fce8e6;color:#c5221f;}
        @media (max-width:900px){.sidebar{width:100%;position:relative;height:auto;}.main-content{margin-left:0;padding:1rem;}}
    </style>
</head>
<body>
<div class="dashboard-container">
    <div class="sidebar">
        <div class="sidebar-header"><h2>Company Dashboard</h2></div>
        <div class="sidebar-menu">
            <a href="company-dashboard.php"><i class="fas fa-home"></i>&nbsp;Dashboard</a>
            <a href="company-profile.php"><i class="fas fa-building"></i>&nbsp;Company Profile</a>
            <a href="company-jobs.php"><i class="fas fa-briefcase"></i>&nbsp;Manage Jobs</a>
            <a href="company-post-job.php"><i class="fas fa-plus"></i>&nbsp;Post New Job</a>
            <a href="company-applications.php"><i class="fas fa-file-alt"></i>&nbsp;Applications</a>
            <a href="company-interviews.php"><i class="fas fa-calendar-alt"></i>&nbsp;Interviews</a>
            <a href="company-candidates.php"><i class="fas fa-users"></i>&nbsp;Candidates</a>
            <a href="company-notifications.php"><i class="fas fa-bell"></i>&nbsp;Notifications</a>
            <a href="company-settings.php" class="active"><i class="fas fa-cog"></i>&nbsp;Settings</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i>&nbsp;Logout</a>
        </div>
    </div>
    <div class="main-content">
        <h1>Company Settings</h1>
        <?php if ($success): ?><div class="alert alert-success"><?php echo e($success); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>

        <div class="card">
            <h2>Change Password</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required minlength="8">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required minlength="8">
                </div>
                <button class="btn" type="submit" name="change_password">Update Password</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
