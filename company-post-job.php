<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole() !== 'company') {
    header("Location: login.php");
    exit();
}
require_once 'db_connect.php';

$company_user_id = (int)$_SESSION['user_id'];
$company_id = 0;
$success = '';
$error = '';
$edit_job = null;
$is_edit_mode = false;

if (isDatabaseAvailable()) {
    $company_stmt = $conn->prepare("SELECT id FROM companies WHERE user_id = ? LIMIT 1");
    $company_stmt->bind_param("i", $company_user_id);
    $company_stmt->execute();
    $company_id = (int)($company_stmt->get_result()->fetch_assoc()['id'] ?? 0);
    $company_stmt->close();

    if ($company_id <= 0) {
        $error = 'Company profile not found. Please complete company registration first.';
    }
}

if (isDatabaseAvailable() && $company_id > 0 && isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    if ($edit_id > 0) {
        $edit_stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ? AND company_id = ? LIMIT 1");
        $edit_stmt->bind_param("ii", $edit_id, $company_id);
        $edit_stmt->execute();
        $edit_job = $edit_stmt->get_result()->fetch_assoc();
        $edit_stmt->close();
        $is_edit_mode = (bool)$edit_job;
        if (!$is_edit_mode) {
            $error = 'Job not found for editing.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_job'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = "Invalid request token. Please refresh and try again.";
    }
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $requirements = $_POST['requirements'] ?? '';
    $required_skills = $_POST['required_skills'] ?? '';
    $location = $_POST['location'] ?? '';
    $salary = $_POST['salary'] ?? '';
    $status = 'active';

    if (empty($error) && (empty($title) || empty($description))) {
        $error = "Title and description are required.";
    } elseif (empty($error) && isDatabaseAvailable()) {
        $edit_id = (int)($_POST['edit_job_id'] ?? 0);
        if ($edit_id > 0) {
            $stmt = $conn->prepare("UPDATE jobs SET title = ?, description = ?, requirements = ?, required_skills = ?, location = ?, salary = ? WHERE id = ? AND company_id = ?");
            $stmt->bind_param("ssssssii", $title, $description, $requirements, $required_skills, $location, $salary, $edit_id, $company_id);
            if ($stmt->execute() && $stmt->affected_rows >= 0) {
                $success = "Job updated successfully!";
                logActivity($conn, $company_user_id, 'company', 'job_updated', 'job', $edit_id, 'Job updated from form');
                createCompanyNotification($conn, $company_user_id, 'Job Updated', 'Your job posting has been updated successfully.', 'success');
            } else {
                $error = "Failed to update job. Please try again.";
            }
            $stmt->close();
            header("Location: company-jobs.php");
            exit();
        } else {
            $stmt = $conn->prepare("INSERT INTO jobs (company_id, title, description, requirements, required_skills, location, salary, status, posted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("isssssss", $company_id, $title, $description, $requirements, $required_skills, $location, $salary, $status);
            if ($stmt->execute()) {
                $success = "Job posted successfully!";
                logActivity($conn, $company_user_id, 'company', 'job_created', 'job', (int)$stmt->insert_id, 'New job posted');
                createCompanyNotification($conn, $company_user_id, 'Job Created', 'New job "' . $title . '" was posted.', 'success');
                $_POST = array(); // Clear form
            } else {
                $error = "Failed to post job. Please try again.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post New Job - Company Dashboard</title>
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
        .form-card { background: white; border-radius: 10px; box-shadow: var(--shadow); padding: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #202124; }
        .form-control { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; }
        .form-control:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(15,118,110,0.2); }
        .btn { padding: 0.7rem 1.8rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; font-size: 1rem; background: var(--primary); color: white; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #2e7d32; }
        .alert-error { background: #ffebee; color: #c62828; border-left: 4px solid #c62828; }
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
                <a href="company-post-job.php" class="active"><i class="fas fa-plus"></i> Post New Job</a>
                <a href="company-applications.php"><i class="fas fa-file-alt"></i> Applications</a>
                <a href="company-interviews.php"><i class="fas fa-calendar-alt"></i> Interviews</a>
                <a href="company-candidates.php"><i class="fas fa-users"></i> Candidates</a>
                <a href="company-notifications.php"><i class="fas fa-bell"></i> Notifications</a>
                <a href="company-settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <h1><?php echo $is_edit_mode ? 'Edit Job' : 'Post New Job'; ?></h1>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo e($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo e($error); ?></div>
            <?php endif; ?>
            <div class="form-card">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                    <?php if ($is_edit_mode): ?>
                        <input type="hidden" name="edit_job_id" value="<?php echo (int)$edit_job['id']; ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Job Title *</label>
                        <input type="text" name="title" class="form-control" required value="<?php echo e($_POST['title'] ?? $edit_job['title'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Description *</label>
                        <textarea name="description" class="form-control" rows="5" required><?php echo e($_POST['description'] ?? $edit_job['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Requirements</label>
                        <textarea name="requirements" class="form-control" rows="4"><?php echo e($_POST['requirements'] ?? $edit_job['requirements'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Required Skills</label>
                        <input type="text" name="required_skills" class="form-control" placeholder="e.g., PHP, JavaScript, MySQL" value="<?php echo e($_POST['required_skills'] ?? $edit_job['required_skills'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" class="form-control" value="<?php echo e($_POST['location'] ?? $edit_job['location'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Salary</label>
                        <input type="text" name="salary" class="form-control" placeholder="e.g., $50,000 - $70,000" value="<?php echo e($_POST['salary'] ?? $edit_job['salary'] ?? ''); ?>">
                    </div>
                    <button type="submit" name="post_job" class="btn"><?php echo $is_edit_mode ? 'Update Job' : 'Post Job'; ?></button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>






