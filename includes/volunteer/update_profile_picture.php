<?php
session_start();
error_reporting(E_ALL); 
ini_set('display_errors', 1);

if (!isset($_SESSION['email']) || !isset($_SESSION['session_token'])) {
    header("Location: ../../index.html");
    exit();
}

$host = "localhost";
$user = "yuvalar2_MainUser";
$password = "poch9M?Thuq";
$db = "yuvalar2_ElderLink";

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_SESSION['email'];
$session_token = $_SESSION['session_token'];

// Fetch the user's picture using the session_token
$sql_select = "SELECT picture FROM users WHERE session_token = ?";
$statement_select = $conn->prepare($sql_select);
$statement_select->bind_param("s", $session_token);
$statement_select->execute();
$result = $statement_select->get_result();
$row = $result->fetch_assoc();
$userPicture = $row['picture'];

// Check if a file was uploaded
if(isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
    // Process the uploaded file
    $fileTmpPath = $_FILES['picture']['tmp_name'];
    $fileName = $_FILES['picture']['name'];
    $fileSize = $_FILES['picture']['size'];
    $fileType = $_FILES['picture']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Check file size and type
    if($fileSize > 5 * 1024 * 1024) { // 5MB
        echo "File is too large. Maximum file size allowed is 5MB.";
        exit();
    }
    $allowedFileExtensions = array('jpg', 'png');
    if(!in_array($fileExtension, $allowedFileExtensions)) {
        echo "Only JPG and PNG files are allowed.";
        exit();
    }

    // Set a unique file name
    $newFileName = uniqid('profile_') . '.' . $fileExtension;

    // Move the uploaded file to the desired directory
    $uploadFileDir = '../../uploads/';
    $dest_path = $uploadFileDir . $newFileName;
    if(move_uploaded_file($fileTmpPath, $dest_path)) {
        // Update the profile picture filename in the database
        $sql = "UPDATE users SET picture = ? WHERE email = ? AND session_token = ?";
        $statement = $conn->prepare($sql);
        $statement->bind_param("sss", $newFileName, $email, $session_token);
        $statement->execute();

        $conn->close();

        // Redirect back to the profile page after updating
        header("Location: settings_profile.php");
        exit();
    } else {
        echo "There was an error uploading the file.";
        exit();
    }
} else {
    echo "No file was uploaded or an error occurred.";
    exit();
}
?>
