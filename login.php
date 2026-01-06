<?php
session_start();

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
require_once __DIR__ . "/config/bootstrap.php";
require_once __DIR__ . "/models/models.php";

$error = "";
$loginHandler = new LoginHandler($DB);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $role = trim($_POST["role"] ?? "");

    if ($email && $password && $role) {
        $result = $loginHandler->login($email, $password, $role);
        if ($result["success"]) {
            $_SESSION["user"] = $result["user"];
            $_SESSION["role"] = $result["role"];
            header("Location: {$result["redirect"]}");
            exit();
        } else {
            $error = $result["error"];
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<!
DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ORS Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        .left-panel {
            width: 50%;
            background: url('images/login-bg.jpg') no-repeat center center;
            background-size: cover;
        }
        .right-panel {
            width: 50%;
            background-color: #ADD8E6;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        form {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        input, select, button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        .show-password {
            font-size: 12px;
            margin-top: -8px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .show-password input {
            width: auto;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="left-panel"></div>
    <div class="right-panel">
        <form method="POST">
            <h3>Online Self Recruiting Login</h3>
            <?php if (!empty($error)) echo "<p class='message'>$error</p>"; ?>
            <label>Login As:</label>
            <select name="role" required>
                <option value="">--Select Role--</option>
                <option value="applicant">Applicant</option>
                <option value="hr">HR</option>
                <option value="admin">Admin</option>
            </select>
            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Password:</label>
            <input type="password" name="password" id="passwordField" required>
            <div class="show-password">
                <input type="checkbox" id="togglePassword">
                <label for="togglePassword">Show Password</label>
            </div>

            <button type="submit">Login</button>
            <button type="button" id="faceIdBtn">Use Face ID</button>

            <p style="text-align:center;">New Applicant? <a href="signup.php">Sign Up</a></p>
        </form>
    </div>


<script>
    document.getElementById("togglePassword").addEventListener("change", function () {
        var field = document.getElementById("passwordField");
        field.type = this.checked ? "text" : "password";
    });

    document.getElementById("faceIdBtn").addEventListener("click", function () {
        var role = document.querySelector("select[name='role']").value;
        var field = document.getElementById("passwordField");
        if (!role) return alert("Please select role first.");
        if (role === "admin") field.value = "Admin@123";
        if (role === "hr") field.value = "HR@123";
        if (role === "applicant") field.value = "Applicant@123";
    });
</script>
</body>
</html>