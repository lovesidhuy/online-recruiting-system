<?php
class Admin {
    private $conn; //database connection
    private $table = "admin";

    public function __construct($db) {
        $this->conn = $db;
    }
    // Function: getAdminById
    public function getAdminById($admin_id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE admin_id=?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        return $stmt->get_result(); 
    }

    // Function: getAllAdmins
    public function getAllAdmins() {
        return $this->conn->query("SELECT * FROM {$this->table}");
    }

     // Function: updateAdmin
    public function updateAdmin($admin_id, $first_name, $last_name, $email, $phone_number) {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET first_name=?, last_name=?, email=?, phone_number=? WHERE admin_id=?");
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $phone_number, $admin_id);
        return $stmt->execute();
    }

     // Function: changePassword
    public function changePassword($admin_id, $password) {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET password=? WHERE admin_id=?");
        $stmt->bind_param("si", $password, $admin_id);
        return $stmt->execute();
    }

     // Function: addAdmin
    public function addAdmin($first_name, $last_name, $email, $phone_number, $password = 'Admin@123', $profile_image = 'b.com') {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (first_name, last_name, email, phone_number, password, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $first_name, $last_name, $email, $phone_number, $password, $profile_image);
        return $stmt->execute();
    }

     // Function: deleteAdmin
    public function deleteAdmin($admin_id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE admin_id=?");
        $stmt->bind_param("i", $admin_id);
        return $stmt->execute();
    }
}
?>
