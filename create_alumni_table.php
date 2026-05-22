<?php
// Run this once to create the alumni_placements table
require_once 'config.php';
require_once 'db_connect.php';

echo "<h2>Creating Alumni Placements Table</h2>";

if (!isDatabaseAvailable()) {
    echo "<div style='background: #ffebee; color: #c62828; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #c62828;'>";
    echo "<strong>Database Connection Failed!</strong><br>";
    echo "Error: " . getDatabaseError() . "<br>";
    echo "</div>";
    exit();
}

// Create alumni_placements table
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

if ($conn->query($create_table) === TRUE) {
    echo "<div style='background: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #2e7d32;'>";
    echo "<strong>✓ Alumni Placements table created successfully!</strong><br>";
    echo "You can now add placement data.";
    echo "</div>";
} else {
    echo "<div style='background: #ffebee; color: #c62828; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #c62828;'>";
    echo "<strong>Error creating table:</strong> " . $conn->error;
    echo "</div>";
}

$conn->close();
?>

