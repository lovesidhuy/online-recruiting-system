<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/models.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'applicant') {
    header("Location: ../login.php");
    exit();
}

$applicant_id = $_SESSION['user']['id'];
$USE_AI = true;
// Models
$appObj       = new Applications($DB);
$screenObj    = new AIScreening($DB);
$prefObj      = new PreferredJobs($DB);
$jobObj       = new Job($DB);
$categoryObj  = new JobCategory($DB);

// Initialize defaults
$message = "";
$selected_job = null;

// POST Handling
require_once __DIR__ . '/../includes/applicantjobs_handler.php';

// GET Data
require_once __DIR__ . '/../views/applicantjobs_data.php';


?>


<!DOCTYPE html>
<html>
<head>
    <title>Applicant Job Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0eafc, #cfdef3);
        }
        nav {
            background: linear-gradient(to right, #4e54c8, #8f94fb);
            padding: 10px;
        }
        nav ul {
            list-style: none;
            display: flex;
            gap: 15px;
            padding: 0;
            margin: 0;
        }
        nav li a {
            color: #fff;
            text-decoration: none;
            padding: 6px 12px;
            background: linear-gradient(135deg, #5c67f2, #667eea);
            border-radius: 4px;
        }
        .container {
            display: flex;
            gap: 20px;
            padding: 20px;
        }
        .column {
            flex: 1;
            background: linear-gradient(135deg, #eef2f3, #ffecd2);
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 0 4px rgba(0,0,0,0.1);
        }
        h3 {
            margin-top: 0;
        }
        form {
            margin-bottom: 10px;
        }
        input[type='submit'],
        button {
            padding: 6px 12px;
            background: linear-gradient(135deg, #5c67f2, #667eea);
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 5px;
        }
        select, input[type='text'], input[type='number'], input[type='date'] {
            width: 100%;
            padding: 6px;
            margin-top: 4px;
            margin-bottom: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        ul {
            padding-left: 0;
        }
        ul li {
            margin-bottom: 8px;
            list-style: none;
        }
    
        .main-message {
            padding: 10px 20px;
            color: green;
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav>
    <ul>
        <li><a href="../dashboards/applicant_dashboard.php">Back</a></li>
        <li><a href="../views/applicantjobs.php">Jobs</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>

<!-- Flash Message -->
<?php if (!empty($message)): ?>
<div class="main-message"><strong><?= htmlspecialchars($message) ?></strong></div>
<?php endif; ?>

<!-- Layout -->
<div class="container">

    <!-- My Applications & Preferred -->
    <div class="column">
        <h3>My Applications</h3>
        <ul>
            <?php if (!empty($applied)): ?>
                <?php foreach ($applied as $row): ?>
                    <li>
                        <?= htmlspecialchars($row['job_title']) ?> -

                        <form method="POST" class="inline">
                            <input type="hidden" name="job_id" value="<?= $row['job_id'] ?>">
                            <input type="submit" name="view_job" value="View">
                        </form>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No applications yet.</li>
            <?php endif; ?>
        </ul>

        <hr>
        <h3>Your Preferred Categories</h3>
        <ul>
            <?php if (!empty($preferred)): ?>
                <?php foreach ($preferred as $p): ?>
                    <li>
                        <?= htmlspecialchars($p['category_name']) ?>
                        <form method="POST" class="inline">
                            <input type="hidden" name="category_id" value="<?= $p['category_id'] ?>">
                            <input type="submit" name="toggle_preferred" value="Remove">
                        </form>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No preferred categories.</li>
            <?php endif; ?>
        </ul>

        <form method="POST">
            <label>Add Preferred Category:</label>
            <select name="category_id" required>
                <option value="">Select</option>
                <?php foreach ($allCategories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="submit" name="toggle_preferred" value="Add">
        </form>
    </div>

    <!-- Job Details -->
    <div class="column">
        <h3>Job Details</h3>
        <?php if ($selected_job): ?>
            <p><strong>Title:</strong> <?= htmlspecialchars($selected_job['job_title']) ?></p>
            <p><strong>Company:</strong> <?= htmlspecialchars($selected_job['company_name'] ?? 'N/A') ?></p>
            <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($selected_job['job_description'] ?? '')) ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($selected_job['location'] ?? 'N/A') ?></p>
            <p><strong>Posted:</strong> <?= htmlspecialchars($selected_job['posting_date'] ?? 'N/A') ?></p>

            <!-- Apply Form -->
            <form method="POST">
                <input type="hidden" name="job_id" value="<?= $selected_job['job_id'] ?>">
                <input type="submit" name="apply" value="Apply">
            </form>

            <!-- Withdraw (if already applied) -->
            <?php foreach ($applied as $row): ?>
                <?php if ((int)$row['job_id'] === (int)$selected_job['job_id']): ?>
                    <form method="POST">
                        <input type="hidden" name="application_id" value="<?= $row['application_id'] ?>">
                        <input type="submit" name="withdraw" value="Withdraw">
                    </form>
                <?php break; endif; ?>
            <?php endforeach; ?>

            <!-- Toggle preferred -->
            <form method="POST">
                <input type="hidden" name="category_id" value="<?= $selected_job['category_id'] ?>">
                <input type="submit" name="toggle_preferred" value="Add/Remove Preferred">
            </form>
        <?php else: ?>
            <p>Select a job to view its details.</p>
        <?php endif; ?>
    </div>

    <!-- Available Jobs -->
    <div class="column">
        <h3>Available Jobs</h3>
        <form method="GET">
            <label>Filter by Category:</label>
            <select name="category_id">
                <option value="">All</option>
                <?php foreach ($allCategories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>" <?= ($filter_category == $cat['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Company:</label>
            <input type="text" name="company" value="<?= htmlspecialchars($filter_company) ?>">
            
            <label>Location:</label>
            <input type="text" name="location" value="<?= htmlspecialchars($filter_location) ?>">


            <label>Min Salary:</label>
            <input type="number" name="min_salary" value="<?= htmlspecialchars($filter_min) ?>">

            <label>Max Salary:</label>
            <input type="number" name="max_salary" value="<?= htmlspecialchars($filter_max) ?>">

            <label>Due Before:</label>
            <input type="date" name="due_date" value="<?= htmlspecialchars($filter_due_date) ?>">

            <input type="submit" value="Apply Filters">
        </form>

        <hr>
        <?php if (!empty($jobs)): ?>
            <?php foreach ($jobs as $j): ?>
                <div>
                    <strong><?= htmlspecialchars($j['job_title']) ?></strong><br>
                    Company: <?= htmlspecialchars($j['company_name']) ?><br>
                    <form method="POST">
                        <input type="hidden" name="job_id" value="<?= $j['job_id'] ?>">
                        <input type="submit" name="view_job" value="View Details">
                    </form>
                    <hr>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No jobs match the filters.</p>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
