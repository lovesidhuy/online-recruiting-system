
<?php
session_start();
$_SESSION = array();  // Clear the session array
session_destroy();    // Destroy the session


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #ADD8E6;
            font-family: 'Arial', sans-serif;
            color: #fff;
        }
        .logout-container {
            background: rgba(0, 0, 0, 0.6);
            padding: 50px;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
            text-align: center;
        }
        h2 {
            margin-bottom: 25px;
            font-size: 28px;
            font-weight: bold;
        }
        a {
            display: inline-block;
            padding: 12px 24px;
            background: #1a5fb4;
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            font-size: 16px;
            transition: background 0.3s ease;
        }
        a:hover {
            background: #144a8d;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <h2>You have been logged out.</h2>
        <a href="login.php">Login Again</a>
    </div>
</body>
</html>