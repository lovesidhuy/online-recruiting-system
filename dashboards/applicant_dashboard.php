<?php
session_start();

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

$uploadSuccess = isset($_GET['uploadsuccess']);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/models.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'applicant') {
    header("Location: ../login.php");
    exit();
}

$applicant_id = $_SESSION['user']['id'];

// Load models
$applicantModel = new Applicant($DB);
$eduModel = new Education($DB);
$expModel = new WorkExperience($DB);
$skillModel = new Skill($DB);
$applicantSkillModel = new ApplicantSkill($DB);
$preferredModel = new PreferredJobs($DB);
$categoryModel = new JobCategory($DB);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type'])) {
    switch ($_POST['form_type']) {
        case 'update_info':
            $applicantModel->updateApplicantInfo(
                $applicant_id,
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['email'],
                $_POST['phone_number'],
                $_POST['dob']
            );
            break;

        case 'add_education':
            $eduModel->addEducation(
                $applicant_id,
                $_POST['degree'],
                $_POST['institution'],
                $_POST['start_year'],
                $_POST['end_year']
            );
            break;

        case 'update_education':
            $eduModel->updateEducation(
                $_POST['education_id'],
                $_POST['degree'],
                $_POST['institution'],
                $_POST['start_year'],
                $_POST['end_year']
            );
            break;

        case 'delete_education':
            $eduModel->deleteEducation($_POST['education_id']);
            break;

        case 'add_experience':
            $expModel->addExperience(
                $applicant_id,
                $_POST['company_name'],
                $_POST['job_title'],
                $_POST['start_date'],
                $_POST['end_date'],
                $_POST['description']
            );
            break;

        case 'update_experience':
            $expModel->updateExperience(
                $_POST['work_id'],
                $_POST['company_name'],
                $_POST['job_title'],
                $_POST['start_date'],
                $_POST['end_date'],
                $_POST['description']
            );
            break;

        case 'delete_experience':
            $expModel->deleteExperience($_POST['work_id']);
            break;

        case 'add_skill':
            $applicantSkillModel->assignSkill($applicant_id, $_POST['skill_id']);
            break;

        case 'remove_skill':
            $applicantSkillModel->removeSkill($applicant_id, $_POST['skill_id']);
            break;

        case 'add_preferred':
            $preferredModel->addPreferredJob($applicant_id, $_POST['category_id']);
            break;

        case 'remove_preferred':
            $preferredModel->deletePreferredJob($applicant_id, $_POST['category_id']);
            break;

        case 'delete_profile':
            $applicantModel->deleteApplicant($applicant_id);
            session_destroy();
            echo "<script>alert('Profile deleted successfully.'); window.location.href='../login.php';</script>";
            exit;
    }

    header("Location: applicant_dashboard.php");
    exit();
}

$applicant = $applicantModel->getApplicantById($applicant_id)->fetch_assoc();
$education = $eduModel->getEducationByApplicant($applicant_id)->fetch_all(MYSQLI_ASSOC);
$experience = $expModel->getExperienceByApplicant($applicant_id)->fetch_all(MYSQLI_ASSOC);
$skills = $applicantSkillModel->getSkillsByApplicant($applicant_id)->fetch_all(MYSQLI_ASSOC);
$preferred = $preferredModel->getPreferredJobsByApplicant($applicant_id)->fetch_all(MYSQLI_ASSOC);
$allCategories = $categoryModel->getAllCategories()->fetch_all(MYSQLI_ASSOC);
$allSkills = $skillModel->getAllSkills()->fetch_all(MYSQLI_ASSOC);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Dashboard</title>

    <style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: linear-gradient(to right, #ffde1a, #ff8d00); 
    }

    .header {
        background-color: #D9D9D9;
        padding: 20px;
        display: flex;
        flex-wrap: wrap; 
        justify-content: space-between;
        align-items: center;
    }

    .header h2 {
        margin: 0;
        font-size: 28px;
    }

    .header ul {
        list-style: none;
        display: flex;
        gap: 20px;
        margin: 0;
        padding: 0;
    }

    .header ul li {
        display: inline;
    }

    .header ul li a {
        text-decoration: none;
        color: black;
        font-weight: bold;
    }

    <!--  -->
    .main {
        display: flex;
        gap: 20px;
        padding: 20px;
    }

      <!--  -->
      <!-- holds side panel that will contain main applicant personal information and button to make changes to profile -->
    .sidebar {
        width: 300px;
        background-color: #D9D9D9;
        padding: 20px;
        border-radius: 8px;
    }

    .profilepic img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        display: block;
        margin: 0 auto;
    }
    
    <!--  centers personal information from sidebar -->
    .profile-info {
        text-align: center;
        margin-top: 15px;
    }
   '
    html, body {
    height: 100%;
    overflow-y: auto;
    }

    <!--  controls personal info text in sidebar-->
    .profile-info p {
        margin: 5px 0;
        font-weight: bold;
        overflow: visible;
        white-space: normal;
    }

    <!--  refButton refereces to button that Allows you to change personal information, work experience, education, etc. Click it again to close. -->
    .refButton {
        text-align: center;
        margin-top: 20px;
    }

    .refButton button {
        background: none;
        border: none;
        cursor: pointer;
    }

    .profile-details {
        flex-grow: 1;
        background-color: #D9D9D9;
        padding: 20px;
        border-radius: 8px;
    }

    .section {
        margin-bottom: 30px;
    }

    .section h4 {
        margin-bottom: 10px;
        font-size: 22px;
        font-weight: bold;
    }

    label {
        display: block;
        margin-top: 10px;
        font-weight: 600;
        font-family: Arial, sans-serif;
    }

    input, select {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        margin-bottom: 10px;
        font-size: 16px;
        box-sizing: border-box;
    }

    select {
        white-space: normal !important;
    }

    select option {
        white-space: normal !important;
    }

    <!-- All buttons that allow changes to applicant profile  -->
    button[type="submit"] {
        background-color:rgb(52, 166, 11);
        color: white;
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        margin-top: 10px;
        border-radius: 4px;
    }
    select {
    width: 350px !important;
}

    ul {
        padding-left: 20px;
    }

    ul li {
        margin-bottom: 8px;
    }

    .edit-section {
        display: none;
    }

    hr {
        border: 1px solid #ccc;
        margin: 30px 0;
    }
</style>

</head>
<body>
<div class="header">
    <h2>Applicant Dashboard</h2>
    <ul>
        <li><a href="../views/applicantjobs.php">Jobs</a></li> <!-- Takes you to Job board  -->
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</div>

<div class="main">
    <div class="sidebar">
        <div class="profilepic">
            <?php
            $uploadDir = __DIR__ . '/uploads/';
            $relativeDir = 'uploads/';
            $extensions = ['jpg', 'jpeg', 'png'];

            $latestFile = null;
            $latestTime = 0;

            foreach ($extensions as $ext) {
                $imagePath = $uploadDir . $applicant_id . '.' . $ext;
                if (file_exists($imagePath)) {
                    $modTime = filemtime($imagePath);
                    if ($modTime > $latestTime) {
                        $latestTime = $modTime;
                        $latestFile = $relativeDir . $applicant_id . '.' . $ext . '?v=' . $modTime;
                    }
                }
            }

            if ($latestFile) {
                echo '<img src="' . $latestFile . '" alt="Applicant Image">';
            } else {
                echo '<img src="' . $relativeDir . 'dummy.jpg" alt="No Image Available">';
            }
            ?>
        </div>

            <!--  Receives Applicant personal information from database -->
        <div class="profile-info">
            <p><?= htmlspecialchars($applicant['first_name'] . ' ' . $applicant['last_name']) ?></p>
            <p class="email"><?= htmlspecialchars($applicant['email']) ?></p>
            <p class="phone-number"><?= htmlspecialchars($applicant['phone_number']) ?></p>
        </div>

            <!--  Allows you to edit applicant profile -->
        <div class="refButton">
            <button type="button" onclick="toggleEditMode()" class="submitForm">
                <img src="https://cdn-icons-png.flaticon.com/512/6065/6065488.png" width="50">
            </button>
        </div>
    </div>

    <div class="profile-details">
        <h3>Profile Information</h3>
        
        <div class="section">
            <h4>Personal Information</h4>
            <div class="edit-section">


            <form action="upload_image.php" method="post" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <input type="hidden" name="applicant_id" value="<?= $applicant_id ?>">
    <button type="submit" name="submit">Upload Image</button>                   <!-- Allows user to upload 
                                                                        their own profile picture -->
    <?php if ($uploadSuccess): ?>
    <div>
        Profile image uploaded successfully!
    </div>
<?php endif; ?>
    </form>

                <form method="POST">
                    
                  <!-- contains a form with all of the applicants personal information if it is already in the data base. Allows user to modify and upon pressing "update info" button sends data to database and newly inputed data is reflected when page is automatically refreshed -->

                    <input type="hidden" name="form_type" value="update_info">
                    <label>First Name</label>
                    <input type="text" name="first_name" value="<?=htmlspecialchars($applicant['first_name']) ?>" required>

                    <label>Last Name</label>
                    <input type="text" name="last_name" value="<?=htmlspecialchars($applicant['last_name']) ?>" required>

                    <label>Email</label>
                    <input type="email" name="email" value="<?=htmlspecialchars($applicant['email']) ?>" required>

                    <label>Phone Number</label>
                    <input type="text" name="phone_number" value="<?=htmlspecialchars($applicant['phone_number']) ?>" required>

                    <label>Date of Birth</label>
                    <input type="date" name="dob" value="<?=htmlspecialchars($applicant['dob']) ?>" required>

                    <button type="submit">Update Info</button>
                </form>
            </div>
        </div>

        <hr>

<!-- EDUCATION -->
<div class="section">
         <!--  Contains Form for updating and adding applicant education. Will fetch data from database and update upon clicking Update button -->

    <h4>Education</h4>
    <ul>
        <?php foreach ($education as $e): ?>
        <li>
            <?=htmlspecialchars($e['degree']) ?> - <?=htmlspecialchars($e['institution']) ?> (<?=$e['start_year'] ?> - <?=$e['end_year'] ?>)
            <div class="edit-section">
                <form method="POST">
                    <input type="hidden" name="form_type" value="delete_education">
                    <input type="hidden" name="education_id" value="<?=$e['education_id'] ?>">
                    <button type="submit">Delete</button>   <!-- Allows applicant to delete previous education-->
                </form>
                 <!--  Fetches previously entered education information allowing you to easily modify it and update -->
                <button type="button" onclick="toggleEditForm('edu_<?= $e['education_id'] ?>')">Modify</button>
                <div id="edu_<?= $e['education_id'] ?>" style="display:none;">
                    <form method="POST">
                        <input type="hidden" name="form_type" value="update_education">
                        <input type="hidden" name="education_id" value="<?= $e['education_id'] ?>">
                        <label>Degree</label>
                        <input type="text" name="degree" value="<?= htmlspecialchars($e['degree']) ?>" required>
                        <label>Institution</label>
                        <input type="text" name="institution" value="<?= htmlspecialchars($e['institution']) ?>" required>
                        <label>Start Year</label>
                        <input type="number" name="start_year" value="<?= $e['start_year'] ?>" required>
                        <label>End Year</label>
                        <input type="number" name="end_year" value="<?= $e['end_year'] ?>" required>
                        <button type="submit">Update</button>
                    </form>
                </div>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>

         <!--  Allows applicant to add education details.  -->
    <div class="edit-section">
        <form method="POST">
            <input type="hidden" name="form_type" value="add_education">
            <label>Degree</label>
            <input type="text" name="degree" required>
            <label>Institution</label>
            <input type="text" name="institution" required>
            <label>Start Year</label>
            <input type="number" name="start_year" min="1940" max="2025" required>
            <label>End Year</label>
            <input type="number" name="end_year" min="1940" max="2040" required>
            <button type="submit">Add Education</button>
        </form>
    </div>
</div>

<hr>

<!-- WORK EXPERIENCE -->
             <!--  Allows Applicant to Add, modify and delete information partaining to Job experience including job title, company name, start and end dates and a description. Fetches previously entered job information and displays. Upon clicking update profile buttons it allows you to interact with forms to update any information then sends it to database and updates view on automatic refresh -->

<div class="section">
    <h4>Work Experience</h4>
    <ul>
        <?php foreach ($experience as $exp): ?>
        <li>
            <?=htmlspecialchars($exp['job_title']) ?> at <?=htmlspecialchars($exp['company_name']) ?> (<?=$exp['start_date'] ?> - <?=$exp['end_date'] ? : 'Present' ?>)
            <div class="edit-section">
                <form method="POST">
                    <input type="hidden" name="form_type" value="delete_experience">
                    <input type="hidden" name="work_id" value="<?=$exp['work_id'] ?>">
                    <button type="submit">Delete</button>  <!--  deletes desired work experience entered by applicant -->
                
                 <!--  Allows applicant to modify existing work experience they have entered -->
                </form>
                <button type="button" onclick="toggleEditForm('exp_<?= $exp['work_id'] ?>')">Modify</button>
                <div id="exp_<?= $exp['work_id'] ?>" style="display:none;">
                    <form method="POST">
                        <input type="hidden" name="form_type" value="update_experience">
                        <input type="hidden" name="work_id" value="<?= $exp['work_id'] ?>">
                        <label>Job Title</label>
                        <input type="text" name="job_title" value="<?= htmlspecialchars($exp['job_title']) ?>" required>
                        <label>Company</label>
                        <input type="text" name="company_name" value="<?= htmlspecialchars($exp['company_name']) ?>" required>
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="<?= $exp['start_date'] ?>" required>
                        <label>End Date</label>
                        <input type="date" name="end_date" value="<?= $exp['end_date'] ?>">
                        <label>Description</label>
                        <input type="text" name="description" value="<?= htmlspecialchars($exp['description']) ?>">
                        <button type="submit">Update</button>
                    </form>
                </div>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>

         <!--  Allows applicant to add multiple previosly worked jobs to their profile -->
    <div class="edit-section">
        <form method="POST">
            <input type="hidden" name="form_type" value="add_experience">
            <label>Job Title</label>
            <input type="text" name="job_title" required>
            <label>Company</label>
            <input type="text" name="company_name" required>
            <label>Start Date</label>
            <input type="date" name="start_date" required>
            <label>End Date</label>
            <input type="date" name="end_date">
            <label>Description</label>
            <input type="text" name="description">
            <button type="submit">Add Experience</button>
        </form>
    </div>
</div>

<hr>
<script>
function toggleEditForm(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>


        <!-- SKILLS -->

          <!--  Allows Applicant to select skills from a pre created list. Multiple skills can be choosen and removed -->
        <div class="section">
            <h4>Skills</h4>
            <ul>
                <?php foreach ($skills as $s): ?>
                <li>
                    <?=htmlspecialchars($s['skill_name']) ?>
                    <div class="edit-section">
                        <form method="POST">
                            <input type="hidden" name="form_type" value="remove_skill">
                            <input type="hidden" name="skill_id" value="<?=$s['skill_id'] ?>">
                            <button type="submit">Remove</button>
                        </form>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="edit-section">
                <form method="POST">
                    <input type="hidden" name="form_type" value="add_skill">
                    <select name="skill_id" required>
                        <option value="">--Select Skill--</option>
                        <?php foreach ($allSkills as $s): ?>
                            <option value="<?=$s['skill_id'] ?>"><?=htmlspecialchars($s['skill_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Add Skill</button>  <!--  Refreshes and updates profile upon click -->
                </form>
            </div>
        </div>

        <hr>

        <!-- PREFERRED JOB CATEGORIES -->        
          <!--  Allows Applicant to select prefered job categories from a pre selected list. Multiple jobs categories can be choosen and removed -->
        <div class="section">
            <h4>Preferred Job Categories</h4>
            <ul>
                <?php foreach ($preferred as $p): ?>
                <li>
                    <?=htmlspecialchars($p['category_name']) ?>
                    <div class="edit-section">
                        <form method="POST">
                            <input type="hidden" name="form_type" value="remove_preferred">
                            <input type="hidden" name="category_id" value="<?=$p['category_id'] ?>">
                            <button type="submit">Remove</button>
                        </form>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="edit-section">
                <form method="POST">
                    <input type="hidden" name="form_type" value="add_preferred">
                    <select name="category_id" required>
                        <option value="">--Select Category--</option>
                        <?php foreach ($allCategories as $cat): ?>
                            <option value="<?=$cat['category_id'] ?>"><?=htmlspecialchars($cat['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Add Preferred</button>
                </form>
            </div>
        </div>

        <hr>

        <!-- DELETE PROFILE -->
            
          <!--  On click brings up alert asking applicant again if they would like to delete their profile -->
        <div class="section edit-section">
            <h4>Delete Profile</h4>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete your profile?');">
                <input type="hidden" name="form_type" value="delete_profile">
                <button type="submit">Delete My Profile</button>
            </form>
        </div>

    </div>
</div>

<script>
    let editMode = false;
    function toggleEditMode() {
        editMode = !editMode;
        document.querySelectorAll('.edit-section').forEach(section => {
            section.style.display = editMode ? 'block' : 'none';
        });
    }

    window.onload = function () {
        toggleEditMode(); toggleEditMode(); 
    };
</script>
</body>
</html>