<?php
session_start();

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['radioUsage']) && isset($_POST['twoFactor'])) {
        $choice = $_POST['radioUsage'];
        $twoFactor = $_POST['twoFactor'];

        // Update the database with the new choice and two-factor authentication setting
        $query = "UPDATE users SET shared_data = '$choice', 2fa = '$twoFactor' WHERE email = '$email'";
        if ($conn->query($query) === TRUE) {
            // Redirect back to settings_security.php after successful update
            header("Location: settings_security.php");
            exit();
        } else {
            echo "Error updating choice: " . $conn->error;
        }
    }
}
?>