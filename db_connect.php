<?php
// db_connect.php
require_once 'config.php';

// Initialize connection variable
$conn = null;
$db_error = null;

try {
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        $db_error = "Database connection failed: " . $conn->connect_error;
        $conn = null;
    } else {
        // Set charset to utf
        $conn->set_charset("utf8");
    }
} catch (Exception $e) {
    $db_error = "Database connection error: " . $e->getMessage();
    $conn = null;
}

// Function to check if database is available
function isDatabaseAvailable() {
    global $conn;
    return $conn !== null;
}

// Function to get database error
function getDatabaseError() {
    global $db_error;
    return $db_error;
}

/**
 * Return list of tables that are missing from the configured database.
 */
function getMissingTables(array $tables) {
    global $conn;
    if (!$conn) {
        return $tables;
    }

    $missing = [];
    $schema = DB_NAME;
    $sql = "SELECT COUNT(*) AS total
            FROM information_schema.tables
            WHERE table_schema = ?
              AND table_name = ?";

    $stmt = $conn->prepare($sql);
    foreach ($tables as $table) {
        $stmt->bind_param('ss', $schema, $table);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result && $result->fetch_assoc()['total'] > 0;
        if (!$exists) {
            $missing[] = $table;
        }
    }
    $stmt->close();

    return $missing;
}
?>