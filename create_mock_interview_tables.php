<?php
// Create mock interview tables
require_once 'db_connect.php';

if (!isDatabaseAvailable()) {
    die("Database not available");
}

$queries = [
    "CREATE TABLE IF NOT EXISTS mock_interviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        job_role VARCHAR(255) NOT NULL,
        interview_type VARCHAR(50) NOT NULL,
        questions TEXT,
        answers TEXT,
        feedback TEXT,
        score INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user (user_id),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($queries as $query) {
    if ($conn->query($query)) {
        echo "Table created successfully<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}

echo "Mock interview tables created successfully!";
?>

