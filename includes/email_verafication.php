<?php
$host = "localhost";
$user = "yuvalar2_MainUser";
$pass = "poch9M?Thuq";
$db = "yuvalar2_ElderLink";

// Connecting to database.
$conn = new mysqli($host, $user, $pass, $db);

// Check if connection failed.
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_POST['email'];

// Check if email already exists
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "exists";
} else {
    echo "not_exists";
}

$stmt->close();
$conn->close();
?>