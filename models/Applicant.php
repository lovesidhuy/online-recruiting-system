<?php
class Applicant {
    private $conn; //database connection
    private $table = "applicant";

    public function __construct($db) {
        $this->conn = $db;
    }

    //Function : getApplicantById
    public function getApplicantById($applicant_id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE applicant_id=?");
        $stmt->bind_param("i", $applicant_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    //Function : deleteApplicant
    public function deleteApplicant($applicant_id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE applicant_id=?");
        $stmt->bind_param("i", $applicant_id);
        return $stmt->execute();
    }

     //Function : addApplicant
    public function addApplicant($first_name, $last_name, $email, $phone_number, $password, $dob, $profile_image = 'd.com') {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (first_name, last_name, email, phone_number, password, dob, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $first_name, $last_name, $email, $phone_number, $password, $dob, $profile_image);
        return $stmt->execute();
    }

     //Function : updateApplicantInfo
    public function updateApplicantInfo($applicant_id, $first_name, $last_name, $email, $phone_number, $dob) {
        $stmt = $this->conn->prepare("UPDATE {$this->table} 
            SET first_name = ?, last_name = ?, email = ?, phone_number = ?, dob = ? 
            WHERE applicant_id = ?");
        $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone_number, $dob, $applicant_id);
        return $stmt->execute();
    }
    
    
}
?>
