<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole() !== 'company') {
    header("Location: login.php");
    exit();
}
require_once 'db_connect.php';

$company_user_id = (int)$_SESSION['user_id'];
$company_id = 0;
$candidates = [];
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? 'all';
$allowed_statuses = ['pending', 'interview', 'accepted', 'rejected'];

if (isDatabaseAvailable()) {
    $company_stmt = $conn->prepare("SELECT id FROM companies WHERE user_id = ? LIMIT 1");
    $company_stmt->bind_param("i", $company_user_id);
    $company_stmt->execute();
    $company_id = (int)($company_stmt->get_result()->fetch_assoc()['id'] ?? 0);
    $company_stmt->close();

    $query = "SELECT DISTINCT s.*, a.status, a.id AS application_id FROM students s 
              JOIN applications a ON s.id = a.student_id 
              JOIN jobs j ON a.job_id = j.id 
              WHERE j.company_id = ?";
    $types = "i";
    $params = [$company_id];
    if ($search !== '') {
        $query .= " AND (s.name LIKE ? OR s.email LIKE ? OR s.skills LIKE ?)";
        $types .= "sss";
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    if (in_array($status_filter, $allowed_statuses, true)) {
        $query .= " AND a.status = ?";
        $types .= "s";
        $params[] = $status_filter;
    }
    $query .= " ORDER BY a.application_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidates - Company Dashboard</title>
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
        .candidate-card { background: white; border-radius: 10px; box-shadow: var(--shadow); padding: 1.5rem; margin-bottom: 1rem; border-left: 4px solid var(--primary); }
        .toolbar { display:grid; grid-template-columns:2fr 1fr auto; gap:0.7rem; margin: 1rem 0 1.25rem; }
        .toolbar input, .toolbar select { width:100%; padding:0.65rem; border:1px solid #ddd; border-radius:6px; }
        .btn { background: var(--primary); color:#fff; border:none; border-radius:6px; padding:0.6rem 0.9rem; text-decoration:none; display:inline-block; }
        @media (max-width: 900px) { .sidebar { width:100%; position:relative; height:auto;} .main-content { margin-left:0; padding:1rem;} .toolbar { grid-template-columns:1fr; } }
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
                <a href="company-interviews.php"><i class="fas fa-calendar-alt"></i> Interviews</a>
                <a href="company-candidates.php" class="active"><i class="fas fa-users"></i> Candidates</a>
                <a href="company-notifications.php"><i class="fas fa-bell"></i> Notifications</a>
                <a href="company-settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <h1>Candidates</h1>
            <form method="GET" class="toolbar">
                <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search candidate by name, email or skills">
                <select name="status">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All statuses</option>
                    <?php foreach ($allowed_statuses as $status): ?>
                        <option value="<?php echo e($status); ?>" <?php echo $status_filter === $status ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn">Apply</button>
            </form>
            <?php if (empty($candidates)): ?>
                <div class="candidate-card">
                    <p>No candidates found.</p>
                </div>
            <?php else: ?>
                <?php foreach ($candidates as $candidate): ?>
                    <div class="candidate-card">
                        <h3><?php echo htmlspecialchars($candidate['name']); ?></h3>
                        <p><strong>Course:</strong> <?php echo htmlspecialchars($candidate['course'] ?? ''); ?></p>
                        <p><strong>Skills:</strong> <?php echo htmlspecialchars($candidate['skills'] ?? ''); ?></p>
                        <p><strong>CGPA:</strong> <?php echo htmlspecialchars($candidate['cgpa'] ?? ''); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($candidate['email'] ?? ''); ?></p>
                        <p><strong>Status:</strong> <?php echo ucfirst(e($candidate['status'] ?? 'pending')); ?></p>
                        <div style="margin-top:0.9rem;display:flex;gap:0.6rem;flex-wrap:wrap;">
                            <a class="btn" href="student_profile.php?id=<?php echo (int)$candidate['id']; ?>">View Profile</a>
                            <a class="btn" href="company-application-details.php?id=<?php echo (int)$candidate['application_id']; ?>">Application Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>






