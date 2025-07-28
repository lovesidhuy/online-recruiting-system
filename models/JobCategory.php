<?php
class JobCategory {
    private $conn;
    private $table = "job_category";

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREATE category
    public function addCategory($category_name) {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (category_name) VALUES (?)");
        $stmt->bind_param("s", $category_name);
        return $stmt->execute();
    }

    // READ all categories
    public function getAllCategories() {
        $sql = "SELECT * FROM {$this->table} ORDER BY category_name ASC";
        return $this->conn->query($sql);
    }

    // READ category by ID
    public function getCategoryById($category_id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE category_id = ?");
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    // UPDATE category
    public function updateCategory($category_id, $category_name) {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET category_name = ? WHERE category_id = ?");
        $stmt->bind_param("si", $category_name, $category_id);
        return $stmt->execute();
    }

    // DELETE category
    public function deleteCategory($category_id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE category_id = ?");
        $stmt->bind_param("i", $category_id);
        return $stmt->execute();
    }
}
?>
