<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/models.php';

$job_id = isset($_GET['job_id']) ? (int) $_GET['job_id'] : 0;

$appObj       = new Applications($DB);
$applicantObj = new Applicant($DB);
$screenObj    = new AIScreening($DB);
$jobObj       = new Job($DB);

$applications = $appObj->getApplicationsByJob($job_id)->fetch_all(MYSQLI_ASSOC);
$jobInfo = $jobObj->getJobById($job_id)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Applications for Job ID <?= $job_id ?></title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #e0eafc, #cfdef3);
      padding: 20px;
    }
    h2 {
      text-align: center;
    }
    input, button {
      margin: 5px;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    button {
      background-color: #5c67f2;
      color: white;
      border: none;
      cursor: pointer;
    }
    button:hover {
      background-color: #434ac1;
    }

        <!-- contains table that will hold list of applicants that applied for a specific job.  -->

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background: #fff;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      border-radius: 5px;
      overflow: hidden;
    }
    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      text-align: left;
    }
    th {
      background: #5c67f2;
      color: white;
    }
  </style>
</head>
<body>
<div style="text-align: center; margin-top: 20px;">
  <button onclick="window.location.href='hr_dashboard.php'" class="btn-back"> Back to Dashboard</button>
</div>


 <!-- Receives the job applications were submitted to -->
<h2>Applications for: <?= htmlspecialchars($jobInfo['job_title'] ?? 'Unknown Job') ?></h2>


 <!-- Allows filtering by name or date applied-->
<div style="text-align: center;">
  <input type="text" id="filterName" placeholder="Search by name or ID">    
  <input type="date" id="filterDate">
  <button onclick="filterTable()">Apply Filters</button>
    <!-- Upon choosing to filter only applications that meet those conditions will be displayed. Remove any letters from search by name box or press clear on date button to see all applications again (unfiltered) -->
</div>


     <!-- Table holds applicant ID, name, date applied, AI Screening result and job category. Fetches data from database. -->
<table>
  <thead>
    <tr>
      <th>Applicant ID</th>
      <th>Applicant Name</th>
      <th>Applied Date</th>
      <th>AI Screening Result</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($applications as $app): 
      $applicant = $applicantObj->getApplicantById($app['applicant_id'])->fetch_assoc();
      $screening = $screenObj->getScreeningResultByApplication($app['application_id'])->fetch_assoc();
      $ai_decision = $screening['ai_decision'] ?? null;
      $ai_result = $ai_decision === 'True' ? 'Get to Next Step' : ($ai_decision === 'False' ? 'Screened Out' : 'Pending');
    ?>
    <tr>
      <td class="applicant-id"><?= $app['applicant_id'] ?></td>
      <td class="applicant-name"><?= htmlspecialchars($applicant['first_name'] . ' ' . $applicant['last_name']) ?></td>
      <td class="applied-date"><?= htmlspecialchars($app['applied_date']) ?></td>
      <td><?= $ai_result ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>


 <!-- Filtering functions requires names or dates to match to show only results that meet conditions. Shows no rows if there are no applications that meet conditions -->
<script>
function filterTable() {
  const nameInput = document.getElementById('filterName').value.toLowerCase();
  const dateInput = document.getElementById('filterDate').value;

  const rows = document.querySelectorAll("table tbody tr");

  rows.forEach(row => {
    const name = row.querySelector(".applicant-name").textContent.toLowerCase();
    const id = row.querySelector(".applicant-id").textContent;
    const date = row.querySelector(".applied-date").textContent;

    const nameMatch = name.includes(nameInput) || id.includes(nameInput);
    const dateMatch = !dateInput || date === dateInput;

    row.style.display = nameMatch && dateMatch ? '' : 'none';
  });
}
</script>

</body>
</html>
