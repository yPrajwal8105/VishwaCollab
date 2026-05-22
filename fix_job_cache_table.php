<?php
require_once 'config.php';
require_once 'db_connect.php';

echo "<h2>Fixing job_cache Table</h2>";

if (!isDatabaseAvailable()) {
    echo "<div style='background: #ffebee; color: #c62828; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #c62828;'>";
    echo "<strong>Database Connection Failed!</strong><br>";
    echo "Error: " . getDatabaseError();
    echo "</div>";
    exit();
}

// Drop the table if it exists with wrong structure
echo "<div style='background: #fff3e0; color: #f57c00; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #f57c00;'>";
echo "<strong>Dropping existing job_cache table (if exists)...</strong><br>";
echo "</div>";

$conn->query("DROP TABLE IF EXISTS job_cache");

// Create the table with correct structure
$createTable = "CREATE TABLE job_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    location VARCHAR(100) NOT NULL,
    skills TEXT,
    jobs JSON NOT NULL,
    cached_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB";

if ($conn->query($createTable) === TRUE) {
    echo "<div style='background: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #2e7d32;'>";
    echo "<strong>✓ job_cache table created successfully!</strong><br>";
    echo "</div>";
} else {
    echo "<div style='background: #ffebee; color: #c62828; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #c62828;'>";
    echo "<strong>Error:</strong> " . $conn->error;
    echo "</div>";
}

echo "<br><a href='add_new_features_tables.php' style='background: #1a73e8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; display: inline-block;'>← Back to Setup</a>";

$conn->close();
?>














