<?php
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

if (isset($_SESSION['selected_job'])) {
    $selected_job = $jobObj->getJobById($_SESSION['selected_job'])->fetch_assoc();
    unset($_SESSION['selected_job']);
}

$allCategories = $categoryObj->getAllCategories()->fetch_all(MYSQLI_ASSOC);
$applied       = $appObj->getApplicationsWithJobTitles($applicant_id)->fetch_all(MYSQLI_ASSOC);
$preferred     = $prefObj->getPreferredJobsByApplicant($applicant_id)->fetch_all(MYSQLI_ASSOC);
$preferred_ids = array_column($preferred, 'category_id');

$filter_category = $_GET['category_id'] ?? '';
$filter_company  = $_GET['company']     ?? '';
$filter_location = $_GET['location'] ?? '';
$filter_min      = $_GET['min_salary']  ?? '';
$filter_max      = $_GET['max_salary']  ?? '';
$filter_due_date = $_GET['due_date']    ?? '';

$jobsRaw = $jobObj->getAllJobsWithCompany();
$jobs    = [];

while ($row = $jobsRaw->fetch_assoc()) {
    $match = true;
    if ($filter_category && $row['category_id'] != $filter_category) $match = false;
    if ($filter_company && stripos($row['company_name'], $filter_company) === false) $match = false;
    if ($filter_location && stripos($row['location'], $filter_location) === false) $match = false;
    if ($filter_min && $row['min_salary'] < $filter_min) $match = false;
    if ($filter_max && $row['max_salary'] > $filter_max) $match = false;
    if ($filter_due_date && $row['close_date'] > $filter_due_date) $match = false;

    if ($match) $jobs[] = $row;
}
