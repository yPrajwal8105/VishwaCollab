<?php
require_once 'config.php';
require_once 'db_connect.php';

/**
 * Ensure the MySQL connection is alive.
 * Reconnects automatically if it dropped (e.g., "MySQL server has gone away").
 */
function ensureDbConnection() {
    global $conn;
    
    if ($conn instanceof mysqli) {
        try {
            if (@$conn->ping()) {
                return $conn;
            }
        } catch (Throwable $t) {
            // fall through to reconnect
        }
        $conn->close();
    }
    
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Database reconnection failed: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8');
    return $conn;
}

// Try to load composer autoloader if available
$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

require_once 'lib/resume_parser.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || getUserRole() !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';
$latestResume = null;

try {
    ensureDbConnection();
} catch (Exception $dbEx) {
    $error = $dbEx->getMessage();
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['resume'])) {
    $file = $_FILES['resume'];
    $allowedTypes = ['application/pdf', 'application/msword', 
                     'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $allowedExts = ['pdf', 'doc', 'docx'];
    
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileType = $file['type'];
    $fileSize = $file['size'];
    $maxSize = 10 * 1024 * 1024; // 10MB
    
    if (!in_array($fileExt, $allowedExts) || !in_array($fileType, $allowedTypes)) {
        $error = 'Invalid file type. Please upload PDF or DOCX files only.';
    } elseif ($fileSize > $maxSize) {
        $error = 'File size must be less than 10MB.';
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Error uploading file.';
    } else {
        // Create uploads directory if it doesn't exist
        $uploadDir = 'uploads/resumes/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = uniqid() . '_' . basename($file['name']);
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            try {
                // Parse resume
                $parsedData = ResumeParser::parseResume($filePath, $fileType);
                
                // Save resume record
                $stmt = $conn->prepare("INSERT INTO resumes (user_id, file_name, file_path, file_size, file_type) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issis", $user_id, $file['name'], $filePath, $fileSize, $fileType);
                $stmt->execute();
                $resumeId = $stmt->insert_id;
                $stmt->close();
                
                // Save parsed data
                $skillsJson = json_encode($parsedData['skills']);
                $workExpJson = json_encode($parsedData['work_experience']);
                $educationJson = json_encode($parsedData['education']);
                $projectsJson = json_encode($parsedData['projects']);
                
                $stmt = $conn->prepare("INSERT INTO parsed_resume_data (resume_id, user_id, raw_text, work_experience, skills, education, projects) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iisssss", $resumeId, $user_id, $parsedData['raw_text'], $workExpJson, $skillsJson, $educationJson, $projectsJson);
                $stmt->execute();
                $stmt->close();
                
                $message = 'Resume uploaded and parsed successfully!';
                
                // Update student skills if students table exists and has skills column
                if (!empty($parsedData['skills'])) {
                    ensureDbConnection();
                    $skillsString = implode(', ', $parsedData['skills']);
                    // Check if students table exists and has skills column
                    $checkTable = $conn->query("SHOW TABLES LIKE 'students'");
                    if ($checkTable && $checkTable->num_rows > 0) {
                        $checkColumn = $conn->query("SHOW COLUMNS FROM students LIKE 'skills'");
                        if ($checkColumn && $checkColumn->num_rows > 0) {
                            $updateStmt = $conn->prepare("UPDATE students SET skills = ? WHERE user_id = ?");
                            if ($updateStmt) {
                                $updateStmt->bind_param("si", $skillsString, $user_id);
                                $updateStmt->execute();
                                $updateStmt->close();
                            }
                        }
                    }
                }
                
            } catch (Exception $e) {
                $error = 'Error parsing resume: ' . $e->getMessage();
                // Delete uploaded file if parsing failed
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        } else {
            $error = 'Failed to upload file.';
        }
    }
}

// Fetch latest resume
if (isDatabaseAvailable()) {
    ensureDbConnection();
    // Check if resumes table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'resumes'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        // Use uploaded_at if it exists, otherwise use created_at
        $orderField = 'created_at';
        $checkField = $conn->query("SHOW COLUMNS FROM resumes LIKE 'uploaded_at'");
        if ($checkField && $checkField->num_rows > 0) {
            $orderField = 'uploaded_at';
        }
        
        $stmt = $conn->prepare("SELECT * FROM resumes WHERE user_id = ? ORDER BY $orderField DESC LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $latestResume = $result->fetch_assoc();
                
                // Get parsed data
                $parsedStmt = $conn->prepare("SELECT * FROM parsed_resume_data WHERE resume_id = ?");
                if ($parsedStmt) {
                    $parsedStmt->bind_param("i", $latestResume['id']);
                    $parsedStmt->execute();
                    $parsedResult = $parsedStmt->get_result();
                    if ($parsedResult && $parsedResult->num_rows > 0) {
                        $latestResume['parsed_data'] = $parsedResult->fetch_assoc();
                    }
                    $parsedStmt->close();
                }
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Resume - VishwaCollab</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .upload-area {
            border: 2px dashed #1a73e8;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background: #f8f9ff;
            margin: 20px 0;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover {
            background: #e8f0fe;
            border-color: #0d47a1;
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
        .btn:hover {
            background: #0d47a1;
        }
        .message {
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        .resume-info {
            background: #f0f7ff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .skill-tag {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 12px;
            border-radius: 20px;
            margin: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-file-upload"></i> Upload Resume</h1>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="upload-area" onclick="document.getElementById('resume').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: #1a73e8; margin-bottom: 20px;"></i>
                <p style="font-size: 18px; margin: 10px 0;">Click to upload or drag and drop</p>
                <p style="color: #666;">PDF, DOC, DOCX (Max 10MB)</p>
                <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" required style="display: none;" onchange="this.form.submit()">
            </div>
        </form>
        
        <?php if ($latestResume): ?>
            <div class="resume-info">
                <h3><i class="fas fa-file-alt"></i> Latest Resume</h3>
                <p><strong>File:</strong> <?php echo htmlspecialchars($latestResume['file_name']); ?></p>
                <p><strong>Uploaded:</strong> <?php 
                    $uploadDate = $latestResume['uploaded_at'] ?? $latestResume['created_at'] ?? 'N/A';
                    echo $uploadDate !== 'N/A' ? date('F j, Y g:i A', strtotime($uploadDate)) : 'N/A';
                ?></p>
                
                <?php if (isset($latestResume['parsed_data'])): 
                    $parsed = $latestResume['parsed_data'];
                    $skills = json_decode($parsed['skills'], true) ?: [];
                ?>
                    <h4>Extracted Skills:</h4>
                    <?php foreach ($skills as $skill): ?>
                        <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                    <?php endforeach; ?>
                    
                    <div style="margin-top: 20px;">
                        <a href="student-ats-score.php?resume_id=<?php echo $latestResume['id']; ?>" class="btn">
                            <i class="fas fa-chart-line"></i> Get ATS Score
                        </a>
                        <a href="student-quiz.php?resume_id=<?php echo $latestResume['id']; ?>" class="btn" style="background: #2e7d32;">
                            <i class="fas fa-question-circle"></i> Take Quiz
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px;">
            <a href="student-dashboard.php" class="btn" style="background: #666;">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>



