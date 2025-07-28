<?php
if (isset($_POST['submit'])) {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {

        $file = $_FILES['file'];
        $applicant_id = $_POST['applicant_id'];

        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (in_array($fileExt, $allowed)) {
            if ($fileError === 0) {
                if ($fileSize < 1000000) {
                    $uploadDir = __DIR__ . '/uploads/';
                    $fileNameNew = $applicant_id . "." . $fileExt;
                    $fileDestination = $uploadDir . $fileNameNew;

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    foreach ($allowed as $ext) {
                        $oldFile = $uploadDir . $applicant_id . '.' . $ext;
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }

                    if (!is_writable($uploadDir)) {
                        echo "Upload directory is not writable: $uploadDir";
                        exit;
                    }

                    if (move_uploaded_file($fileTmpName, $fileDestination)) {
                        error_log("UPLOAD SUCCESS: $fileDestination");
                        header("Location: ./applicant_dashboard.php?uploadsuccess=1");
                        exit();
                    } else {
                        echo "move_uploaded_file() failed.";
                        error_log("Failed to move file to: $fileDestination");
                    }
                } else {
                    echo "File is too large. Max size is 1MB.";
                }
            } else {
                echo "Upload error: $fileError";
            }
        } else {
            echo "Invalid file type: $fileExt";
        }
    } else {
        echo "No file uploaded or error occurred.";
    }
}
?>
