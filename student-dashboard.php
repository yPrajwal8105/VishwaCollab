<?php
require_once 'config.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || getUserRole() !== 'student') {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// Fetch student data
$student_id = $_SESSION['user_id'];
$student = null;
$error_message = null;

// Check if database is available
if (!isDatabaseAvailable()) {
    $error_message = "Database connection failed: " . getDatabaseError() . 
                    "<br><br><strong>Please ensure:</strong><br>" .
                    "1. XAMPP MySQL service is running<br>" .
                    "2. Database 'vishwacollab' exists in phpMyAdmin<br>" .
                    "3. MySQL server is accessible<br><br>" .
                    "<a href='init_db.php' class='btn btn-primary'>Try Database Setup</a>";
} else {
    try {
        $student_query = "SELECT * FROM students WHERE user_id = $student_id";
        $student_result = $conn->query($student_query);
        if ($student_result && $student_result->num_rows > 0) {
            $student = $student_result->fetch_assoc();
        }
    } catch (Exception $e) {
        $error_message = "Database query error: " . $e->getMessage();
    }
}

// Initialize variables
$applied_jobs = null;
$recommended_jobs = null;
$total_applications = 0;
$interviews_scheduled = 0;
$offers_received = 0;

// Only run database queries if connection is available
if (isDatabaseAvailable() && !$error_message) {
    try {
        // Fetch applied jobs
        $applied_jobs_query = "SELECT j.*, a.application_date, a.status, c.name AS company_name
                              FROM jobs j 
                              JOIN applications a ON j.id = a.job_id 
                              LEFT JOIN companies c ON c.id = j.company_id
                              WHERE a.student_id = $student_id 
                              ORDER BY a.application_date DESC 
                              LIMIT 5";
        $applied_jobs = $conn->query($applied_jobs_query);

        // Fetch recommended jobs
        $recommended_jobs_query = "SELECT j.*, c.name AS company_name FROM jobs j 
                                  LEFT JOIN companies c ON c.id = j.company_id
                                  WHERE j.status = 'active' AND (j.required_skills LIKE '%" . ($student ? mysqli_real_escape_string($conn, $student['skills']) : '') . "%' OR j.required_skills IS NULL)
                                  ORDER BY j.posted_at DESC 
                                  LIMIT 4";
        $recommended_jobs = $conn->query($recommended_jobs_query);

        // Fetch recent senior placements
        $recent_placements = null;
        if ($conn->query("SHOW TABLES LIKE 'alumni_placements'")->num_rows > 0) {
            $placements_query = "SELECT * FROM alumni_placements ORDER BY id DESC LIMIT 3";
            $recent_placements = $conn->query($placements_query);
        }

        // Statistics
        $total_applications_result = $conn->query("SELECT COUNT(*) as total FROM applications WHERE student_id = $student_id");
        if ($total_applications_result) {
            $total_applications = $total_applications_result->fetch_assoc()['total'];
        }
        
        $interviews_result = $conn->query("SELECT COUNT(*) as total FROM applications WHERE student_id = $student_id AND status = 'interview'");
        if ($interviews_result) {
            $interviews_scheduled = $interviews_result->fetch_assoc()['total'];
        }
        
        $offers_result = $conn->query("SELECT COUNT(*) as total FROM applications WHERE student_id = $student_id AND status = 'accepted'");
        if ($offers_result) {
            $offers_received = $offers_result->fetch_assoc()['total'];
        }
    } catch (Exception $e) {
        $error_message = "Database query error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - VishwaCollab</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a73e8;
            --primary-dark: #0d47a1;
            --secondary: #202124;
            --success: #34a853;
            --warning: #fbbc04;
            --danger: #ea4335;
            --gray: #5f6368;
            --light-gray: #f1f3f4;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            background: #f5f7fa;
        }
        
        .sidebar {
            width: 250px;
            background: white;
            box-shadow: var(--shadow);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 2rem 1.5rem;
            background: var(--primary);
            color: white;
        }
        
        .sidebar-header h2 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: var(--secondary);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: #e8f0fe;
            border-left-color: var(--primary);
            color: var(--primary);
        }
        
        .sidebar-menu i {
            margin-right: 1rem;
            width: 20px;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            text-align: center;
            border-top: 4px solid var(--primary);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary);
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-pending { background: #fef7e0; color: #b06000; }
        .status-approved { background: #e6f4ea; color: #137333; }
        .status-rejected { background: #fce8e6; color: #c5221f; }
        .status-interview { background: #e8f0fe; color: var(--primary); }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .job-card {
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary);
        }
        
        .profile-section {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Student Dashboard</h2>
                <p><?php echo $_SESSION['email']; ?></p>
            </div>
            <div class="sidebar-menu">
                <a href="student-dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="student-resume-upload.php"><i class="fas fa-file-upload"></i> Upload Resume</a>
                <a href="student-resume-ai.php"><i class="fas fa-magic"></i> AI Resume Builder</a>
                <a href="student-resume.php"><i class="fas fa-file-pdf"></i> Resume Builder</a>
                <a href="student-ats-score.php"><i class="fas fa-chart-line"></i> ATS Score</a>
                <a href="student-job-recommendations.php"><i class="fas fa-briefcase"></i> Job Recommendations</a>
                <a href="student-jobs.php"><i class="fas fa-search"></i> Job Search</a>
                <a href="student-applications.php"><i class="fas fa-file-alt"></i> Applications</a>
                <a href="student-interviews.php"><i class="fas fa-calendar-alt"></i> Interviews</a>
                <a href="student-mock-interview.php"><i class="fas fa-microphone"></i> Mock Interview</a>
                <a href="student-chat.php"><i class="fas fa-comments"></i> Chat with Seniors</a>
                <a href="student-quiz.php"><i class="fas fa-question-circle"></i> Take Quiz</a>
                <a href="student-leaderboard.php"><i class="fas fa-trophy"></i> Leaderboard</a>
                <a href="seniors-placements.php"><i class="fas fa-graduation-cap"></i> Seniors Placements</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <h1>Welcome back, <?php echo $student ? $student['name'] : 'Student'; ?>!</h1>
                <a href="profile.php" class="btn btn-primary">Edit Profile</a>
            </div>

            <?php if ($error_message): ?>
                <div style="background: #ffebee; color: #c62828; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #c62828;">
                    <strong>Database Setup Required:</strong> <?php echo $error_message; ?>
                    <br><br>
                    <a href="init_db.php" class="btn btn-primary" style="color: white; text-decoration: none; padding: 0.5rem 1rem; background: #1a73e8; border-radius: 4px;">Initialize Database</a>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_applications; ?></div>
                    <p>Total Applications</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $interviews_scheduled; ?></div>
                    <p>Interviews Scheduled</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $offers_received; ?></div>
                    <p>Offers Received</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number">85%</div>
                    <p>Profile Completion</p>
                </div>
            </div>

            <!-- New Features Section -->
            <div style="margin: 2rem 0;">
                <h2 style="margin-bottom: 1.5rem; color: var(--secondary);">
                    <i class="fas fa-star" style="color: var(--warning);"></i> New Features
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                    <!-- Resume Upload & Analysis -->
                    <div class="card" style="border-left: 4px solid #1a73e8;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="background: #e3f2fd; padding: 1rem; border-radius: 8px;">
                                <i class="fas fa-file-upload" style="font-size: 2rem; color: #1a73e8;"></i>
                            </div>
                            <div>
                                <h3 style="margin: 0;">Resume Analysis</h3>
                                <p style="margin: 0; color: var(--gray); font-size: 0.9rem;">Upload & Parse Resume</p>
                            </div>
                        </div>
                        <p style="color: var(--gray); margin-bottom: 1rem;">Upload your resume and get instant analysis with skill extraction.</p>
                        <a href="student-resume-upload.php" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-upload"></i> Upload Resume
                        </a>
                    </div>

                    <!-- ATS Score -->
                    <div class="card" style="border-left: 4px solid #34a853;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="background: #e8f5e9; padding: 1rem; border-radius: 8px;">
                                <i class="fas fa-chart-line" style="font-size: 2rem; color: #34a853;"></i>
                            </div>
                            <div>
                                <h3 style="margin: 0;">ATS Score</h3>
                                <p style="margin: 0; color: var(--gray); font-size: 0.9rem;">Resume Compatibility</p>
                            </div>
                        </div>
                        <p style="color: var(--gray); margin-bottom: 1rem;">Get your resume's ATS compatibility score and improvement suggestions.</p>
                        <a href="student-ats-score.php" class="btn" style="width: 100%; background: #34a853; color: white;">
                            <i class="fas fa-chart-bar"></i> Check ATS Score
                        </a>
                    </div>

                    <!-- Quiz -->
                    <div class="card" style="border-left: 4px solid #fbbc04;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="background: #fff8e1; padding: 1rem; border-radius: 8px;">
                                <i class="fas fa-question-circle" style="font-size: 2rem; color: #fbbc04;"></i>
                            </div>
                            <div>
                                <h3 style="margin: 0;">Skill Quiz</h3>
                                <p style="margin: 0; color: var(--gray); font-size: 0.9rem;">Test Your Skills</p>
                            </div>
                        </div>
                        <p style="color: var(--gray); margin-bottom: 1rem;">Take AI-generated quizzes based on your resume skills and compete on leaderboard.</p>
                        <a href="student-quiz.php" class="btn" style="width: 100%; background: #fbbc04; color: white;">
                            <i class="fas fa-play"></i> Take Quiz
                        </a>
                    </div>

                    <!-- Leaderboard -->
                    <div class="card" style="border-left: 4px solid #ea4335;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="background: #ffebee; padding: 1rem; border-radius: 8px;">
                                <i class="fas fa-trophy" style="font-size: 2rem; color: #ea4335;"></i>
                            </div>
                            <div>
                                <h3 style="margin: 0;">Leaderboard</h3>
                                <p style="margin: 0; color: var(--gray); font-size: 0.9rem;">Top Performers</p>
                            </div>
                        </div>
                        <p style="color: var(--gray); margin-bottom: 1rem;">See top performers and compete with other students on skill quizzes.</p>
                        <a href="student-leaderboard.php" class="btn" style="width: 100%; background: #ea4335; color: white;">
                            <i class="fas fa-trophy"></i> View Leaderboard
                        </a>
                    </div>

                    <!-- Job Recommendations -->
                    <div class="card" style="border-left: 4px solid #9c27b0;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="background: #f3e5f5; padding: 1rem; border-radius: 8px;">
                                <i class="fas fa-briefcase" style="font-size: 2rem; color: #9c27b0;"></i>
                            </div>
                            <div>
                                <h3 style="margin: 0;">Job Recommendations</h3>
                                <p style="margin: 0; color: var(--gray); font-size: 0.9rem;">AI-Powered Matching</p>
                            </div>
                        </div>
                        <p style="color: var(--gray); margin-bottom: 1rem;">Get personalized job recommendations based on your skills and preferences.</p>
                        <a href="student-job-recommendations.php" class="btn" style="width: 100%; background: #9c27b0; color: white;">
                            <i class="fas fa-search"></i> Find Jobs
                        </a>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Left Column -->
                <div>
                    <!-- Recent Applications -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Recent Applications</h3>
                            <a href="student-applications.php">View All</a>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th>Company</th>
                                    <th>Applied Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($job = $applied_jobs->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $job['title']; ?></td>
                                    <td><?php echo $job['company_name']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($job['application_date'])); ?></td>
                                    <td><span class="status-badge status-<?php echo $job['status']; ?>"><?php echo ucfirst($job['status']); ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Recommended Jobs -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Recommended Jobs</h3>
                            <a href="student-jobs.php">Browse More</a>
                        </div>
                        <?php while($job = $recommended_jobs->fetch_assoc()): ?>
                        <div class="job-card">
                            <h4><?php echo $job['title']; ?></h4>
                            <p><strong><?php echo $job['company_name']; ?></strong> • <?php echo $job['required_skills']; ?></p>
                            <p class="small">Posted: <?php echo date('M j, Y', strtotime($job['posted_at'])); ?></p>
                            <a href="job_details.php?id=<?php echo $job['id']; ?>" class="btn btn-primary">View Job</a>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <!-- Profile Summary -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Profile Summary</h3>
                        </div>
                        <div class="profile-section">
                            <div class="profile-avatar">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div>
                                <h4><?php echo $student ? $student['name'] : 'Your Name'; ?></h4>
                                <p><?php echo $student ? $student['course'] : 'Your Course'; ?></p>
                                <p>CGPA: <?php echo $student ? $student['cgpa'] : '0.0'; ?></p>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <p>Profile Strength: 85%</p>
                            <div style="background: #f0f0f0; border-radius: 10px; height: 10px;">
                                <div style="background: var(--primary); width: 85%; height: 100%; border-radius: 10px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Quick Actions</h3>
                        </div>
                        <div style="display: grid; gap: 1rem;">
                            <a href="student-resume-upload.php" class="btn btn-primary"><i class="fas fa-file-upload"></i> Upload Resume</a>
                            <a href="student-ats-score.php" class="btn btn-primary"><i class="fas fa-chart-line"></i> Get ATS Score</a>
                            <a href="student-quiz.php" class="btn btn-primary"><i class="fas fa-question-circle"></i> Take Quiz</a>
                            <a href="student-jobs.php" class="btn btn-primary"><i class="fas fa-search"></i> Search Jobs</a>
                            <a href="profile.php" class="btn btn-primary"><i class="fas fa-edit"></i> Edit Profile</a>
                        </div>
                    </div>

                    <!-- Upcoming Interviews -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Upcoming Interviews</h3>
                        </div>
                        <p>No upcoming interviews scheduled.</p>
                    </div>

                    <!-- Recent Senior Placements -->
                    <?php if ($recent_placements && $recent_placements->num_rows > 0): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3>Recent Placements</h3>
                            <a href="seniors-placements.php">View All</a>
                        </div>
                        <?php while($p = $recent_placements->fetch_assoc()): ?>
                        <div class="job-card" style="border-left-color: #0077b5;">
                            <h4><?php echo htmlspecialchars($p['student_name']); ?></h4>
                            <p><strong><?php echo htmlspecialchars($p['company_name']); ?></strong></p>
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
                            <a href="<?php echo htmlspecialchars($linkedin); ?>" target="_blank" rel="noopener noreferrer" style="display: inline-block; margin-top: 0.5rem; color: #0077b5; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                                <i class="fab fa-linkedin"></i> Connect on LinkedIn
                            </a>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>