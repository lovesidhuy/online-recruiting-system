<?php
session_start();
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/models.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$jobModel = new Job($DB);
$jobData = null;
$message = '';

// Log incoming POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    error_log("DELETE JOB POST: " . print_r($_POST, true));

    if (isset($_POST['job_id']) && empty($_POST['delete'])) {
        // Fetch Job Data
        $jobData = $jobModel->getJobById(intval($_POST['job_id']))->fetch_assoc();
        if (!$jobData) {
            $message = "Job not found.";
        }
    } elseif (isset($_POST['delete']) && isset($_POST['job_id'])) {
        $deleted = $jobModel->deleteJob(intval($_POST['job_id']));
        if ($deleted) {
            header("Location: delete_job.php?message=deleted");
            exit();
        } else {
            $message = "Failed to delete job.";
        }
    }
}

if (isset($_GET['message']) && $_GET['message'] === 'deleted') {
    $message = "Job deleted successfully!";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Job</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this job?");
        }
    </script>
</head>
<body>
<div class="dashboard">
    <form action="../dashboards/admin_dashboard.php" method="get" style="margin-bottom: 20px;">
        <button type="submit"> Go Back to Dashboard</button>
    </form>

    <h1>Delete Job</h1>
    <?php if ($message) echo "<p>$message</p>"; ?>

    <?php if (!$jobData): ?>
        <form method="POST" action="delete_job.php">
            <label for="job_id">Enter Job ID to Delete:</label>
            <input type="number" name="job_id" required>
            <button type="submit">Fetch Job</button>
        </form>
    <?php else: ?>
        <p><strong>Title:</strong> <?= htmlspecialchars($jobData['job_title']) ?></p>
        <p><strong>Company:</strong> <?= htmlspecialchars($jobData['company_name']) ?></p>
        <p><strong>Salary Range:</strong> <?= htmlspecialchars($jobData['salary_range']) ?></p>
        <p><strong>Posting Date:</strong> <?= htmlspecialchars($jobData['posting_date']) ?></p>
        <p><strong>Close Date:</strong> <?= htmlspecialchars($jobData['close_date']) ?></p>

        <form method="POST" action="delete_job.php" onsubmit="return confirmDelete();">
    <input type="hidden" name="job_id" value="<?= htmlspecialchars($jobData['job_id']) ?>">
    <input type="hidden" name="delete" value="1">
    <button type="submit">Confirm Delete</button>
</form>

    <?php endif; ?>
</div>
</body>
</html>
