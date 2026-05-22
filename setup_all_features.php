<?php
// Comprehensive setup script for all new features
// Run this once to set up all required database tables and columns

require_once 'db_connect.php';

if (!isDatabaseAvailable()) {
    die("❌ Database not available. Please check your database connection in db_connect.php");
}

echo "<h2>🚀 VishwaCollab Feature Setup</h2>";
echo "<p>Setting up all required database tables and columns...</p><hr>";

$errors = [];
$success = [];

// 1. Add missing columns to students table
echo "<h3>1. Updating Students Table</h3>";

// Check if students table exists first
$tableCheck = $conn->query("SHOW TABLES LIKE 'students'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    // Get existing columns
    $result = $conn->query("SHOW COLUMNS FROM students");
    $existingColumns = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $existingColumns[] = $row['Field'];
        }
    }
    
    // Add experience column if it doesn't exist
    if (!in_array('experience', $existingColumns)) {
        $query = "ALTER TABLE students ADD COLUMN experience TEXT";
        if ($conn->query($query)) {
            echo "✅ Added column: experience<br>";
            $success[] = "Added column experience to students table";
        } else {
            echo "⚠️ Error adding column experience: " . $conn->error . "<br>";
            $errors[] = "Failed to add column experience: " . $conn->error;
        }
    } else {
        echo "ℹ️ Column experience already exists<br>";
    }
    
    // Add education column if it doesn't exist
    if (!in_array('education', $existingColumns)) {
        $query = "ALTER TABLE students ADD COLUMN education VARCHAR(255)";
        if ($conn->query($query)) {
            echo "✅ Added column: education<br>";
            $success[] = "Added column education to students table";
        } else {
            echo "⚠️ Error adding column education: " . $conn->error . "<br>";
            $errors[] = "Failed to add column education: " . $conn->error;
        }
    } else {
        echo "ℹ️ Column education already exists<br>";
    }
} else {
    echo "⚠️ Students table does not exist. Please create it first or import sample data.<br>";
}

// 2. Create chat system tables
echo "<hr><h3>2. Creating Chat System Tables</h3>";
$chatQueries = [
    "CREATE TABLE IF NOT EXISTS chat_conversations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        junior_id INT NOT NULL,
        senior_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_conversation (junior_id, senior_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS chat_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        conversation_id INT NOT NULL,
        sender_id INT NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_read TINYINT(1) DEFAULT 0,
        INDEX idx_conversation (conversation_id),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS alumni_students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        graduation_year YEAR,
        current_company VARCHAR(255),
        current_position VARCHAR(255),
        expertise_areas TEXT,
        is_available_for_chat TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($chatQueries as $query) {
    if ($conn->query($query)) {
        $tableName = preg_match("/CREATE TABLE.*?(\w+)/i", $query, $matches) ? $matches[1] : 'table';
        echo "✅ Created table: $tableName<br>";
        $success[] = "Created table $tableName";
    } else {
        $tableName = preg_match("/CREATE TABLE.*?(\w+)/i", $query, $matches) ? $matches[1] : 'table';
        echo "⚠️ Error creating $tableName: " . $conn->error . "<br>";
        $errors[] = "Failed to create table $tableName: " . $conn->error;
    }
}

// 3. Create mock interview table
echo "<hr><h3>3. Creating Mock Interview Table</h3>";
$mockInterviewQuery = "CREATE TABLE IF NOT EXISTS mock_interviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_role VARCHAR(255) NOT NULL,
    interview_type VARCHAR(50) NOT NULL,
    questions TEXT,
    answers TEXT,
    feedback TEXT,
    score INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($mockInterviewQuery)) {
    echo "✅ Created table: mock_interviews<br>";
    $success[] = "Created table mock_interviews";
} else {
    echo "⚠️ Error creating mock_interviews: " . $conn->error . "<br>";
    $errors[] = "Failed to create table mock_interviews: " . $conn->error;
}

// 4. Add foreign keys if users table exists (optional, may fail if users table doesn't exist)
echo "<hr><h3>4. Adding Foreign Keys (Optional)</h3>";
$foreignKeyQueries = [
    "ALTER TABLE chat_conversations ADD CONSTRAINT fk_chat_junior FOREIGN KEY (junior_id) REFERENCES users(id) ON DELETE CASCADE",
    "ALTER TABLE chat_conversations ADD CONSTRAINT fk_chat_senior FOREIGN KEY (senior_id) REFERENCES users(id) ON DELETE CASCADE",
    "ALTER TABLE chat_messages ADD CONSTRAINT fk_msg_conv FOREIGN KEY (conversation_id) REFERENCES chat_conversations(id) ON DELETE CASCADE",
    "ALTER TABLE chat_messages ADD CONSTRAINT fk_msg_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE",
    "ALTER TABLE alumni_students ADD CONSTRAINT fk_alumni_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE",
    "ALTER TABLE mock_interviews ADD CONSTRAINT fk_mock_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE"
];

foreach ($foreignKeyQueries as $query) {
    // Check if foreign key already exists
    $fkName = preg_match("/fk_\w+/", $query, $matches) ? $matches[0] : '';
    if ($fkName) {
        $checkQuery = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                       WHERE CONSTRAINT_NAME = '$fkName' AND TABLE_SCHEMA = DATABASE()";
        $result = $conn->query($checkQuery);
        if ($result && $result->num_rows == 0) {
            if ($conn->query($query)) {
                echo "✅ Added foreign key: $fkName<br>";
            } else {
                // Foreign key might fail if users table doesn't exist, that's okay
                echo "ℹ️ Skipped foreign key $fkName (users table may not exist yet)<br>";
            }
        } else {
            echo "ℹ️ Foreign key $fkName already exists<br>";
        }
    }
}

// Summary
echo "<hr><h3>📊 Setup Summary</h3>";
echo "<p><strong>✅ Successfully completed:</strong> " . count($success) . " operations</p>";
if (!empty($errors)) {
    echo "<p><strong>⚠️ Errors encountered:</strong> " . count($errors) . " operations</p>";
    foreach ($errors as $error) {
        echo "<p style='color: red;'>• $error</p>";
    }
} else {
    echo "<p style='color: green;'><strong>🎉 All setup completed successfully!</strong></p>";
}

echo "<hr>";
echo "<h3>✅ Next Steps:</h3>";
echo "<ol>";
echo "<li>✅ Database tables are now ready</li>";
echo "<li>✅ You can now access all features:</li>";
echo "<ul>";
echo "<li><a href='student-resume-ai.php'>AI Resume Builder</a></li>";
echo "<li><a href='student-chat.php'>Chat with Seniors</a></li>";
echo "<li><a href='student-mock-interview.php'>Mock Interview</a></li>";
echo "<li><a href='student-job-recommendations.php'>Job Recommendations</a></li>";
echo "</ul>";
echo "<li>To add seniors for chat, insert records into <code>alumni_students</code> table</li>";
echo "</ol>";

echo "<p><a href='student-dashboard.php'>← Back to Dashboard</a></p>";
?>

