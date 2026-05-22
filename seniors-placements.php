<?php
require_once 'config.php';
require_once 'db_connect.php';

// Search and filter variables
$search = $_GET['search'] ?? '';
$filter_batch = $_GET['batch_year'] ?? '';
$filter_course = $_GET['course'] ?? '';
$filter_company = $_GET['company'] ?? '';

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$placements = [];
$total_placements = 0;

if (isDatabaseAvailable()) {
    // Build query with filters
    $where = "WHERE 1=1";
    $params = [];
    $types = '';
    
    if ($search) {
        $where .= " AND (student_name LIKE ? OR company_name LIKE ? OR job_title LIKE ? OR course LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= 'ssss';
    }
    
    if ($filter_batch) {
        $where .= " AND batch_year = ?";
        $params[] = $filter_batch;
        $types .= 'i';
    }
    
    if ($filter_course) {
        $where .= " AND course LIKE ?";
        $params[] = "%$filter_course%";
        $types .= 's';
    }
    
    if ($filter_company) {
        $where .= " AND company_name LIKE ?";
        $params[] = "%$filter_company%";
        $types .= 's';
    }
    
    // Count total
    $count_query = "SELECT COUNT(*) as total FROM alumni_placements $where";
    if (!empty($params)) {
        $stmt = $conn->prepare($count_query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $total_placements = $stmt->get_result()->fetch_assoc()['total'];
    } else {
        $total_placements = $conn->query($count_query)->fetch_assoc()['total'];
    }
    
    // Get placements
    $query = "SELECT * FROM alumni_placements $where ORDER BY batch_year DESC, placement_date DESC, student_name ASC LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $placements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get filter options
    $batch_years = $conn->query("SELECT DISTINCT batch_year FROM alumni_placements WHERE batch_year IS NOT NULL ORDER BY batch_year DESC")->fetch_all(MYSQLI_ASSOC);
    $courses = $conn->query("SELECT DISTINCT course FROM alumni_placements WHERE course IS NOT NULL AND course != '' ORDER BY course ASC")->fetch_all(MYSQLI_ASSOC);
    $companies = $conn->query("SELECT DISTINCT company_name FROM alumni_placements ORDER BY company_name ASC")->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seniors Placements - VishwaCollab</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: #f5f7fa; }
        .container { max-width: 1400px; margin: 0 auto; padding: 2rem; }
        .page-header { background: linear-gradient(135deg, #1a73e8, #0d47a1); color: white; padding: 2rem; border-radius: 10px; margin-bottom: 2rem; }
        .page-header h1 { font-size: 2rem; margin-bottom: 0.5rem; }
        .filters-card { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .filters-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 1rem; align-items: end; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-weight: 600; margin-bottom: 0.5rem; color: #202124; }
        .form-control { padding: 0.7rem; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; }
        .btn { padding: 0.7rem 1.5rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; font-size: 1rem; }
        .btn-primary { background: #1a73e8; color: white; }
        .btn-primary:hover { background: #0d47a1; }
        .placements-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem; }
        .placement-card { background: white; border-radius: 10px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid #1a73e8; transition: transform 0.2s; }
        .placement-card:hover { transform: translateY(-5px); }
        .placement-card h3 { color: #1a73e8; margin-bottom: 0.5rem; font-size: 1.3rem; }
        .placement-card .company { font-size: 1.1rem; font-weight: 600; color: #202124; margin: 0.5rem 0; }
        .placement-card .info { color: #5f6368; margin: 0.3rem 0; }
        .placement-card .info strong { color: #202124; }
        .badge { display: inline-block; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; margin-top: 0.5rem; }
        .badge-success { background: #e8f5e9; color: #2e7d32; }
        .pagination { display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem; }
        .pagination a, .pagination span { padding: 0.7rem 1rem; background: white; border: 1px solid #ddd; border-radius: 6px; text-decoration: none; color: #1a73e8; }
        .pagination a:hover { background: #e8f0fe; }
        .pagination .active { background: #1a73e8; color: white; border-color: #1a73e8; }
        .no-results { text-align: center; padding: 3rem; background: white; border-radius: 10px; }
        .stats { display: flex; gap: 1rem; margin-top: 1rem; }
        .stat-item { background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 6px; }
    </style>
</head>
<body>
    <header style="background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15); padding: 1rem 5%; margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1400px; margin: 0 auto;">
            <a href="index.php" style="font-size: 2rem; color: #202124; text-decoration: none; font-weight: 700;">
                <span style="color: #1a73e8;">Vishwa</span>Collab
            </a>
            <nav>
                <a href="index.php" style="padding: 0.7rem 1.5rem; background: #1a73e8; color: white; border-radius: 6px; text-decoration: none; font-weight: 600; margin-right: 0.5rem;">Home</a>
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo getUserRole(); ?>-dashboard.php" style="padding: 0.7rem 1.5rem; background: #f0f0f0; color: #202124; border-radius: 6px; text-decoration: none; font-weight: 600;">Dashboard</a>
                <?php else: ?>
                    <a href="login.php" style="padding: 0.7rem 1.5rem; background: #f0f0f0; color: #202124; border-radius: 6px; text-decoration: none; font-weight: 600;">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-graduation-cap"></i> Where Our Seniors Got Placed</h1>
            <p>Explore placement records of alumni and see their career paths</p>
            <div class="stats">
                <div class="stat-item"><strong><?php echo $total_placements; ?></strong> Total Placements</div>
                <div class="stat-item"><strong><?php echo count($batch_years ?? []); ?></strong> Batch Years</div>
                <div class="stat-item"><strong><?php echo count($companies ?? []); ?></strong> Companies</div>
            </div>
        </div>
        
        <div class="filters-card">
            <form method="GET" class="filters-grid">
                <div class="form-group">
                    <label><i class="fas fa-search"></i> Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, company, job title..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-calendar"></i> Batch Year</label>
                    <select name="batch_year" class="form-control">
                        <option value="">All Years</option>
                        <?php foreach ($batch_years ?? [] as $year): ?>
                            <option value="<?php echo $year['batch_year']; ?>" <?php echo $filter_batch == $year['batch_year'] ? 'selected' : ''; ?>>
                                <?php echo $year['batch_year']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-book"></i> Course</label>
                    <select name="course" class="form-control">
                        <option value="">All Courses</option>
                        <?php foreach ($courses ?? [] as $course): ?>
                            <option value="<?php echo htmlspecialchars($course['course']); ?>" <?php echo $filter_course == $course['course'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['course']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-building"></i> Company</label>
                    <select name="company" class="form-control">
                        <option value="">All Companies</option>
                        <?php foreach ($companies ?? [] as $comp): ?>
                            <option value="<?php echo htmlspecialchars($comp['company_name']); ?>" <?php echo $filter_company == $comp['company_name'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($comp['company_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                </div>
            </form>
            <?php if ($search || $filter_batch || $filter_course || $filter_company): ?>
                <a href="seniors-placements.php" style="display: inline-block; margin-top: 1rem; color: #1a73e8; text-decoration: none;">
                    <i class="fas fa-times"></i> Clear Filters
                </a>
            <?php endif; ?>
        </div>
        
        <?php if (empty($placements)): ?>
            <div class="no-results">
                <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <h3>No placements found</h3>
                <p>Try adjusting your search or filters</p>
            </div>
        <?php else: ?>
            <div class="placements-grid">
                <?php foreach ($placements as $placement): ?>
                    <div class="placement-card">
                        <h3><?php echo htmlspecialchars($placement['student_name']); ?></h3>
                        <div class="company"><?php echo htmlspecialchars($placement['company_name']); ?></div>
                        <?php if (!empty($placement['student_email'])): ?>
                            <div class="info"><strong>Email:</strong> <?php echo htmlspecialchars($placement['student_email']); ?></div>
                        <?php endif; ?>
                        <?php
                            $rawSkills = trim($placement['skills'] ?? '');
                            $isLinkedinUrl = !empty($rawSkills) && (strpos(strtolower($rawSkills), 'linkedin.com') !== false || preg_match('~^https?://~i', $rawSkills));
                            
                            if ($isLinkedinUrl) {
                                $linkedin = $rawSkills;
                                if (!preg_match('~^https?://~i', $linkedin)) {
                                    $linkedin = 'https://' . $linkedin;
                                }
                            } else {
                                $fullName = trim($placement['student_name']);
                                $parts = preg_split('/\s+/', $fullName);
                                $searchName = (count($parts) >= 2) ? $parts[0] . ' ' . $parts[count($parts) - 1] : $fullName;
                                $companyName = trim($placement['company_name']);
                                // Removed strict quotes to improve search hit rate on DuckDuckGo
                                $searchQuery = '!ducky site:linkedin.com/in ' . $searchName . ' ' . $companyName;
                                $linkedin = "https://duckduckgo.com/?q=" . urlencode($searchQuery);
                            }
                        ?>
                        <div class="info" style="margin-top: 1rem;">
                            <a href="<?php echo htmlspecialchars($linkedin); ?>" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 0.5rem; background: #0077b5; color: white; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.95rem; transition: background 0.3s;">
                                <i class="fab fa-linkedin" style="font-size: 1.2rem;"></i> Connect on LinkedIn
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (ceil($total_placements / $per_page) > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&batch_year=<?php echo urlencode($filter_batch); ?>&course=<?php echo urlencode($filter_course); ?>&company=<?php echo urlencode($filter_company); ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min(ceil($total_placements / $per_page), $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&batch_year=<?php echo urlencode($filter_batch); ?>&course=<?php echo urlencode($filter_course); ?>&company=<?php echo urlencode($filter_company); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < ceil($total_placements / $per_page)): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&batch_year=<?php echo urlencode($filter_batch); ?>&course=<?php echo urlencode($filter_course); ?>&company=<?php echo urlencode($filter_company); ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
