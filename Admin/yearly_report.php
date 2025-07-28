<?php
session_start();
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/models.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$conn = $DB;
$category_summary = [];
$qualification_summary = [];
$year = date('Y');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $year = intval($_POST['year']);

    // Query 1: Job Category Summary
    $sql1 = "
        SELECT jc.category_name, COUNT(j.job_id) AS total_jobs, COUNT(a.application_id) AS total_applications
        FROM job j
        LEFT JOIN applications a ON j.job_id = a.job_id
        LEFT JOIN job_category jc ON j.category_id = jc.category_id
        WHERE YEAR(j.posting_date) = ?
        GROUP BY jc.category_name
        ORDER BY total_applications DESC
    ";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $year);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    $category_summary = $result1->fetch_all(MYSQLI_ASSOC);
    $stmt1->close();

    // Query 2: Qualified Candidates Summary
    $sql2 = "
        SELECT j.job_title, jc.category_name, COUNT(a.application_id) AS qualified_applications
        FROM job j
        LEFT JOIN applications a ON j.job_id = a.job_id AND a.application_status = 'In Review'
        LEFT JOIN job_category jc ON j.category_id = jc.category_id
        WHERE YEAR(j.posting_date) = ?
        GROUP BY j.job_title, jc.category_name
        ORDER BY qualified_applications DESC
    ";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $year);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $qualification_summary = $result2->fetch_all(MYSQLI_ASSOC);
    $stmt2->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Yearly Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .dashboard {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .dashboard form {
            margin-bottom: 20px;
        }
        .dashboard input[type="number"] {
            font-size: 14px;
            width: 120px;
        }
        .dashboard button {
            font-size: 14px;
            padding: 8px 16px;
            margin-top: 10px;
        }
        .dashboard h1 {
            color: white;
        }
        .dashboard h2 {
            color: white;
        }
        .dashboard table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            color: white;
        }
        .dashboard th, .dashboard td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .dashboard th {
            background-color: #444;
        }
        .dashboard td {
            background-color: #555;
        }
        .dashboard form button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .dashboard form button[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="dashboard">
    <form action="../dashboards/admin_dashboard.php" method="get">
        <button type="submit"> Go Back to Dashboard</button>
    </form>

    <h1>Generate Yearly Report</h1>
    <form method="POST" action="yearly_report.php">
        <label for="year">Select Year:</label>
        <input type="number" name="year" value="<?= $year ?>" required>
        <button type="submit">Generate Report</button>
    </form>

               <!-- Shows table in order of job categories with the most jobs posted per category and most applications received per job category. -->
    <?php if (!empty($category_summary)) : ?>
        <h2>Summary of Job Categories (Most to Least Applications)</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Jobs Posted</th>
                    <th>Applications Received</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($category_summary as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['category_name']) ?></td>
                        <td><?= htmlspecialchars($row['total_jobs']) ?></td>
                        <td><?= htmlspecialchars($row['total_applications']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

       <!-- In order of how many qualified applications were recived-->
    <?php if (!empty($qualification_summary)) : ?>
        <h2>Qualified Candidates Summary (Easiest to Hardest)</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Category</th>
                    <th>Qualified Applications</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($qualification_summary as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['job_title']) ?></td>
                        <td><?= htmlspecialchars($row['category_name']) ?></td>
                        <td><?= htmlspecialchars($row['qualified_applications']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
