<?php
class Company {
    private $conn; //database connection
    private $table = "company";

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREATE company and return insert ID
    public function addCompany($company_name, $contact_email, $phone_number) {
        $stmt = $this->conn->prepare("
            INSERT INTO {$this->table} (company_name, contact_email, phone_number) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("sss", $company_name, $contact_email, $phone_number);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return null;
    }

    // READ all companies
    public function getAllCompanies() {
        return $this->conn->query("SELECT * FROM {$this->table}");
    }

    // READ company by ID
    public function getCompanyById($company_id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE company_id = ?");
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    // UPDATE company
    public function updateCompany($company_id, $company_name, $contact_email, $phone_number) {
        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET company_name = ?, contact_email = ?, phone_number = ?
            WHERE company_id = ?
        ");
        $stmt->bind_param("sssi", $company_name, $contact_email, $phone_number, $company_id);
        return $stmt->execute();
    }

    // DELETE company
    public function deleteCompany($company_id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE company_id = ?");
        $stmt->bind_param("i", $company_id);
        return $stmt->execute();
    }
}
?>
