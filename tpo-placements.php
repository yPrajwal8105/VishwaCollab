<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole() !== 'tpo') {
    header("Location: login.php");
    exit();
}
require_once 'db_connect.php';

$placements = [];
$alumni_placements = [];

if (isDatabaseAvailable()) {
    // 1) Official senior placements imported via CSV (alumni_placements table)
    // Create table if it doesn't exist (defensive)
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

    // Fetch alumni placements (from CSV / manual upload)
    if ($result = $conn->query("SELECT * FROM alumni_placements ORDER BY placement_date DESC, id DESC")) {
        $alumni_placements = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
    }

    // 2) Legacy placements from applications/jobs tables (for backward compatibility)
    $query = "SELECT a.*, j.title, s.name AS student_name, c.name AS company_name FROM applications a 
              JOIN jobs j ON a.job_id = j.id 
              JOIN students s ON a.student_id = s.user_id 
              LEFT JOIN companies c ON c.id = j.company_id 
              WHERE a.status = 'accepted' ORDER BY a.application_date DESC";
    if ($result = $conn->query($query)) {
        $placements = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placements - TPO Dashboard</title>
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
        .table { width: 100%; background: white; border-radius: 10px; box-shadow: var(--shadow); overflow: hidden; }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid #e0e0e0; }
        .table th { background: var(--primary); color: white; font-weight: 600; }
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
                <a href="tpo-placements.php" class="active"><i class="fas fa-chart-line"></i> Placements</a>
                <a href="tpo-reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <h1>Successful Placements</h1>

            <?php if (!empty($alumni_placements)): ?>
                <h2 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.2rem;">Senior / Official Placements (from CSV)</h2>
                <table class="table" style="margin-bottom: 2rem;">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Company</th>
                            <th>Batch</th>
                            <th>LinkedIn Profile</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alumni_placements as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($p['company_name']); ?></td>
                                <td>
                                    <?php
                                        // Prefer explicit batch_year; if missing, fall back to anything stored in course column
                                        $batch = $p['batch_year'] ?? '';
                                        if ($batch === '' || $batch === null) {
                                            $batch = $p['course'] ?? '';
                                        }
                                        echo htmlspecialchars((string)$batch);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        $rawSkills = trim($p['skills'] ?? '');
                                        $isLinkedinUrl = !empty($rawSkills) && (strpos(strtolower($rawSkills), 'linkedin.com') !== false || preg_match('~^https?://~i', $rawSkills));
                                        
                                        if ($isLinkedinUrl) {
                                            $linkedin = $rawSkills;
                                            if (!preg_match('~^https?://~i', $linkedin)) {
                                                $linkedin = 'https://' . $linkedin;
                                            }
                                        } else {
                                            $fullName = trim($p['student_name']);
                                            $parts = preg_split('/\s+/', $fullName);
                                            $searchName = (count($parts) >= 2) ? $parts[0] . ' ' . $parts[count($parts) - 1] : $fullName;
                                            $searchQuery = '!ducky site:linkedin.com/in ' . $searchName . ' ' . trim($p['company_name']);
                                            $linkedin = "https://duckduckgo.com/?q=" . urlencode($searchQuery);
                                        }
                                    ?>
                                    <a href="<?php echo htmlspecialchars($linkedin); ?>" target="_blank" rel="noopener noreferrer" style="color: #0077b5; text-decoration: none; font-weight: 600;">
                                        <i class="fab fa-linkedin"></i> View Profile
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h2 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.2rem;">On‑Portal Applications</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Company</th>
                        <th>Job Title</th>
                        <th>Placement Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($placements) && empty($alumni_placements)): ?>
                        <tr><td colspan="4" style="text-align: center; padding: 2rem;">No placements recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($placements as $placement): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($placement['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($placement['company_name'] ?? 'Company'); ?></td>
                                <td><?php echo htmlspecialchars($placement['title']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($placement['application_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>






