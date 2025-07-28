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
    if (isset($_POST['hr_id']) && !isset($_POST['delete'])) {
        $hrData = $hrModel->getHRById((int)$_POST['hr_id'])->fetch_assoc();
        $message = $hrData ? '' : "HR Staff not found.";
    } elseif (isset($_POST['delete']) && isset($_POST['hr_id'])) {
        $result = $hrModel->deleteHR((int)$_POST['hr_id']);
        $message = $result === true ? "HR Staff deleted successfully." : $result;
        $hrData = null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete HR Staff</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/style.css">
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this HR staff?");
        }
    </script>
</head>
<body>
    <div class="dashboard">
        <form action="../dashboards/admin_dashboard.php" method="get">
            <button type="submit">Go Back to Dashboard</button>
        </form>

        <h1>Delete HR Staff</h1>

        <?php if (!empty($message)): ?>
            <div class="card"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if (!$hrData): ?>
            <form method="POST" action="delete_hr.php">
                <label for="hr_id">Enter HR ID to Delete</label>
                <input type="number" name="hr_id" id="hr_id" required>
                <button type="submit">Fetch HR</button>
                       <!-- Giving HR ID brings up their information to confirm if is the one you wish to delete -->
            </form>
        <?php else: ?>
            <div class="card">
                <p><strong>Name:</strong> <?= htmlspecialchars($hrData['first_name'] . ' ' . $hrData['last_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($hrData['email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($hrData['phone_number']) ?></p>
                <form method="POST" action="delete_hr.php" onsubmit="return confirmDelete();">
                    <input type="hidden" name="hr_id" value="<?= $hrData['hr_id'] ?>">
                    <button type="submit" name="delete" value="1">Confirm Delete</button>
                               <!-- delete brings up alert to prevent accidental deletion -->
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
