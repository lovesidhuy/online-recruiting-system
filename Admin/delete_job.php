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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['delete']) && $_POST['delete'] === "1" && isset($_POST['job_id'])) {
        // Perform deletion
        $deleted = $jobModel->deleteJob((int) $_POST['job_id']);
        $message = $deleted ? "Job deleted successfully!" : "Failed to delete job.";
        $jobData = null;
    } elseif (isset($_POST['job_id'])) {
        // Fetch job data
        $jobData = $jobModel->getJobById((int) $_POST['job_id'])->fetch_assoc();
        if (!$jobData) {
            $message = " Job not found.";
        }
    }
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
        <button type="submit">Go Back to Dashboard</button>
    </form>

    <h1>Delete Job</h1>
    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if (!$jobData): ?>
        <form method="POST" action="delete_job.php">
            <label for="job_id">Enter Job ID to Delete:</label>
            <input type="number" name="job_id" required>
            <button type="submit">Fetch Job</button>
        </form>
               <!-- Upon entering a job ID and pressing fetch job, job details are visible and delete button appears-->
    <?php else: ?>
        <p><strong>Title:</strong> <?= htmlspecialchars($jobData['job_title']) ?></p>
        <p><strong>Company:</strong> <?= htmlspecialchars($jobData['company_name']) ?></p>
        <p><strong>Salary Range:</strong> <?= htmlspecialchars($jobData['salary_range']) ?></p>
        <p><strong>Posting Date:</strong> <?= htmlspecialchars($jobData['posting_date']) ?></p>
        <p><strong>Close Date:</strong> <?= htmlspecialchars($jobData['close_date']) ?></p>

       <!-- Alert prompted when confirm delete is pressed to prevent accidental deletion -->
        <form method="POST" action="delete_job.php" onsubmit="return confirmDelete();">
            <input type="hidden" name="job_id" value="<?= (int) $jobData['job_id'] ?>">
            <button type="submit" name="delete" value="1">Confirm Delete</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
