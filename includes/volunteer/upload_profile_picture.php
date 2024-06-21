<?php
// Database connection details
$host = "localhost";
$user = "yuvalar2_MainUser";
$password = "poch9M?Thuq";
$db = "yuvalar2_ElderLink";

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check session and get user ID
$user_id = check_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $picture = file_get_contents($_FILES['picture']['tmp_name']);

    // Update the user's picture in the database
    $query = "UPDATE users SET picture = ? WHERE id = $user_id";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $picture);
    $stmt->execute();

    // Redirect back to settings_profile.php
    header('Location: settings_profile.php');
    exit;
}