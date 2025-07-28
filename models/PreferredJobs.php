<?php
class PreferredJobs {
    private $conn;
    private $table = "applicant_preferred_job_categories";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add preferred job category
    public function addPreferredJob($applicant_id, $category_id) {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (applicant_id, category_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $applicant_id, $category_id);
        return $stmt->execute();
    }

    // Get all preferred job categories for an applicant (with category name)
    public function getPreferredJobsByApplicant($applicant_id) {
        $stmt = $this->conn->prepare("
            SELECT p.category_id, jc.category_name 
            FROM {$this->table} p
            JOIN job_category jc ON p.category_id = jc.category_id 
            WHERE p.applicant_id = ?
        ");
        $stmt->bind_param("i", $applicant_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Remove a preferred job category
    public function deletePreferredJob($applicant_id, $category_id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE applicant_id = ? AND category_id = ?");
        $stmt->bind_param("ii", $applicant_id, $category_id);
        return $stmt->execute();
    }

    // Check if category is already preferred
    public function preferenceExists($applicant_id, $category_id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE applicant_id = ? AND category_id = ?");
        $stmt->bind_param("ii", $applicant_id, $category_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // Get all entries
    public function getAllPreferredJobs() {
        return $this->conn->query("SELECT * FROM {$this->table}");
    }
}
?>
