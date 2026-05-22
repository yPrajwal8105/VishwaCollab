<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole() !== 'tpo') {
    header("Location: login.php");
    exit();
}
require_once 'db_connect.php';

$stats = [
    'total_students' => 0,
    'total_companies' => 0,
    'total_jobs' => 0,
    'total_applications' => 0,
    'total_placements' => 0
];

if (isDatabaseAvailable()) {
    $stats['total_students'] = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
    $stats['total_companies'] = $conn->query("SELECT COUNT(*) as total FROM companies")->fetch_assoc()['total'];
    $stats['total_jobs'] = $conn->query("SELECT COUNT(*) as total FROM jobs")->fetch_assoc()['total'];
    $stats['total_applications'] = $conn->query("SELECT COUNT(*) as total FROM applications")->fetch_assoc()['total'];
    $stats['total_placements'] = $conn->query("SELECT COUNT(*) as total FROM applications WHERE status = 'accepted'")->fetch_assoc()['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - TPO Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #4caf50; --shadow: 0 4px 12px rgba(0,0,0,0.15); }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        .dashboard-container { display: flex; min-height: 100vh; background: #f5f7fa; }
        .sidebar { width: 250px; background: white; box-shadow: var(--shadow); position: fixed; height: 100vh; overflow-y: auto; }
        .sidebar-header { padding: 2rem 1.5rem; background: var(--primary); color: white; }
        .sidebar-menu { padding: 1rem 0; }
        .sidebar-menu a { display: flex; align-items: center; padding: 1rem 1.5rem; color: #202124; text-decoration: none; transition: all 0.3s; border-left: 4px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: #e8f5e9; border-left-color: var(--primary); color: var(--primary); }
        .main-content { flex: 1; margin-left: 250px; padding: 2rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; border-radius: 10px; box-shadow: var(--shadow); padding: 1.5rem; text-align: center; border-top: 4px solid var(--primary); }
        .stat-number { font-size: 2.5rem; font-weight: bold; color: var(--primary); }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>TPO Dashboard</h2>
            </div>
            <div class="sidebar-menu">
                <a href="tpo-dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="tpo-students.php"><i class="fas fa-user-graduate"></i> Students</a>
                <a href="tpo-companies.php"><i class="fas fa-building"></i> Companies</a>
                <a href="tpo-jobs.php"><i class="fas fa-briefcase"></i> Jobs</a>
                <a href="tpo-placements.php"><i class="fas fa-chart-line"></i> Placements</a>
                <a href="tpo-reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <h1>Reports & Statistics</h1>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_students']; ?></div>
                    <p>Total Students</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_companies']; ?></div>
                    <p>Registered Companies</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_jobs']; ?></div>
                    <p>Job Postings</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_applications']; ?></div>
                    <p>Total Applications</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_placements']; ?></div>
                    <p>Successful Placements</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_jobs'] > 0 ? round(($stats['total_placements'] / $stats['total_jobs']) * 100, 1) : 0; ?>%</div>
                    <p>Placement Rate</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>






