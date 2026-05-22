<?php
require_once 'config.php';
require_once 'db_connect.php';

if (!isLoggedIn() || getUserRole() !== 'company') {
    header("Location: login.php");
    exit();
}

$company_user_id = (int)$_SESSION['user_id'];
$company_id = 0;
$application_id = max(0, (int)($_GET['id'] ?? 0));
$application = null;
$scheduled_interview = null;
$error_message = '';
$success_message = '';
$allowed_statuses = ['pending', 'interview', 'accepted', 'rejected'];

if (isDatabaseAvailable()) {
    $company_stmt = $conn->prepare("SELECT id FROM companies WHERE user_id = ? LIMIT 1");
    $company_stmt->bind_param("i", $company_user_id);
    $company_stmt->execute();
    $company_id = (int)($company_stmt->get_result()->fetch_assoc()['id'] ?? 0);
    $company_stmt->close();

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
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && isDatabaseAvailable()) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error_message = 'Invalid request token. Please refresh and try again.';
    } else {
        $status = $_POST['status'] ?? 'pending';
        if (!in_array($status, $allowed_statuses, true)) {
            $error_message = 'Invalid status selected.';
        } else {
            $update_stmt = $conn->prepare(
                "UPDATE applications a
                 JOIN jobs j ON a.job_id = j.id
                 SET a.status = ?
                 WHERE a.id = ? AND j.company_id = ?"
            );
            $update_stmt->bind_param("sii", $status, $application_id, $company_id);
            if ($update_stmt->execute() && $update_stmt->affected_rows >= 0) {
                logActivity(
                    $conn,
                    $company_user_id,
                    'company',
                    'application_status_updated',
                    'application',
                    $application_id,
                    'Status changed to ' . $status . ' from details page'
                );
                createCompanyNotification($conn, $company_user_id, 'Application Status Updated', 'Application #' . $application_id . ' moved to ' . $status . '.', 'info');
                $success_message = 'Application status updated.';
            } else {
                $error_message = 'Unable to update application status.';
            }
            $update_stmt->close();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_interview']) && isDatabaseAvailable()) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error_message = 'Invalid request token. Please refresh and try again.';
    } else {
        $interview_at_raw = trim($_POST['interview_at'] ?? '');
        $mode = $_POST['mode'] ?? 'online';
        $meeting_link = trim($_POST['meeting_link'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $result_status = $_POST['result_status'] ?? 'scheduled';

        $allowed_modes = ['online', 'onsite', 'phone'];
        $allowed_results = ['scheduled', 'completed', 'cancelled'];
        $interview_at = date('Y-m-d H:i:s', strtotime($interview_at_raw));

        if (!$interview_at_raw || $interview_at === '1970-01-01 00:00:00') {
            $error_message = 'Please provide a valid interview date and time.';
        } elseif (!in_array($mode, $allowed_modes, true) || !in_array($result_status, $allowed_results, true)) {
            $error_message = 'Invalid interview details.';
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO company_interviews (application_id, company_user_id, interview_at, mode, meeting_link, notes, result_status)
                 VALUES (?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE interview_at = VALUES(interview_at), mode = VALUES(mode), meeting_link = VALUES(meeting_link), notes = VALUES(notes), result_status = VALUES(result_status)"
            );
            $stmt->bind_param("iisssss", $application_id, $company_user_id, $interview_at, $mode, $meeting_link, $notes, $result_status);
            if ($stmt->execute()) {
                $success_message = 'Interview schedule updated.';
                logActivity($conn, $company_user_id, 'company', 'interview_scheduled', 'application', $application_id, 'Interview set for ' . $interview_at);
                createCompanyNotification($conn, $company_user_id, 'Interview Scheduled', 'Interview saved for application #' . $application_id . '.', 'success');
            } else {
                $error_message = 'Unable to save interview schedule.';
            }
            $stmt->close();
        }
    }
}

if (isDatabaseAvailable() && $application_id > 0) {
    $stmt = $conn->prepare(
        "SELECT a.*, j.title, j.location, j.salary, s.name AS student_name, s.email AS student_email,
                s.course, s.skills, s.cgpa, s.education, s.experience
         FROM applications a
         JOIN jobs j ON a.job_id = j.id
         JOIN students s ON a.student_id = s.id
         WHERE a.id = ? AND j.company_id = ?
         LIMIT 1"
    );
    $stmt->bind_param("ii", $application_id, $company_id);
    $stmt->execute();
    $application = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($application) {
        $interview_stmt = $conn->prepare("SELECT * FROM company_interviews WHERE application_id = ? AND company_user_id = ? LIMIT 1");
        $interview_stmt->bind_param("ii", $application_id, $company_user_id);
        $interview_stmt->execute();
        $scheduled_interview = $interview_stmt->get_result()->fetch_assoc();
        $interview_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Details - Company Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f5f7fa; color: #202124; }
        .container { max-width: 980px; margin: 1.5rem auto; padding: 0 1rem; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; gap: 0.75rem; }
        .card { background: #fff; border-radius: 12px; padding: 1.25rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 1rem; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.9rem; }
        .muted { color: #5f6368; }
        .badge { display: inline-block; padding: 0.3rem 0.7rem; border-radius: 999px; font-size: 0.85rem; font-weight: 600; }
        .status-pending { background: #fef7e0; color: #b06000; }
        .status-interview { background: #e8f0fe; color: #1a73e8; }
        .status-accepted { background: #e6f4ea; color: #137333; }
        .status-rejected { background: #fce8e6; color: #c5221f; }
        .btn { padding: 0.55rem 0.95rem; border-radius: 6px; border: none; cursor: pointer; background: #0f766e; color: #fff; text-decoration: none; font-weight: 600; }
        .flash { margin: 0 0 1rem; padding: 0.7rem 0.9rem; border-radius: 8px; }
        .flash-success { background: #e6f4ea; color: #137333; }
        .flash-error { background: #fce8e6; color: #c5221f; }
        select { padding: 0.5rem; border-radius: 6px; border: 1px solid #ddd; }
        @media (max-width: 768px) { .grid { grid-template-columns: 1fr; } .topbar { flex-direction: column; align-items: flex-start; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="topbar">
            <h1 style="margin:0;">Application Details</h1>
            <a href="company-applications.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Applications</a>
        </div>

        <?php if ($success_message): ?><div class="flash flash-success"><?php echo e($success_message); ?></div><?php endif; ?>
        <?php if ($error_message): ?><div class="flash flash-error"><?php echo e($error_message); ?></div><?php endif; ?>

        <?php if (!$application): ?>
            <div class="card">
                <h3>Application not found</h3>
                <p class="muted">This application does not exist or does not belong to your company.</p>
            </div>
        <?php else: ?>
            <div class="card">
                <h3 style="margin-top:0;"><?php echo e($application['student_name']); ?></h3>
                <p class="muted" style="margin-top:0;"><?php echo e($application['student_email']); ?></p>
                <div class="grid">
                    <div><strong>Job Title:</strong> <?php echo e($application['title']); ?></div>
                    <div><strong>Applied On:</strong> <?php echo date('M j, Y g:i A', strtotime($application['application_date'])); ?></div>
                    <div><strong>Course:</strong> <?php echo e($application['course'] ?? 'N/A'); ?></div>
                    <div><strong>CGPA:</strong> <?php echo e((string)($application['cgpa'] ?? 'N/A')); ?></div>
                    <div><strong>Location:</strong> <?php echo e($application['location'] ?? 'N/A'); ?></div>
                    <div><strong>Salary:</strong> <?php echo e($application['salary'] ?? 'N/A'); ?></div>
                </div>
                <p style="margin-top:0.9rem;"><strong>Status:</strong>
                    <span class="badge status-<?php echo e($application['status']); ?>"><?php echo ucfirst(e($application['status'])); ?></span>
                </p>
            </div>

            <div class="card">
                <h3 style="margin-top:0;">Skills</h3>
                <p class="muted"><?php echo nl2br(e($application['skills'] ?? 'No skills listed.')); ?></p>
                <h3>Education</h3>
                <p class="muted"><?php echo nl2br(e($application['education'] ?? 'No education details.')); ?></p>
                <h3>Experience</h3>
                <p class="muted"><?php echo nl2br(e($application['experience'] ?? 'No experience details.')); ?></p>
            </div>

            <div class="card">
                <h3 style="margin-top:0;">Update Status</h3>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                    <input type="hidden" name="update_status" value="1">
                    <select name="status">
                        <?php foreach ($allowed_statuses as $status): ?>
                            <option value="<?php echo e($status); ?>" <?php echo $application['status'] === $status ? 'selected' : ''; ?>>
                                <?php echo ucfirst($status); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn">Save</button>
                </form>
            </div>

            <div class="card">
                <h3 style="margin-top:0;">Schedule Interview</h3>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                    <input type="hidden" name="schedule_interview" value="1">
                    <div class="grid">
                        <div>
                            <label><strong>Interview date and time</strong></label><br>
                            <input type="datetime-local" name="interview_at" value="<?php echo e(isset($scheduled_interview['interview_at']) ? date('Y-m-d\TH:i', strtotime($scheduled_interview['interview_at'])) : ''); ?>" style="width:100%;padding:0.5rem;border:1px solid #ddd;border-radius:6px;">
                        </div>
                        <div>
                            <label><strong>Mode</strong></label><br>
                            <select name="mode" style="width:100%;padding:0.5rem;border:1px solid #ddd;border-radius:6px;">
                                <?php $mode = $scheduled_interview['mode'] ?? 'online'; ?>
                                <option value="online" <?php echo $mode === 'online' ? 'selected' : ''; ?>>Online</option>
                                <option value="onsite" <?php echo $mode === 'onsite' ? 'selected' : ''; ?>>On-site</option>
                                <option value="phone" <?php echo $mode === 'phone' ? 'selected' : ''; ?>>Phone</option>
                            </select>
                        </div>
                        <div style="grid-column:1 / -1;">
                            <label><strong>Meeting link / location</strong></label><br>
                            <input type="text" name="meeting_link" value="<?php echo e($scheduled_interview['meeting_link'] ?? ''); ?>" style="width:100%;padding:0.5rem;border:1px solid #ddd;border-radius:6px;">
                        </div>
                        <div style="grid-column:1 / -1;">
                            <label><strong>Internal notes</strong></label><br>
                            <textarea name="notes" rows="3" style="width:100%;padding:0.5rem;border:1px solid #ddd;border-radius:6px;"><?php echo e($scheduled_interview['notes'] ?? ''); ?></textarea>
                        </div>
                        <div>
                            <label><strong>Interview result status</strong></label><br>
                            <?php $result_status = $scheduled_interview['result_status'] ?? 'scheduled'; ?>
                            <select name="result_status" style="width:100%;padding:0.5rem;border:1px solid #ddd;border-radius:6px;">
                                <option value="scheduled" <?php echo $result_status === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                <option value="completed" <?php echo $result_status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $result_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top:0.8rem;">
                        <button type="submit" class="btn">Save Interview Schedule</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
