<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = $_POST['first_name'] ?? $hrData['first_name'];
    $last_name  = $_POST['last_name']  ?? $hrData['last_name'];
    $email      = $_POST['email']      ?? $hrData['email'];
    $phone      = $_POST['phone_number'] ?? $hrData['phone_number'];

    $updated = $hrModel->updateHR($hr_id, $hrData['admin_id'], $first_name, $last_name, $email, $phone);

    $_SESSION['profile_message'] = $updated ? "Profile updated!" : "Update failed.";
    $_SESSION['user']['first_name'] = $first_name;
    $_SESSION['user']['last_name']  = $last_name;
    $_SESSION['user']['email']      = $email;

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
