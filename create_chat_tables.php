<?php
// Create chat system tables
require_once 'db_connect.php';

if (!isDatabaseAvailable()) {
    die("Database not available");
}

$queries = [
    "CREATE TABLE IF NOT EXISTS chat_conversations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        junior_id INT NOT NULL,
        senior_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (junior_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (senior_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_conversation (junior_id, senior_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS chat_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        conversation_id INT NOT NULL,
        sender_id INT NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_read TINYINT(1) DEFAULT 0,
        FOREIGN KEY (conversation_id) REFERENCES chat_conversations(id) ON DELETE CASCADE,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
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
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($queries as $query) {
    if ($conn->query($query)) {
        echo "Table created successfully<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}

echo "Chat system tables created successfully!";
?>

