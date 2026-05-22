<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole() !== 'student') {
    header("Location: login.php");
    exit();
}
require_once 'db_connect.php';
require_once 'external_jobs_adzuna.php';

$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Always fetch live external jobs from Adzuna (doesn't require database)
$location = 'India';
// Fetch jobs - API will return general jobs if no search term provided
$external_jobs = fetch_adzuna_jobs($search, $location, 20);
$adzuna_error = $GLOBALS['ADZUNA_LAST_ERROR'] ?? '';

// Debug: Uncomment the line below to see API response in error log
// error_log("Fetched " . count($external_jobs) . " jobs from Adzuna API");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Search - Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
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
                <a href="student-job-recommendations.php"><i class="fas fa-briefcase"></i> Job Recommendations</a>
                <a href="student-jobs.php" class="active"><i class="fas fa-search"></i> Job Search</a>
                <a href="student-applications.php"><i class="fas fa-file-alt"></i> Applications</a>
                <a href="student-interviews.php"><i class="fas fa-calendar-alt"></i> Interviews</a>
                <a href="student-mock-interview.php"><i class="fas fa-microphone"></i> Mock Interview</a>
                <a href="student-resume-ai.php"><i class="fas fa-magic"></i> AI Resume Builder</a>
                <a href="student-resume.php"><i class="fas fa-file-pdf"></i> Resume Builder</a>
                <a href="student-chat.php"><i class="fas fa-comments"></i> Chat with Seniors</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header-section">
                <div>
                    <h1 class="page-title">Job Search</h1>
                    <p class="page-subtitle">Discover opportunities from top companies across India</p>
                </div>
            </div>

            <!-- Enhanced Search Bar -->
            <div class="search-container">
                <form method="GET" class="search-form">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" class="search-input-enhanced" 
                               placeholder="Search by job title, skills, company, or location..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <?php if ($search): ?>
                            <a href="student-jobs.php" class="clear-search" title="Clear search">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i>
                        <span>Search</span>
                    </button>
                </form>
            </div>
            
            <!-- Live External Jobs Section -->
            <div class="jobs-section">
                <div class="section-header">
                    <div class="section-title-wrapper">
                        <i class="fas fa-briefcase section-icon"></i>
                        <div>
                            <h2 class="section-title">Live External Jobs</h2>
                            <p class="section-subtitle">Powered by Adzuna • <?php echo count($external_jobs); ?> <?php echo count($external_jobs) === 1 ? 'job' : 'jobs'; ?> found</p>
                        </div>
                    </div>
                </div>

                <?php if (empty($external_jobs) && $adzuna_error): ?>
                    <div class="empty-state" style="border-style: solid; border-color: rgba(234, 67, 53, 0.35);">
                        <div class="empty-state-icon" style="background: rgba(234, 67, 53, 0.08);">
                            <i class="fas fa-triangle-exclamation" style="color: #ea4335;"></i>
                        </div>
                        <h3>Job search is temporarily unavailable</h3>
                        <p><?php echo htmlspecialchars($adzuna_error); ?> Try again in a minute, or search later.</p>
                    </div>
                <?php elseif (empty($external_jobs)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>No jobs found</h3>
                        <p>We couldn't find any jobs matching your search. Try different keywords or check back later.</p>
                        <?php if ($search): ?>
                            <a href="student-jobs.php" class="btn btn-primary">View All Jobs</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="jobs-grid">
                        <?php foreach ($external_jobs as $ej): ?>
                            <?php $host = parse_url($ej['url'], PHP_URL_HOST); ?>
                            <div class="job-card-enhanced">
                                <div class="job-card-header">
                                    <div class="job-title-section">
                                        <h3 class="job-title">
                                            <a href="<?php echo htmlspecialchars($ej['url']); ?>" target="_blank" class="job-title-link">
                                                <?php echo htmlspecialchars($ej['title']); ?>
                                            </a>
                                        </h3>
                                        <div class="job-meta-info">
                                            <span class="job-company">
                                                <i class="fas fa-building"></i>
                                                <?php echo htmlspecialchars($ej['company']); ?>
                                            </span>
                                            <span class="job-location">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?php echo htmlspecialchars($ej['location']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="job-source-badge">
                                        <i class="fas fa-external-link-alt"></i>
                                        <span>External</span>
                                    </div>
                                </div>
                                
                                <div class="job-description">
                                    <p><?php echo htmlspecialchars(mb_substr($ej['snippet'], 0, 200)); ?><?php echo mb_strlen($ej['snippet']) > 200 ? '...' : ''; ?></p>
                                </div>

                                <div class="job-card-footer">
                                    <div class="job-source-info">
                                        <i class="fas fa-link"></i>
                                        <span>Source: <?php echo htmlspecialchars($host ?: 'adzuna.com'); ?></span>
                                    </div>
                                    <a href="<?php echo htmlspecialchars($ej['url']); ?>" target="_blank" class="btn-apply">
                                        <span>Apply Now</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>


