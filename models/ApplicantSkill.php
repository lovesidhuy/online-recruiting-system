<?php
class ApplicantSkill {
    private $conn; //database connection
    private $table = "applicant_skill";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Assign a skill to an applicant
    public function assignSkill($applicant_id, $skill_id) {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (applicant_id, skill_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $applicant_id, $skill_id);
        return $stmt->execute();
    }

    // Remove a skill from an applicant
    public function removeSkill($applicant_id, $skill_id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE applicant_id = ? AND skill_id = ?");
        $stmt->bind_param("ii", $applicant_id, $skill_id);
        return $stmt->execute();
    }

    // Get all skills for an applicant 
    public function getSkillsByApplicant($applicant_id) {
        $stmt = $this->conn->prepare("
            SELECT s.skill_id, s.skill_name
            FROM {$this->table} AS asp
            JOIN skill s ON asp.skill_id = s.skill_id
            WHERE asp.applicant_id = ?
            ORDER BY s.skill_name
        ");
        $stmt->bind_param("i", $applicant_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Check if a skill is already assigned
    public function skillExists($applicant_id, $skill_id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE applicant_id = ? AND skill_id = ?");
        $stmt->bind_param("ii", $applicant_id, $skill_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}
?>
