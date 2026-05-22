<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole() !== 'tpo') {
    header("Location: login.php");
    exit();
}
require_once 'db_connect.php';

$students = [];

if (isDatabaseAvailable()) {
    // Avoid duplicate rows caused by sample data / repeated imports.
    // Show one record per unique email (or per student id when email is missing).
    $sql = "
        SELECT s.*
        FROM students s
        INNER JOIN (
            SELECT 
                CASE 
                    WHEN email IS NULL OR email = '' THEN CONCAT('id_', id)
                    ELSE email
                END AS dedupe_key,
                MIN(id) AS min_id
            FROM students
            GROUP BY dedupe_key
        ) t ON t.min_id = s.id
        ORDER BY s.name ASC
    ";

    if ($result = $conn->query($sql)) {
        $students = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - TPO Dashboard</title>
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
        .table { width: 100%; background: white; border-radius: 10px; box-shadow: var(--shadow); overflow: hidden; }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid #e0e0e0; }
        .table th { background: var(--primary); color: white; font-weight: 600; }
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
                <a href="tpo-students.php" class="active"><i class="fas fa-user-graduate"></i> Students</a>
                <a href="tpo-companies.php"><i class="fas fa-building"></i> Companies</a>
                <a href="tpo-jobs.php"><i class="fas fa-briefcase"></i> Jobs</a>
                <a href="tpo-placements.php"><i class="fas fa-chart-line"></i> Placements</a>
                <a href="tpo-reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <h1>Students</h1>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>CGPA</th>
                        <th>Skills</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 2rem;">No students registered yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($student['course'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($student['cgpa'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($student['skills'] ?? ''); ?></td>
                                <td><a href="student_profile.php?id=<?php echo $student['id']; ?>" style="color: var(--primary);">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>






