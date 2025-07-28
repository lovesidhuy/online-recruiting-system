<?php
class WorkExperience {
    private $conn;
    private $table = "work_experience";

    public function __construct($db) {
        $this->conn = $db;
    }


    //Function: addExperience
    public function addExperience($applicant_id, $company_name, $job_title, $start_date, $end_date, $description = '') {
        $end_date = empty($end_date) ? null : $end_date;
    
        $stmt = $this->conn->prepare("INSERT INTO {$this->table}
            (applicant_id, company_name, job_title, start_date, end_date, description)
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $applicant_id, $company_name, $job_title, $start_date, $end_date, $description);
        return $stmt->execute();
    }
    
    //Function: deleteExperience

    public function deleteExperience($work_id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE work_id = ?");
        $stmt->bind_param("i", $work_id);
        return $stmt->execute();
    }

        //Function: getExperienceByApplicant

    public function getExperienceByApplicant($applicant_id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE applicant_id = ? ORDER BY start_date DESC");
        $stmt->bind_param("i", $applicant_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    //Function: updateExperience
    public function updateExperience($work_id, $company_name, $job_title, $start_date, $end_date, $description) {
        $end_date = empty($end_date) ? null : $end_date;
    
        $stmt = $this->conn->prepare("UPDATE work_experience SET company_name = ?, job_title = ?, start_date = ?, end_date = ?, description = ? WHERE work_id = ?");
        $stmt->bind_param("sssssi", $company_name, $job_title, $start_date, $end_date, $description, $work_id);
        return $stmt->execute();
    }
    
}
?>
