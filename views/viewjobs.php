<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/models.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['role'], ['hr', 'admin'])) {
    header("Location: ../login.php");
    exit;
}

$DB = $DB ?? (new Database())->connect();
$user = $_SESSION['user'];
$role = $_SESSION['role'];
$user_name = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');

$jobModel = new Job($DB);
$companyModel = new Company($DB);
$categoryModel = new JobCategory($DB);

$selected_job = null;
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['view_job'])) {
    $job_id = (int) $_POST['job_id'];
    $selected_job = $jobModel->getJobById($job_id)->fetch_assoc();
    if ($selected_job) {
        $company = $companyModel->getCompanyById($selected_job["company_id"])->fetch_assoc();
        $category = $categoryModel->getCategoryById($selected_job["category_id"])->fetch_assoc();
        $selected_job["company_name"] = $company["company_name"] ?? "N/A";
        $selected_job["company_contact_email"] = $company["contact_email"] ?? "N/A";
        $selected_job["company_phone_number"] = $company["phone_number"] ?? "N/A";
        $selected_job["category_name"] = $category["category_name"] ?? "N/A";
    } else {
        $error_message = "Job not found.";
    }
}

$all_jobs = $jobModel->getAllJobsWithCompany();
$dashboard_link = ($role === 'hr') ? '../dashboards/hr_dashboard.php' : '../dashboards/admin_dashboard.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Job View Dashboard</title>
    <style>
        body 
        { 
            background: #eef2f3; 
            padding: 20px; 
            font-family: Arial; 
        }
        
        nav 
        { 
            background: #fff; 
            padding: 10px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
        }
        
        nav a 
        { 
            margin-right: 10px; 
            text-decoration: none; 
            color: #333; 
        }
        
        .box-container 
        { 
            display: flex; 
            gap: 20px; 
        }
        
        .box 
        { 
            flex: 1; 
            background: #fff; 
            padding: 15px; 
            border-radius: 8px; 
            border: 1px solid #ccc; 
        }
        
        .job-block 
        { 
            margin-bottom: 12px; 
            border-bottom: 1px solid #ccc; 
            padding-bottom: 10px; 
        }
        
        .btn-view 
        { 
            background: #5c67f2; 
            color: white; 
            border: none; 
            padding: 6px 12px; 
            cursor: pointer; 
            border-radius: 5px; 
        }
        
        .btn-view:hover 
        { 
            background: #434ac1; 
        }
    </style>
</head>
<body>

<nav>
  <strong>Job View Dashboard</strong> |
  Welcome, <?= htmlspecialchars($user_name ?? '') ?> |
  <a href="<?= $dashboard_link ?>">Back to <?= ucfirst($role) ?> Dashboard</a> |
  <a href="../logout.php">Logout</a>
</nav>

<?php if (!empty($error_message)): ?>
  <div style="color:red;"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<div class="box-container">
<div class="box">
  <h3>Job Details</h3>
  <?php if ($selected_job): ?>
    <p><strong>Title:</strong> <?= htmlspecialchars($selected_job["job_title"] ?? '') ?></p>
    <p><strong>Company:</strong> <?= htmlspecialchars($selected_job["company_name"] ?? '') ?></p>
    <p><strong>Contact Email:</strong> <?= htmlspecialchars($selected_job["company_contact_email"] ?? 'N/A') ?></p>
    <p><strong>Phone Number:</strong> <?= htmlspecialchars($selected_job["company_phone_number"] ?? 'N/A') ?></p>
    <p><strong>Category:</strong> <?= htmlspecialchars($selected_job["category_name"] ?? '') ?></p>
    <p><strong>Position Type:</strong> <?= htmlspecialchars($selected_job["position_type"] ?? '') ?></p>
    <p><strong>Location:</strong> <?= htmlspecialchars($selected_job["location"] ?? 'N/A') ?></p> 
    <p><strong>Salary Range:</strong> <?= htmlspecialchars($selected_job["salary_range"] ?? '') ?></p>
    <p><strong>Min Salary:</strong> $<?= number_format((float)($selected_job["min_salary"] ?? 0), 2) ?></p>
    <p><strong>Max Salary:</strong> $<?= number_format((float)($selected_job["max_salary"] ?? 0), 2) ?></p>
    <p><strong>Education:</strong> <?= htmlspecialchars($selected_job["required_education"] ?? '') ?></p>
    <p><strong>Experience:</strong> <?= htmlspecialchars($selected_job["required_experience"] ?? '') ?></p>
    <p><strong>Posting Date:</strong> <?= htmlspecialchars($selected_job["posting_date"] ?? '') ?></p>
    <p><strong>Close Date:</strong> <?= htmlspecialchars($selected_job["close_date"] ?? '') ?></p>
    <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($selected_job["job_description"] ?? '')) ?></p>
  <?php else: ?>
    <p>Select a job from the list to view details.</p>
  <?php endif; ?>
</div>


  <div class="box">
    <h3>Available Jobs</h3>
    <?php if ($all_jobs && $all_jobs->num_rows > 0): ?>
      <?php while ($row = $all_jobs->fetch_assoc()): ?>
        <div class="job-block">
          <strong><?= htmlspecialchars($row["job_title"] ?? '') ?></strong><br>
          <small>Company: <?= htmlspecialchars($row["company_name"] ?? '') ?></small>
          <form method="POST">
            <input type="hidden" name="job_id" value="<?= htmlspecialchars($row["job_id"] ?? '') ?>">
            <button type="submit" name="view_job" class="btn-view">View Job</button>
          </form>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No jobs found.</p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
