<?php
class AIScreening {
    private $conn;
    private $table = "ai_screening_results";

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREATE
    public function addScreeningResult($application_id, $ai_decision) {
        // $ai_decision should be "True" or "False"
        $stmt = $this->conn->prepare("
            INSERT INTO {$this->table} (application_id, ai_decision) 
            VALUES (?, ?)
        ");
        $stmt->bind_param("is", $application_id, $ai_decision);
        return $stmt->execute();
    }

    // READ all
    public function getAllScreeningResults() {
        $result = $this->conn->query("SELECT * FROM {$this->table}");
        return $result;
    }

    // READ by application ID
    public function getScreeningResultByApplication($application_id) {
        $stmt = $this->conn->prepare("
            SELECT * FROM {$this->table} 
            WHERE application_id = ?
        ");
        $stmt->bind_param("i", $application_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getScreeningResultsByApplicant($applicant_id) {
        $stmt = $this->conn->prepare("
            SELECT sr.*, a.job_id, j.job_title
            FROM {$this->table} sr
            JOIN applications a ON sr.application_id = a.application_id
            JOIN job j ON a.job_id = j.job_id
            WHERE a.applicant_id = ?
        ");
        $stmt->bind_param("i", $applicant_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    // UPDATE
    public function updateScreeningResult($screening_id, $application_id, $ai_decision) {
        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET application_id = ?, ai_decision = ?
            WHERE screening_id = ?
        ");
        $stmt->bind_param("isi", $application_id, $ai_decision, $screening_id);
        return $stmt->execute();
    }

    // DELETE
    public function deleteScreeningResult($screening_id) {
        $stmt = $this->conn->prepare("
            DELETE FROM {$this->table} 
            WHERE screening_id = ?
        ");
        $stmt->bind_param("i", $screening_id);
        return $stmt->execute();
    }
}
