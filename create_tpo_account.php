<?php
// Create a default TPO account
require_once 'config.php';
require_once 'db_connect.php';

echo "<h2>Create TPO Account</h2>";

if (!isDatabaseAvailable()) {
    echo "<div style='background: #ffebee; color: #c62828; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #c62828;'>";
    echo "<strong>Database Connection Failed!</strong><br>";
    echo "Error: " . getDatabaseError() . "<br>";
    echo "</div>";
    exit();
}

// Default TPO credentials
$tpo_email = 'tpo@vishwacollab.com';
$tpo_password = 'tpo123';
$tpo_name = 'TPO Admin';

// Check if TPO account already exists
$check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR role = 'tpo' LIMIT 1");
$check_stmt->bind_param("s", $tpo_email);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    echo "<div style='background: #fff3cd; color: #856404; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #ffc107;'>";
    echo "<strong>⚠️ TPO Account Already Exists!</strong><br>";
    echo "A TPO account is already in the database.<br><br>";
    echo "<strong>To login, try:</strong><br>";
    echo "Email: <code>$tpo_email</code><br>";
    echo "Password: <code>$tpo_password</code><br><br>";
    echo "Or check your database for existing TPO accounts.";
    echo "</div>";
} else {
    // Create TPO account
    $password_hash = password_hash($tpo_password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'tpo')");
    $stmt->bind_param("sss", $tpo_name, $tpo_email, $password_hash);
    
    if ($stmt->execute()) {
        echo "<div style='background: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #2e7d32;'>";
        echo "<strong>✅ TPO Account Created Successfully!</strong><br><br>";
        echo "<strong>Login Credentials:</strong><br>";
        echo "<table style='margin: 1rem 0; border-collapse: collapse;'>";
        echo "<tr><td style='padding: 0.5rem; font-weight: 600;'>Email:</td><td style='padding: 0.5rem;'><code>$tpo_email</code></td></tr>";
        echo "<tr><td style='padding: 0.5rem; font-weight: 600;'>Password:</td><td style='padding: 0.5rem;'><code>$tpo_password</code></td></tr>";
        echo "<tr><td style='padding: 0.5rem; font-weight: 600;'>Role:</td><td style='padding: 0.5rem;'>TPO (Training & Placement Officer)</td></tr>";
        echo "</table>";
        echo "<br><strong>🔗 Login Link:</strong><br>";
        echo "<a href='login.php' style='display: inline-block; margin-top: 0.5rem; padding: 0.7rem 1.5rem; background: #1a73e8; color: white; text-decoration: none; border-radius: 6px;'>Go to Login Page</a>";
        echo "</div>";
    } else {
        echo "<div style='background: #ffebee; color: #c62828; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #c62828;'>";
        echo "<strong>❌ Error creating TPO account:</strong><br>";
        echo $conn->error;
        echo "</div>";
    }
}

$conn->close();
?>

