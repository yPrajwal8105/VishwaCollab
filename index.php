<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VishwaCollab - Campus-Industry Connect Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Base Styles */
        :root {
            --primary: #1a73e8;
            --primary-dark: #0d47a1;
            --secondary: #202124;
            --accent: #fbbc04;
            --light: #f8f9fa;
            --dark: #202124;
            --success: #34a853;
            --warning: #fbbc04;
            --danger: #ea4335;
            --gray: #5f6368;
            --light-gray: #f1f3f4;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        body {
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: url('https://assets.kollegeapply.com/images/1751572490731-1742190808phpbsJWrN.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.5);
            z-index: -1;
        }
        
        a {
            text-decoration: none;
            color: var(--primary);
            transition: var(--transition);
            font-weight: 500;
        }
        
        a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        /* Header Styles */
        header {
            background-color: white;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 3px solid var(--primary);
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo h1 {
            font-size: 2rem;
            color: var(--secondary);
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .logo span {
            color: var(--primary);
        }
        
        .logo-icon {
            font-size: 2.5rem;
            color: var(--primary);
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 2.5rem;
        }
        
        nav a {
            font-weight: 600;
            color: var(--secondary);
            padding: 0.5rem 0;
            position: relative;
            font-size: 1.1rem;
        }
        
        nav a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 3px;
            bottom: 0;
            left: 0;
            background-color: var(--primary);
            transition: var(--transition);
        }
        
        nav a:hover::after {
            width: 100%;
        }
        
        .auth-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.7rem 1.8rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 1rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }

        /* User Profile Styles */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .user-avatar:hover {
            transform: scale(1.1);
        }

        .user-info {
            display: flex;
            flex-direction: column;
            color: white;
        }

        .user-name {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.2rem;
        }

        .user-role {
            font-size: 0.85rem;
            opacity: 0.8;
            text-transform: capitalize;
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 0.5rem 0;
            min-width: 200px;
            display: none;
            z-index: 1000;
        }

        .user-dropdown.show {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            color: var(--secondary);
            text-decoration: none;
            transition: var(--transition);
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
            color: var(--primary);
        }

        .dropdown-divider {
            height: 1px;
            background: #e0e0e0;
            margin: 0.5rem 0;
        }

        /* Search Results Styles */
        .search-results {
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin: 2rem 0;
            padding: 2rem;
            max-height: 600px;
            overflow-y: auto;
        }

        .search-results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e0e0e0;
        }

        .search-results-header h3 {
            color: var(--secondary);
            margin: 0;
        }

        .result-count {
            background: var(--primary);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .search-section {
            margin-bottom: 2rem;
        }

        .search-section h4 {
            color: var(--primary);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .search-result-item {
            background: #f8f9ff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: var(--transition);
        }

        .search-result-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }

        .search-result-item h5 {
            margin: 0 0 0.5rem 0;
            color: var(--secondary);
        }

        .search-result-item h5 a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .search-result-item h5 a:hover {
            text-decoration: underline;
        }

        .search-result-item .company,
        .search-result-item .industry,
        .search-result-item .course {
            color: var(--gray);
            font-weight: 500;
            margin: 0.3rem 0;
        }

        .search-result-item .description {
            color: #666;
            margin: 0.5rem 0;
            line-height: 1.5;
        }

        .search-result-item .skills {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }

        .search-loading {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
            font-size: 1.1rem;
        }

        .search-error {
            background: #ffebee;
            color: #c62828;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #c62828;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }

        .no-results h3 {
            color: var(--secondary);
            margin-bottom: 1rem;
        }
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-warning {
            background-color: var(--warning);
            color: white;
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        /* Main Content */
        main {
            flex: 1;
            padding: 2.5rem 5%;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }
        
        /* Hero Section */
        .hero {
            text-align: center;
            padding: 3.5rem 2rem;
            margin-bottom: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border-left: 5px solid var(--primary);
        }
        
        .hero h2 {
            font-size: 2.8rem;
            margin-bottom: 1.5rem;
            color: var(--secondary);
            font-weight: 700;
        }
        
        .hero p {
            font-size: 1.3rem;
            max-width: 800px;
            margin: 0 auto 2.5rem;
            color: var(--secondary);
            line-height: 1.8;
        }
        
        /* Card Styles */
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            padding: 2.5rem;
            margin-bottom: 2.5rem;
            transition: var(--transition);
            border-top: 4px solid var(--primary);
        }
        
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }
        
        .card h2, .card h3 {
            color: var(--secondary);
            margin-bottom: 1.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid var(--light-gray);
            font-size: 1.8rem;
        }
        
        .small {
            font-size: 0.95rem;
            color: var(--gray);
        }
        
        /* Table Styles */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table th {
            background-color: var(--primary);
            color: white;
            padding: 1.2rem;
            text-align: left;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .table td {
            padding: 1.2rem;
            border-bottom: 1px solid var(--light-gray);
            font-size: 1.05rem;
        }
        
        .table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .table tr:hover {
            background-color: #e8f0fe;
        }
        
        /* Dashboard Styles */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            text-align: center;
            border-left: 5px solid var(--primary);
        }
        
        .stat-card h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: var(--gray);
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }
        
        .dashboard-card h3 {
            margin-bottom: 1rem;
            color: var(--secondary);
            border-bottom: 2px solid var(--light-gray);
            padding-bottom: 0.5rem;
        }
        
        /* Footer Styles */
        footer {
            background-color: var(--secondary);
            color: white;
            padding: 3rem 5%;
            margin-top: auto;
        }
        
        .footer-container {
            display: flex;
            justify-content: space-between;
            max-width: 1400px;
            margin: 0 auto;
            flex-wrap: wrap;
            gap: 2.5rem;
        }
        
        .footer-section {
            flex: 1;
            min-width: 250px;
        }
        
        .footer-section h3 {
            margin-bottom: 1.5rem;
            color: white;
            font-size: 1.4rem;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 0.5rem;
            display: inline-block;
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section ul li {
            margin-bottom: 0.8rem;
        }
        
        .footer-section a {
            color: #bdc3c7;
            font-size: 1.05rem;
        }
        
        .footer-section a:hover {
            color: white;
            text-decoration: underline;
        }
        
        .copyright {
            text-align: center;
            padding-top: 2.5rem;
            margin-top: 2.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            color: #bdc3c7;
            font-size: 1rem;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Responsive Design */
        @media (max-width: 900px) {
            .header-container {
                flex-direction: column;
                gap: 1.5rem;
            }
            
            nav ul {
                gap: 1.5rem;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .hero h2 {
                font-size: 2.2rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .table {
                display: block;
                overflow-x: auto;
            }
            
            .footer-container {
                flex-direction: column;
                gap: 2rem;
            }
            
            .stats {
                flex-direction: column;
            }
            
            .how-it-works {
                flex-direction: column;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 600px) {
            .hero {
                padding: 2rem 1rem;
            }
            
            .hero h2 {
                font-size: 1.8rem;
            }
            
            .card {
                padding: 1.5rem;
            }
            
            .table th, .table td {
                padding: 0.8rem;
            }
        }
        
        /* Animation for cards */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .card {
            animation: fadeIn 0.6s ease-out;
        }
        
        /* Stats Section */
        .stats {
            display: flex;
            justify-content: space-around;
            text-align: center;
            margin: 3rem 0;
            background: white;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: var(--shadow);
            border: 1px solid #e0e0e0;
        }
        
        .stat-item {
            flex: 1;
            padding: 1rem;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: var(--gray);
            font-weight: 500;
        }
        
        /* How It Works Section */
        .how-it-works {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        
        .how-it-works-item {
            flex: 1;
            min-width: 250px;
            padding: 1.5rem;
            text-align: center;
            background: #f8f9fa;
            border-radius: 10px;
            transition: var(--transition);
        }
        
        .how-it-works-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        
        .how-it-works-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
        }
        
        .how-it-works h4 {
            margin-bottom: 1rem;
            color: var(--secondary);
            font-size: 1.4rem;
        }
        
        .how-it-works p {
            color: var(--gray);
            line-height: 1.6;
        }
        
        /* Search Bar */
        .search-container {
            display: flex;
            margin: 2rem auto;
            gap: 10px;
            max-width: 700px;
        }
        
        .search-input {
            flex: 1;
            padding: 1rem 1.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1.1rem;
            transition: var(--transition);
        }
        
        .search-input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.2);
        }
        
        .search-button {
            padding: 1rem 2rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .search-button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        /* Alert for no jobs */
        .no-jobs {
            text-align: center;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 8px;
            color: var(--gray);
            font-size: 1.2rem;
        }
        
        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-new {
            background: #e6f4ea;
            color: #137333;
        }
        
        .status-featured {
            background: #fef7e0;
            color: #b06000;
        }
        
        .status-pending {
            background: #fef7e0;
            color: #b06000;
        }
        
        .status-approved {
            background: #e6f4ea;
            color: #137333;
        }
        
        .status-rejected {
            background: #fce8e6;
            color: #c5221f;
        }
        
        /* Dashboard Navigation */
        .dashboard-nav {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .dashboard-nav a {
            padding: 0.8rem 1.5rem;
            background: white;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            font-weight: 600;
        }
        
        .dashboard-nav a.active {
            background: var(--primary);
            color: white;
        }
        
        /* Profile Section */
        .profile-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
        }
        
        .profile-info h2 {
            margin-bottom: 0.5rem;
        }
        
        .profile-info p {
            color: var(--gray);
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.2);
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 1.5rem;
        }
        
        .tab {
            padding: 1rem 1.5rem;
            cursor: pointer;
            border-bottom: 3px solid transparent;
        }
        
        .tab.active {
            border-bottom: 3px solid var(--primary);
            font-weight: 600;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <div class="logo-icon">💼</div>
                <h1>Vishwa<span>Collab</span></h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#jobs-section">Jobs</a></li>
                    <li><a href="#companies-section">Companies</a></li>
                    <li><a href="#students-section">Students</a></li>
                    <li><a href="#tpo-section">TPO</a></li>
                    <li><a href="seniors-placements.php">Seniors Placements</a></li>
                </ul>
            </nav>
            <?php if (isLoggedIn()): ?>
                <div class="user-profile" id="userProfile">
                    <div class="user-avatar" onclick="toggleUserDropdown()">
                        <?php echo strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($_SESSION['role'] ?? 'user'); ?></div>
                    </div>
                    <div class="user-dropdown" id="userDropdown">
                        <a href="<?php echo $_SESSION['role'] ?? 'student'; ?>-dashboard.php" class="dropdown-item">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <a href="settings.php" class="dropdown-item">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-outline">Login</a>
                    <a href="signup.php" class="btn btn-primary">Sign Up</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <section class="hero">
            <h2>Welcome to VishwaCollab</h2>
            <p class="small">A campus–industry connect platform. Students, companies and TPOs collaborate here.</p>
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Search for jobs, companies, or skills...">
                <button class="search-button">Search</button>
            </div>
            
            <!-- Search Results Container -->
            <div id="searchResults" class="search-results" style="display: none;">
                <!-- Search results will be displayed here -->
            </div>
        </section>

        <!-- New Features Section -->
        <div class="card" id="features-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-top: 4px solid #fbbc04; margin-bottom: 2.5rem;">
            <h2 style="color: white; border-bottom: 2px solid rgba(255,255,255,0.3);">
                <i class="fas fa-star" style="color: #fbbc04;"></i> New Advanced Features
            </h2>
            <p style="font-size: 1.2rem; margin-bottom: 2rem; opacity: 0.9;">Powerful AI-powered tools to enhance your career journey</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-top: 2rem;">
                <div style="background: rgba(255,255,255,0.1); padding: 2rem; border-radius: 12px; backdrop-filter: blur(10px);">
                    <div style="font-size: 3rem; margin-bottom: 1rem; text-align: center;"><i class="fas fa-file-upload"></i></div>
                    <h3 style="color: white; margin-bottom: 1rem; text-align: center;">Resume Analysis</h3>
                    <p style="opacity: 0.9; text-align: center; margin-bottom: 1.5rem;">Upload & parse resume with automatic skill extraction</p>
                    <ul style="list-style: none; padding: 0; opacity: 0.9;">
                        <li><i class="fas fa-check" style="color: #4caf50;"></i> PDF & DOCX Support</li>
                        <li><i class="fas fa-check" style="color: #4caf50;"></i> Automatic Parsing</li>
                        <li><i class="fas fa-check" style="color: #4caf50;"></i> Skill Extraction</li>
                    </ul>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 2rem; border-radius: 12px; backdrop-filter: blur(10px);">
                    <div style="font-size: 3rem; margin-bottom: 1rem; text-align: center;"><i class="fas fa-chart-line"></i></div>
                    <h3 style="color: white; margin-bottom: 1rem; text-align: center;">ATS Score</h3>
                    <p style="opacity: 0.9; text-align: center; margin-bottom: 1.5rem;">Get resume compatibility score with AI analysis</p>
                    <ul style="list-style: none; padding: 0; opacity: 0.9;">
                        <li><i class="fas fa-check" style="color: #4caf50;"></i> Score (0-100)</li>
                        <li><i class="fas fa-check" style="color: #4caf50;"></i> Missing Skills</li>
                        <li><i class="fas fa-check" style="color: #4caf50;"></i> Suggestions</li>
                    </ul>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 2rem; border-radius: 12px; backdrop-filter: blur(10px);">
                    <div style="font-size: 3rem; margin-bottom: 1rem; text-align: center;"><i class="fas fa-question-circle"></i></div>
                    <h3 style="color: white; margin-bottom: 1rem; text-align: center;">Skill Quiz</h3>
                    <p style="opacity: 0.9; text-align: center; margin-bottom: 1.5rem;">AI-generated quizzes based on your skills</p>
                    <ul style="list-style: none; padding: 0; opacity: 0.9;">
                        <li><i class="fas fa-check" style="color: #4caf50;"></i> Role-Based</li>
                        <li><i class="fas fa-check" style="color: #4caf50;"></i> Multiple Levels</li>
                        <li><i class="fas fa-check" style="color: #4caf50;"></i> Leaderboard</li>
                    </ul>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 2rem; border-radius: 12px; backdrop-filter: blur(10px);">
                    <div style="font-size: 3rem; margin-bottom: 1rem; text-align: center;"><i class="fas fa-trophy"></i></div>
                    <h3 style="color: white; margin-bottom: 1rem; text-align: center;">Leaderboard</h3>
                    <p style="opacity: 0.9; text-align: center; margin-bottom: 1.5rem;">Compete and see top performers</p>
                    <ul style="list-style: none; padding: 0; opacity: 0.9;">
                        <li><i class="fas fa-check" style="color: #4caf50;"></i> Global Rankings</li>
                        <li><i class="fas fa-check" style="color: #4caf50;"></i> Role-Based</li>
                        <li><i class="fas fa-check" style="color: #4caf50;"></i> Your Rank</li>
                    </ul>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 2rem; border-radius: 12px; backdrop-filter: blur(10px);">
                    <div style="font-size: 3rem; margin-bottom: 1rem; text-align: center;"><i class="fas fa-briefcase"></i></div>
                    <h3 style="color: white; margin-bottom: 1rem; text-align: center;">Job Recommendations</h3>
                    <p style="opacity: 0.9; text-align: center; margin-bottom: 1.5rem;">Personalized jobs based on your skills</p>
                    <ul style="list-style: none; padding: 0; opacity: 0.9;">
                        <li><i class="fas fa-check" style="color: #4caf50;"></i> Skill Matching</li>
                        <li><i class="fas fa-check" style="color: #4caf50;"></i> Location Based</li>
                        <li><i class="fas fa-check" style="color: #4caf50;"></i> Real-Time Data</li>
                    </ul>
                </div>
            </div>
            <div style="text-align: center; margin-top: 3rem;">
                <?php if (isLoggedIn() && getUserRole() === 'student'): ?>
                    <a href="student-dashboard.php" class="btn" style="background: white; color: #667eea; font-size: 1.2rem; padding: 1rem 2.5rem;">
                        <i class="fas fa-rocket"></i> Try These Features Now
                    </a>
                <?php else: ?>
                    <a href="signup.php" class="btn" style="background: white; color: #667eea; font-size: 1.2rem; padding: 1rem 2.5rem;">
                        <i class="fas fa-user-plus"></i> Sign Up to Get Started
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="stats">
            <div class="stat-item">
                <div class="stat-number">500+</div>
                <div class="stat-label">Active Companies</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">10,000+</div>
                <div class="stat-label">Registered Students</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">2,000+</div>
                <div class="stat-label">Job Opportunities</div>
            </div>
        </div>

        <div class="card" id="jobs-section">
            <h3>Recent Opportunities</h3>
            <?php
            // Sample job data
            $jobs = array(
                array(
                    'id' => 1,
                    'title' => 'Software Developer Intern',
                    'company_name' => 'Tech Solutions Inc.',
                    'required_skills' => 'PHP, JavaScript, MySQL',
                    'posted_at' => '2 days ago'
                ),
                array(
                    'id' => 2,
                    'title' => 'Data Analyst',
                    'company_name' => 'Analytics Pro',
                    'required_skills' => 'Python, SQL, Data Visualization',
                    'posted_at' => '5 days ago'
                ),
                array(
                    'id' => 3,
                    'title' => 'Marketing Intern',
                    'company_name' => 'Digital Marketing Co.',
                    'required_skills' => 'SEO, Social Media, Content Writing',
                    'posted_at' => '1 week ago'
                )
            );
            ?>
            <?php if(count($jobs)==0): ?>
                <div class="no-jobs">
                    <p>No jobs posted yet. Check back later for new opportunities!</p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Company</th>
                            <th>Skills</th>
                            <th>Posted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($jobs as $j): ?>
                            <tr>
                                <td>
                                    <a href="job_details.php?id=<?php echo $j['id']; ?>">
                                        <?php echo htmlspecialchars($j['title']); ?>
                                    </a>
                                    <span class="status-badge status-new">New</span>
                                </td>
                                <td><?php echo htmlspecialchars($j['company_name']); ?></td>
                                <td><?php echo htmlspecialchars($j['required_skills']); ?></td>
                                <td class="small"><?php echo $j['posted_at']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card" id="companies-section">
            <h3>Featured Companies</h3>
            <?php
            // Sample company data
            $companies = array(
                array(
                    'id' => 1,
                    'name' => 'Tech Solutions Inc.',
                    'industry' => 'Information Technology',
                    'jobs_posted' => 15,
                    'location' => 'Bangalore, India'
                ),
                array(
                    'id' => 2,
                    'name' => 'Analytics Pro',
                    'industry' => 'Data Analytics',
                    'jobs_posted' => 8,
                    'location' => 'Hyderabad, India'
                ),
                array(
                    'id' => 3,
                    'name' => 'Digital Marketing Co.',
                    'industry' => 'Marketing',
                    'jobs_posted' => 12,
                    'location' => 'Mumbai, India'
                )
            );
            ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Industry</th>
                        <th>Jobs Posted</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($companies as $c): ?>
                        <tr>
                            <td>
                                <a href="company-profile.php?id=<?php echo (int)$c['id']; ?>">
                                    <?php echo htmlspecialchars($c['name']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($c['industry']); ?></td>
                            <td><?php echo $c['jobs_posted']; ?></td>
                            <td><?php echo htmlspecialchars($c['location']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card" id="students-section">
            <h3>Top Students</h3>
            <?php
            // Sample student data
            $students = array(
                array(
                    'id' => 1,
                    'name' => 'Rahul Sharma',
                    'course' => 'B.Tech Computer Science',
                    'skills' => 'Java, Python, Web Development',
                    'cgpa' => 9.2
                ),
                array(
                    'id' => 2,
                    'name' => 'Priya Patel',
                    'course' => 'MCA',
                    'skills' => 'Data Science, Machine Learning',
                    'cgpa' => 9.5
                ),
                array(
                    'id' => 3,
                    'name' => 'Amit Kumar',
                    'course' => 'B.Tech Information Technology',
                    'skills' => 'Android Development, UI/UX',
                    'cgpa' => 8.9
                )
            );
            ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Course</th>
                        <th>Skills</th>
                        <th>CGPA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($students as $s): ?>
                        <tr>
                            <td>
                                <a href="student_profile.php?id=<?php echo $s['id']; ?>">
                                    <?php echo htmlspecialchars($s['name']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($s['course']); ?></td>
                            <td><?php echo htmlspecialchars($s['skills']); ?></td>
                            <td><?php echo $s['cgpa']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card" id="tpo-section">
            <h3>How It Works</h3>
            <div class="how-it-works">
                <div class="how-it-works-item" onclick="handleUserTypeClick('student')">
                    <div class="how-it-works-icon">🎓</div>
                    <h4>Students</h4>
                    <p>Create profiles, apply for jobs, and showcase your skills to top companies.</p>
                    <button class="btn btn-primary" style="margin-top: 1rem;">Get Started as Student</button>
                </div>
                <div class="how-it-works-item" onclick="handleUserTypeClick('company')">
                    <div class="how-it-works-icon">🏢</div>
                    <h4>Companies</h4>
                    <p>Post job opportunities, find talented students, and collaborate with colleges.</p>
                    <button class="btn btn-primary" style="margin-top: 1rem;">Join as Company</button>
                </div>
                <div class="how-it-works-item" onclick="handleUserTypeClick('tpo')">
                    <div class="how-it-works-icon">📚</div>
                    <h4>TPOs</h4>
                    <p>Manage campus placements, coordinate with companies, and track student progress.</p>
                    <button class="btn btn-primary" style="margin-top: 1rem;">Join as TPO</button>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>About</h3>
                <p>VishwaCollab is a platform connecting students, companies, and Training & Placement Officers for better campus–industry collaboration.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#jobs-section">Jobs</a></li>
                    <li><a href="#companies-section">Companies</a></li>
                    <li><a href="#students-section">Students</a></li>
                    <li><a href="#tpo-section">TPO</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p>Email: support@vishwacollab.com</p>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2025 VishwaCollab. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Handle user type clicks
        function handleUserTypeClick(userType) {
            // Redirect to signup page with pre-selected user type
            window.location.href = `signup.php?type=${userType}`;
        }

        // Toggle user dropdown
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userProfile = document.getElementById('userProfile');
            const dropdown = document.getElementById('userDropdown');
            
            if (userProfile && dropdown && !userProfile.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Search functionality
        function handleSearch() {
            const searchInput = document.querySelector('.search-input');
            const searchTerm = searchInput.value.trim();
            
            if (searchTerm) {
                // Perform database search
                performDatabaseSearch(searchTerm);
            } else {
                // Show all content
                showAllContent();
            }
        }

        function performDatabaseSearch(searchTerm) {
            // Show loading state
            showSearchLoading();
            
            // Create form data for AJAX request
            const formData = new FormData();
            formData.append('search_term', searchTerm);
            formData.append('action', 'search');
            
            // Send AJAX request to search handler
            fetch('search_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                displaySearchResults(data, searchTerm);
            })
            .catch(error => {
                console.error('Search error:', error);
                showSearchError();
            });
        }

        function showSearchLoading() {
            const searchResults = document.getElementById('searchResults');
            if (searchResults) {
                searchResults.innerHTML = '<div class="search-loading">Searching...</div>';
                searchResults.style.display = 'block';
            }
        }

        function displaySearchResults(data, searchTerm) {
            const searchResults = document.getElementById('searchResults');
            if (!searchResults) return;
            
            let html = `<div class="search-results-header">
                <h3>Search Results for "${searchTerm}"</h3>
                <span class="result-count">${data.total_results} results found</span>
            </div>`;
            
            if (data.jobs && data.jobs.length > 0) {
                html += '<div class="search-section"><h4>Jobs</h4>';
                data.jobs.forEach(job => {
                    html += `<div class="search-result-item">
                        <h5><a href="job_details.php?id=${job.id}">${job.title}</a></h5>
                        <p class="company">${job.company_name}</p>
                        <p class="description">${job.description}</p>
                        <span class="skills">${job.requirements}</span>
                    </div>`;
                });
                html += '</div>';
            }
            
            if (data.companies && data.companies.length > 0) {
                html += '<div class="search-section"><h4>Companies</h4>';
                data.companies.forEach(company => {
                    html += `<div class="search-result-item">
                        <h5><a href="company-profile.php?id=${company.id}">${company.name}</a></h5>
                        <p class="industry">${company.industry}</p>
                        <p class="location">${company.location}</p>
                    </div>`;
                });
                html += '</div>';
            }
            
            if (data.students && data.students.length > 0) {
                html += '<div class="search-section"><h4>Students</h4>';
                data.students.forEach(student => {
                    html += `<div class="search-result-item">
                        <h5><a href="student_profile.php?id=${student.id}">${student.name}</a></h5>
                        <p class="course">${student.course}</p>
                        <p class="skills">${student.skills}</p>
                    </div>`;
                });
                html += '</div>';
            }
            
            if (data.total_results === 0) {
                html = `<div class="no-results">
                    <h3>No results found for "${searchTerm}"</h3>
                    <p>Try different keywords or check your spelling.</p>
                </div>`;
            }
            
            searchResults.innerHTML = html;
            searchResults.style.display = 'block';
        }

        function showSearchError() {
            const searchResults = document.getElementById('searchResults');
            if (searchResults) {
                searchResults.innerHTML = '<div class="search-error">Search temporarily unavailable. Please try again.</div>';
                searchResults.style.display = 'block';
            }
        }

        // links now navigate directly

        function filterContent(searchTerm) {
            const cards = document.querySelectorAll('.card');
            const searchTermLower = searchTerm.toLowerCase();
            
            cards.forEach(card => {
                const cardText = card.textContent.toLowerCase();
                if (cardText.includes(searchTermLower)) {
                    card.style.display = 'block';
                    card.style.animation = 'fadeIn 0.5s ease-out';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function showAllContent() {
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.style.display = 'block';
                card.style.animation = 'fadeIn 0.5s ease-out';
            });
            
            // Hide search results
            const searchResults = document.getElementById('searchResults');
            if (searchResults) {
                searchResults.style.display = 'none';
            }
        }

        // Smooth scrolling for navigation links
        function smoothScrollTo(targetId) {
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Search button functionality
            const searchButton = document.querySelector('.search-button');
            if (searchButton) {
                searchButton.addEventListener('click', handleSearch);
            }

            // Search input enter key and real-time search
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                let searchTimeout;
                
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        handleSearch();
                    }
                });
                
                // Real-time search as user types
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.trim();
                    
                    // Clear previous timeout
                    clearTimeout(searchTimeout);
                    
                    if (searchTerm.length >= 2) {
                        // Wait 500ms after user stops typing
                        searchTimeout = setTimeout(() => {
                            performDatabaseSearch(searchTerm);
                        }, 500);
                    } else if (searchTerm.length === 0) {
                        // Clear results if search is empty
                        showAllContent();
                    }
                });
            }

            // Navigation link smooth scrolling
            const navLinks = document.querySelectorAll('nav a[href^="#"]');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    smoothScrollTo(targetId);
                });
            });

            // Footer link smooth scrolling
            const footerLinks = document.querySelectorAll('.footer-section a[href^="#"]');
            footerLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    smoothScrollTo(targetId);
                });
            });

            // Tab switching (if tabs exist)
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Hide all tab content
                    const tabContents = document.querySelectorAll('.tab-content');
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Show corresponding tab content
                    const tabId = this.getAttribute('data-tab');
                    if (tabId) {
                        document.getElementById(tabId).classList.add('active');
                    }
                });
            });
            
            // Dashboard navigation
            const dashboardLinks = document.querySelectorAll('.dashboard-nav a');
            dashboardLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    dashboardLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                    
                    // In a real application, you would load the corresponding content here
                    alert('Loading ' + this.textContent + ' section...');
                });
            });

            // direct links; no interception
        });
    </script>
</body>
</html>