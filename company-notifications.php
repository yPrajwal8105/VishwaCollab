<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole() !== 'company') {
    header("Location: login.php");
    exit();
}
require_once 'db_connect.php';

$company_id = (int)$_SESSION['user_id'];
$notifications = [];
$success = '';
$error = '';

if (isDatabaseAvailable()) {
    $conn->query(
        "CREATE TABLE IF NOT EXISTS company_notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            company_user_id INT NOT NULL,
            title VARCHAR(180) NOT NULL,
            message TEXT NOT NULL,
            type ENUM('info','success','warning','error') DEFAULT 'info',
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_company_unread (company_user_id, is_read, created_at),
            FOREIGN KEY (company_user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $error = 'Invalid request token.';
        } else {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $conn->prepare("UPDATE company_notifications SET is_read = 1 WHERE id = ? AND company_user_id = ?");
            $stmt->bind_param("ii", $id, $company_id);
            if ($stmt->execute()) {
                $success = 'Notification marked as read.';
            } else {
                $error = 'Unable to update notification.';
            }
            $stmt->close();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $error = 'Invalid request token.';
        } else {
            $stmt = $conn->prepare("UPDATE company_notifications SET is_read = 1 WHERE company_user_id = ? AND is_read = 0");
            $stmt->bind_param("i", $company_id);
            $stmt->execute();
            $stmt->close();
            $success = 'All notifications marked as read.';
        }
    }

    $stmt = $conn->prepare("SELECT * FROM company_notifications WHERE company_user_id = ? ORDER BY created_at DESC LIMIT 100");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Company Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#0f766e; --shadow:0 4px 12px rgba(0,0,0,0.15); }
        * { box-sizing:border-box; font-family:'Segoe UI',sans-serif; }
        body { margin:0; background:#f5f7fa; }
        .dashboard-container { display:flex; min-height:100vh; }
        .sidebar { width:250px; background:white; box-shadow:var(--shadow); position:fixed; height:100vh; overflow-y:auto; }
        .sidebar-header { padding:2rem 1.5rem; background:var(--primary); color:#fff; }
        .sidebar-menu a { display:flex; align-items:center; padding:1rem 1.5rem; color:#202124; text-decoration:none; border-left:4px solid transparent; }
        .sidebar-menu a:hover,.sidebar-menu a.active { background:#fce4ec; border-left-color:var(--primary); color:var(--primary); }
        .main-content { flex:1; margin-left:250px; padding:2rem; }
        .card { background:#fff; border-radius:10px; box-shadow:var(--shadow); padding:1rem 1.2rem; margin-bottom:0.8rem; border-left:4px solid #ddd; }
        .unread { border-left-color:var(--primary); }
        .meta { color:#5f6368; font-size:0.9rem; }
        .btn { border:none; border-radius:6px; padding:0.45rem 0.8rem; cursor:pointer; background:var(--primary); color:#fff; }
        .alert { margin-bottom:1rem; padding:0.8rem 1rem; border-radius:8px; }
        .alert-success { background:#e6f4ea; color:#137333; }
        .alert-error { background:#fce8e6; color:#c5221f; }
        @media (max-width:900px) { .sidebar { width:100%; position:relative; height:auto; } .main-content { margin-left:0; padding:1rem; } }
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
            <a href="company-notifications.php" class="active"><i class="fas fa-bell"></i>&nbsp;Notifications</a>
            <a href="company-settings.php"><i class="fas fa-cog"></i>&nbsp;Settings</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i>&nbsp;Logout</a>
        </div>
    </div>
    <div class="main-content">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;">
            <h1>Notifications</h1>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                <button class="btn" name="mark_all_read" type="submit">Mark all as read</button>
            </form>
        </div>
        <?php if ($success): ?><div class="alert alert-success"><?php echo e($success); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>

        <?php if (empty($notifications)): ?>
            <div class="card"><p>No notifications yet.</p></div>
        <?php else: ?>
            <?php foreach ($notifications as $n): ?>
                <div class="card <?php echo (int)$n['is_read'] === 0 ? 'unread' : ''; ?>">
                    <h3 style="margin:0 0 0.4rem;"><?php echo e($n['title']); ?></h3>
                    <p style="margin:0 0 0.5rem;"><?php echo e($n['message']); ?></p>
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
                        <span class="meta"><?php echo date('M j, Y g:i A', strtotime($n['created_at'])); ?> · <?php echo strtoupper(e($n['type'])); ?></span>
                        <?php if ((int)$n['is_read'] === 0): ?>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                                <input type="hidden" name="id" value="<?php echo (int)$n['id']; ?>">
                                <button type="submit" name="mark_read" class="btn">Mark read</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
