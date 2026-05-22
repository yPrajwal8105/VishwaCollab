<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole() !== 'company') {
    header("Location: login.php");
    exit();
}
require_once 'db_connect.php';

$company_user_id = (int)$_SESSION['user_id'];
$company_id = 0;
$interviews = [];
$error = '';
$success = '';

if (isDatabaseAvailable()) {
    $conn->query(
        "CREATE TABLE IF NOT EXISTS company_interviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            application_id INT NOT NULL UNIQUE,
            company_user_id INT NOT NULL,
            interview_at DATETIME NOT NULL,
            mode ENUM('online','onsite','phone') DEFAULT 'online',
            meeting_link VARCHAR(255) NULL,
            notes TEXT NULL,
            result_status ENUM('scheduled','completed','cancelled') DEFAULT 'scheduled',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_company_interview_at (company_user_id, interview_at),
            FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
            FOREIGN KEY (company_user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_interview_result'])) {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $error = 'Invalid request token. Please refresh and try again.';
        } else {
            $record_id = (int)($_POST['record_id'] ?? 0);
            $result_status = $_POST['result_status'] ?? 'scheduled';
            $allowed = ['scheduled', 'completed', 'cancelled'];
            if (!in_array($result_status, $allowed, true)) {
                $error = 'Invalid status selected.';
            } else {
                $update_stmt = $conn->prepare("UPDATE company_interviews SET result_status = ? WHERE id = ? AND company_user_id = ?");
                $update_stmt->bind_param("sii", $result_status, $record_id, $company_user_id);
                if ($update_stmt->execute() && $update_stmt->affected_rows >= 0) {
                    $success = 'Interview status updated.';
                } else {
                    $error = 'Unable to update interview status.';
                }
                $update_stmt->close();
            }
        }
    }

    $company_stmt = $conn->prepare("SELECT id FROM companies WHERE user_id = ? LIMIT 1");
    $company_stmt->bind_param("i", $company_user_id);
    $company_stmt->execute();
    $company_id = (int)($company_stmt->get_result()->fetch_assoc()['id'] ?? 0);
    $company_stmt->close();

    $query = "SELECT ci.id AS interview_record_id, ci.interview_at, ci.mode, ci.meeting_link, ci.notes, ci.result_status,
                     a.id AS application_id, a.status AS application_status, a.application_date,
                     j.title, s.name AS student_name, s.email AS student_email
              FROM company_interviews ci
              JOIN applications a ON ci.application_id = a.id
              JOIN jobs j ON a.job_id = j.id
              JOIN students s ON a.student_id = s.id
              WHERE ci.company_user_id = ?
              ORDER BY ci.interview_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $company_user_id);
    $stmt->execute();
    $interviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interviews - Company Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #0f766e; --shadow: 0 4px 12px rgba(0,0,0,0.15); }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        .dashboard-container { display: flex; min-height: 100vh; background: #f5f7fa; }
        .sidebar { width: 250px; background: white; box-shadow: var(--shadow); position: fixed; height: 100vh; overflow-y: auto; }
        .sidebar-header { padding: 2rem 1.5rem; background: var(--primary); color: white; }
        .sidebar-menu { padding: 1rem 0; }
        .sidebar-menu a { display: flex; align-items: center; padding: 1rem 1.5rem; color: #202124; text-decoration: none; transition: all 0.3s; border-left: 4px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: #fce4ec; border-left-color: var(--primary); color: var(--primary); }
        .main-content { flex: 1; margin-left: 250px; padding: 2rem; }
        .interview-card { background: white; border-radius: 10px; box-shadow: var(--shadow); padding: 1.5rem; margin-bottom: 1rem; border-left: 4px solid var(--primary); }
        .alert { margin-bottom: 1rem; padding: 0.8rem 1rem; border-radius: 8px; }
        .alert-success { background: #e6f4ea; color: #137333; }
        .alert-error { background: #fce8e6; color: #c5221f; }
        .btn { padding: 0.45rem 0.8rem; border-radius: 6px; border: none; background: var(--primary); color: #fff; cursor: pointer; font-weight: 600; }
        select { padding: 0.45rem; border: 1px solid #ddd; border-radius: 6px; }
        @media (max-width: 900px) { .sidebar { width: 100%; height: auto; position: relative; } .main-content { margin-left: 0; padding: 1rem; } }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Company Dashboard</h2>
            </div>
            <div class="sidebar-menu">
                <a href="company-dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="company-profile.php"><i class="fas fa-building"></i> Company Profile</a>
                <a href="company-jobs.php"><i class="fas fa-briefcase"></i> Manage Jobs</a>
                <a href="company-post-job.php"><i class="fas fa-plus"></i> Post New Job</a>
                <a href="company-applications.php"><i class="fas fa-file-alt"></i> Applications</a>
                <a href="company-interviews.php" class="active"><i class="fas fa-calendar-alt"></i> Interviews</a>
                <a href="company-candidates.php"><i class="fas fa-users"></i> Candidates</a>
                <a href="company-notifications.php"><i class="fas fa-bell"></i> Notifications</a>
                <a href="company-settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <h1>Scheduled Interviews</h1>
            <?php if ($success): ?><div class="alert alert-success"><?php echo e($success); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
            <?php if (empty($interviews)): ?>
                <div class="interview-card">
                    <p>No interviews scheduled yet. Set interview schedules from application details.</p>
                </div>
            <?php else: ?>
                <?php foreach ($interviews as $interview): ?>
                    <div class="interview-card">
                        <h3><?php echo e($interview['title']); ?></h3>
                        <p><strong>Candidate:</strong> <?php echo e($interview['student_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo e($interview['student_email']); ?></p>
                        <p><strong>Interview At:</strong> <?php echo date('M j, Y g:i A', strtotime($interview['interview_at'])); ?></p>
                        <p><strong>Mode:</strong> <?php echo ucfirst(e($interview['mode'])); ?></p>
                        <p><strong>Meeting/Location:</strong> <?php echo e($interview['meeting_link'] ?? 'N/A'); ?></p>
                        <p><strong>Notes:</strong> <?php echo e($interview['notes'] ?? 'N/A'); ?></p>
                        <p><a href="company-application-details.php?id=<?php echo (int)$interview['application_id']; ?>">Open application details</a></p>
                        <form method="POST" style="margin-top:0.6rem;display:flex;gap:0.5rem;align-items:center;">
                            <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                            <input type="hidden" name="record_id" value="<?php echo (int)$interview['interview_record_id']; ?>">
                            <select name="result_status">
                                <option value="scheduled" <?php echo $interview['result_status'] === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                <option value="completed" <?php echo $interview['result_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $interview['result_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_interview_result" class="btn">Update</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>






