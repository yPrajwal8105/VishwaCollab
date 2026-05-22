<?php
require_once 'config.php';
require_once 'db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$job = null;
$applied = false;
$student_profile_id = 0;

if ($id > 0 && isDatabaseAvailable()) {
    $stmt = $conn->prepare("SELECT j.*, c.name AS company_name, c.id AS company_id FROM jobs j LEFT JOIN companies c ON c.id = j.company_id WHERE j.id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $job = $stmt->get_result()->fetch_assoc();
    
    // Resolve student profile id from logged-in user id.
    if (isLoggedIn() && getUserRole() === 'student') {
        $user_id = (int)$_SESSION['user_id'];
        $student_stmt = $conn->prepare("SELECT id FROM students WHERE user_id = ? LIMIT 1");
        $student_stmt->bind_param("i", $user_id);
        $student_stmt->execute();
        $student_profile_id = (int)($student_stmt->get_result()->fetch_assoc()['id'] ?? 0);
        $student_stmt->close();

        // Check if user has already applied
        $check_stmt = $conn->prepare("SELECT id FROM applications WHERE job_id = ? AND student_id = ?");
        $check_stmt->bind_param("ii", $id, $student_profile_id);
        $check_stmt->execute();
        $applied = $check_stmt->get_result()->num_rows > 0;
        $check_stmt->close();
    }
}

// Handle job application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_job']) && isLoggedIn() && getUserRole() === 'student') {
    if (isDatabaseAvailable() && $job && !$applied) {
        $user_id = (int)$_SESSION['user_id'];
        $student_stmt = $conn->prepare("SELECT id FROM students WHERE user_id = ? LIMIT 1");
        $student_stmt->bind_param("i", $user_id);
        $student_stmt->execute();
        $student_profile_id = (int)($student_stmt->get_result()->fetch_assoc()['id'] ?? 0);
        $student_stmt->close();

        if ($student_profile_id <= 0) {
            $error = "Student profile not found. Please complete your profile and try again.";
        } else {
            $stmt = $conn->prepare("INSERT INTO applications (job_id, student_id, application_date, status) VALUES (?, ?, NOW(), 'pending')");
            $stmt->bind_param("ii", $id, $student_profile_id);
            if ($stmt->execute()) {
                $success = "Application submitted successfully!";
                $applied = true;
            } else {
                $error = "Failed to submit application. Please try again.";
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
    <title>Job Details - VishwaCollab</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
        .job-container { max-width: 1000px; margin: 2rem auto; padding: 0 1rem; }
        .job-header { background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 2rem; margin-bottom: 2rem; }
        .job-title { font-size: 2rem; color: #202124; margin-bottom: 1rem; }
        .job-meta { display: flex; flex-wrap: wrap; gap: 2rem; margin: 1rem 0; }
        .meta-item { display: flex; align-items: center; gap: 0.5rem; color: #5f6368; }
        .job-content { background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 2rem; margin-bottom: 2rem; }
        .job-section { margin-bottom: 2rem; }
        .job-section h3 { color: #1a73e8; margin-bottom: 1rem; border-bottom: 2px solid #e0e0e0; padding-bottom: 0.5rem; }
        .btn { padding: 0.7rem 1.8rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; font-size: 1rem; text-decoration: none; display: inline-block; }
        .btn-primary { background: #1a73e8; color: white; }
        .btn-primary:hover { background: #0d47a1; }
        .btn-success { background: #34a853; color: white; }
        .btn-disabled { background: #ccc; color: #666; cursor: not-allowed; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #2e7d32; }
        .alert-error { background: #ffebee; color: #c62828; border-left: 4px solid #c62828; }
    </style>
</head>
<body>
    <header style="background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15); padding: 1rem 5%;">
        <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1400px; margin: 0 auto;">
            <a href="index.php" style="font-size: 2rem; color: #202124; text-decoration: none; font-weight: 700;"><span style="color: #1a73e8;">Vishwa</span>Collab</a>
            <nav>
                <a href="index.php" class="btn btn-primary">Back to Home</a>
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo getUserRole(); ?>-dashboard.php" class="btn" style="background: #f0f0f0; color: #202124; margin-left: 0.5rem;">Dashboard</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="job-container">
        <?php if ($job): ?>
            <div class="job-header">
                <h1 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h1>
                <div class="job-meta">
                    <div class="meta-item"><i class="fas fa-building"></i> <strong><?php echo htmlspecialchars($job['company_name'] ?? 'Company'); ?></strong></div>
                    <div class="meta-item"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location'] ?? 'Not specified'); ?></div>
                    <div class="meta-item"><i class="fas fa-dollar-sign"></i> <?php echo htmlspecialchars($job['salary'] ?? 'Not specified'); ?></div>
                    <div class="meta-item"><i class="fas fa-calendar"></i> Posted: <?php echo date('M j, Y', strtotime($job['posted_at'] ?? 'now')); ?></div>
                </div>
                <?php if (isLoggedIn() && getUserRole() === 'student'): ?>
                    <div style="margin-top: 1.5rem;">
                        <?php if ($applied): ?>
                            <button class="btn btn-success" disabled><i class="fas fa-check"></i> Already Applied</button>
                        <?php else: ?>
                            <form method="POST" style="display: inline;">
                                <button type="submit" name="apply_job" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Apply Now</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php elseif (!isLoggedIn()): ?>
                    <div style="margin-top: 1.5rem;">
                        <a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Login to Apply</a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="job-content">
                <div class="job-section">
                    <h3><i class="fas fa-info-circle"></i> Job Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($job['description'] ?? 'No description available.')); ?></p>
                </div>

                <div class="job-section">
                    <h3><i class="fas fa-list-check"></i> Requirements</h3>
                    <p><?php echo nl2br(htmlspecialchars($job['requirements'] ?? $job['required_skills'] ?? 'No requirements specified.')); ?></p>
                </div>

                <?php if (!empty($job['required_skills'])): ?>
                <div class="job-section">
                    <h3><i class="fas fa-tools"></i> Required Skills</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <?php 
                        $skills = explode(',', $job['required_skills']);
                        foreach ($skills as $skill): 
                        ?>
                            <span style="background: #e8f0fe; color: #1a73e8; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.9rem;"><?php echo trim(htmlspecialchars($skill)); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="job-content">
                <h2>Job Not Found</h2>
                <p>The job you're looking for doesn't exist or has been removed.</p>
                <a href="index.php" class="btn btn-primary">Back to Home</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>


