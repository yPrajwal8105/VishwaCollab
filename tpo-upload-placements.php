<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole() !== 'tpo') {
    header("Location: login.php");
    exit();
}
require_once 'db_connect.php';

// Status flags for UI
$success = '';
$error = '';
$uploaded_count = 0;
$skipped_count = 0;

// Handle CSV file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    if (isDatabaseAvailable()) {
        // First, ensure the table exists
        $create_table = "CREATE TABLE IF NOT EXISTS alumni_placements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_name VARCHAR(100) NOT NULL,
            student_email VARCHAR(100),
            course VARCHAR(100),
            batch_year YEAR,
            company_name VARCHAR(200) NOT NULL,
            job_title VARCHAR(200) NOT NULL,
            salary VARCHAR(50),
            location VARCHAR(100),
            placement_date DATE,
            cgpa DECIMAL(3,2),
            skills TEXT,
            additional_info TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->query($create_table);

        // Decide import mode: replace all (default) or append
        $replace_mode = $_POST['replace_mode'] ?? 'replace_all';
        if ($replace_mode === 'replace_all') {
            // Fully reset alumni placements so we always show the latest official file
            $conn->query("TRUNCATE TABLE alumni_placements");
        }

        if ($_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($file, 'r');

            if ($handle !== false) {
                // Skip header row
                $header = fgetcsv($handle);

                $stmt = $conn->prepare("INSERT INTO alumni_placements 
                    (student_name, student_email, course, batch_year, company_name, job_title, salary, location, placement_date, cgpa, skills, additional_info) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                while (($data = fgetcsv($handle)) !== false) {
                    // Ignore completely empty lines
                    $nonEmpty = array_filter($data, function ($v) {
                        return trim((string)$v) !== '';
                    });
                    if (count($nonEmpty) === 0) {
                        continue;
                    }

                    // Map CSV columns based on your Excel sheet structure:
                    // 0: Sr.N, 1: PRN/GRN, 2: Student_Name, 3: Division, 4: Roll No., 5: WhatsApp, 6: Email, 
                    // 7: Company Name, 8: Type of Internship, 9: Source, 10: Start Date, 11: Duration, 12: Stipend

                    $student_name = trim($data[2] ?? '');
                    $student_email = trim($data[6] ?? '');
                    $course = trim($data[3] ?? '');

                    // Extract batch year from email (e.g., name21@vit.edu = 2021, name22@vit.edu = 2022)
                    $batch_year = null;
                    if ($student_email !== '' && preg_match('/(\d{2})@/', $student_email, $matches)) {
                        $year_suffix = (int)$matches[1];
                        if ($year_suffix >= 0 && $year_suffix <= 99) {
                            $batch_year = 2000 + $year_suffix;
                        }
                    }

                    $company_name = trim($data[7] ?? '');
                    $job_title = trim($data[8] ?? 'Industry Internship');
                    if ($job_title === '') {
                        $job_title = 'Industry Internship';
                    }

                    // Clean salary format
                    $salary = trim($data[12] ?? '');
                    $salary = preg_replace('/[Rr][Ss]\.?\s*/', '', $salary);
                    $salary = preg_replace('/\s*per\s*month/i', '', $salary);
                    $salary = preg_replace('/\s*\/\s*month/i', '', $salary);
                    $salary = trim($salary);
                    if ($salary === '' || strtoupper($salary) === 'NA' || strtoupper($salary) === 'N/A') {
                        $salary = null;
                    }

                    // Do not store personal phone/WhatsApp numbers.
                    // The sheet doesn't provide a location column, so we keep this empty.
                    $location = '';

                    // Parse date (handles M-D-YYYY, MM/DD/YYYY formats)
                    $placement_date = null;
                    if (!empty($data[10])) {
                        $date_str = trim($data[10]);
                        $normalized_date = str_replace('/', '-', $date_str);
                        $parts = explode('-', $normalized_date);
                        if (count($parts) === 3) {
                            $month = (int)$parts[0];
                            $day = (int)$parts[1];
                            $year = (int)$parts[2];
                            if ($year < 100) {
                                $year = 2000 + $year;
                            }
                            if (checkdate($month, $day, $year)) {
                                $placement_date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                            }
                        }
                        if (!$placement_date) {
                            $timestamp = strtotime($date_str);
                            if ($timestamp !== false) {
                                $placement_date = date('Y-m-d', $timestamp);
                            }
                        }
                    }

                    $cgpa = null; // Not in sheet
                    // Store no extra info; we only care about main fields
                    $skills = '';
                    $additional_info = '';

                    if ($student_name !== '' && $company_name !== '' && $job_title !== '') {
                        $stmt->bind_param(
                            "sssssssssdss",
                            $student_name,
                            $student_email,
                            $course,
                            $batch_year,
                            $company_name,
                            $job_title,
                            $salary,
                            $location,
                            $placement_date,
                            $cgpa,
                            $skills,
                            $additional_info
                        );

                        if ($stmt->execute()) {
                            $uploaded_count++;
                        }
                    } else {
                        $skipped_count++;
                    }
                }

                fclose($handle);
                $success = "Successfully imported {$uploaded_count} placement records.";
                if ($skipped_count > 0) {
                    $success .= " Skipped {$skipped_count} row(s) due to missing required data.";
                }
            } else {
                $error = "Failed to read CSV file.";
            }
        } else {
            $error = "File upload error. Please try again.";
        }
    }
}

// Handle manual entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_placement'])) {
    if (isDatabaseAvailable()) {
        // Ensure table exists
        $create_table = "CREATE TABLE IF NOT EXISTS alumni_placements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_name VARCHAR(100) NOT NULL,
            student_email VARCHAR(100),
            course VARCHAR(100),
            batch_year YEAR,
            company_name VARCHAR(200) NOT NULL,
            job_title VARCHAR(200) NOT NULL,
            salary VARCHAR(50),
            location VARCHAR(100),
            placement_date DATE,
            cgpa DECIMAL(3,2),
            skills TEXT,
            additional_info TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->query($create_table);
        
        $student_name = $_POST['student_name'] ?? '';
        $student_email = $_POST['student_email'] ?? '';
        $course = $_POST['course'] ?? '';
        $batch_year = !empty($_POST['batch_year']) ? $_POST['batch_year'] : null;
        $company_name = $_POST['company_name'] ?? '';
        $job_title = $_POST['job_title'] ?? '';
        $salary = $_POST['salary'] ?? '';
        $location = $_POST['location'] ?? '';
        $placement_date = !empty($_POST['placement_date']) ? $_POST['placement_date'] : null;
        $cgpa = !empty($_POST['cgpa']) ? $_POST['cgpa'] : null;
        // Reuse "skills" column to store LinkedIn URL for simplicity
        $skills = $_POST['linkedin_url'] ?? '';
        $additional_info = $_POST['additional_info'] ?? '';
        
        if (!empty($student_name) && !empty($company_name) && !empty($job_title)) {
            $stmt = $conn->prepare("INSERT INTO alumni_placements 
                (student_name, student_email, course, batch_year, company_name, job_title, salary, location, placement_date, cgpa, skills, additional_info) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssdss", 
                $student_name, $student_email, $course, $batch_year, 
                $company_name, $job_title, $salary, $location, 
                $placement_date, $cgpa, $skills, $additional_info);
            
            if ($stmt->execute()) {
                $success = "Placement record added successfully!";
            } else {
                $error = "Failed to add placement record.";
            }
        } else {
            $error = "Please fill in required fields (Student Name, Company Name, Job Title).";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Placements - TPO Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #4caf50; --shadow: 0 4px 12px rgba(0,0,0,0.15); }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        .dashboard-container { display: flex; min-height: 100vh; background: #f5f7fa; }
        .sidebar { width: 250px; background: white; box-shadow: var(--shadow); position: fixed; height: 100vh; overflow-y: auto; }
        .sidebar-header { padding: 2rem 1.5rem; background: var(--primary); color: white; }
        .sidebar-menu { padding: 1rem 0; }
        .sidebar-menu a { display: flex; align-items: center; padding: 1rem 1.5rem; color: #202124; text-decoration: none; transition: all 0.3s; border-left: 4px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: #e8f5e9; border-left-color: var(--primary); color: var(--primary); }
        .main-content { flex: 1; margin-left: 250px; padding: 2rem; }
        .card { background: white; border-radius: 10px; box-shadow: var(--shadow); padding: 2rem; margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #202124; }
        .form-control { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; }
        .form-control:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(76,175,80,0.2); }
        .btn { padding: 0.7rem 1.8rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; font-size: 1rem; background: var(--primary); color: white; }
        .btn:hover { background: #45a049; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #2e7d32; }
        .alert-error { background: #ffebee; color: #c62828; border-left: 4px solid #c62828; }
        .info-box { background: #e3f2fd; color: #1565c0; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border-left: 4px solid #2196f3; }
        .tabs { display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 2px solid #e0e0e0; }
        .tab { padding: 1rem 2rem; cursor: pointer; border-bottom: 3px solid transparent; font-weight: 600; }
        .tab.active { color: var(--primary); border-bottom-color: var(--primary); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>TPO Dashboard</h2>
            </div>
            <div class="sidebar-menu">
                <a href="tpo-dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="tpo-students.php"><i class="fas fa-user-graduate"></i> Students</a>
                <a href="tpo-companies.php"><i class="fas fa-building"></i> Companies</a>
                <a href="tpo-jobs.php"><i class="fas fa-briefcase"></i> Jobs</a>
                <a href="tpo-placements.php"><i class="fas fa-chart-line"></i> Placements</a>
                <a href="tpo-upload-placements.php" class="active"><i class="fas fa-upload"></i> Upload Placements</a>
                <a href="tpo-reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <h1>Upload Senior Placements</h1>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="info-box">
                <strong><i class="fas fa-info-circle"></i> CSV Format:</strong><br>
                Your CSV file should match your Excel sheet format with these columns (in order):<br>
                <code>Sr.N, PRN/GRN, Student_Name, Division, Roll No., WhatsApp No., Email, Company Name, Type of Internship, Internship Source, Start Date, Duration, Stipend Amount</code><br><br>
                <strong>Note:</strong> 
                <ul style="margin: 0.5rem 0 0 1.5rem;">
                    <li>First row should be headers</li>
                    <li>Date format: M-D-YYYY (e.g., 7-14-2025) or MM/DD/YYYY</li>
                    <li>Batch Year will need to be added manually after import (or update the CSV to include it)</li>
                    <li>Salary/Stipend can be in format: "25000", "Rs.10000 per month", "NA", etc.</li>
                </ul>
            </div>
            
            <div class="tabs">
                <div class="tab active" onclick="switchTab('csv')">📁 Upload CSV File</div>
                <div class="tab" onclick="switchTab('manual')">✍️ Manual Entry</div>
            </div>
            
            <!-- CSV Upload Tab -->
            <div id="csv-tab" class="tab-content active">
                <div class="card">
                    <h2>Upload CSV File</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Select CSV File</label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                        </div>
                        <div class="form-group">
                            <label>Import Mode</label>
                            <select name="replace_mode" class="form-control">
                                <option value="replace_all" selected>Replace all existing records (official latest list)</option>
                                <option value="append">Append to existing records</option>
                            </select>
                        </div>
                        <button type="submit" class="btn"><i class="fas fa-upload"></i> Upload and Import</button>
                    </form>
                </div>
            </div>
            
            <!-- Manual Entry Tab -->
            <div id="manual-tab" class="tab-content">
                <div class="card">
                    <h2>Add Placement Manually</h2>
                    <form method="POST">
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                            <div class="form-group">
                                <label>Student Name *</label>
                                <input type="text" name="student_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Student Email</label>
                                <input type="email" name="student_email" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Course</label>
                                <input type="text" name="course" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Batch Year</label>
                                <input type="number" name="batch_year" class="form-control" min="2000" max="2099">
                            </div>
                            <div class="form-group">
                                <label>Company Name *</label>
                                <input type="text" name="company_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Job Title *</label>
                                <input type="text" name="job_title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Salary</label>
                                <input type="text" name="salary" class="form-control" placeholder="e.g., 8-12 LPA">
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" name="location" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Placement Date</label>
                                <input type="date" name="placement_date" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>CGPA</label>
                                <input type="number" step="0.01" name="cgpa" class="form-control" min="0" max="10">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>LinkedIn Profile URL</label>
                            <input type="url" name="linkedin_url" class="form-control" placeholder="https://www.linkedin.com/in/username">
                        </div>
                        <div class="form-group">
                            <label>Additional Info</label>
                            <textarea name="additional_info" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" name="add_placement" class="btn"><i class="fas fa-plus"></i> Add Placement</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(tab + '-tab').classList.add('active');
        }
    </script>
</body>
</html>

