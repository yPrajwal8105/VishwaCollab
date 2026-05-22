<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole() !== 'company') {
    header("Location: login.php");
    exit();
}
require_once 'db_connect.php';

$company_user_id = (int)$_SESSION['user_id'];
$company_id = 0;
$jobs = [];
$success = '';
$error = '';

$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? 'all';
$sort = $_GET['sort'] ?? 'latest';
$allowed_statuses = ['active', 'closed'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isDatabaseAvailable()) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request token. Please refresh and try again.';
    } else {
        $job_id = (int)($_POST['job_id'] ?? 0);
        if (isset($_POST['delete_job'])) {
            $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ? AND company_id = ?");
            $stmt->bind_param("ii", $job_id, $company_id);
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $success = 'Job deleted successfully.';
                logActivity($conn, $company_user_id, 'company', 'job_deleted', 'job', $job_id, 'Deleted by company');
                createCompanyNotification($conn, $company_user_id, 'Job Deleted', 'A job posting was removed from your account.', 'warning');
            } else {
                $error = 'Unable to delete job.';
            }
            $stmt->close();
        } elseif (isset($_POST['toggle_status'])) {
            $next_status = $_POST['next_status'] ?? 'active';
            if (!in_array($next_status, $allowed_statuses, true)) {
                $error = 'Invalid job status.';
            } else {
                $stmt = $conn->prepare("UPDATE jobs SET status = ? WHERE id = ? AND company_id = ?");
                $stmt->bind_param("sii", $next_status, $job_id, $company_id);
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $success = 'Job status updated.';
                    logActivity($conn, $company_user_id, 'company', 'job_status_updated', 'job', $job_id, 'Status changed to ' . $next_status);
                    createCompanyNotification($conn, $company_user_id, 'Job Status Updated', 'Job status changed to ' . $next_status . '.', 'info');
                } else {
                    $error = 'Unable to update job status.';
                }
                $stmt->close();
            }
        }
    }
}

if (isDatabaseAvailable()) {
    $company_stmt = $conn->prepare("SELECT id FROM companies WHERE user_id = ? LIMIT 1");
    $company_stmt->bind_param("i", $company_user_id);
    $company_stmt->execute();
    $company_id = (int)($company_stmt->get_result()->fetch_assoc()['id'] ?? 0);
    $company_stmt->close();
    if ($company_id <= 0) {
        $error = 'Company profile not found. Please complete company registration first.';
    }

    $query = "SELECT * FROM jobs WHERE company_id = ?";
    $types = "i";
    $params = [$company_id];

    if ($search !== '') {
        $query .= " AND (title LIKE ? OR location LIKE ? OR required_skills LIKE ?)";
        $types .= "sss";
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    if (in_array($status_filter, $allowed_statuses, true)) {
        $query .= " AND status = ?";
        $types .= "s";
        $params[] = $status_filter;
    }

    $query .= $sort === 'oldest' ? " ORDER BY posted_at ASC" : " ORDER BY posted_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs - Company Dashboard</title>
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
        .job-card { background: white; border-radius: 10px; box-shadow: var(--shadow); padding: 1.5rem; margin-bottom: 1rem; border-left: 4px solid var(--primary); }
        .btn { padding: 0.7rem 1.8rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; font-size: 1rem; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--primary); color: white; }
        .status-badge { padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .status-active { background: #e6f4ea; color: #137333; }
        .status-closed { background: #fce8e6; color: #c5221f; }
        .toolbar { display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 0.75rem; margin: 1rem 0 1.5rem; }
        .toolbar input, .toolbar select { width: 100%; padding: 0.7rem; border: 1px solid #ddd; border-radius: 6px; }
        .actions { margin-top: 1rem; display: flex; gap: 0.6rem; flex-wrap: wrap; }
        .btn-danger { background: #d93025; color: #fff; }
        .btn-muted { background: #f1f3f4; color: #202124; }
        .alert { margin-bottom: 1rem; padding: 0.8rem 1rem; border-radius: 8px; }
        .alert-success { background: #e6f4ea; color: #137333; }
        .alert-error { background: #fce8e6; color: #c5221f; }
        @media (max-width: 900px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content { margin-left: 0; padding: 1rem; }
            .toolbar { grid-template-columns: 1fr; }
        }
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
                <a href="company-jobs.php" class="active"><i class="fas fa-briefcase"></i> Manage Jobs</a>
                <a href="company-post-job.php"><i class="fas fa-plus"></i> Post New Job</a>
                <a href="company-applications.php"><i class="fas fa-file-alt"></i> Applications</a>
                <a href="company-interviews.php"><i class="fas fa-calendar-alt"></i> Interviews</a>
                <a href="company-candidates.php"><i class="fas fa-users"></i> Candidates</a>
                <a href="company-notifications.php"><i class="fas fa-bell"></i> Notifications</a>
                <a href="company-settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>Manage Jobs</h1>
                <a href="company-post-job.php" class="btn btn-primary"><i class="fas fa-plus"></i> Post New Job</a>
            </div>

            <?php if ($success): ?><div class="alert alert-success"><?php echo e($success); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>

            <form method="GET" class="toolbar">
                <input type="text" name="search" placeholder="Search title, location, or skills" value="<?php echo e($search); ?>">
                <select name="status">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All statuses</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                </select>
                <select name="sort">
                    <option value="latest" <?php echo $sort === 'latest' ? 'selected' : ''; ?>>Newest first</option>
                    <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest first</option>
                </select>
                <button type="submit" class="btn btn-primary">Apply</button>
            </form>

            <?php if (empty($jobs)): ?>
                <div class="job-card">
                    <p>No jobs posted yet. <a href="company-post-job.php" style="color: var(--primary);">Post your first job</a> to get started!</p>
                </div>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <div class="job-card">
                        <h3><?php echo e($job['title']); ?></h3>
                        <p><strong>Location:</strong> <?php echo e($job['location'] ?? ''); ?></p>
                        <p><strong>Salary:</strong> <?php echo e($job['salary'] ?? ''); ?></p>
                        <p><strong>Status:</strong> <span class="status-badge status-<?php echo e($job['status']); ?>"><?php echo ucfirst(e($job['status'])); ?></span></p>
                        <p><strong>Posted:</strong> <?php echo date('M j, Y', strtotime($job['posted_at'])); ?></p>
                        <div class="actions">
                            <a href="job_details.php?id=<?php echo (int)$job['id']; ?>" class="btn btn-muted">View</a>
                            <a href="company-post-job.php?edit=<?php echo (int)$job['id']; ?>" class="btn btn-primary">Edit</a>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                                <input type="hidden" name="job_id" value="<?php echo (int)$job['id']; ?>">
                                <input type="hidden" name="next_status" value="<?php echo $job['status'] === 'active' ? 'closed' : 'active'; ?>">
                                <button type="submit" name="toggle_status" class="btn btn-muted">
                                    <?php echo $job['status'] === 'active' ? 'Close Job' : 'Reopen Job'; ?>
                                </button>
                            </form>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this job permanently?');">
                                <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                                <input type="hidden" name="job_id" value="<?php echo (int)$job['id']; ?>">
                                <button type="submit" name="delete_job" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>






