<?php
session_start();
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/models.php';

// Test database connections and model functionality
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Model Testing Page - ORS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .info {
            background-color: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 10px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .nav-link {
            display: inline-block;
            margin: 10px 0;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .nav-link:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Model Testing Page</h1>
        <p><a href="../index.php" class="nav-link">← Back to Home</a></p>

        <?php
        // Test database connection
        echo "<h2>Database Connection Test</h2>";
        if ($DB) {
            echo "<p class='success'>✓ Database connected successfully</p>";
            echo "<div class='info'>Connected to database: " . $DB->server_info . "</div>";
        } else {
            echo "<p class='error'>✗ Database connection failed</p>";
            exit();
        }

        // Test models
        echo "<h2>Model Loading Tests</h2>";
        $models = [];
        $modelClasses = ['Admin', 'HR', 'Applicant', 'Job', 'Company', 'JobCategory', 'Applications', 'Education', 'WorkExperience', 'Skill'];
        
        foreach ($modelClasses as $className) {
            try {
                $models[$className] = new $className($DB);
                echo "<p class='success'>✓ $className model loaded successfully</p>";
            } catch (Exception $e) {
                echo "<p class='error'>✗ $className model failed to load: " . $e->getMessage() . "</p>";
            }
        }

        // Test sample data
        echo "<h2>Database Content Overview</h2>";
        try {
            $tables = [
                'admin' => 'Administrators',
                'hr' => 'HR Staff',
                'applicant' => 'Applicants',
                'job' => 'Job Postings',
                'company' => 'Companies',
                'job_category' => 'Job Categories',
                'applications' => 'Applications',
                'ai_screening_results' => 'AI Screening Results'
            ];

            echo "<table>";
            echo "<tr><th>Table</th><th>Description</th><th>Record Count</th></tr>";
            
            foreach ($tables as $table => $description) {
                $result = $DB->query("SELECT COUNT(*) as count FROM $table");
                $count = $result->fetch_assoc()['count'];
                echo "<tr><td>$table</td><td>$description</td><td>$count</td></tr>";
            }
            echo "</table>";

        } catch (Exception $e) {
            echo "<p class='error'>Error querying database: " . $e->getMessage() . "</p>";
        }

        // Test specific model functionality
        echo "<h2>Model Functionality Tests</h2>";
        
        // Test Admin model
        if (isset($models['Admin'])) {
            try {
                $adminResult = $models['Admin']->getAllAdmins();
                $adminCount = $adminResult->num_rows;
                echo "<p class='success'>✓ Admin model: Retrieved $adminCount administrators</p>";
            } catch (Exception $e) {
                echo "<p class='error'>✗ Admin model test failed: " . $e->getMessage() . "</p>";
            }
        }

        // Test Job model
        if (isset($models['Job'])) {
            try {
                $jobResult = $models['Job']->getAllJobs();
                $jobCount = $jobResult->num_rows;
                echo "<p class='success'>✓ Job model: Retrieved $jobCount job postings</p>";
            } catch (Exception $e) {
                echo "<p class='error'>✗ Job model test failed: " . $e->getMessage() . "</p>";
            }
        }

        // Test Applicant model
        if (isset($models['Applicant'])) {
            try {
                $applicantResult = $models['Applicant']->getAllApplicants();
                $applicantCount = $applicantResult->num_rows;
                echo "<p class='success'>✓ Applicant model: Retrieved $applicantCount applicants</p>";
            } catch (Exception $e) {
                echo "<p class='error'>✗ Applicant model test failed: " . $e->getMessage() . "</p>";
            }
        }

        // Test login credentials
        echo "<h2>Test Login Credentials</h2>";
        echo "<div class='info'>";
        echo "<h3>Default Login Credentials:</h3>";
        echo "<strong>Admin:</strong><br>";
        echo "Email: michael.scott@dundermifflin.com | Password: Admin@123<br>";
        echo "Email: seera.singh@dundermifflin.com | Password: Admin@123<br><br>";
        
        echo "<strong>HR Staff:</strong><br>";
        echo "Email: pam.beesely@dm.com | Password: HR@123<br>";
        echo "Email: dwight.schrute@dm.com | Password: HR@123<br>";
        echo "Email: anjali.hill@dm.com | Password: HR@123<br><br>";
        
        echo "<strong>Applicants:</strong><br>";
        echo "Email: james.wilson@gmail.com | Password: Applicant@123<br>";
        echo "Email: sarah.chen@yahoo.com | Password: Applicant@123<br>";
        echo "Email: mohammed.ali@ymail.com | Password: Applicant@123<br>";
        echo "And 7 more applicant accounts...<br>";
        echo "</div>";

        // Test file structure
        echo "<h2>File Structure Tests</h2>";
        $requiredDirs = [
            'uploads' => 'Profile image uploads',
            'dashboards/uploads' => 'Dashboard uploads',
            'logs' => 'Application logs'
        ];

        foreach ($requiredDirs as $dir => $description) {
            if (is_dir(__DIR__ . "/../$dir")) {
                echo "<p class='success'>✓ Directory exists: $dir ($description)</p>";
            } else {
                echo "<p class='error'>✗ Missing directory: $dir ($description)</p>";
            }
        }

        echo "<h2>Quick Actions</h2>";
        echo "<p><a href='../login.php' class='nav-link'>Test Login System</a></p>";
        echo "<p><a href='../dashboards/admin_dashboard.php' class='nav-link'>Admin Dashboard</a></p>";
        echo "<p><a href='../dashboards/hr_dashboard.php' class='nav-link'>HR Dashboard</a></p>";
        echo "<p><a href='../dashboards/applicant_dashboard.php' class='nav-link'>Applicant Dashboard</a></p>";
        ?>
    </div>
</body>
</html>
