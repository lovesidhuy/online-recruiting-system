<?php
session_start();
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/models.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$year = date('Y');
$month = date('m');
$category_summary = [];
$qualification_summary = [];
$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $year = intval($_POST['year']);
    $month = intval($_POST['month']);

    // Query for Category Summary: Job count and applications received, sorted by most applications
    $stmt1 = $DB->prepare("
        SELECT jc.category_name, COUNT(DISTINCT j.job_id) AS total_jobs, COUNT(a.application_id) AS total_applications
        FROM job j
        LEFT JOIN job_category jc ON j.category_id = jc.category_id
        LEFT JOIN applications a ON j.job_id = a.job_id
        WHERE YEAR(j.posting_date) = ? AND MONTH(j.posting_date) = ?
        GROUP BY jc.category_name
        ORDER BY total_applications DESC
    ");
    $stmt1->bind_param("ii", $year, $month);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    $category_summary = $result1->fetch_all(MYSQLI_ASSOC);
    $stmt1->close();

    // Query for Qualification Summary: Qualified applications for each job, sorted from easiest to hardest
    $stmt2 = $DB->prepare("
        SELECT j.job_title, jc.category_name, COUNT(a.application_id) AS qualified_applications
        FROM job j
        LEFT JOIN job_category jc ON j.category_id = jc.category_id
        LEFT JOIN applications a ON j.job_id = a.job_id AND a.application_status = 1
        WHERE YEAR(j.posting_date) = ? AND MONTH(j.posting_date) = ?
        GROUP BY j.job_title, jc.category_name
        ORDER BY qualified_applications DESC
    ");
    $stmt2->bind_param("ii", $year, $month);
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
    <title>Monthly Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            width: 100px;
        }
        .pie-chart-container {
            width: 300px;
            height: 300px;
            margin: auto;
        }
        .pie-chart-container canvas {
            background-color: #333;
        }
        .dashboard h1, .dashboard h2 {
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
    </style>
</head>
<body>
<div class="dashboard">
    <form action="../dashboards/admin_dashboard.php" method="get" style="margin-bottom: 20px;">
        <button type="submit">Go Back to Dashboard</button>
    </form>

    <h1>Generate Monthly Report</h1>
    <form method="POST" action="monthly_report.php">
        <label for="year">Select Year:</label>
        <input type="number" name="year" value="<?= htmlspecialchars($year) ?>" required>

        <label for="month">Select Month:</label>
        <input type="number" name="month" value="<?= htmlspecialchars($month) ?>" min="1" max="12" required>

        <button type="submit">Generate Report</button>
    </form>

    <?php if (!empty($category_summary)): ?>
        <h2>Job Categories Summary</h2>
        <div class="pie-chart-container">
            <canvas id="categoryChart"></canvas>
        </div>

        <h3>Category Summary Table</h3>
        <table border="1" cellpadding="5">
            <tr>
                <th>Category</th>
                <th>Total Jobs</th>
                <th>Applications Received</th>
            </tr>
            <?php foreach ($category_summary as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['category_name']) ?: 'Uncategorized' ?></td>
                    <td><?= $row['total_jobs'] ?></td>
                    <td><?= $row['total_applications'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <?php if (!empty($qualification_summary)): ?>
        <h2>Qualified Candidates Summary</h2>
        <table border="1" cellpadding="5">
            <tr>
                <th>Job Title</th>
                <th>Category</th>
                <th>Qualified Applications</th>
            </tr>
            <?php foreach ($qualification_summary as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['job_title']) ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?: 'Uncategorized' ?></td>
                    <td><?= $row['qualified_applications'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<script>
    const categoryData = <?php echo json_encode($category_summary); ?>;
    const labels = categoryData.map(item => item.category_name || 'Uncategorized');
    const data = categoryData.map(item => item.total_applications);

    const ctx = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'],
                hoverOffset: 4
            }]
        },
        options: {
            plugins: {
                legend: {
                    labels: {
                        font: {
                            size: 14,
                            family: 'Arial'
                        },
                        color: 'white'
                    }
                }
            }
        }
    });
</script>
</body>
</html>
