<?php
class Skill {
    private $conn;
    private $table = "skill";

    public function __construct($db) {
        $this->conn = $db;
    }

    //Function: addSkill
    public function addSkill($skill_name) {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (skill_name) VALUES (?)");
        $stmt->bind_param("s", $skill_name);
        return $stmt->execute();
    }

     //Function: getAllSkills

    public function getAllSkills() {
        return $this->conn->query("SELECT * FROM {$this->table} ORDER BY skill_name");
    }

     //Function: getSkillById

    public function getSkillById($skill_id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE skill_id = ?");
        $stmt->bind_param("i", $skill_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    //Function: updateSkill

    public function updateSkill($skill_id, $skill_name) {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET skill_name = ? WHERE skill_id = ?");
        $stmt->bind_param("si", $skill_name, $skill_id);
        return $stmt->execute();
    }

    //Function: deleteSkill

    public function deleteSkill($skill_id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE skill_id = ?");
        $stmt->bind_param("i", $skill_id);
        return $stmt->execute();
    }

     //Function: findSkillIdByName

    public function findSkillIdByName($skill_name) {
        $stmt = $this->conn->prepare("SELECT skill_id FROM {$this->table} WHERE LOWER(skill_name) = LOWER(?)");
        $stmt->bind_param("s", $skill_name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['skill_id'];
        }
        return null;
    }
}
?>
