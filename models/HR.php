<?php
class HR {
    private $conn;
    private $table = "hr";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function addHR($admin_id, $first_name, $last_name, $email, $phone_number, $password = 'HR@123', $profile_image = 'c.com') {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} 
            (admin_id, first_name, last_name, email, phone_number, password, profile_image) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $admin_id, $first_name, $last_name, $email, $phone_number, $password, $profile_image);
        return $stmt->execute();
    }

    public function getHRById($hr_id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE hr_id=?");
        $stmt->bind_param("i", $hr_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getAllHRs() {
        return $this->conn->query("SELECT * FROM {$this->table}");
    }

    public function updateHR($hr_id, $admin_id, $first_name, $last_name, $email, $phone_number, $password = null, $profile_image = null) {
        if ($password && $profile_image) {
            $stmt = $this->conn->prepare("UPDATE {$this->table} SET admin_id=?, first_name=?, last_name=?, email=?, phone_number=?, password=?, profile_image=? WHERE hr_id=?");
            $stmt->bind_param("issssssi", $admin_id, $first_name, $last_name, $email, $phone_number, $password, $profile_image, $hr_id);
        } elseif ($password) {
            $stmt = $this->conn->prepare("UPDATE {$this->table} SET admin_id=?, first_name=?, last_name=?, email=?, phone_number=?, password=? WHERE hr_id=?");
            $stmt->bind_param("isssssi", $admin_id, $first_name, $last_name, $email, $phone_number, $password, $hr_id);
        } else {
            $stmt = $this->conn->prepare("UPDATE {$this->table} SET admin_id=?, first_name=?, last_name=?, email=?, phone_number=? WHERE hr_id=?");
            $stmt->bind_param("issssi", $admin_id, $first_name, $last_name, $email, $phone_number, $hr_id);
        }
        return $stmt->execute();
    }

    public function deleteHR($hr_id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE hr_id=?");
        $stmt->bind_param("i", $hr_id);
        return $stmt->execute();
    }
}
?>
