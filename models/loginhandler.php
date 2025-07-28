<?php
class LoginHandler {
    private $conn;
    private $roles = [
        'admin' => ['table' => 'admin', 'id' => 'admin_id'],
        'hr' => ['table' => 'hr', 'id' => 'hr_id'],
        'applicant' => ['table' => 'applicant', 'id' => 'applicant_id']
    ];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Handles login for the given email, password, and role

    public function login($email, $password, $role) {
        if (!isset($this->roles[$role])) {
            return ['success' => false, 'error' => "Invalid role selected."];
        }

        $roleData = $this->roles[$role];

        //  SQL to select user by email from the table
        $stmt = $this->conn->prepare("SELECT {$roleData['id']} AS id, first_name, last_name, email, password FROM {$roleData['table']} WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
              // Return success with user info, role, and redirect path
            return [
                'success' => true,
                'user' => $user,
                'redirect' => "dashboards/{$role}_dashboard.php",
                'role' => $role
            ];
        }

        return ['success' => false, 'error' => $user ? "Incorrect password." : "User not found."];
    }
}
?>