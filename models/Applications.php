<?php
class Applications {
    private $conn; //database connection
    private $table = "applications";

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREATE
    public function addApplication($applicant_id, $job_id, $applied_date, $status = false) {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} 
            (applicant_id, job_id, applied_date, application_status) 
            VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $applicant_id, $job_id, $applied_date, $status);
        return $stmt->execute();
    }

    // Function: getApplicationsWithJobTitles
    public function getApplicationsWithJobTitles($applicant_id) {
        $sql = "SELECT a.*, j.job_title 
                FROM applications a
                JOIN job j ON a.job_id = j.job_id
                WHERE a.applicant_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $applicant_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Function: READ all
    public function getAllApplications() {
        $sql = "SELECT * FROM {$this->table}";
        return $this->conn->query($sql);
    }

    public function getConn() {
        return $this->conn;
    }
    

    // Function: READ by applicant
    public function getApplicationsByApplicant($applicant_id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE applicant_id = ?");
        $stmt->bind_param("i", $applicant_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Function: READ by job
    public function getApplicationsByJob($job_id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE job_id = ?");
        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    //Function:  UPDATE
    public function updateApplication($application_id, $applicant_id, $job_id, $applied_date, $status) {
        $stmt = $this->conn->prepare("
            UPDATE {$this->table} 
            SET applicant_id = ?, job_id = ?, applied_date = ?, application_status = ?
            WHERE application_id = ?
        ");
        $stmt->bind_param("iisii", $applicant_id, $job_id, $applied_date, $status, $application_id);
        return $stmt->execute();
    }

    // Function: deleteApplication
    public function deleteApplication($application_id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE application_id = ?");
        $stmt->bind_param("i", $application_id);
        return $stmt->execute();
    }
}
