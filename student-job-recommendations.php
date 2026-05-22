<?php
require_once 'config.php';
require_once 'db_connect.php';

if (!isLoggedIn() || getUserRole() !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$student = null;
$recommendedJobs = [];

function ensureJobConn()
{
    global $conn;
    if ($conn instanceof mysqli && @$conn->ping()) {
        return $conn;
    }
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8');
    return $conn;
}

function tableExistsJR($table)
{
    global $conn;
    if (!$conn) return false;
    $safe = $conn->real_escape_string($table);
    $result = $conn->query("SHOW TABLES LIKE '{$safe}'");
    $exists = $result && $result->num_rows > 0;
    $result && $result->free();
    return $exists;
}

try {
    ensureJobConn();
} catch (Exception $ex) {
    $error = $ex->getMessage();
}

if (!$error && tableExistsJR('students')) {
    // Note: older schemas may not have a 'location_preference' column, so we only select fields that are guaranteed to exist
    $stmt = $conn->prepare("SELECT name, course, skills FROM students WHERE user_id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result ? $result->fetch_assoc() : null;
        $stmt->close();
    }
}

$skillList = [];
if ($student && !empty($student['skills'])) {
    $skillList = array_filter(array_map('trim', explode(',', $student['skills'])));
}

if (!$error && tableExistsJR('jobs')) {
    $sql = "
        SELECT j.*, c.name AS company_name
        FROM jobs j
        LEFT JOIN companies c ON c.id = j.company_id
        WHERE j.status = 'active' OR j.status IS NULL
        ORDER BY j.posted_at DESC
        LIMIT 50
    ";
    if ($result = $conn->query($sql)) {
        while ($job = $result->fetch_assoc()) {
            $jobSkills = array_filter(array_map('trim', preg_split('/[,|]/', $job['required_skills'] ?? '')));
            $matches = array_intersect(array_map('strtolower', $skillList), array_map('strtolower', $jobSkills));
            $matchScore = $skillList ? round((count($matches) / count($skillList)) * 100) : 0;
            $job['match_score'] = $matchScore;
            $job['matched_skills'] = $matches;
            $recommendedJobs[] = $job;
        }
        $result->free();
    }
}

if (!$recommendedJobs) {
    $recommendedJobs = [
        [
            'title' => 'Frontend Developer Intern',
            'company_name' => 'Innovate Labs',
            'location' => 'Remote',
            'required_skills' => 'React, JavaScript, CSS',
            'match_score' => $skillList ? 60 : 0,
            'matched_skills' => $skillList ? array_slice($skillList, 0, 2) : [],
            'posted_at' => date('Y-m-d'),
            'id' => 0,
        ],
        [
            'title' => 'Data Analyst Trainee',
            'company_name' => 'InsightWorks',
            'location' => 'Bengaluru',
            'required_skills' => 'Python, SQL, Excel',
            'match_score' => $skillList ? 55 : 0,
            'matched_skills' => $skillList ? array_slice($skillList, 0, 2) : [],
            'posted_at' => date('Y-m-d', strtotime('-2 days')),
            'id' => 0,
        ],
    ];
}

usort($recommendedJobs, function ($a, $b) {
    return $b['match_score'] <=> $a['match_score'];
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Recommendations - VishwaCollab</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f5f7fa; margin:0; padding:20px; }
        .container { max-width: 960px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .btn { display:inline-block; padding: 10px 18px; border-radius:6px; background:#1a73e8; color:white; text-decoration:none; }
        .job-card { border:1px solid #e5e7eb; border-radius:10px; padding:20px; margin-bottom:15px; background:#f9fafb; }
        .match { font-weight:bold; color:#1a73e8; }
        .badge { display:inline-block; background:#e8f0fe; color:#1a73e8; padding:4px 10px; border-radius:15px; font-size:13px; margin-right:6px; }
        .message { padding:14px; border-radius:6px; margin-bottom:20px; }
        .error { background:#ffebee; color:#c62828; }
    </style>
</head>
<body>
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h1>Recommended Jobs</h1>
                <p style="color:#555;">Based on your resume skills.</p>
            </div>
            <a href="student-dashboard.php" class="btn">⬅ Back to Dashboard</a>
        </div>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!$skillList): ?>
            <div class="message" style="background:#fff8e1; color:#8c6d1f;">
                We couldn't find any skills in your profile. Please upload a resume or update your skills in the profile page for better recommendations.
            </div>
        <?php endif; ?>

        <?php foreach ($recommendedJobs as $job): ?>
            <div class="job-card">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h2 style="margin:0;"><?php echo htmlspecialchars($job['title']); ?></h2>
                        <p style="margin:4px 0; color:#555;"><?php echo htmlspecialchars($job['company_name'] ?? 'Company Confidential'); ?> • <?php echo htmlspecialchars($job['location'] ?? 'Flexible'); ?></p>
                    </div>
                    <div class="match"><?php echo (int) $job['match_score']; ?>% match</div>
                </div>
                <p style="margin:10px 0; color:#333;"><strong>Required Skills:</strong> <?php echo htmlspecialchars($job['required_skills'] ?? 'Not specified'); ?></p>
                <?php if (!empty($job['matched_skills'])): ?>
                    <p style="margin:10px 0;"><strong>Matched Skills:</strong>
                        <?php foreach ($job['matched_skills'] as $skill): ?>
                            <span class="badge"><?php echo htmlspecialchars($skill); ?></span>
                        <?php endforeach; ?>
                    </p>
                <?php endif; ?>
                <p style="font-size:14px; color:#777;">Posted on: <?php echo date('M j, Y', strtotime($job['posted_at'] ?? 'now')); ?></p>
                <div style="margin-top:10px;">
                    <?php if (!empty($job['id'])): ?>
                        <a href="job_details.php?id=<?php echo $job['id']; ?>" class="btn">View Details</a>
                    <?php else: ?>
                        <a href="student-jobs.php" class="btn">Browse Similar Jobs</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>









