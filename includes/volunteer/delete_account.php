<?php
session_start();

if (!isset($_SESSION['email'])) {
    echo "Error: User email not found.";
    exit();
}

$host = "localhost";
$user = "yuvalar2_MainUser";
$password = "poch9M?Thuq";
$db = "yuvalar2_ElderLink";

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    echo "Error: Connection failed: " . $conn->connect_error;
    exit();
}

$email = $_SESSION['email'];

// Delete the user's account from the database
$query = "DELETE FROM users WHERE email = '$email'";
if ($conn->query($query) === TRUE) {
    echo "פעולת המחיקה הצליחה. החשבון שלך נמחק בהצלחה.";

} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>

