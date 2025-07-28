<?php
if (isset($_POST['upload_image']) && isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    if (in_array($fileExt, $allowed) && $fileError === 0 && $fileSize < 2000000) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileNameNew = $hr_id . ".jpg";
        $fileDestination = $uploadDir . $fileNameNew;
        $_SESSION['profile_message'] = move_uploaded_file($fileTmpName, $fileDestination)
            ? "Image uploaded successfully!"
            : "Error moving uploaded file.";
    } else {
        $_SESSION['profile_message'] = "Invalid upload. Only JPG/PNG under 2MB allowed.";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
