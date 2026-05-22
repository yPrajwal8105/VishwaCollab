<?php
require_once 'config.php';
require_once 'db_connect.php';

echo "<h2>VishwaCollab Database Migration</h2>";

if (!isDatabaseAvailable()) {
    echo "<div style='background: #ffebee; color: #c62828; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #c62828;'>";
    echo "<strong>Database Connection Failed!</strong><br>";
    echo "Error: " . getDatabaseError() . "<br>";
    echo "</div>";
    exit();
}

echo "<div style='background: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #2e7d32;'>";
echo "<strong>✓ Database connection successful!</strong><br>";
echo "Adding missing columns to existing tables...";
echo "</div>";

// Function to check if column exists
function columnExists($conn, $table, $column) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && $result->num_rows > 0;
}

// Add missing columns to students table
$students_columns = [
    'address' => "ALTER TABLE students ADD COLUMN address TEXT AFTER cgpa"
];

foreach ($students_columns as $col => $query) {
    if (!columnExists($conn, 'students', $col)) {
        if ($conn->query($query) === TRUE) {
            echo "✓ Added column '{$col}' to students table.<br>";
        } else {
            echo "✗ Error adding column '{$col}': " . $conn->error . "<br>";
        }
    } else {
        echo "✓ Column '{$col}' already exists in students table.<br>";
    }
}

// Add missing columns to companies table
$companies_columns = [
    'phone' => "ALTER TABLE companies ADD COLUMN phone VARCHAR(20) AFTER location",
    'website' => "ALTER TABLE companies ADD COLUMN website VARCHAR(255) AFTER phone",
    'description' => "ALTER TABLE companies ADD COLUMN description TEXT AFTER website"
];

foreach ($companies_columns as $col => $query) {
    if (!columnExists($conn, 'companies', $col)) {
        if ($conn->query($query) === TRUE) {
            echo "✓ Added column '{$col}' to companies table.<br>";
        } else {
            echo "✗ Error adding column '{$col}': " . $conn->error . "<br>";
        }
    } else {
        echo "✓ Column '{$col}' already exists in companies table.<br>";
    }
}

// Add missing columns to jobs table
$jobs_columns = [
    'required_skills' => "ALTER TABLE jobs ADD COLUMN required_skills VARCHAR(255) AFTER requirements",
    'posted_at' => "ALTER TABLE jobs ADD COLUMN posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status"
];

foreach ($jobs_columns as $col => $query) {
    if (!columnExists($conn, 'jobs', $col)) {
        if ($conn->query($query) === TRUE) {
            echo "✓ Added column '{$col}' to jobs table.<br>";
        } else {
            echo "✗ Error adding column '{$col}': " . $conn->error . "<br>";
        }
    } else {
        echo "✓ Column '{$col}' already exists in jobs table.<br>";
    }
}

// Ensure applications table supports interview status (used by Applications/Interviews pages).
$applications_table_check = $conn->query("SHOW TABLES LIKE 'applications'");
if ($applications_table_check && $applications_table_check->num_rows > 0) {
    // This is safe to run repeatedly; MySQL will just update the enum definition.
    if ($conn->query("ALTER TABLE applications MODIFY status ENUM('pending','interview','accepted','rejected') DEFAULT 'pending'")) {
        echo "✓ Updated applications.status enum to include 'interview'.<br>";
    } else {
        echo "✗ Error updating applications.status enum: " . $conn->error . "<br>";
    }
}

echo "<br><div style='background: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #2e7d32;'>";
echo "<strong>Migration completed!</strong><br>";
echo "You can now use the profile update feature without errors.";
echo "</div>";

$conn->close();
?>

