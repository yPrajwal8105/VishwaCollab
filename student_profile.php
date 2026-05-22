<?php
require_once 'config.php';
require_once 'db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$student = null;

if ($id > 0 && isDatabaseAvailable()) {
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - VishwaCollab</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', sans-serif; }
        .profile-container { max-width: 1000px; margin: 2rem auto; padding: 0 1rem; }
        .profile-header { background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 2rem; margin-bottom: 2rem; }
        .profile-header h1 { color: #202124; margin-bottom: 1rem; }
        .profile-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem; }
        .info-item { padding: 0.5rem 0; }
        .info-item strong { color: #1a73e8; }
        .skills-section { margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e0e0e0; }
        .skill-tag { display: inline-block; background: #e8f0fe; color: #1a73e8; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.9rem; margin: 0.3rem 0.3rem 0.3rem 0; }
        .btn { padding: 0.7rem 1.8rem; border-radius: 6px; font-weight: 600; text-decoration: none; display: inline-block; background: #1a73e8; color: white; }
    </style>
</head>
<body>
    <header style="background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15); padding: 1rem 5%;">
        <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1400px; margin: 0 auto;">
            <a href="index.php" style="font-size: 2rem; color: #202124; text-decoration: none; font-weight: 700;"><span style="color: #1a73e8;">Vishwa</span>Collab</a>
            <nav>
                <a href="index.php" class="btn">Back to Home</a>
            </nav>
        </div>
    </header>

    <div class="profile-container">
        <?php if ($student): ?>
            <div class="profile-header">
                <h1><i class="fas fa-user-graduate"></i> <?php echo htmlspecialchars($student['name']); ?></h1>
                <div class="profile-info">
                    <div class="info-item"><strong>Course:</strong> <?php echo htmlspecialchars($student['course'] ?? 'Not specified'); ?></div>
                    <div class="info-item"><strong>CGPA:</strong> <?php echo htmlspecialchars($student['cgpa'] ?? 'Not specified'); ?></div>
                    <div class="info-item"><strong>Email:</strong> <?php echo htmlspecialchars($student['email'] ?? ''); ?></div>
                    <?php if (!empty($student['address'])): ?>
                        <div class="info-item"><strong>Address:</strong> <?php echo htmlspecialchars($student['address']); ?></div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($student['skills'])): ?>
                    <div class="skills-section">
                        <h3>Skills</h3>
                        <?php 
                        $skills = explode(',', $student['skills']);
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
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="profile-header">
                <h2>Student Not Found</h2>
                <p>The student you're looking for doesn't exist.</p>
                <a href="index.php" class="btn">Back to Home</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>


