<?php
require_once 'config.php';
requireAuth();
require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$profile_data = null;

if (isDatabaseAvailable()) {
    if ($role === 'student') {
        $stmt = $conn->prepare("SELECT * FROM students WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $profile_data = $stmt->get_result()->fetch_assoc();
    } elseif ($role === 'company') {
        $stmt = $conn->prepare("SELECT * FROM companies WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $profile_data = $stmt->get_result()->fetch_assoc();
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (isDatabaseAvailable() && $profile_data) {
        if ($role === 'student') {
            $name = $_POST['name'] ?? '';
            $course = $_POST['course'] ?? '';
            $skills = $_POST['skills'] ?? '';
            $cgpa = $_POST['cgpa'] ?? 0;
            $address = $_POST['address'] ?? '';
            
            $stmt = $conn->prepare("UPDATE students SET name=?, course=?, skills=?, cgpa=?, address=? WHERE user_id=?");
            $stmt->bind_param("sssdsi", $name, $course, $skills, $cgpa, $address, $user_id);
            if ($stmt->execute()) {
                $success = "Profile updated successfully!";
                $stmt = $conn->prepare("SELECT * FROM students WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $profile_data = $stmt->get_result()->fetch_assoc();
            } else {
                $error = "Failed to update profile.";
            }
        } elseif ($role === 'company') {
            $name = $_POST['name'] ?? '';
            $industry = $_POST['industry'] ?? '';
            $location = $_POST['location'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $website = $_POST['website'] ?? '';
            $description = $_POST['description'] ?? '';
            
            $stmt = $conn->prepare("UPDATE companies SET name=?, industry=?, location=?, phone=?, website=?, description=? WHERE user_id=?");
            $stmt->bind_param("ssssssi", $name, $industry, $location, $phone, $website, $description, $user_id);
            if ($stmt->execute()) {
                $success = "Profile updated successfully!";
                $stmt = $conn->prepare("SELECT * FROM companies WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $profile_data = $stmt->get_result()->fetch_assoc();
            } else {
                $error = "Failed to update profile.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - VishwaCollab</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .profile-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .profile-header-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        .profile-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            font-weight: bold;
        }
        .profile-info h1 {
            margin: 0 0 0.5rem 0;
            color: #202124;
        }
        .profile-info p {
            color: #5f6368;
            margin: 0.3rem 0;
        }
        .profile-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e0e0e0;
        }
        .tab-btn {
            padding: 1rem 2rem;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: #5f6368;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        .tab-btn.active {
            color: #1a73e8;
            border-bottom-color: #1a73e8;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .profile-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #202124;
        }
        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
        }
        .form-control:focus {
            border-color: #1a73e8;
            outline: none;
            box-shadow: 0 0 0 3px rgba(26,115,232,0.2);
        }
        .btn {
            padding: 0.7rem 1.8rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #1a73e8;
            color: white;
        }
        .btn-primary:hover {
            background: #0d47a1;
        }
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 5%; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <div class="logo" style="display: flex; align-items: center; gap: 12px;">
                <h1 style="font-size: 2rem; color: #202124;"><span style="color: #1a73e8;">Vishwa</span>Collab</h1>
            </div>
            <nav>
                <a href="<?php echo ($_SESSION['role'] ?? 'student'); ?>-dashboard.php" style="padding: 0.7rem 1.8rem; background: #1a73e8; color: white; border-radius: 6px; text-decoration: none; font-weight: 600;">Dashboard</a>
            </nav>
        </div>
    </header>
    
    <div class="profile-container">
        <div class="profile-header-card">
            <div class="profile-avatar-large">
                <?php echo strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)); ?>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($profile_data['name'] ?? $_SESSION['name'] ?? 'User'); ?></h1>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                <p><i class="fas fa-user-tag"></i> <?php echo ucfirst($role); ?></p>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="profile-tabs">
            <button class="tab-btn active" onclick="switchTab('edit')">Edit Profile</button>
            <button class="tab-btn" onclick="switchTab('view')">View Profile</button>
        </div>

        <div id="edit-tab" class="tab-content active">
            <div class="profile-card">
                <h2>Edit Profile Information</h2>
                <form method="POST">
                    <?php if ($role === 'student'): ?>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($profile_data['name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Course</label>
                                <input type="text" name="course" class="form-control" value="<?php echo htmlspecialchars($profile_data['course'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>CGPA</label>
                                <input type="number" step="0.01" name="cgpa" class="form-control" value="<?php echo htmlspecialchars($profile_data['cgpa'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Skills</label>
                            <textarea name="skills" class="form-control" rows="3"><?php echo htmlspecialchars($profile_data['skills'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($profile_data['address'] ?? ''); ?></textarea>
                        </div>
                    <?php elseif ($role === 'company'): ?>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Company Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($profile_data['name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Industry</label>
                                <input type="text" name="industry" class="form-control" value="<?php echo htmlspecialchars($profile_data['industry'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($profile_data['location'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($profile_data['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Website</label>
                                <input type="url" name="website" class="form-control" value="<?php echo htmlspecialchars($profile_data['website'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($profile_data['description'] ?? ''); ?></textarea>
                        </div>
                    <?php endif; ?>
                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>

        <div id="view-tab" class="tab-content">
            <div class="profile-card">
                <h2>Profile Information</h2>
                <?php if ($role === 'student' && $profile_data): ?>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($profile_data['name'] ?? ''); ?></p>
                    <p><strong>Course:</strong> <?php echo htmlspecialchars($profile_data['course'] ?? ''); ?></p>
                    <p><strong>CGPA:</strong> <?php echo htmlspecialchars($profile_data['cgpa'] ?? ''); ?></p>
                    <p><strong>Skills:</strong> <?php echo htmlspecialchars($profile_data['skills'] ?? ''); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($profile_data['address'] ?? ''); ?></p>
                <?php elseif ($role === 'company' && $profile_data): ?>
                    <p><strong>Company Name:</strong> <?php echo htmlspecialchars($profile_data['name'] ?? ''); ?></p>
                    <p><strong>Industry:</strong> <?php echo htmlspecialchars($profile_data['industry'] ?? ''); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($profile_data['location'] ?? ''); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($profile_data['phone'] ?? ''); ?></p>
                    <p><strong>Website:</strong> <a href="<?php echo htmlspecialchars($profile_data['website'] ?? ''); ?>" target="_blank"><?php echo htmlspecialchars($profile_data['website'] ?? ''); ?></a></p>
                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($profile_data['description'] ?? '')); ?></p>
                <?php else: ?>
                    <p>No profile data available. Please update your profile.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(tab + '-tab').classList.add('active');
        }
    </script>
</body>
</html>


