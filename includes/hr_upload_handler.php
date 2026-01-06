<?php
if (isset($_POST['upload_image']) && isset($_FILES['profile_image'])) {
    session_start();
    
    // Get HR ID from session
    $hr_id = $_SESSION['user']['id'] ?? null;

    if (!$hr_id) {
        $_SESSION['profile_message'] = "Error: HR ID not found in session.";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    // File details
    $file = $_FILES['profile_image'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    // Validate and move file
    if (in_array($fileExt, $allowed) && $fileError === 0 && $fileSize < 2 * 1024 * 1024) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileNameNew = $hr_id . ".jpg";  // Save as .jpg regardless of original extension
        $fileDestination = $uploadDir . $fileNameNew;

        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            $_SESSION['profile_message'] = "✅ Image uploaded successfully!";
        } else {
            $_SESSION['profile_message'] = "❌ Error moving uploaded file.";
        }
    } else {
        $_SESSION['profile_message'] = "❌ Invalid file. Only JPG/PNG under 2MB allowed.";
    }

    // Redirect back to dashboard
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
