<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole() !== 'tpo') {
    header("Location: login.php");
    exit();
}
require_once 'db_connect.php';
require_once 'external_jobs_adzuna.php';

// Fetch only live external jobs from Adzuna for TPOs to analyse market demand.
$search = $_GET['search'] ?? '';
$location = $_GET['location'] ?? 'India';
// Always fetch jobs (doesn't require database)
$external_jobs = fetch_adzuna_jobs($search, $location, 30);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobs - TPO Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="theme-tpo">
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>TPO Dashboard</h2>
            </div>
            <div class="sidebar-menu">
                <a href="tpo-dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="tpo-students.php"><i class="fas fa-user-graduate"></i> Students</a>
                <a href="tpo-companies.php"><i class="fas fa-building"></i> Companies</a>
                <a href="tpo-jobs.php" class="active"><i class="fas fa-briefcase"></i> Jobs</a>
                <a href="tpo-placements.php"><i class="fas fa-chart-line"></i> Placements</a>
                <a href="tpo-reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header-section">
                <div>
                    <h1 class="page-title">Live Market Jobs</h1>
                    <p class="page-subtitle">Analyze market demand and job trends from Adzuna</p>
                </div>
            </div>

            <!-- Enhanced Search Form -->
            <div class="search-container">
                <form method="GET" class="search-form">
                    <div class="search-input-wrapper">
                        <i class="fas fa-briefcase search-icon"></i>
                        <input type="text" name="search" class="search-input-enhanced" 
                               placeholder="Search by role, skills (e.g. Data Scientist, Java)" 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <?php if ($search): ?>
                            <a href="tpo-jobs.php?location=<?php echo urlencode($location); ?>" class="clear-search" title="Clear search">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="search-input-wrapper">
                        <i class="fas fa-map-marker-alt search-icon"></i>
                        <input type="text" name="location" class="search-input-enhanced" 
                               placeholder="Location (e.g. India, Pune, Remote)" 
                               value="<?php echo htmlspecialchars($location); ?>">
                        <?php if ($location && $location !== 'India'): ?>
                            <a href="tpo-jobs.php?search=<?php echo urlencode($search); ?>" class="clear-search" title="Clear location">
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
            
            <!-- Jobs Section -->
            <div class="jobs-section">
                <div class="section-header">
                    <div class="section-title-wrapper">
                        <i class="fas fa-chart-line section-icon"></i>
                        <div>
                            <h2 class="section-title">Market Job Listings</h2>
                            <p class="section-subtitle">Powered by Adzuna • <?php echo count($external_jobs); ?> <?php echo count($external_jobs) === 1 ? 'job' : 'jobs'; ?> found</p>
                        </div>
                    </div>
                </div>

                <?php if (empty($external_jobs)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>No jobs found</h3>
                        <p>We couldn't find any jobs matching your search criteria. Try adjusting your filters or check back later.</p>
                        <?php if ($search || ($location && $location !== 'India')): ?>
                            <a href="tpo-jobs.php" class="btn btn-primary">View All Jobs</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="jobs-grid">
                        <?php foreach ($external_jobs as $job): ?>
                            <?php $host = parse_url($job['url'], PHP_URL_HOST); ?>
                            <div class="job-card-enhanced">
                                <div class="job-card-header">
                                    <div class="job-title-section">
                                        <h3 class="job-title">
                                            <a href="<?php echo htmlspecialchars($job['url']); ?>" target="_blank" class="job-title-link">
                                                <?php echo htmlspecialchars($job['title']); ?>
                                            </a>
                                        </h3>
                                        <div class="job-meta-info">
                                            <span class="job-company">
                                                <i class="fas fa-building"></i>
                                                <?php echo htmlspecialchars($job['company']); ?>
                                            </span>
                                            <span class="job-location">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?php echo htmlspecialchars($job['location']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="job-source-badge">
                                        <i class="fas fa-external-link-alt"></i>
                                        <span>External</span>
                                    </div>
                                </div>
                                
                                <div class="job-description">
                                    <p><?php echo htmlspecialchars(mb_substr($job['snippet'], 0, 200)); ?><?php echo mb_strlen($job['snippet']) > 200 ? '...' : ''; ?></p>
                                </div>

                                <div class="job-card-footer">
                                    <div class="job-source-info">
                                        <i class="fas fa-link"></i>
                                        <span>Source: <?php echo htmlspecialchars($host ?: 'adzuna.com'); ?></span>
                                    </div>
                                    <a href="<?php echo htmlspecialchars($job['url']); ?>" target="_blank" class="btn-apply">
                                        <span>View Details</span>
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






