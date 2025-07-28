<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/models.php';
require_once __DIR__ . '/../helpers/notification_helper.php';
require_once __DIR__ . '/../includes/hr_enum_helper.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'hr') {
    header("Location: ../login.php");
    exit();
}

$user      = $_SESSION['user'];
$hr_id     = $user['id'];
$user_name = $user['first_name'] . ' ' . $user['last_name'];

$jobObj      = new Job($DB);
$companyObj  = new Company($DB);
$categoryObj = new JobCategory($DB);
$hrModel     = new HR($DB);
$hrData      = $hrModel->getHRById($hr_id)->fetch_assoc();

$create_message  = $_SESSION['create_message'] ?? '';
$update_message  = $_SESSION['update_message'] ?? '';
$profile_message = $_SESSION['profile_message'] ?? '';
unset($_SESSION['create_message'], $_SESSION['update_message'], $_SESSION['profile_message']);

$companies     = $companyObj->getAllCompanies()->fetch_all(MYSQLI_ASSOC);
$categories    = $categoryObj->getAllCategories()->fetch_all(MYSQLI_ASSOC);
$allowed_types = getEnumValues($DB, 'job', 'position_type');
$hr_jobs       = $jobObj->getJobsByHR($hr_id);

// Include handlers
require_once __DIR__ . '/../includes/hr_upload_handler.php';
require_once __DIR__ . '/../includes/hr_profile_update.php';
require_once __DIR__ . '/../includes/hr_job_form_handler.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HR Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
      body {
          background-color: #ADD8E6;
      }
      .container {
          max-width: 900px;
          margin-top: 30px;
      }
      h3 {
          color: #00796b;
      }
      h4 {
          color: #004d40;
      }
      .alert {
          font-weight: bold;
      }
      .btn-custom, 
      .btn-success, 
      .btn-primary, 
      .btn-info {
          border-radius: 5px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          margin: 2px;
      }
      .btn-custom {
          background-color: #00796b;
          color: white;
      }
      .btn-custom:hover {
          background-color: #004d40;
      }
      .form-control {
          border-radius: 5px;
      }
      .table th,
      .table td {
          text-align: center;
          vertical-align: middle;
      }
    </style>
</head>

<body>
<div class="container py-4">
    <h3 class="mb-3">
      Welcome, <?= htmlspecialchars($user_name) ?>
      <a href="../logout.php" class="btn btn-secondary btn-sm float-end">Logout</a>
    </h3>

      <!-- Profile Image Section -->
    <div class="d-flex align-items-center mb-4">
  <!-- Display Profile Picture -->
     <div class="me-3">
    <?php
    $uploadPath = '../uploads/' . $hr_id . '.jpg';
    $imgSrc = file_exists(__DIR__ . '/../uploads/' . $hr_id . '.jpg') ? $uploadPath : '../uploads/dummy.jpg';
    ?>
<img src="<?= $imgSrc . '?v=' . time() ?>" class="rounded-circle border" width="150" height="150" alt="Profile Image">
</div>

  <!-- Upload Form -->
     <form method="POST" enctype="multipart/form-data">
    <label class="form-label">Upload new profile picture:</label>
    <input type="file" name="profile_image" class="form-control form-control-sm mb-2" required>
    <button type="submit" name="upload_image" class="btn btn-sm btn-primary">Upload</button>
  </form>
    </div>


    <div class="mb-4">
      <button type="button" class="btn btn-primary btn-sm" onclick="toggleProfileEdit();">
        Edit My Profile
      </button>
      <button type="button" class="btn btn-primary btn-sm" onclick="window.location.href='../views/viewjobs.php';">
        View All Jobs
      </button>
    </div>

    <?php if (!empty($profile_message)): ?>
      <div class="alert alert-info">
        <?= htmlspecialchars($profile_message) ?>
      </div>
    <?php endif; ?>

        <!-- Allows HR to update personal information -->

    <div id="profileEditForm" style="display:none; margin-bottom:20px;">
      <form method="POST">
        <input type="hidden" name="update_profile" value="1">
        <div class="row mb-2">
          <div class="col">
            <input type="text"
                   name="first_name"
                   class="form-control"
                   value="<?= htmlspecialchars($hrData['first_name'] ?? '') ?>"
                   placeholder="First Name">
          </div>
          <div class="col">
            <input type="text"
                   name="last_name"
                   class="form-control"
                   value="<?= htmlspecialchars($hrData['last_name'] ?? '') ?>"
                   placeholder="Last Name">
          </div>
        </div>

        <input type="email"
               name="email"
               class="form-control mb-2"
               value="<?= htmlspecialchars($hrData['email'] ?? '') ?>"
               placeholder="Email">

        <input type="text"
               name="phone_number"
               class="form-control mb-2"
               value="<?= htmlspecialchars($hrData['phone_number'] ?? '') ?>"
               placeholder="Phone Number">

        <button class="btn btn-success">Save Profile</button>
      </form>
    </div>

    <?php if ($create_message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($create_message) ?></div>
    <?php endif; ?>
    <?php if ($update_message): ?>
      <div class="alert alert-info"><?= htmlspecialchars($update_message) ?></div>
    <?php endif; ?>

    <!-- Allows HR staff to create jobs -->

    <h4>Create Job</h4>
    <form method="POST" class="mb-4">
        <input type="hidden" name="create_job" value="1">
        <input type="text" name="job_title"
               class="form-control mb-2"
               placeholder="Job Title" required>

 <!-- HR can choose from a pre selected list of companies or add companies to the list. Upon adding they will be added to the database for other HR members to use -->
        <select name="company_id"
                class="form-control mb-2"                  
                id="company_select">
            <option value="">Select Company</option>
            <?php foreach ($companies as $c): ?>
                <option value="<?= $c['company_id'] ?>">
                  <?= htmlspecialchars($c['company_name']) ?>
                </option>
            <?php endforeach; ?>
            <option value="new">+ New Company</option>
        </select>

        <div id="new_company_fields" style="display:none;">
            <input type="text"
                   name="new_company_name"
                   class="form-control mb-1"
                   placeholder="Company Name">
            <input type="email"
                   name="new_company_email"
                   class="form-control mb-1"
                   placeholder="Company Email">
            <input type="text"
                   name="new_company_phone"
                   class="form-control mb-2"
                   placeholder="Company Phone">
        </div>

        <textarea name="job_description"
                  class="form-control mb-2"
                  placeholder="Job Description"></textarea>

        <input type="text"
               name="location"
               class="form-control mb-2"
              placeholder="Location"
              required>


        <input type="text"
               name="salary_range"
               class="form-control mb-2"
               placeholder="Salary Range">
        
        <select name="category_id"
                class="form-control mb-2">
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>">
                  <?= htmlspecialchars($cat['category_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="position_type"
                class="form-control mb-2">
            <?php foreach ($allowed_types as $type): ?>
                <option value="<?= $type ?>">
                  <?= htmlspecialchars($type) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="number"
               name="min_salary"
               step="0.01"
               class="form-control mb-2"
               placeholder="Min Salary">

        <input type="number"
               name="max_salary"
               step="0.01"
               class="form-control mb-2"
               placeholder="Max Salary">

        <input type="text"
               name="required_education"
               class="form-control mb-2"
               placeholder="Required Education">

        <input type="text"
               name="required_experience"
               class="form-control mb-2"
               placeholder="Required Experience">

        <input type="date"
               name="posting_date"
               class="form-control mb-2">

        <input type="date"
               name="close_date"
               class="form-control mb-2">

        <button class="btn btn-success">Create Job</button>
    </form>

 <!-- Allows to HR members to see only the jobs they have created and added to the job board -->
    <h4>Your Jobs</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Job Title</th>
                <th>Company</th>
                <th>Posted</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($job = $hr_jobs->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($job['job_title']) ?></td>
                <td><?= htmlspecialchars($job['company_name']) ?></td>
                <td><?= htmlspecialchars($job['posting_date']) ?></td>
                <td>
                  <button class="btn btn-sm btn-info"
                          onclick='populateUpdateForm(<?= json_encode($job) ?>)'>
                    Edit
                  </button>
                  <a href="viewapplications.php?job_id=<?= $job['job_id'] ?>"
                     class="btn btn-sm btn-primary">
                    View Applications           
                            <!-- View applications button takes you to a page where you can see how many people applied for that specific job -->
                  </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>


    <!-- Update Job button can be pressed on the tables of jobs HR member created. It fetches all of that jobs information from the database and fills list allowing HR to easily change any details before pressing the update job button to confirm changes. -->
    
    <h4>Update Job</h4>
    <form method="POST" id="updateForm" class="mb-4">
        <input type="hidden" name="update_job" value="1">
        <input type="hidden" name="job_id" id="update_job_id">

        <input type="text"
               name="job_title"
               id="update_job_title"
               class="form-control mb-2"
               placeholder="Job Title" required>

        <select name="company_id"
                class="form-control mb-2"
                id="update_company_id">
            <option value="">Select Company</option>
            <?php foreach ($companies as $c): ?>
                <option value="<?= $c['company_id'] ?>">
                  <?= htmlspecialchars($c['company_name']) ?>
                </option>
            <?php endforeach; ?>
            <option value="new">+ New Company</option>
        </select>

        <div id="update_new_company_fields" style="display:none;">
            <input type="text"
                   name="new_company_name"
                   class="form-control mb-1"
                   placeholder="Company Name">
            <input type="email"
                   name="new_company_email"
                   class="form-control mb-1"
                   placeholder="Company Email">
            <input type="text"
                   name="new_company_phone"
                   class="form-control mb-2"
                   placeholder="Company Phone">
        </div>

        <textarea name="job_description"
                  id="update_job_description"
                  class="form-control mb-2"
                  placeholder="Job Description"></textarea>
        <input type="text"
                  name="location"
                  id="update_location"
                  class="form-control mb-2"
                  placeholder="Location"
                  required>


        <input type="text"
               name="salary_range"
               id="update_salary_range"
               class="form-control mb-2"
               placeholder="Salary Range">

        <select name="category_id"
                class="form-control mb-2"
                id="update_category_id">
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>">
                  <?= htmlspecialchars($cat['category_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="position_type"
                class="form-control mb-2"
                id="update_position_type">
            <?php foreach ($allowed_types as $type): ?>
                <option value="<?= $type ?>">
                  <?= htmlspecialchars($type) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="number"
               name="min_salary"
               step="0.01"
               id="update_min_salary"
               class="form-control mb-2"
               placeholder="Min Salary">

        <input type="number"
               name="max_salary"
               step="0.01"
               id="update_max_salary"
               class="form-control mb-2"
               placeholder="Max Salary">

        <input type="text"
               name="required_education"
               class="form-control mb-2"
               id="update_required_education"
               placeholder="Required Education">

        <input type="text"
               name="required_experience"
               class="form-control mb-2"
               id="update_required_experience"
               placeholder="Required Experience">

        <input type="date"
               name="posting_date"
               class="form-control mb-2"
               id="update_posting_date">

        <input type="date"
               name="close_date"
               class="form-control mb-2"
               id="update_close_date">

        <button class="btn btn-primary">Update Job</button>
    </form>
</div>

<script>
function toggleProfileEdit() {
    const f = document.getElementById("profileEditForm");
    f.style.display = (f.style.display === "none") ? "block" : "none";
}

document.getElementById('company_select').addEventListener('change', function() {
    const newCompanyFields = document.getElementById('new_company_fields');
    newCompanyFields.style.display = (this.value === 'new') ? 'block' : 'none';
});

document.getElementById('update_company_id').addEventListener('change', function() {
    const updateNewCompanyFields = document.getElementById('update_new_company_fields');
    updateNewCompanyFields.style.display = (this.value === 'new') ? 'block' : 'none';
});

function populateUpdateForm(job) {
    document.getElementById('update_job_id').value             = job.job_id;
    document.getElementById('update_job_title').value          = job.job_title;
    document.getElementById('update_company_id').value         = job.company_id;
    document.getElementById('update_job_description').value    = job.job_description;
    document.getElementById('update_location').value = job.location || '';
    document.getElementById('update_salary_range').value       = job.salary_range;
    document.getElementById('update_category_id').value        = job.category_id;
    document.getElementById('update_position_type').value      = job.position_type;
    document.getElementById('update_min_salary').value         = job.min_salary;
    document.getElementById('update_max_salary').value         = job.max_salary;
    document.getElementById('update_required_education').value = job.required_education;
    document.getElementById('update_required_experience').value= job.required_experience;
    document.getElementById('update_posting_date').value       = job.posting_date;
    document.getElementById('update_close_date').value         = job.close_date;

    const updateNewCompanyFields = document.getElementById('update_new_company_fields');
    updateNewCompanyFields.style.display = (job.company_id === 'new') ? 'block' : 'none';
}
</script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>