<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole() !== 'company') {
    header("Location: login.php");
    exit();
}
require_once 'db_connect.php';

$company_user_id = (int)$_SESSION['user_id'];
$company_id = 0;
$applications = [];
$error_message = '';
$success_message = $_GET['updated'] ?? '' ? 'Application status updated successfully.' : '';

$allowed_statuses = ['pending', 'interview', 'accepted', 'rejected'];
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? 'all';
$sort = $_GET['sort'] ?? 'latest';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;
$total_rows = 0;
$total_pages = 1;
$sort_sql = $sort === 'oldest' ? 'a.application_date ASC' : 'a.application_date DESC';

// Resolve company_id as early as possible (used by both reads and writes).
if (isDatabaseAvailable()) {
    $company_stmt = $conn->prepare("SELECT id FROM companies WHERE user_id = ? LIMIT 1");
    $company_stmt->bind_param("i", $company_user_id);
    $company_stmt->execute();
    $company_id = (int)($company_stmt->get_result()->fetch_assoc()['id'] ?? 0);
    $company_stmt->close();
    if ($company_id <= 0) {
        $error_message = 'Company profile not found. Please complete company registration first.';
    }
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $app_id = (int)$_POST['application_id'];
    $new_status = $_POST['status'] ?? 'pending';

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error_message = 'Invalid request token. Please refresh and try again.';
    } elseif (!in_array($new_status, $allowed_statuses, true)) {
        $error_message = 'Invalid status selected.';
    }

    if (isDatabaseAvailable()) {
        // Ensure companies can only update applications on their own jobs.
        $authorize_stmt = $conn->prepare(
            "SELECT a.id
             FROM applications a
             JOIN jobs j ON a.job_id = j.id
             WHERE a.id = ? AND j.company_id = ? LIMIT 1"
        );
        $authorize_stmt->bind_param("ii", $app_id, $company_id);
        $authorize_stmt->execute();
        $authorized = $authorize_stmt->get_result()->fetch_assoc();
        $authorize_stmt->close();

        if (!$error_message && $authorized) {
            $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $app_id);
            if ($stmt->execute()) {
                logActivity(
                    $conn,
                    $company_user_id,
                    'company',
                    'application_status_updated',
                    'application',
                    $app_id,
                    'Status changed to ' . $new_status
                );
                createCompanyNotification($conn, $company_user_id, 'Application Updated', 'Application #' . $app_id . ' moved to ' . $new_status . '.', 'info');
                $query_params = $_GET;
                $query_params['updated'] = 1;
                header("Location: company-applications.php?" . http_build_query($query_params));
                exit();
            }
            $stmt->close();
            $error_message = 'Unable to update status right now.';
        } elseif (!$error_message) {
            $error_message = 'You are not authorized to update this application.';
        }
    }
}

if (isDatabaseAvailable() && $company_id > 0) {
    $count_sql = "SELECT COUNT(*) AS total
                  FROM applications a
                  JOIN jobs j ON a.job_id = j.id
                  JOIN students s ON a.student_id = s.id
                  WHERE j.company_id = ?";
    $count_types = "i";
    $count_params = [$company_id];

    if ($search !== '') {
        $count_sql .= " AND (s.name LIKE ? OR s.email LIKE ? OR j.title LIKE ?)";
        $count_types .= "sss";
        $search_like = '%' . $search . '%';
        $count_params[] = $search_like;
        $count_params[] = $search_like;
        $count_params[] = $search_like;
    }
    if (in_array($status_filter, $allowed_statuses, true)) {
        $count_sql .= " AND a.status = ?";
        $count_types .= "s";
        $count_params[] = $status_filter;
    }

    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param($count_types, ...$count_params);
    $count_stmt->execute();
    $total_rows = (int)($count_stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $count_stmt->close();

    $total_pages = max(1, (int)ceil($total_rows / $per_page));
    $page = min($page, $total_pages);
    $offset = ($page - 1) * $per_page;

    $query = "SELECT a.*, j.title, s.name AS student_name, s.email AS student_email
              FROM applications a
              JOIN jobs j ON a.job_id = j.id
              JOIN students s ON a.student_id = s.id
              WHERE j.company_id = ?";
    $types = "i";
    $params = [$company_id];

    if ($search !== '') {
        $query .= " AND (s.name LIKE ? OR s.email LIKE ? OR j.title LIKE ?)";
        $types .= "sss";
        $search_like = '%' . $search . '%';
        $params[] = $search_like;
        $params[] = $search_like;
        $params[] = $search_like;
    }
    if (in_array($status_filter, $allowed_statuses, true)) {
        $query .= " AND a.status = ?";
        $types .= "s";
        $params[] = $status_filter;
    }

    $query .= " ORDER BY {$sort_sql} LIMIT ? OFFSET ?";
    $types .= "ii";
    $params[] = $per_page;
    $params[] = $offset;

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=company_applications_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Application ID', 'Candidate Name', 'Candidate Email', 'Job Title', 'Applied Date', 'Status']);
        foreach ($applications as $row) {
            fputcsv($output, [
                $row['id'],
                $row['student_name'],
                $row['student_email'],
                $row['title'],
                $row['application_date'],
                $row['status']
            ]);
        }
        fclose($output);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications - Company Dashboard</title>
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
        .table { width: 100%; background: white; border-radius: 10px; box-shadow: var(--shadow); overflow: hidden; }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid #e0e0e0; }
        .table th { background: var(--primary); color: white; font-weight: 600; }
        .status-badge { padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .status-pending { background: #fef7e0; color: #b06000; }
        .status-approved { background: #e6f4ea; color: #137333; }
        .status-rejected { background: #fce8e6; color: #c5221f; }
        .status-interview { background: #e8f0fe; color: var(--primary); }
        .btn { padding: 0.5rem 1rem; border-radius: 4px; font-weight: 600; cursor: pointer; border: none; font-size: 0.9rem; background: var(--primary); color: white; }
        select { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
        .toolbar { display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 0.75rem; margin: 1rem 0 1.25rem; }
        .toolbar input, .toolbar select { width: 100%; padding: 0.65rem; border: 1px solid #ddd; border-radius: 6px; }
        .toolbar .btn { white-space: nowrap; }
        .flash { margin: 0.75rem 0 1rem; padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.92rem; }
        .flash-success { background: #e6f4ea; color: #137333; }
        .flash-error { background: #fce8e6; color: #c5221f; }
        .pagination { margin-top: 1rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: 0.45rem 0.75rem; border-radius: 6px; text-decoration: none; border: 1px solid #ddd; color: #202124; }
        .pagination .active { background: var(--primary); color: #fff; border-color: var(--primary); }
        @media (max-width: 900px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content { margin-left: 0; padding: 1rem; }
            .toolbar { grid-template-columns: 1fr; }
            .table { display: block; overflow-x: auto; }
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
                <a href="company-jobs.php"><i class="fas fa-briefcase"></i> Manage Jobs</a>
                <a href="company-post-job.php"><i class="fas fa-plus"></i> Post New Job</a>
                <a href="company-applications.php" class="active"><i class="fas fa-file-alt"></i> Applications</a>
                <a href="company-interviews.php"><i class="fas fa-calendar-alt"></i> Interviews</a>
                <a href="company-candidates.php"><i class="fas fa-users"></i> Candidates</a>
                <a href="company-notifications.php"><i class="fas fa-bell"></i> Notifications</a>
                <a href="company-settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <h1>Job Applications</h1>
            <?php if ($success_message): ?>
                <div class="flash flash-success"><?php echo e($success_message); ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="flash flash-error"><?php echo e($error_message); ?></div>
            <?php endif; ?>

            <form method="GET" class="toolbar">
                <input type="text" name="search" placeholder="Search by candidate, email, or job title" value="<?php echo e($search); ?>">
                <select name="status">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All statuses</option>
                    <?php foreach ($allowed_statuses as $status): ?>
                        <option value="<?php echo e($status); ?>" <?php echo $status_filter === $status ? 'selected' : ''; ?>>
                            <?php echo ucfirst($status); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="sort">
                    <option value="latest" <?php echo $sort === 'latest' ? 'selected' : ''; ?>>Newest first</option>
                    <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest first</option>
                </select>
                <div style="display:flex;gap:0.5rem;">
                    <button type="submit" class="btn">Apply</button>
                    <?php
                        $export_params = $_GET;
                        $export_params['export'] = 'csv';
                    ?>
                    <a class="btn" href="<?php echo e('company-applications.php?' . http_build_query($export_params)); ?>" style="text-decoration:none;display:inline-flex;align-items:center;">Export CSV</a>
                </div>
            </form>

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
                    <?php if (empty($applications)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 2rem;">No applications received yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($app['student_name']); ?><br><small><?php echo htmlspecialchars($app['student_email']); ?></small></td>
                                <td><?php echo htmlspecialchars($app['title']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($app['application_date'])); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                                        <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $app['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="interview" <?php echo $app['status'] == 'interview' ? 'selected' : ''; ?>>Interview</option>
                                            <option value="accepted" <?php echo $app['status'] == 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                                            <option value="rejected" <?php echo $app['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td><a href="company-application-details.php?id=<?php echo (int)$app['id']; ?>" class="btn">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_rows > 0): ?>
                <div class="pagination">
                    <?php
                        $params = $_GET;
                        for ($i = 1; $i <= $total_pages; $i++):
                            $params['page'] = $i;
                            $href = 'company-applications.php?' . http_build_query($params);
                    ?>
                        <?php if ($i === $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php echo e($href); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>






