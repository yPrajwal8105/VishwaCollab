<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Database Setup - VishwaCollab</title>
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
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: #1a73e8;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 1rem 0;
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
            margin: 1rem 0;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #c62828;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Quick Database Setup</h1>
        <p>This will populate your database with comprehensive sample data for testing the search functionality.</p>
        
        <?php
        if (isset($_POST['setup_database'])) {
            require_once 'config.php';
            require_once 'db_connect.php';
            
            if (!isDatabaseAvailable()) {
                echo '<div class="error"><strong>Database Connection Failed!</strong><br>Please ensure MySQL is running and the database exists.</div>';
            } else {
                echo '<div class="success"><strong>✓ Database Connected!</strong><br>Setting up sample data...</div>';
                
                // Execute the database setup
                include 'init_db.php';
                
                echo '<div class="success"><strong>🎉 Setup Complete!</strong><br>Your database now has sample data. You can test the search functionality!</div>';
                echo '<a href="index.php" class="btn">Go to Home Page</a>';
                echo '<a href="index.php#searchResults" class="btn">Test Search</a>';
            }
        } else {
        ?>
        
        <h3>What this will add:</h3>
        <ul>
            <li><strong>10 Students</strong> with various courses and skills</li>
            <li><strong>10 Companies</strong> from different industries</li>
            <li><strong>15 Job Postings</strong> with diverse requirements</li>
            <li><strong>15 Job Applications</strong> with different statuses</li>
        </ul>
        
        <h3>Search Terms to Try:</h3>
        <ul>
            <li><strong>Jobs:</strong> "Software", "Developer", "Python", "React", "Java"</li>
            <li><strong>Companies:</strong> "Tech", "Data", "Cloud", "AI", "Mobile"</li>
            <li><strong>Students:</strong> "Computer", "Engineering", "Machine Learning", "AWS"</li>
        </ul>
        
        <form method="POST">
            <button type="submit" name="setup_database" class="btn">Setup Sample Data</button>
        </form>
        
        <p><small>Note: This will only add data if tables are empty. Existing data will not be affected.</small></p>
        
        <?php } ?>
    </div>
</body>
</html>
