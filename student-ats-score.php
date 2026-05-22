<?php
require_once 'config.php';
require_once 'db_connect.php';

if (!isLoggedIn() || getUserRole() !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id   = $_SESSION['user_id'];
$resume_id = isset($_GET['resume_id']) ? (int) $_GET['resume_id'] : 0;
$error     = '';
$scoreData = null;
$jobInput  = [
    'title' => '',
    'description' => '',
];

function ensureConn()
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

try {
    ensureConn();
} catch (Exception $dbEx) {
    $error = $dbEx->getMessage();
}

function getLatestResumeId($userId)
{
    global $conn;
    $orderField = 'created_at';
    $columnCheck = $conn->query("SHOW COLUMNS FROM resumes LIKE 'uploaded_at'");
    if ($columnCheck && $columnCheck->num_rows > 0) {
        $orderField = 'uploaded_at';
    }
    $columnCheck && $columnCheck->free();

    $stmt = $conn->prepare("SELECT id FROM resumes WHERE user_id = ? ORDER BY {$orderField} DESC LIMIT 1");
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $id = ($result && $result->num_rows > 0) ? (int) $result->fetch_assoc()['id'] : 0;
    $stmt->close();
    return $id;
}

if (!$resume_id && !$error) {
    $resume_id = getLatestResumeId($user_id);
    if (!$resume_id) {
        $error = 'Please upload a resume first to access ATS scoring.';
    }
}

$resume = null;
$parsed = [
    'skills' => [],
    'raw_text' => '',
    'work_experience' => [],
    'education' => [],
    'projects' => [],
];

if (!$error) {
$stmt = $conn->prepare("
        SELECT r.*, pr.skills AS parsed_skills, pr.raw_text, pr.work_experience, pr.education, pr.projects
        FROM resumes r
        LEFT JOIN parsed_resume_data pr ON pr.resume_id = r.id
        WHERE r.id = ? AND r.user_id = ?
        LIMIT 1
    ");
    if ($stmt) {
        $stmt->bind_param('ii', $resume_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $resume = $result->fetch_assoc();
$parsed['skills'] = json_decode($resume['parsed_skills'] ?? '[]', true) ?: [];
            $parsed['raw_text'] = $resume['raw_text'] ?? '';
            $parsed['work_experience'] = json_decode($resume['work_experience'] ?? '[]', true) ?: [];
            $parsed['education'] = json_decode($resume['education'] ?? '[]', true) ?: [];
            $parsed['projects'] = json_decode($resume['projects'] ?? '[]', true) ?: [];
        } else {
            $error = 'Resume not found.';
        }
        $stmt->close();
    } else {
        $error = 'Failed to fetch resume.';
    }
}

function computeAtsScore(array $skills, string $jobTitle, string $jobDescription)
{
    $jobText = strtolower($jobTitle . ' ' . $jobDescription);
    $matchedSkills = [];
    foreach ($skills as $skill) {
        if (stripos($jobText, strtolower($skill)) !== false) {
            $matchedSkills[] = $skill;
        }
    }

    $skillCount = count($skills);
    $matchRatio = $skillCount > 0 ? count($matchedSkills) / $skillCount : 0;
    $missingSkills = array_values(array_diff($skills, $matchedSkills));

    // Basic keyword coverage from job description
    $jobKeywords = array_slice(array_unique(array_filter(
        preg_split('/[\s,.;:!?]+/', strtolower($jobDescription)),
        fn($word) => strlen($word) > 4
    )), 0, 25);

    $keywordMatch = count($jobKeywords) > 0
        ? count(array_intersect(array_map('strtolower', $skills), $jobKeywords)) / count($jobKeywords)
        : 0;

    $overallScore = round((($matchRatio * 0.7) + ($keywordMatch * 0.3)) * 100);

    $recommendations = [];
    if ($missingSkills) {
        $recommendations[] = 'Consider adding the following skills if you have them: ' . implode(', ', $missingSkills);
    }
    if ($overallScore < 70) {
        $recommendations[] = 'Tailor your resume to highlight the job\'s key responsibilities.';
    } else {
        $recommendations[] = 'Great match! Double-check formatting and quantify achievements.';
    }

    return [
        'overall_score' => max(5, min(100, $overallScore)),
        'skill_match_percent' => round($matchRatio * 100),
        'keyword_match_percent' => round($keywordMatch * 100),
        'matched_skills' => $matchedSkills,
        'missing_skills' => $missingSkills,
        'recommendations' => $recommendations,
    ];
}

if (!$error && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobInput['title'] = trim($_POST['job_title'] ?? '');
    $jobInput['description'] = trim($_POST['job_description'] ?? '');

    if (empty($jobInput['description'])) {
        $error = 'Please paste a job description to analyze.';
    } else {
        $scoreData = computeAtsScore($parsed['skills'], $jobInput['title'], $jobInput['description']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATS Score - VishwaCollab</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .message {
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        .success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        form textarea {
            width: 100%;
            min-height: 160px;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 15px;
            resize: vertical;
        }
        form input[type="text"] {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 15px;
            margin-bottom: 10px;
        }
        .btn {
            background: #1a73e8;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-secondary {
            background: #666;
        }
        .score-card {
            background: #f0f7ff;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .score-value {
            font-size: 48px;
            font-weight: bold;
            color: #1a73e8;
        }
        .badge {
            display: inline-block;
            background: #e3f2fd;
            color: #1a73e8;
            padding: 4px 12px;
            border-radius: 20px;
            margin: 4px;
            font-size: 14px;
        }
        .recommendations {
            margin-top: 20px;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>ATS Compatibility Score</h1>
                <?php if ($resume): ?>
                    <p style="color:#555;">Resume: <strong><?php echo htmlspecialchars($resume['file_name']); ?></strong></p>
                <?php endif; ?>
            </div>
            <a href="student-resume-upload.php" class="btn btn-secondary">⬅ Back</a>
        </div>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!$error): ?>
        <form method="POST" style="margin-top: 20px;">
            <label for="job_title"><strong>Job Title (optional)</strong></label>
            <input type="text" id="job_title" name="job_title" placeholder="e.g., Frontend Developer" value="<?php echo htmlspecialchars($jobInput['title']); ?>">

            <label for="job_description"><strong>Job Description / Requirements</strong></label>
            <textarea id="job_description" name="job_description" placeholder="Paste the job description here..." required><?php echo htmlspecialchars($jobInput['description']); ?></textarea>

            <div style="margin-top: 15px;">
                <button type="submit" class="btn">Analyze Compatibility</button>
            </div>
        </form>
        <?php endif; ?>

        <?php if ($scoreData): ?>
            <div class="score-card">
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <p style="margin:0;color:#555;">Overall Score</p>
                        <div class="score-value"><?php echo $scoreData['overall_score']; ?>%</div>
                    </div>
                    <div>
                        <p><strong>Skill Match:</strong> <?php echo $scoreData['skill_match_percent']; ?>%</p>
                        <p><strong>Keyword Match:</strong> <?php echo $scoreData['keyword_match_percent']; ?>%</p>
                    </div>
                </div>

                <div style="margin-top:20px;">
                    <h3>Matched Skills</h3>
                    <?php if ($scoreData['matched_skills']): ?>
                        <?php foreach ($scoreData['matched_skills'] as $skill): ?>
                            <span class="badge"><?php echo htmlspecialchars($skill); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No direct skill matches found yet. Try highlighting relevant keywords.</p>
                    <?php endif; ?>
                </div>

                <?php if ($scoreData['missing_skills']): ?>
                <div style="margin-top:20px;">
                    <h3>Missing Skills</h3>
                    <?php foreach ($scoreData['missing_skills'] as $skill): ?>
                        <span class="badge" style="background:#fdecea; color:#c62828;"><?php echo htmlspecialchars($skill); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="recommendations">
                    <h3>Recommendations</h3>
                    <ul>
                        <?php foreach ($scoreData['recommendations'] as $tip): ?>
                            <li><?php echo htmlspecialchars($tip); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

