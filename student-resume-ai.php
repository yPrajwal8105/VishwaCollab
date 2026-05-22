<?php
require_once 'config.php';
require_once 'db_connect.php';
require_once 'ai_resume_builder.php';

if (!isLoggedIn() || getUserRole() !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';
$resumeContent = null;
$student = null;

// Fetch student data
if (isDatabaseAvailable()) {
    // Check which columns exist first
    $columns = ['name', 'email', 'course', 'skills'];
    $availableColumns = [];
    
    // Get table structure
    $result = $conn->query("SHOW COLUMNS FROM students");
    $existingColumns = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $existingColumns[] = $row['Field'];
        }
    }
    
    // Only select columns that exist
    $selectColumns = array_intersect($columns, $existingColumns);
    if (in_array('experience', $existingColumns)) {
        $selectColumns[] = 'experience';
    }
    if (in_array('education', $existingColumns)) {
        $selectColumns[] = 'education';
    }
    
    if (!empty($selectColumns)) {
        $columnList = implode(', ', $selectColumns);
        $stmt = $conn->prepare("SELECT {$columnList} FROM students WHERE user_id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $student = $result->fetch_assoc();
            $stmt->close();
        }
    }
}

// Handle AI resume generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_resume'])) {
    $jobRole = $_POST['job_role'] ?? '';
    $userSkills = $student['skills'] ?? '';
    $userExperience = $student['experience'] ?? ($student['course'] ?? 'Student with academic projects and coursework');
    $userEducation = $student['education'] ?? ($student['course'] ?? '');
    
    if (empty($jobRole)) {
        $error = "Please enter a job role";
    } else {
        $resumeContent = generateResumeContent($jobRole, $userSkills, $userExperience, $userEducation);
        $success = "Resume content generated successfully!";
    }
}

// Get job role suggestions
$roleSuggestions = getJobRoleSuggestions($_GET['role_search'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Resume Builder - Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        .resume-builder-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        .resume-form-card, .resume-preview-card {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text);
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
        }
        .role-suggestions {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-top: 0.5rem;
            display: none;
        }
        .role-suggestions.show {
            display: block;
        }
        .suggestion-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: background 0.2s;
            border-bottom: 1px solid var(--border);
        }
        .suggestion-item:hover {
            background: rgba(26, 115, 232, 0.05);
        }
        .suggestion-item:last-child {
            border-bottom: none;
        }
        .btn-generate {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(26, 115, 232, 0.3);
        }
        .resume-preview {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 12px;
            min-height: 400px;
            font-family: 'Georgia', serif;
        }
        .resume-preview h2 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        .resume-preview h3 {
            color: var(--text);
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            font-size: 1.2rem;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
        }
        .resume-preview p {
            line-height: 1.6;
            color: var(--muted);
            margin-bottom: 1rem;
        }
        .resume-preview ul {
            list-style: none;
            padding-left: 0;
        }
        .resume-preview li {
            padding: 0.5rem 0;
            padding-left: 1.5rem;
            position: relative;
            color: var(--muted);
        }
        .resume-preview li:before {
            content: "▸";
            position: absolute;
            left: 0;
            color: var(--primary-color);
        }
        .skill-tag {
            display: inline-block;
            background: rgba(26, 115, 232, 0.1);
            color: var(--primary-color);
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            margin: 0.25rem;
            font-size: 0.9rem;
        }
        .btn-download {
            margin-top: 1rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-download:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        .ai-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        @media (max-width: 960px) {
            .resume-builder-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="theme-student">
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Student Dashboard</h2>
            </div>
            <div class="sidebar-menu">
                <a href="student-dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="student-jobs.php"><i class="fas fa-briefcase"></i> Job Search</a>
                <a href="student-applications.php"><i class="fas fa-file-alt"></i> Applications</a>
                <a href="student-interviews.php"><i class="fas fa-calendar-alt"></i> Interviews</a>
                <a href="student-resume.php"><i class="fas fa-file-pdf"></i> Upload Resume</a>
                <a href="student-resume-ai.php" class="active"><i class="fas fa-magic"></i> AI Resume Builder</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <div class="page-header-section">
                <div>
                    <h1 class="page-title">AI-Powered Resume Builder</h1>
                    <p class="page-subtitle">Generate professional resume content tailored to your target job role</p>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success" style="background: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border-left: 4px solid #2e7d32;">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error" style="background: #ffebee; color: #c62828; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border-left: 4px solid #c62828;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="resume-builder-container">
                <div class="resume-form-card">
                    <div class="ai-badge">
                        <i class="fas fa-robot"></i>
                        <span>Powered by AI</span>
                    </div>
                    <h2 style="margin-bottom: 1.5rem;">Generate Resume Content</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Target Job Role *</label>
                            <input type="text" name="job_role" id="job_role" class="form-control" 
                                   placeholder="e.g., Software Developer, Data Analyst" 
                                   value="<?php echo htmlspecialchars($_POST['job_role'] ?? ''); ?>" required
                                   autocomplete="off">
                            <div class="role-suggestions" id="roleSuggestions"></div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Your Skills</label>
                            <textarea class="form-control" rows="3" placeholder="Enter your skills (comma-separated)"><?php echo htmlspecialchars($student['skills'] ?? ''); ?></textarea>
                            <small style="color: var(--muted);">Skills from your profile are pre-filled</small>
                        </div>

                        <button type="submit" name="generate_resume" class="btn-generate">
                            <i class="fas fa-magic"></i>
                            <span>Generate Resume Content</span>
                        </button>
                    </form>
                </div>

                <div class="resume-preview-card">
                    <h2 style="margin-bottom: 1rem;">Resume Preview</h2>
                    <?php if ($resumeContent): ?>
                        <div class="resume-preview">
                            <h2><?php echo htmlspecialchars($student['name'] ?? 'Your Name'); ?></h2>
                            <p style="color: var(--muted);"><?php echo htmlspecialchars($student['email'] ?? ''); ?></p>
                            
                            <h3>Professional Summary</h3>
                            <p><?php echo htmlspecialchars($resumeContent['summary'] ?? ''); ?></p>
                            
                            <h3>Key Skills</h3>
                            <div>
                                <?php 
                                $skills = is_array($resumeContent['skills']) ? $resumeContent['skills'] : explode(',', $resumeContent['skills'] ?? '');
                                foreach ($skills as $skill): 
                                    $skill = trim($skill);
                                    if (!empty($skill)):
                                ?>
                                    <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                            
                            <h3>Key Achievements</h3>
                            <ul>
                                <?php 
                                $achievements = is_array($resumeContent['achievements']) ? $resumeContent['achievements'] : [];
                                foreach ($achievements as $achievement): 
                                ?>
                                    <li><?php echo htmlspecialchars($achievement); ?></li>
                                <?php endforeach; ?>
                            </ul>

                            <button class="btn-download" onclick="downloadResume()">
                                <i class="fas fa-download"></i> Download as PDF
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="resume-preview" style="display: flex; align-items: center; justify-content: center; color: var(--muted);">
                            <div style="text-align: center;">
                                <i class="fas fa-file-alt" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                <p>Enter a job role and click "Generate Resume Content" to see your personalized resume</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        const jobRoleInput = document.getElementById('job_role');
        const suggestionsDiv = document.getElementById('roleSuggestions');
        const roleSuggestions = <?php echo json_encode($roleSuggestions); ?>;

        jobRoleInput.addEventListener('input', function() {
            const value = this.value.toLowerCase();
            if (value.length > 0) {
                const filtered = roleSuggestions.filter(role => 
                    role.toLowerCase().includes(value)
                );
                showSuggestions(filtered);
            } else {
                suggestionsDiv.classList.remove('show');
            }
        });

        function showSuggestions(suggestions) {
            if (suggestions.length === 0) {
                suggestionsDiv.classList.remove('show');
                return;
            }
            
            suggestionsDiv.innerHTML = suggestions.map(role => 
                `<div class="suggestion-item" onclick="selectRole('${role}')">${role}</div>`
            ).join('');
            suggestionsDiv.classList.add('show');
        }

        function selectRole(role) {
            jobRoleInput.value = role;
            suggestionsDiv.classList.remove('show');
        }

        document.addEventListener('click', function(e) {
            if (!jobRoleInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                suggestionsDiv.classList.remove('show');
            }
        });

        function downloadResume() {
            window.print();
        }
    </script>
</body>
</html>

