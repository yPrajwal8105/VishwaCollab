<?php
require_once 'config.php';
require_once 'db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_manage_mode = ($id === 0 && isLoggedIn() && getUserRole() === 'company');
$company = null;
$jobs = [];
$success = '';
$error = '';

if ($is_manage_mode) {
    $user_id = (int)$_SESSION['user_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_company_profile']) && isDatabaseAvailable()) {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $error = 'Invalid request token. Please refresh and try again.';
        } else {
            $name = trim($_POST['name'] ?? '');
            $industry = trim($_POST['industry'] ?? '');
            $location = trim($_POST['location'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $website = trim($_POST['website'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($name === '') {
                $error = 'Company name is required.';
            } else {
                $stmt = $conn->prepare("UPDATE companies SET name = ?, industry = ?, location = ?, phone = ?, website = ?, description = ? WHERE user_id = ?");
                $stmt->bind_param("ssssssi", $name, $industry, $location, $phone, $website, $description, $user_id);
                if ($stmt->execute() && $stmt->affected_rows >= 0) {
                    $success = 'Profile updated successfully.';
                    $_SESSION['name'] = $name;
                    logActivity($conn, $user_id, 'company', 'company_profile_updated', 'company', null, 'Updated company profile');
                } else {
                    $error = 'Failed to update profile.';
                }
                $stmt->close();
            }
        }
    }

    if (isDatabaseAvailable()) {
        $stmt = $conn->prepare("SELECT * FROM companies WHERE user_id = ? LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $company = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($company) {
            $id = (int)$company['id'];
            $jobs_query = $conn->prepare("SELECT * FROM jobs WHERE company_id = ? ORDER BY posted_at DESC LIMIT 5");
            $jobs_query->bind_param("i", $id);
            $jobs_query->execute();
            $jobs = $jobs_query->get_result()->fetch_all(MYSQLI_ASSOC);
            $jobs_query->close();
        }
    }
} elseif ($id > 0 && isDatabaseAvailable()) {
    $stmt = $conn->prepare("SELECT * FROM companies WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $company = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($company) {
        $jobs_query = $conn->prepare("SELECT * FROM jobs WHERE company_id = ? ORDER BY posted_at DESC LIMIT 5");
        $jobs_query->bind_param("i", $id);
        $jobs_query->execute();
        $jobs = $jobs_query->get_result()->fetch_all(MYSQLI_ASSOC);
        $jobs_query->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_manage_mode ? 'Manage Company Profile' : 'Company Profile'; ?> - VishwaCollab</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
        .profile-container { max-width: 1000px; margin: 2rem auto; padding: 0 1rem; }
        .profile-header { background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 2rem; margin-bottom: 2rem; }
        .profile-header h1 { color: #202124; margin-bottom: 1rem; }
        .profile-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem; }
        .info-item { padding: 0.5rem 0; }
        .info-item strong { color: #1a73e8; }
        .jobs-section { background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 2rem; margin-bottom: 1rem; }
        .job-card { background: #f8f9ff; border-left: 4px solid #1a73e8; padding: 1rem; margin-bottom: 1rem; border-radius: 6px; }
        .btn { padding: 0.7rem 1.8rem; border-radius: 6px; font-weight: 600; text-decoration: none; display: inline-block; background: #1a73e8; color: white; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display:block; margin-bottom:0.4rem; font-weight:600; }
        .form-control { width:100%; padding:0.75rem; border:1px solid #ddd; border-radius:6px; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .alert { padding: 0.9rem 1rem; border-radius:8px; margin-bottom:1rem; }
        .alert-success { background:#e8f5e9; color:#2e7d32; border-left:4px solid #2e7d32; }
        .alert-error { background:#ffebee; color:#c62828; border-left:4px solid #c62828; }
        @media (max-width: 800px) { .grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
    <header style="background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15); padding: 1rem 5%;">
        <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1400px; margin: 0 auto;">
            <a href="index.php" style="font-size: 2rem; color: #202124; text-decoration: none; font-weight: 700;"><span style="color: #1a73e8;">Vishwa</span>Collab</a>
            <nav>
                <?php if ($is_manage_mode): ?>
                    <a href="company-dashboard.php" class="btn">Back to Dashboard</a>
                <?php else: ?>
                    <a href="index.php" class="btn">Back to Home</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="profile-container">
        <?php if ($company): ?>
            <?php if ($success): ?><div class="alert alert-success"><?php echo e($success); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>

            <div class="profile-header">
                <h1><i class="fas fa-building"></i> <?php echo htmlspecialchars($company['name']); ?></h1>
                <div class="profile-info">
                    <div class="info-item"><strong>Industry:</strong> <?php echo htmlspecialchars($company['industry'] ?? 'Not specified'); ?></div>
                    <div class="info-item"><strong>Location:</strong> <?php echo htmlspecialchars($company['location'] ?? 'Not specified'); ?></div>
                    <div class="info-item"><strong>Email:</strong> <?php echo htmlspecialchars($company['email'] ?? ''); ?></div>
                    <?php if (!empty($company['phone'])): ?>
                        <div class="info-item"><strong>Phone:</strong> <?php echo htmlspecialchars($company['phone']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($company['website'])): ?>
                        <div class="info-item"><strong>Website:</strong> <a href="<?php echo htmlspecialchars($company['website']); ?>" target="_blank"><?php echo htmlspecialchars($company['website']); ?></a></div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($company['description'])): ?>
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e0e0e0;">
                        <h3>About</h3>
                        <p><?php echo nl2br(htmlspecialchars($company['description'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($is_manage_mode): ?>
                <div class="jobs-section">
                    <h2>Edit Company Profile</h2>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                        <div class="grid">
                            <div class="form-group">
                                <label>Company Name *</label>
                                <input type="text" class="form-control" name="name" required value="<?php echo e($company['name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Industry</label>
                                <input type="text" class="form-control" name="industry" value="<?php echo e($company['industry'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" class="form-control" name="location" value="<?php echo e($company['location'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" class="form-control" name="phone" value="<?php echo e($company['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label>Website</label>
                                <input type="url" class="form-control" name="website" value="<?php echo e($company['website'] ?? ''); ?>">
                            </div>
                            <div class="form-group" style="grid-column: 1 / -1;">
                                <label>Description</label>
                                <textarea class="form-control" rows="4" name="description"><?php echo e($company['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <button type="submit" name="save_company_profile" class="btn">Save Profile</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (!empty($jobs)): ?>
                <div class="jobs-section">
                    <h2>Recent Job Postings</h2>
                    <?php foreach ($jobs as $job): ?>
                        <div class="job-card">
                            <h3><a href="job_details.php?id=<?php echo (int)$job['id']; ?>" style="color: #1a73e8; text-decoration: none;"><?php echo htmlspecialchars($job['title']); ?></a></h3>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location'] ?? ''); ?></p>
                            <p><strong>Posted:</strong> <?php echo date('M j, Y', strtotime($job['posted_at'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="profile-header">
                <h2><?php echo $is_manage_mode ? 'Company Profile Not Available' : 'Company Not Found'; ?></h2>
                <p><?php echo $is_manage_mode ? 'No company profile is linked to this account yet.' : "The company you're looking for doesn't exist."; ?></p>
                <a href="<?php echo $is_manage_mode ? 'company-dashboard.php' : 'index.php'; ?>" class="btn">
                    <?php echo $is_manage_mode ? 'Back to Dashboard' : 'Back to Home'; ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>






