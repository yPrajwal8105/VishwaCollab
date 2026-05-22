<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VishwaCollab Setup Guide</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: #f5f7fa;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .step {
            background: #f8f9ff;
            padding: 1.5rem;
            margin: 1rem 0;
            border-radius: 8px;
            border-left: 4px solid #1a73e8;
        }
        .step h3 {
            color: #1a73e8;
            margin-bottom: 1rem;
        }
        .code {
            background: #f1f3f4;
            padding: 1rem;
            border-radius: 4px;
            font-family: monospace;
            margin: 0.5rem 0;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #1a73e8;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 0.5rem 0.5rem 0.5rem 0;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #0d47a1;
        }
        .success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #2e7d32;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #c62828;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 VishwaCollab Setup Guide</h1>
        <p>Follow these steps to set up your VishwaCollab platform:</p>

        <div class="step">
            <h3>Step 1: Start XAMPP Services</h3>
            <p>Make sure XAMPP is running with the following services:</p>
            <ul>
                <li>✅ Apache (for web server)</li>
                <li>✅ MySQL (for database)</li>
            </ul>
            <p>If MySQL is not running, click the "Start" button next to MySQL in XAMPP Control Panel.</p>
        </div>

        <div class="step">
            <h3>Step 2: Create Database</h3>
            <p>Open phpMyAdmin and create the database:</p>
            <ol>
                <li>Go to <a href="http://localhost/phpmyadmin" target="_blank">http://localhost/phpmyadmin</a></li>
                <li>Click "New" in the left sidebar</li>
                <li>Enter database name: <strong>vishwacollab</strong></li>
                <li>Click "Create"</li>
            </ol>
        </div>

        <div class="step">
            <h3>Step 3: Initialize Database</h3>
            <p>Run the database setup script to create tables and sample data:</p>
            <a href="init_db.php" class="btn">Initialize Database</a>
        </div>

        <div class="step">
            <h3>Step 4: Test the Application</h3>
            <p>Once the database is set up, you can:</p>
            <ul>
                <li><a href="index.php" class="btn">Go to Home Page</a></li>
                <li><a href="signup.php" class="btn">Create Account</a></li>
                <li><a href="login.php" class="btn">Login</a></li>
            </ul>
        </div>

        <div class="step">
            <h3>Step 5: Troubleshooting</h3>
            <p><strong>If you get database connection errors:</strong></p>
            <ul>
                <li>Make sure MySQL service is running in XAMPP</li>
                <li>Check that the database 'vishwacollab' exists</li>
                <li>Verify MySQL port 3306 is not blocked</li>
                <li>Try restarting XAMPP services</li>
            </ul>
        </div>

        <div class="success">
            <strong>🎉 Setup Complete!</strong><br>
            Your VishwaCollab platform is ready to use. You can now register users, create accounts, and access the dashboards.
        </div>
    </div>
</body>
</html>
