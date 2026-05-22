<?php
require_once 'config.php';

// Check if user is logged in and is a company
if (!isLoggedIn() || getUserRole() !== 'company') {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

$company_user_id = (int)$_SESSION['user_id'];
$company_id = 0;

$company = null;
$total_jobs = 0;
$total_applications = 0;
$active_jobs = 0;
$shortlisted_candidates = 0;
$unread_notifications = 0;
$recent_apps = null;
$application_status_counts = [
    'pending' => 0,
    'interview' => 0,
    'accepted' => 0,
    'rejected' => 0
];

if (isDatabaseAvailable()) {
    // Company details
    $stmt = $conn->prepare("SELECT * FROM companies WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("i", $company_user_id);
    $stmt->execute();
    $company = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $company_id = (int)($company['id'] ?? 0);

    // Statistics (prepared statements keep this safe and consistent).
    $count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM jobs WHERE company_id = ?");
    $count_stmt->bind_param("i", $company_id);
    $count_stmt->execute();
    $total_jobs = (int)($count_stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $count_stmt->close();

    $count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.company_id = ?");
    $count_stmt->bind_param("i", $company_id);
    $count_stmt->execute();
    $total_applications = (int)($count_stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $count_stmt->close();

    $active_status = 'active';
    $count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM jobs WHERE company_id = ? AND status = ?");
    $count_stmt->bind_param("is", $company_id, $active_status);
    $count_stmt->execute();
    $active_jobs = (int)($count_stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $count_stmt->close();

    $count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.company_id = ? AND a.status IN ('interview','accepted')");
    $count_stmt->bind_param("i", $company_id);
    $count_stmt->execute();
    $shortlisted_candidates = (int)($count_stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $count_stmt->close();

    // Recent applications
    $recent_apps_query = "SELECT a.*, j.title, s.name as student_name 
                          FROM applications a 
                          JOIN jobs j ON a.job_id = j.id 
                          JOIN students s ON a.student_id = s.id 
                          WHERE j.company_id = ? 
                          ORDER BY a.application_date DESC 
                          LIMIT 5";
    $recent_stmt = $conn->prepare($recent_apps_query);
    $recent_stmt->bind_param("i", $company_id);
    $recent_stmt->execute();
    $recent_apps = $recent_stmt->get_result();

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

    $count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM company_notifications WHERE company_user_id = ? AND is_read = 0");
    $count_stmt->bind_param("i", $company_user_id);
    $count_stmt->execute();
    $unread_notifications = (int)($count_stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $count_stmt->close();

    $status_stmt = $conn->prepare(
        "SELECT a.status, COUNT(*) AS total
         FROM applications a
         JOIN jobs j ON a.job_id = j.id
         WHERE j.company_id = ?
         GROUP BY a.status"
    );
    $status_stmt->bind_param("i", $company_id);
    $status_stmt->execute();
    $status_rows = $status_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($status_rows as $row) {
        $key = $row['status'];
        if (isset($application_status_counts[$key])) {
            $application_status_counts[$key] = (int)$row['total'];
        }
    }
    $status_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard - VishwaCollab</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0f766e;
            --primary-dark: #115e59;
            --primary-light: #99f6e4;
            --secondary: #202124;
            --accent: #ff9800;
            --success: #4caf50;
            --info: #2196f3;
            --warning: #ff9800;
            --danger: #f44336;
            --gray: #5f6368;
            --light-gray: #f1f3f4;
            --white: #ffffff;
            --shadow: 0 2px 8px rgba(0,0,0,0.1);
            --shadow-lg: 0 4px 16px rgba(0,0,0,0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #202124;
            line-height: 1.6;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: var(--white);
            box-shadow: var(--shadow-lg);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .sidebar-header p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: var(--secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            font-weight: 500;
        }

        .sidebar-menu a i {
            width: 24px;
            margin-right: 1rem;
            font-size: 1.1rem;
        }

        .sidebar-menu a:hover {
            background: #fce4ec;
            border-left-color: var(--primary-light);
            color: var(--primary);
        }

        .sidebar-menu a.active {
            background: #fce4ec;
            border-left-color: var(--primary);
            color: var(--primary);
            font-weight: 600;
        }

        /* Main content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--light-gray);
        }

        .dashboard-header h1 {
            font-size: 2rem;
            color: var(--secondary);
            font-weight: 700;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border-top: 4px solid var(--primary);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--gray);
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* Layout grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #f8f9fa, #fce4ec);
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            font-size: 1.2rem;
            color: var(--secondary);
            font-weight: 600;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Table */
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table thead {
            background: var(--primary);
            color: white;
        }

        .table th {
            padding: 0.85rem 1rem;
            text-align: left;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--light-gray);
            font-size: 0.9rem;
        }

        .table tbody tr:hover {
            background: #fdf2f8;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.7rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending { background: #fef7e0; color: #b06000; }
        .status-interview { background: #e8f0fe; color: #1a73e8; }
        .status-accepted { background: #e6f4ea; color: #137333; }
        .status-rejected { background: #fce8e6; color: #c5221f; }

        .empty-state {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 2.5rem;
            color: var(--light-gray);
            margin-bottom: 0.75rem;
        }

        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-building"></i> Company</h2>
                <p><?php echo $company ? htmlspecialchars($company['name']) : 'Company'; ?></p>
            </div>
            <div class="sidebar-menu">
                <a href="company-dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="company-profile.php"><i class="fas fa-id-card"></i> Company Profile</a>
                <a href="company-jobs.php"><i class="fas fa-briefcase"></i> Manage Jobs</a>
                <a href="company-post-job.php"><i class="fas fa-plus"></i> Post New Job</a>
                <a href="company-applications.php"><i class="fas fa-file-alt"></i> Applications</a>
                <a href="company-interviews.php"><i class="fas fa-calendar-alt"></i> Interviews</a>
                <a href="company-candidates.php"><i class="fas fa-users"></i> Candidates</a>
                <a href="company-notifications.php"><i class="fas fa-bell"></i> Notifications <?php if ($unread_notifications > 0): ?><span style="margin-left:0.4rem;background:#fff;color:var(--primary);padding:0.1rem 0.45rem;border-radius:999px;font-size:0.75rem;font-weight:700;"><?php echo $unread_notifications; ?></span><?php endif; ?></a>
                <a href="company-settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="main-content">
            <div class="dashboard-header">
                <h1>Welcome, <?php echo $company ? htmlspecialchars($company['name']) : 'Company'; ?></h1>
                <a href="company-post-job.php" class="btn btn-primary">Post New Job</a>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_jobs; ?></div>
                    <div class="stat-label">Total Jobs Posted</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_applications; ?></div>
                    <div class="stat-label">Total Applications</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $active_jobs; ?></div>
                    <div class="stat-label">Active Jobs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $shortlisted_candidates; ?></div>
                    <div class="stat-label">Shortlisted / Interviewing</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $unread_notifications; ?></div>
                    <div class="stat-label">Unread Notifications</div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div>
                    <div class="card">
                        <div class="card-header">
                            <h3>Recent Applications</h3>
                            <a href="company-applications.php" style="color: var(--primary); text-decoration: none; font-size: 0.9rem;">View All →</a>
                        </div>
                        <div class="card-body">
                            <?php if (!$recent_apps || $recent_apps->num_rows === 0): ?>
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>No applications received yet.</p>
                                </div>
                            <?php else: ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Candidate</th>
                                            <th>Job Title</th>
                                            <th>Applied Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($app = $recent_apps->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($app['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($app['title']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($app['application_date'])); ?></td>
                                            <td><span class="status-badge status-<?php echo htmlspecialchars($app['status']); ?>"><?php echo ucfirst($app['status']); ?></span></td>
                                            <td><a href="company-application-details.php?id=<?php echo (int)$app['id']; ?>" class="btn btn-primary" style="padding: 0.4rem 0.9rem; font-size: 0.85rem;">View</a></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="card">
                        <div class="card-header">
                            <h3>Quick Actions</h3>
                        </div>
                        <div class="card-body" style="display: grid; gap: 0.8rem;">
                            <a href="company-post-job.php" class="btn btn-primary" style="justify-content: center;">
                                <i class="fas fa-plus"></i> Post New Job
                            </a>
                            <a href="company-jobs.php" class="btn" style="justify-content: center; background: #fce4ec; color: var(--secondary);">
                                <i class="fas fa-briefcase"></i> Manage Jobs
                            </a>
                            <a href="company-applications.php" class="btn" style="justify-content: center; background: #f5f7fa; color: var(--secondary);">
                                <i class="fas fa-file-alt"></i> View Applications
                            </a>
                            <a href="company-candidates.php" class="btn" style="justify-content: center; background: #f5f7fa; color: var(--secondary);">
                                <i class="fas fa-users"></i> View Candidates
                            </a>
                        </div>
                    </div>
                    <div class="card" style="margin-top:1rem;">
                        <div class="card-header">
                            <h3>Application Analytics</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="appStatusChart" height="180"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('appStatusChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'Interview', 'Accepted', 'Rejected'],
                    datasets: [{
                        data: [
                            <?php echo (int)$application_status_counts['pending']; ?>,
                            <?php echo (int)$application_status_counts['interview']; ?>,
                            <?php echo (int)$application_status_counts['accepted']; ?>,
                            <?php echo (int)$application_status_counts['rejected']; ?>
                        ],
                        backgroundColor: ['#fbbc05', '#4285f4', '#34a853', '#ea4335']
                    }]
                },
                options: {
                    plugins: { legend: { position: 'bottom' } },
                    cutout: '60%'
                }
            });
        }
    </script>
</body>
</html>