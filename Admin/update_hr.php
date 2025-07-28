<?php
session_start();
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/models.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$hrModel = new HR($DB);
$hrData = null;
$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $admin_id = $_SESSION['user']['id'];

    // Step 1: Fetch HR by ID
    if (isset($_POST['hr_id']) && empty($_POST['update'])) {
        $hr_id = (int)$_POST['hr_id'];
        $hrData = $hrModel->getHRById($hr_id)->fetch_assoc();
        $message = $hrData ? '' : "HR Staff not found.";
    }

    // Step 2: Update HR
    elseif (isset($_POST['update']) && isset($_POST['hr_id'])) {
        $hr_id = (int)$_POST['hr_id'];
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone_number = trim($_POST['phone_number']);
        $password = !empty($_POST['password']) ? $_POST['password'] : null;
        $profile_image = "htsbc.png";

        $success = $hrModel->updateHR($hr_id, $admin_id, $first_name, $last_name, $email, $phone_number, $password, $profile_image);
        $message = $success ? "HR Staff updated successfully." : "Failed to update HR Staff.";

        $hrData = $hrModel->getHRById($hr_id)->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update HR Staff</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="dashboard">
        <form action="../dashboards/admin_dashboard.php" method="get">
            <button type="submit">Go Back to Dashboard</button>
        </form>

        <h1>Update HR Staff</h1>

        <?php if (!empty($message)): ?>
            <div class="card"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if (!$hrData): ?>
            <form method="POST" action="update_hr.php">
                <label for="hr_id">Enter HR ID to Update</label>
                <input type="number" name="hr_id" id="hr_id" required>
                <button type="submit">Fetch HR</button>
                           <!-- prompts Admin for HR ID to bring up HR members information in a form that can be easily updated-->
            </form>
        <?php else: ?>
                       <!-- Form will contain the information of the HR member being fetched -->
            <div class="card">
                <form method="POST" action="update_hr.php">
                    <input type="hidden" name="hr_id" value="<?= $hrData['hr_id'] ?>">

                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($hrData['first_name']) ?>" required>

                    <label for="last_name">Last Name</label>
                    <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars($hrData['last_name']) ?>" required>

                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($hrData['email']) ?>" required>

                    <label for="phone_number">Phone Number</label>
                    <input type="text" name="phone_number" id="phone_number" value="<?= htmlspecialchars($hrData['phone_number']) ?>" required>

                    <label for="password">New Password (leave blank to keep unchanged)</label>
                    <input type="password" name="password" id="password">

                    <button type="submit" name="update" value="1">Update HR Staff</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
