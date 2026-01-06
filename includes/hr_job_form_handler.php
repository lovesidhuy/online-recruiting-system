<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_job']) || isset($_POST['update_job'])) {
        $isCreate   = isset($_POST['create_job']);
        $job_id     = $_POST['job_id'] ?? null;
        $job_title  = $_POST['job_title'] ?? '';
        $company_id = $_POST['company_id'];
        $description = $_POST['job_description'] ?? '';
        $location   = $_POST['location'] ?? '';
        $salary_range = $_POST['salary_range'] ?? '';
        $category_id  = $_POST['category_id'] ?? '';
        $position_type = $_POST['position_type'] ?? '';
        $min_salary = is_numeric($_POST['min_salary']) ? $_POST['min_salary'] : 0;
        $max_salary = is_numeric($_POST['max_salary']) ? $_POST['max_salary'] : 0;
        $education  = $_POST['required_education'] ?? '';
        $experience = $_POST['required_experience'] ?? '';
        $posting_date = $_POST['posting_date'] ?: date('Y-m-d');
        $close_date   = $_POST['close_date']   ?: date('Y-m-d', strtotime('+30 days'));

        // Handle new company creation
        if ($company_id === 'new') {
            $company_id = $companyObj->addCompany(
                $_POST['new_company_name'],
                $_POST['new_company_email'],
                $_POST['new_company_phone']
            ) ? $DB->insert_id : null;
        }

        $result = $isCreate
            ? $jobObj->addJob(
                $job_title, $hr_id, $company_id, $description, $location, $salary_range,
                $category_id, $position_type, $min_salary, $max_salary,
                $education, $experience, $posting_date, $close_date
            )
            : $jobObj->updateJob(
                $job_id, $job_title, $hr_id, $company_id, $description, $location, $salary_range,
                $category_id, $position_type, $min_salary, $max_salary,
                $education, $experience, $posting_date, $close_date
            );

        if ($isCreate && $result) {
            sendJobNotificationToApplicants([
                'job_title'     => $job_title,
                'category_id'   => $category_id,
                'posting_date'  => $posting_date
            ]);
        }

        $_SESSION[$isCreate ? 'create_message' : 'update_message'] = $result
            ? "Job " . ($isCreate ? "created" : "updated") . " successfully!"
            : "Failed to " . ($isCreate ? "create" : "update") . " job.";

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
