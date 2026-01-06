<?php

class Job
{
    private $conn;
    private $table = "job";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function addJob(
        $job_title,
        $hr_id,
        $company_id,
        $job_description,
        $location,
        $salary_range,
        $category_id,
        $position_type,
        $min_salary,
        $max_salary,
        $required_education,
        $required_experience,
        $posting_date,
        $close_date
    ) {
        $posting_date = !empty($posting_date) ? $posting_date : date("Y-m-d");
        $close_date = !empty($close_date) ? $close_date : date("Y-m-d", strtotime("+30 days"));

        $stmt = $this->conn->prepare("
            INSERT INTO {$this->table} 
            (job_title, hr_id, company_id, job_description, location, salary_range, category_id, position_type, 
            min_salary, max_salary, required_education, required_experience, posting_date, close_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "siisssisssssss", 
            $job_title,
            $hr_id,
            $company_id,
            $job_description,
            $location,
            $salary_range,
            $category_id,
            $position_type,
            $min_salary,
            $max_salary,
            $required_education,
            $required_experience,
            $posting_date,
            $close_date
        );

        return $stmt->execute();
    }

    public function updateJob(
        $job_id,
        $job_title,
        $hr_id,
        $company_id,
        $job_description,
        $location,
        $salary_range,
        $category_id,
        $position_type,
        $min_salary,
        $max_salary,
        $required_education,
        $required_experience,
        $posting_date,
        $close_date
    ) {
        $posting_date = !empty($posting_date) ? $posting_date : date("Y-m-d");
        $close_date = !empty($close_date) ? $close_date : date("Y-m-d", strtotime("+30 days"));

        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET job_title = ?, hr_id = ?, company_id = ?, job_description = ?, location = ?, salary_range = ?, 
                category_id = ?, position_type = ?, min_salary = ?, max_salary = ?, 
                required_education = ?, required_experience = ?, posting_date = ?, close_date = ?
            WHERE job_id = ?
        ");

        $stmt->bind_param(
            "siisssisssssssi",
            $job_title,
            $hr_id,
            $company_id,
            $job_description,
            $location,
            $salary_range,
            $category_id,
            $position_type,
            $min_salary,
            $max_salary,
            $required_education,
            $required_experience,
            $posting_date,
            $close_date,
            $job_id
        );

        return $stmt->execute();
    }

    public function deleteJob($job_id)
{
    error_log("Attempting to delete job_id: " . $job_id); // log job_id

    $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE job_id = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $this->conn->error);
        return false;
    }

    $stmt->bind_param("i", $job_id);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    }

    error_log("Job deleted successfully.");
    return true;
}


    public function getAllJobs()
    {
        return $this->conn->query("SELECT * FROM {$this->table}");
    }

    public function getJobById($job_id)
    {
        $sql = "SELECT j.*, c.company_name 
                FROM {$this->table} j
                LEFT JOIN company c ON j.company_id = c.company_id
                WHERE j.job_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getAllJobsWithCompany()
    {
        $sql = "SELECT j.*, c.company_name 
                FROM {$this->table} j
                LEFT JOIN company c ON j.company_id = c.company_id
                ORDER BY j.job_id DESC";
        return $this->conn->query($sql);
    }

    public function getJobsByHR($hr_id)
    {
        $sql = "SELECT j.*, c.company_name 
                FROM {$this->table} j
                LEFT JOIN company c ON j.company_id = c.company_id
                WHERE j.hr_id = ?
                ORDER BY j.job_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $hr_id);
        $stmt->execute();
        return $stmt->get_result();
    }
}
