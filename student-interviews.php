<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole() !== 'student') {
    header("Location: login.php");
    exit();
}
require_once 'db_connect.php';

$user_id = (int)$_SESSION['user_id'];
$student_id = 0;
$interviews = [];
$error_message = '';

if (isDatabaseAvailable()) {
    $student_stmt = $conn->prepare("SELECT id FROM students WHERE user_id = ? LIMIT 1");
    $student_stmt->bind_param("i", $user_id);
    $student_stmt->execute();
    $student_id = (int)($student_stmt->get_result()->fetch_assoc()['id'] ?? 0);
    $student_stmt->close();

    if ($student_id <= 0) {
        $error_message = "Student profile not found. Please complete your profile first.";
    }

    if ($student_id > 0) {
        // Show interviews that are actually scheduled by companies (company_interviews).
        // This makes the student "Interviews" page work even if application.status wasn't updated.
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

        $query = "SELECT 
                    a.id AS application_id,
                    a.job_id,
                    a.application_date,
                    a.status AS application_status,
                    j.title,
                    c.name AS company_name_full,
                    ci.interview_at,
                    ci.mode,
                    ci.meeting_link,
                    ci.notes,
                    ci.result_status
                  FROM applications a
                  JOIN jobs j ON a.job_id = j.id
                  LEFT JOIN companies c ON c.id = j.company_id
                  JOIN company_interviews ci ON ci.application_id = a.id
                  WHERE a.student_id = ?
                  ORDER BY ci.interview_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $interviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
} else {
    $error_message = "Database connection is not available. Please start MySQL and run init_db.php.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interviews - Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #1a73e8; --shadow: 0 4px 12px rgba(0,0,0,0.15); }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        .dashboard-container { display: flex; min-height: 100vh; background: #f5f7fa; }
        .sidebar { width: 250px; background: white; box-shadow: var(--shadow); position: fixed; height: 100vh; overflow-y: auto; }
        .sidebar-header { padding: 2rem 1.5rem; background: var(--primary); color: white; }
        .sidebar-menu { padding: 1rem 0; }
        .sidebar-menu a { display: flex; align-items: center; padding: 1rem 1.5rem; color: #202124; text-decoration: none; transition: all 0.3s; border-left: 4px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: #e8f0fe; border-left-color: var(--primary); color: var(--primary); }
        .main-content { flex: 1; margin-left: 250px; padding: 2rem; }
        .interview-card { background: white; border-radius: 10px; box-shadow: var(--shadow); padding: 1.5rem; margin-bottom: 1rem; border-left: 4px solid var(--primary); }
        .interview-card h3 { margin-bottom: 0.5rem; color: #202124; }
        .interview-card p { color: #5f6368; margin: 0.3rem 0; }
        .alert { margin-bottom: 1rem; padding: 0.8rem 1rem; border-radius: 8px; background: #fce8e6; color: #c5221f; }
        .alert a { color: inherit; font-weight: 700; }
        .pill { display:inline-block; padding:0.25rem 0.6rem; border-radius:999px; font-size:0.8rem; font-weight:700; }
        .pill-scheduled { background:#e8f0fe; color:var(--primary); }
        .pill-completed { background:#e6f4ea; color:#137333; }
        .pill-cancelled { background:#fce8e6; color:#c5221f; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Student Dashboard</h2>
            </div>
            <div class="sidebar-menu">
                <a href="student-dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="student-jobs.php"><i class="fas fa-search"></i> Job Search</a>
                <a href="student-applications.php"><i class="fas fa-file-alt"></i> Applications</a>
                <a href="student-interviews.php" class="active"><i class="fas fa-calendar-alt"></i> Interviews</a>
                <a href="student-mock-interview.php"><i class="fas fa-microphone"></i> Mock Interview</a>
                <a href="student-resume-ai.php"><i class="fas fa-magic"></i> AI Resume Builder</a>
                <a href="student-resume.php"><i class="fas fa-file-pdf"></i> Resume Builder</a>
                <a href="student-chat.php"><i class="fas fa-comments"></i> Chat with Seniors</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <h1>Upcoming Interviews</h1>
            <?php if ($error_message): ?>
                <div class="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <?php if (strpos($error_message, 'profile') !== false): ?>
                        &nbsp;→ <a href="profile.php">Go to Profile</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (empty($interviews)): ?>
                <div class="interview-card">
                    <p>No interviews scheduled at the moment. Keep applying to jobs!</p>
                </div>
            <?php else: ?>
                <?php foreach ($interviews as $interview): ?>
                    <div class="interview-card">
                        <h3><?php echo htmlspecialchars($interview['title']); ?></h3>
                        <p><strong>Company:</strong> <?php echo htmlspecialchars($interview['company_name_full'] ?? 'Company'); ?></p>
                        <?php if (!empty($interview['interview_at'])): ?>
                            <p><strong>Interview At:</strong> <?php echo date('M j, Y g:i A', strtotime($interview['interview_at'])); ?></p>
                        <?php endif; ?>
                        <p><strong>Mode:</strong> <?php echo htmlspecialchars(ucfirst($interview['mode'] ?? 'online')); ?></p>
                        <p><strong>Meeting/Location:</strong> <?php echo htmlspecialchars($interview['meeting_link'] ?? 'N/A'); ?></p>
                        <p>
                            <strong>Status:</strong>
                            <?php
                                $rs = $interview['result_status'] ?? 'scheduled';
                                $pillClass = $rs === 'completed' ? 'pill-completed' : ($rs === 'cancelled' ? 'pill-cancelled' : 'pill-scheduled');
                            ?>
                            <span class="pill <?php echo $pillClass; ?>"><?php echo htmlspecialchars(ucfirst($rs)); ?></span>
                        </p>
                        <a href="job_details.php?id=<?php echo (int)$interview['job_id']; ?>" style="color: var(--primary); text-decoration: none; margin-top: 1rem; display: inline-block;">View Job Details →</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>


