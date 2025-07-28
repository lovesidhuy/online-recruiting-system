<?php
class Education {
    private $conn; //database connection
    private $table = "education";

    public function __construct($db) {
        $this->conn = $db;
    }


    //Function: addEducation
    public function addEducation($applicant_id, $degree, $institution, $start_year, $end_year) {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} 
            (applicant_id, degree, institution, start_year, end_year)
            VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issii", $applicant_id, $degree, $institution, $start_year, $end_year);
        return $stmt->execute();
    }

     //Function: deleteEducation
    public function deleteEducation($education_id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE education_id = ?");
        $stmt->bind_param("i", $education_id);
        return $stmt->execute();
    }

     //Function: getEducationByApplicant
    public function getEducationByApplicant($applicant_id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE applicant_id = ? ORDER BY end_year DESC");
        $stmt->bind_param("i", $applicant_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    //Function: updateEducation
    public function updateEducation($education_id, $degree, $institution, $start_year, $end_year) {
        $stmt = $this->conn->prepare("UPDATE education SET degree = ?, institution = ?, start_year = ?, end_year = ? WHERE education_id = ?");
        $stmt->bind_param("ssiii", $degree, $institution, $start_year, $end_year, $education_id);
        return $stmt->execute();
    }
    
}
?>
