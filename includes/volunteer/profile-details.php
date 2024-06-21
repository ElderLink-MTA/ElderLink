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
$sql_select_user = "SELECT * FROM users WHERE email = ? AND session_token = ?";
$statement_select_user = $conn->prepare($sql_select_user);
$statement_select_user->bind_param("ss", $email, $session_token);
$statement_select_user->execute();
$result_user = $statement_select_user->get_result();
$row_user = $result_user->fetch_assoc();

if ($row_user) {
    // Fetch user picture
    $userPicture = $row_user['picture'];

    if (!empty($userPicture)) {
        echo '<img class="img-account-profile rounded-circle mb-2" src="uploads/' . $userPicture . '" alt="User Picture">';
    } else {
        // If user picture is not available, show default picture
        echo '<img class="img-account-profile rounded-circle mb-2" src="https://static.vecteezy.com/system/resources/thumbnails/009/292/244/small/default-avatar-icon-of-social-media-user-vector.jpg" alt="Default Picture">';
    }

    echo '<p>Email: ' . $row_user['email'] . '</p>';
    echo '<p>First Name: ' . $row_user['first_name'] . '</p>';
    echo '<p>Last Name: ' . $row_user['last_name'] . '</p>';
} else {
    echo "User not found.";
}

$conn->close();
?>
