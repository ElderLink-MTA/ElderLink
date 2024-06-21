<?php
session_start();

$host = "localhost";
$user = "yuvalar2_MainUser";
$pass = "poch9M?Thuq";
$db = "yuvalar2_ElderLink";

// Connecting to the database.
$conn = new mysqli($host, $user, $pass, $db);

// Check if connection failed.
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if a file is uploaded.
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    // Validate file size and type.
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileSize = $_FILES['image']['size'];
    $fileType = $_FILES['image']['type'];

    // Allow only JPEG and PNG files up to 5MB.
    $allowedMimeTypes = ['image/jpeg', 'image/png'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($fileType, $allowedMimeTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG and PNG are allowed.']);
        exit;
    }

    if ($fileSize > $maxFileSize) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds the maximum limit of 5MB.']);
        exit;
    }

    // Read file contents.
    $fileContent = file_get_contents($fileTmpPath);

    // Get the user's ID (assuming it's stored in the session).
    $userId = $_SESSION['user_id'];

    // Fetch the current picture path from the database.
    $fetchStatement = $conn->prepare("SELECT picture FROM users WHERE id = ?");
    $fetchStatement->bind_param("i", $userId);
    $fetchStatement->execute();
    $result = $fetchStatement->get_result();
    $user = $result->fetch_assoc();
    $currentPicture = $user['picture'];

    // Update the picture column in the users table.
    $updateStatement = $conn->prepare("UPDATE users SET picture = ? WHERE id = ?");
    $null = NULL; // for sending the content as a blob
    $updateStatement->bind_param("bi", $null, $userId);
    $updateStatement->send_long_data(0, $fileContent);

    if ($updateStatement->execute()) {
        // Define the default picture name to protect
        $defaultPictureName = 'default-picture.jpg';

        // Check if the current picture is not the default picture before deleting it
        if (basename($currentPicture) !== $defaultPictureName && !empty($currentPicture) && file_exists('../uploads/' . basename($currentPicture))) {
            unlink('../uploads/' . basename($currentPicture));
        }

        echo json_encode(['success' => true, 'message' => 'Profile picture updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile picture.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or there was an error uploading the file.']);
}

$conn->close();
?>