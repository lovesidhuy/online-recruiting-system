<?php
session_start();
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/models.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$hrModel = new HR($DB);
$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $admin_id = $_SESSION['user']['id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $password = $_POST['password'];
    $profile_image = "c";

    $success = $hrModel->addHR($admin_id, $first_name, $last_name, $email, $phone_number, $password, $profile_image);
    $message = $success ? "HR Staff Account Created Successfully." : "Failed to create HR Staff.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create HR Staff</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        form label {
            display: block;
            text-align: left;
            margin-top: 15px;
            margin-left: 5%;
            font-weight: bold;
        }

        form input {
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .form-wrapper {
            max-width: 600px;
            margin: 0 auto;
            text-align: left;
        }

        h1 {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <form action="../dashboards/admin_dashboard.php" method="get">
            <button type="submit">Go Back to Dashboard</button>
        </form>

        <h1>Create HR Staff Account</h1>

        <?php if (!empty($message)): ?>
            <div class="card"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

           <!-- Form asking for first and last name of HR member being added, email, phone number and password they will assign. Once information is filled press created account to send data to database -->
        <div class="form-wrapper">
            <form method="POST" action="create_hr.php">
                <label for="first_name">First Name</label>
                <input type="text" name="first_name" id="first_name" required>

                <label for="last_name">Last Name</label>
                <input type="text" name="last_name" id="last_name" required>

                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>

                <label for="phone_number">Phone Number</label>
                <input type="text" name="phone_number" id="phone_number" required>

                <label for="password">Password</label>
                <input type="text" name="password" id="password" value="HR@123" required>

                <button type="submit">Create Account</button>
            </form>
        </div>
    </div>
</body>
</html>
