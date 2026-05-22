<?php
require_once 'config.php';
if (!isLoggedIn() || getUserRole() !== 'student') {
    header("Location: login.php");
    exit();
}
require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['resume'])) {
    $uploadDir = 'uploads/resumes/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $file = $_FILES['resume'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    if ($fileError === 0) {
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'doc', 'docx'];
        
        if (in_array($fileExt, $allowedExts)) {
            if ($fileSize < 5 * 1024 * 1024) {
                $newFileName = 'resume_' . $user_id . '_' . time() . '.' . $fileExt;
                $fileDestination = $uploadDir . $newFileName;
                
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    $success = "Resume uploaded successfully!";
                } else {
                    $error = "Failed to upload resume.";
                }
            } else {
                $error = "File size must be less than 5MB.";
            }
        } else {
            $error = "Invalid file type. Please upload PDF, DOC, or DOCX files only.";
        }
    } else {
        $error = "Error uploading file.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Builder - Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #1a73e8; --shadow: 0 4px 12px rgba(0,0,0,0.15); }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        .dashboard-container { display: flex; min-height: 100vh; background: #f5f7fa; }
        .sidebar { width: 250px; background: white; box-shadow: var(--shadow); position: fixed; height: 100vh; overflow-y: auto; }
        .sidebar-header { padding: 2rem 1.5rem; background: var(--primary); color: white; }
        .sidebar-menu { padding: 1rem 0; }
        .sidebar-menu a { display: flex; align-items: center; padding: 1rem 1.5rem; color: #202124; text-decoration: none; transition: all 0.3s; border-left: 4px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: #e8f0fe; border-left-color: var(--primary); color: var(--primary); }
        .main-content { flex: 1; margin-left: 250px; padding: 2rem; }
        .upload-card { background: white; border-radius: 10px; box-shadow: var(--shadow); padding: 2rem; }
        .file-upload-area { border: 2px dashed var(--primary); border-radius: 8px; padding: 3rem; text-align: center; background: #f8f9ff; cursor: pointer; transition: all 0.3s; }
        .file-upload-area:hover { background: #e8f0fe; }
        .form-group { margin-bottom: 1.5rem; }
        .form-control { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; }
        .btn { padding: 0.7rem 1.8rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; font-size: 1rem; background: var(--primary); color: white; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #2e7d32; }
        .alert-error { background: #ffebee; color: #c62828; border-left: 4px solid #c62828; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Student Dashboard</h2>
            </div>
            <div class="sidebar-menu">
                <a href="student-dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="student-jobs.php"><i class="fas fa-search"></i> Job Search</a>
                <a href="student-applications.php"><i class="fas fa-file-alt"></i> Applications</a>
                <a href="student-interviews.php"><i class="fas fa-calendar-alt"></i> Interviews</a>
                <a href="student-mock-interview.php"><i class="fas fa-microphone"></i> Mock Interview</a>
                <a href="student-resume-ai.php"><i class="fas fa-magic"></i> AI Resume Builder</a>
                <a href="student-resume.php" class="active"><i class="fas fa-file-pdf"></i> Resume Builder</a>
                <a href="student-chat.php"><i class="fas fa-comments"></i> Chat with Seniors</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <h1>Resume Builder</h1>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <div class="upload-card">
                <h2>Upload Your Resume</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="file-upload-area" onclick="document.getElementById('resumeFile').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"></i>
                        <p style="font-size: 1.1rem; margin-bottom: 0.5rem;">Click to upload or drag and drop</p>
                        <p style="color: #666;">PDF, DOC, DOCX (Max 5MB)</p>
                        <input type="file" name="resume" id="resumeFile" style="display: none;" accept=".pdf,.doc,.docx" required>
                    </div>
                    <button type="submit" class="btn">Upload Resume</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>


