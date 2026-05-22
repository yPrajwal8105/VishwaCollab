<?php
require_once 'config.php';

// Check if user is logged in and is a TPO
if (!isLoggedIn() || getUserRole() !== 'tpo') {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// TPO statistics
$total_students = 0;
$total_companies = 0;
$total_jobs = 0;
$placements = 0;
$pending_applications = 0;
$upcoming_drives = [];

// Advanced analytics: compute skill demand vs student skill supply to show unique "Skill Gap Insights"
$skill_gaps = [];

// Top hiring companies based on official CSV placements
$top_companies = [];

if (isDatabaseAvailable()) {
    $total_students = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'] ?? 0;
    $total_companies = $conn->query("SELECT COUNT(*) as total FROM companies")->fetch_assoc()['total'] ?? 0;
    $total_jobs = $conn->query("SELECT COUNT(*) as total FROM jobs WHERE status = 'active'")->fetch_assoc()['total'] ?? 0;
    $placements = $conn->query("SELECT COUNT(*) as total FROM applications WHERE status = 'accepted'")->fetch_assoc()['total'] ?? 0;
    $pending_applications = $conn->query("SELECT COUNT(*) as total FROM applications WHERE status = 'pending'")->fetch_assoc()['total'] ?? 0;

    // ---------- Top Hiring Companies (from alumni_placements CSV data) ----------
    // Ensure alumni_placements table exists (same structure as upload page)
    $create_table = "CREATE TABLE IF NOT EXISTS alumni_placements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_name VARCHAR(100) NOT NULL,
        student_email VARCHAR(100),
        course VARCHAR(100),
        batch_year YEAR,
        company_name VARCHAR(200) NOT NULL,
        job_title VARCHAR(200) NOT NULL,
        salary VARCHAR(50),
        location VARCHAR(100),
        placement_date DATE,
        cgpa DECIMAL(3,2),
        skills TEXT,
        additional_info TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->query($create_table);

    // Get top 5 companies by number of placements
    $top_sql = "
        SELECT 
            company_name,
            COUNT(*) AS total_placements,
            MIN(batch_year) AS min_batch,
            MAX(batch_year) AS max_batch
        FROM alumni_placements
        WHERE company_name IS NOT NULL AND company_name <> ''
        GROUP BY company_name
        ORDER BY total_placements DESC, company_name ASC
        LIMIT 5
    ";
    if ($res = $conn->query($top_sql)) {
        $top_companies = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();
    }

    // ---------- Skill Gap Insights ----------
    $skillDemand = [];
    $skillSupply = [];

    // 1) Demand side: skills required in active jobs
    if ($jobsRes = $conn->query("SELECT required_skills FROM jobs WHERE status = 'active'")) {
        while ($row = $jobsRes->fetch_assoc()) {
            $skills = preg_split('/[,|]/', $row['required_skills'] ?? '');
            foreach ($skills as $s) {
                $s = strtolower(trim($s));
                if ($s !== '') {
                    $skillDemand[$s] = ($skillDemand[$s] ?? 0) + 1;
                }
            }
        }
        $jobsRes->free();
    }

    // 2) Supply side: skills mentioned by students who are NOT yet placed (no accepted application)
    $supply_sql = "SELECT s.skills
                   FROM students s
                   LEFT JOIN applications a 
                     ON a.student_id = s.user_id 
                    AND a.status = 'accepted'
                   WHERE a.id IS NULL";
    if ($studRes = $conn->query($supply_sql)) {
        while ($row = $studRes->fetch_assoc()) {
            $skills = preg_split('/[,|]/', $row['skills'] ?? '');
            foreach ($skills as $s) {
                $s = strtolower(trim($s));
                if ($s !== '') {
                    $skillSupply[$s] = ($skillSupply[$s] ?? 0) + 1;
                }
            }
        }
        $studRes->free();
    }

    // 3) Build a list of skills where demand is strong but supply is weak
    foreach ($skillDemand as $skill => $demand) {
        $supply = $skillSupply[$skill] ?? 0;
        // Gap score: higher when many jobs need it but few students have it
        $gapScore = max(0, ($demand * 2) - $supply);
        if ($gapScore > 0) {
            $skill_gaps[] = [
                'skill' => $skill,
                'demand' => $demand,
                'supply' => $supply,
                'gap_score' => $gapScore,
            ];
        }
    }

    // Sort by gap score descending and keep top 8 to show in UI
    usort($skill_gaps, function ($a, $b) {
        return $b['gap_score'] <=> $a['gap_score'];
    });
    $skill_gaps = array_slice($skill_gaps, 0, 8);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TPO Dashboard - VishwaCollab</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4caf50;
            --primary-dark: #388e3c;
            --primary-light: #81c784;
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
        
        /* Sidebar Styles */
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
            background: #f5f7fa;
            border-left-color: var(--primary-light);
            color: var(--primary);
        }
        
        .sidebar-menu a.active {
            background: #e8f5e9;
            border-left-color: var(--primary);
            color: var(--primary);
            font-weight: 600;
        }
        
        /* Main Content */
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
        
        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
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
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(76, 175, 80, 0.05));
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--gray);
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .stat-change {
            font-size: 0.85rem;
            color: var(--success);
            margin-top: 0.5rem;
        }
        
        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 0;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 1.5rem;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h3 {
            font-size: 1.25rem;
            color: var(--secondary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .card-header h3 i {
            color: var(--primary);
        }
        
        .card-body {
            padding: 1.5rem;
        }

        /* Skill Gap pills */
        .skill-gap-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .skill-gap-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .skill-gap-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .skill-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            background: #e8f5e9;
            color: #1b5e20;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .skill-metrics {
            text-align: right;
            font-size: 0.8rem;
            color: var(--gray);
        }

        .gap-bar {
            margin-top: 0.3rem;
            height: 6px;
            background: #e0e0e0;
            border-radius: 999px;
            overflow: hidden;
        }

        .gap-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff9800, #f44336);
        }
        
        /* Table Styles */
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table thead {
            background: var(--primary);
            color: white;
        }
        
        .table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table td {
            padding: 1rem;
            border-bottom: 1px solid var(--light-gray);
            color: var(--secondary);
        }
        
        .table tbody tr {
            transition: background 0.2s ease;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* Job Card */
        .job-card {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border-left: 4px solid var(--accent);
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .job-card:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow);
        }
        
        .job-card:last-child {
            margin-bottom: 0;
        }
        
        .job-card h4 {
            color: var(--secondary);
            margin-bottom: 0.75rem;
            font-size: 1.1rem;
        }
        
        .job-card p {
            color: var(--gray);
            margin: 0.25rem 0;
            font-size: 0.9rem;
        }
        
        .job-card strong {
            color: var(--secondary);
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        
        .badge-success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .badge-warning {
            background: #fff3e0;
            color: #e65100;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--light-gray);
            margin-bottom: 1rem;
        }
        
        /* Responsive */
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
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-chart-line"></i> TPO Dashboard</h2>
                <p>Training & Placement Officer</p>
            </div>
            <div class="sidebar-menu">
                <a href="tpo-dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="tpo-students.php"><i class="fas fa-user-graduate"></i> Students</a>
                <a href="tpo-companies.php"><i class="fas fa-building"></i> Companies</a>
                <a href="tpo-jobs.php"><i class="fas fa-briefcase"></i> Jobs</a>
                <a href="tpo-placements.php"><i class="fas fa-chart-line"></i> Placements</a>
                <a href="tpo-upload-placements.php"><i class="fas fa-upload"></i> Upload Placements</a>
                <a href="tpo-reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <h1>Dashboard Overview</h1>
                <a href="tpo-reports.php" class="btn btn-primary">
                    <i class="fas fa-file-download"></i> Generate Report
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-number"><?php echo $total_students; ?></div>
                            <div class="stat-label">Total Students</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-number"><?php echo $total_companies; ?></div>
                            <div class="stat-label">Registered Companies</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-number"><?php echo $total_jobs; ?></div>
                            <div class="stat-label">Active Job Postings</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-number"><?php echo $placements; ?></div>
                            <div class="stat-label">Successful Placements</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content Grid -->
            <div class="dashboard-grid">
                <!-- Top Hiring Companies (from CSV placements) -->
                <div>
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-chart-line"></i> Top Hiring Companies</h3>
                            <a href="tpo-placements.php" style="color: var(--primary); text-decoration: none; font-size: 0.9rem;">View Placements →</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($top_companies)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>No placement data from CSV has been uploaded yet.</p>
                                </div>
                            <?php else: ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Company</th>
                                            <th>Total Placements</th>
                                            <th>Batch Range</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_companies as $row): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($row['company_name']); ?></strong></td>
                                                <td><?php echo (int) $row['total_placements']; ?></td>
                                                <td>
                                                    <?php
                                                        $minBatch = $row['min_batch'] ?? '';
                                                        $maxBatch = $row['max_batch'] ?? '';
                                                        if ($minBatch && $maxBatch && $minBatch != $maxBatch) {
                                                            echo htmlspecialchars($minBatch . ' - ' . $maxBatch);
                                                        } elseif ($minBatch) {
                                                            echo htmlspecialchars($minBatch);
                                                        } else {
                                                            echo '—';
                                                        }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions & Info -->
                <div>
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <a href="tpo-upload-placements.php" class="btn btn-primary" style="width: 100%; margin-bottom: 0.75rem; justify-content: center;">
                                <i class="fas fa-upload"></i> Upload Placements
                            </a>
                            <a href="tpo-students.php" class="btn" style="width: 100%; margin-bottom: 0.75rem; justify-content: center; background: #f5f7fa; color: var(--secondary);">
                                <i class="fas fa-user-graduate"></i> Manage Students
                            </a>
                            <a href="tpo-jobs.php" class="btn" style="width: 100%; justify-content: center; background: #f5f7fa; color: var(--secondary);">
                                <i class="fas fa-briefcase"></i> View Jobs
                            </a>
                        </div>
                    </div>
                    
                    <div class="card" style="margin-top: 1.5rem;">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> System Status</h3>
                        </div>
                        <div class="card-body">
                            <div style="margin-bottom: 1rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Pending Applications</span>
                                    <strong><?php echo $pending_applications; ?></strong>
                                </div>
                                <div style="height: 8px; background: #e0e0e0; border-radius: 4px; overflow: hidden;">
                                    <div style="height: 100%; background: var(--warning); width: <?php echo min(100, ($pending_applications / max(1, $total_students)) * 100); ?>%;"></div>
                                </div>
                            </div>
                            <div style="padding-top: 1rem; border-top: 1px solid var(--light-gray);">
                                <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 0.5rem;">
                                    <i class="fas fa-check-circle" style="color: var(--success);"></i> System Operational
                                </p>
                                <p style="color: var(--gray); font-size: 0.9rem;">
                                    <i class="fas fa-database" style="color: var(--info);"></i> Database Connected
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="margin-top: 1.5rem;">
                        <div class="card-header">
                            <h3><i class="fas fa-brain"></i> Skill Gap Insights</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($skill_gaps)): ?>
                                <div class="empty-state" style="padding: 1.5rem 0;">
                                    <i class="fas fa-check-circle"></i>
                                    <p>Currently no clear skill gaps detected between jobs and students.</p>
                                </div>
                            <?php else: ?>
                                <p style="font-size: 0.9rem; color: var(--gray); margin-bottom: 1rem;">
                                    These skills are highly demanded by active jobs but under-represented among unplaced students.
                                    Use this to plan workshops, trainings, and targeted upskilling.
                                </p>
                                <ul class="skill-gap-list">
                                    <?php foreach ($skill_gaps as $gap): 
                                        $maxScore = $skill_gaps[0]['gap_score'] ?: 1;
                                        $percent = min(100, round(($gap['gap_score'] / $maxScore) * 100));
                                    ?>
                                        <li class="skill-gap-item">
                                            <div>
                                                <span class="skill-tag">
                                                    <i class="fas fa-lightbulb"></i>
                                                    <?php echo htmlspecialchars($gap['skill']); ?>
                                                </span>
                                            </div>
                                            <div class="skill-metrics">
                                                <div>Jobs needing: <strong><?php echo (int)$gap['demand']; ?></strong></div>
                                                <div>Students with skill: <strong><?php echo (int)$gap['supply']; ?></strong></div>
                                                <div class="gap-bar">
                                                    <div class="gap-bar-fill" style="width: <?php echo $percent; ?>%;"></div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
