<?php
// Quick Fix - Run this to add missing columns immediately
require_once 'config.php';
require_once 'db_connect.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quick Database Fix</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #17a2b8; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <h1>🔧 Quick Database Fix</h1>
    
<?php
if (!isDatabaseAvailable()) {
    echo '<div class="error"><strong>❌ Database Connection Failed!</strong><br>Error: ' . getDatabaseError() . '</div>';
    echo '<div class="info"><strong>Please check:</strong><br>1. XAMPP MySQL is running<br>2. Database "vishwacollab" exists</div>';
    exit();
}

echo '<div class="success"><strong>✅ Database Connected!</strong></div>';

// Function to check if column exists
function columnExists($conn, $table, $column) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && $result->num_rows > 0;
}

$all_success = true;

// Fix students table
echo '<h2>Fixing Students Table...</h2>';
$students_fixes = [
    'address' => "ALTER TABLE students ADD COLUMN address TEXT AFTER cgpa"
];

foreach ($students_fixes as $col => $query) {
    if (!columnExists($conn, 'students', $col)) {
        if ($conn->query($query) === TRUE) {
            echo "<div class='success'>✅ Added column '{$col}' to students table</div>";
        } else {
            echo "<div class='error'>❌ Error adding '{$col}': " . $conn->error . "</div>";
            $all_success = false;
        }
    } else {
        echo "<div class='info'>ℹ️ Column '{$col}' already exists in students table</div>";
    }
}

// Fix companies table
echo '<h2>Fixing Companies Table...</h2>';
$companies_fixes = [
    'phone' => "ALTER TABLE companies ADD COLUMN phone VARCHAR(20) AFTER location",
    'website' => "ALTER TABLE companies ADD COLUMN website VARCHAR(255) AFTER phone",
    'description' => "ALTER TABLE companies ADD COLUMN description TEXT AFTER website"
];

foreach ($companies_fixes as $col => $query) {
    if (!columnExists($conn, 'companies', $col)) {
        if ($conn->query($query) === TRUE) {
            echo "<div class='success'>✅ Added column '{$col}' to companies table</div>";
        } else {
            echo "<div class='error'>❌ Error adding '{$col}': " . $conn->error . "</div>";
            $all_success = false;
        }
    } else {
        echo "<div class='info'>ℹ️ Column '{$col}' already exists in companies table</div>";
    }
}

// Fix jobs table
echo '<h2>Fixing Jobs Table...</h2>';
$jobs_fixes = [
    'required_skills' => "ALTER TABLE jobs ADD COLUMN required_skills VARCHAR(255) AFTER requirements",
    'posted_at' => "ALTER TABLE jobs ADD COLUMN posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status"
];

foreach ($jobs_fixes as $col => $query) {
    if (!columnExists($conn, 'jobs', $col)) {
        if ($conn->query($query) === TRUE) {
            echo "<div class='success'>✅ Added column '{$col}' to jobs table</div>";
        } else {
            echo "<div class='error'>❌ Error adding '{$col}': " . $conn->error . "</div>";
            $all_success = false;
        }
    } else {
        echo "<div class='info'>ℹ️ Column '{$col}' already exists in jobs table</div>";
    }
}

if ($all_success) {
    echo '<div class="success" style="font-size: 18px; padding: 20px; margin-top: 20px;">';
    echo '<strong>🎉 ALL FIXES APPLIED SUCCESSFULLY!</strong><br><br>';
    echo 'You can now update your profile without errors.<br>';
    echo '<a href="profile.php" style="display: inline-block; margin-top: 10px; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;">Go to Profile →</a>';
    echo '</div>';
} else {
    echo '<div class="error"><strong>⚠️ Some errors occurred. Please check the messages above.</strong></div>';
}

$conn->close();
?>
</body>
</html>

