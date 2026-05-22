<?php
require_once 'config.php';
require_once 'db_connect.php';

echo "<h2>Adding New Features Tables to VishwaCollab</h2>";

if (!isDatabaseAvailable()) {
    echo "<div style='background: #ffebee; color: #c62828; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #c62828;'>";
    echo "<strong>Database Connection Failed!</strong><br>";
    echo "Error: " . getDatabaseError();
    echo "</div>";
    exit();
}

// New tables for advanced features
$new_tables = [
    // Resumes table
    "CREATE TABLE IF NOT EXISTS resumes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_size BIGINT NOT NULL,
        file_type VARCHAR(50) NOT NULL,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",
    
    // Parsed resume data
    "CREATE TABLE IF NOT EXISTS parsed_resume_data (
        id INT AUTO_INCREMENT PRIMARY KEY,
        resume_id INT NOT NULL,
        user_id INT NOT NULL,
        raw_text TEXT NOT NULL,
        work_experience JSON,
        skills TEXT,
        education JSON,
        projects JSON,
        parsed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",
    
    // Quiz questions
    "CREATE TABLE IF NOT EXISTS quiz_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role VARCHAR(100) NOT NULL,
        question TEXT NOT NULL,
        options JSON NOT NULL,
        correct_answer INT NOT NULL,
        difficulty ENUM('easy', 'medium', 'hard') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",
    
    // Quiz results
    "CREATE TABLE IF NOT EXISTS quiz_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        role VARCHAR(100) NOT NULL,
        score DECIMAL(5,2) NOT NULL,
        total_questions INT NOT NULL,
        correct_answers INT NOT NULL,
        time_taken INT NOT NULL,
        answers JSON NOT NULL,
        completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",
    
    // Leaderboard
    "CREATE TABLE IF NOT EXISTS leaderboard (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        role VARCHAR(100) NOT NULL,
        score DECIMAL(5,2) NOT NULL,
        quiz_result_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (quiz_result_id) REFERENCES quiz_results(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",
    
    // ATS scores
    "CREATE TABLE IF NOT EXISTS ats_scores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        resume_id INT NOT NULL,
        job_description TEXT,
        role VARCHAR(100),
        overall_score INT NOT NULL CHECK (overall_score >= 0 AND overall_score <= 100),
        keyword_match_percentage DECIMAL(5,2) NOT NULL,
        missing_skills TEXT,
        suggestions JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",
    
    // Job cache
    "CREATE TABLE IF NOT EXISTS job_cache (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        location VARCHAR(100) NOT NULL,
        skills TEXT,
        jobs JSON NOT NULL,
        cached_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB"
];

// Create indexes for better performance
$indexes = [
    "CREATE INDEX IF NOT EXISTS idx_resumes_user_id ON resumes(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_parsed_resume_user_id ON parsed_resume_data(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_quiz_results_user_id ON quiz_results(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_leaderboard_role ON leaderboard(role)",
    "CREATE INDEX IF NOT EXISTS idx_leaderboard_score ON leaderboard(score DESC)",
    "CREATE INDEX IF NOT EXISTS idx_ats_scores_user_id ON ats_scores(user_id)"
];

echo "<div style='background: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #2e7d32;'>";
echo "<strong>Creating new tables...</strong><br>";
echo "</div>";

// Create tables
foreach ($new_tables as $query) {
    if ($conn->query($query) === TRUE) {
        echo "✓ Table created successfully or already exists.<br>";
    } else {
        // If job_cache table exists with wrong structure, try to fix it
        if (strpos($conn->error, 'job_cache') !== false && strpos($conn->error, 'expires_at') !== false) {
            echo "⚠ Fixing job_cache table structure...<br>";
            // Try to alter the column
            $fixQuery = "ALTER TABLE job_cache MODIFY expires_at DATETIME NOT NULL";
            if ($conn->query($fixQuery) === TRUE) {
                echo "✓ Fixed job_cache table.<br>";
            } else {
                // If alter fails, drop and recreate
                $conn->query("DROP TABLE IF EXISTS job_cache");
                if ($conn->query($query) === TRUE) {
                    echo "✓ Recreated job_cache table.<br>";
                } else {
                    echo "⚠ Error: " . $conn->error . "<br>";
                }
            }
        } else {
            echo "⚠ Error: " . $conn->error . "<br>";
        }
    }
}

// Create indexes
echo "<br><strong>Creating indexes...</strong><br>";
foreach ($indexes as $query) {
    if ($conn->query($query) === TRUE) {
        echo "✓ Index created.<br>";
    } else {
        // Index might already exist, that's okay
        if (strpos($conn->error, 'Duplicate key name') === false) {
            echo "Note: " . $conn->error . "<br>";
        }
    }
}

echo "<br><div style='background: #e3f2fd; color: #1976d2; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #1976d2;'>";
echo "<strong>✓ New features tables setup completed!</strong><br>";
echo "You can now use:<br>";
echo "- Resume Upload & Parsing<br>";
echo "- ATS Score Engine<br>";
echo "- Auto-Generated Quizzes<br>";
echo "- Leaderboard System<br>";
echo "- Job Recommendations<br>";
echo "</div>";

$conn->close();
?>

