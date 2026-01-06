<?php
session_start();

require_once __DIR__ . "/config/bootstrap.php";
require_once __DIR__ . "/models/models.php";

class SignupController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function processSignup() {
        $message = "";
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $first_name   = trim($_POST["first_name"] ?? "");
            $last_name    = trim($_POST["last_name"] ?? "");
            $email        = trim($_POST["email"] ?? "");
            $phone_number = trim($_POST["phone_number"] ?? "");
            $dob          = trim($_POST["dob"] ?? "");

            if ($first_name && $last_name && $email && $phone_number && $dob) {
                $sql = "INSERT INTO applicant (first_name, last_name, email, phone_number, dob)
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                if (!$stmt) {
                    $message = "Prepare failed: " . $this->conn->error;
                    return $message;
                }
                $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone_number, $dob);
                if ($stmt->execute()) {
                    $_SESSION["user"] = [
                        'id' => $this->conn->insert_id,
                        'role' => 'applicant'
                    ];
                    $_SESSION["role"] = 'applicant';
                    header("Location: dashboards/applicant_dashboard.php");
                    exit();
                } else {
                    $message = "Signup failed: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $message = "All fields are required.";
            }
        }
        return $message;
    }
}

$signupController = new SignupController();
$message = $signupController->processSignup();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Applicant Signup</title>
    <style>
        body {
            background-color: #ADD8E6;
            font-family: Arial, sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .signup-container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 400px;
        }
        h2 {
            text-align: center;
        }
        label, input {
            display: block;
            width: 100%;
            margin-bottom: 15px;
        }
        input[type="text"], input[type="email"], input[type="date"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            padding: 10px;
            background-color: #ff8d00;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #ff7400;
        }
        p a {
            color: #ff8d00;
        }
    </style>
</head>
<body>

<div class="signup-container">
    <h2>Applicant Signup</h2>

    <?php if (!empty($message)) echo "<p style='color:red'><strong>" . htmlspecialchars($message) . "</strong></p>"; ?>

    <form method="POST">
        <label>First Name:</label>
        <input type="text" name="first_name" required>

        <label>Last Name:</label>
        <input type="text" name="last_name" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Phone Number:</label>
        <input type="text" name="phone_number" required>

        <label>Date of Birth:</label>
        <input type="date" name="dob" required>

        <input type="submit" value="Sign Up">
    </form>

    <p>Already signed up? <a href="login.php">Go to Login</a></p>
</div>

</body>
</html>
