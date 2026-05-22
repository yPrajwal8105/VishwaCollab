<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole() !== 'student') {
    header("Location: login.php");
    exit();
}
require_once 'db_connect.php';

$user_id = (int)$_SESSION['user_id'];
$student_id = 0;
$applications = [];

if (isDatabaseAvailable()) {
    $student_stmt = $conn->prepare("SELECT id FROM students WHERE user_id = ? LIMIT 1");
    $student_stmt->bind_param("i", $user_id);
    $student_stmt->execute();
    $student_id = (int)($student_stmt->get_result()->fetch_assoc()['id'] ?? 0);
    $student_stmt->close();

    $query = "SELECT a.*, j.title, c.name AS company_name_full FROM applications a 
              JOIN jobs j ON a.job_id = j.id 
              LEFT JOIN companies c ON c.id = j.company_id 
              WHERE a.student_id = ? ORDER BY a.application_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - Student Dashboard</title>
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
        .table { width: 100%; background: white; border-radius: 10px; box-shadow: var(--shadow); overflow: hidden; }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid #e0e0e0; }
        .table th { background: var(--primary); color: white; font-weight: 600; }
        .status-badge { padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .status-pending { background: #fef7e0; color: #b06000; }
        .status-approved { background: #e6f4ea; color: #137333; }
        .status-rejected { background: #fce8e6; color: #c5221f; }
        .status-interview { background: #e8f0fe; color: var(--primary); }
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
                <a href="student-jobs.php"><i class="fas fa-briefcase"></i> Job Search</a>
                <a href="student-applications.php" class="active"><i class="fas fa-file-alt"></i> Applications</a>
                <a href="student-interviews.php"><i class="fas fa-calendar-alt"></i> Interviews</a>
                <a href="student-resume.php"><i class="fas fa-file-pdf"></i> Resume Builder</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <h1>My Applications</h1>
            <table class="table">
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Company</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 2rem;">No applications yet. <a href="student-jobs.php">Browse jobs</a> to get started!</td></tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><a href="job_details.php?id=<?php echo $app['job_id']; ?>" style="color: var(--primary); text-decoration: none;"><?php echo htmlspecialchars($app['title']); ?></a></td>
                                <td><?php echo htmlspecialchars($app['company_name_full'] ?? 'Company'); ?></td>
                                <td><?php echo date('M j, Y', strtotime($app['application_date'])); ?></td>
                                <td><span class="status-badge status-<?php echo $app['status']; ?>"><?php echo ucfirst($app['status']); ?></span></td>
                                <td><a href="job_details.php?id=<?php echo $app['job_id']; ?>" style="color: var(--primary);">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>


