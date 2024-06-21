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

$first_name = isset($_POST['first_name']) ? $_POST['first_name'] : '';
$last_name = isset($_POST['last_name']) ? $_POST['last_name'] : '';
$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
$date_of_birth = isset($_POST['date_of_birth']) ? $_POST['date_of_birth'] : '';
$language = isset($_POST['languages']) ? implode(',', $_POST['languages']) : '';
$city = isset($_POST['city']) ? $_POST['city'] : '';
$address = isset($_POST['address']) ? $_POST['address'] : '';
$address_num = isset($_POST['address_num']) ? $_POST['address_num'] : '';

// Update the users table
$sql_users = "UPDATE users SET first_name = ?, last_name = ?, phone = ?, date_of_birth = ?, language = ?, city = ?, address = ?, address_num = ? WHERE email = ? AND session_token = ?";
$statement_users = $conn->prepare($sql_users);

if ($statement_users === false) {
    die("Prepare failed for users table: " . htmlspecialchars($conn->error));
}

$bind_users = $statement_users->bind_param("ssssssssss", $first_name, $last_name, $phone, $date_of_birth, $language, $city, $address, $address_num, $email, $session_token);

if ($bind_users === false) {
    die("Bind failed for users table: " . htmlspecialchars($statement_users->error));
}

$execute_users = $statement_users->execute();

if ($execute_users === false) {
    die("Execute failed for users table: " . htmlspecialchars($statement_users->error));
}

// Update the inquiries table
$sql_inquiries = "UPDATE inquiries SET first_name = ?, last_name = ?, phone = ? WHERE email = ?";
$statement_inquiries = $conn->prepare($sql_inquiries);

if ($statement_inquiries === false) {
    die("Prepare failed for inquiries table: " . htmlspecialchars($conn->error));
}

$bind_inquiries = $statement_inquiries->bind_param("ssss", $first_name, $last_name, $phone, $email);

if ($bind_inquiries === false) {
    die("Bind failed for inquiries table: " . htmlspecialchars($statement_inquiries->error));
}

$execute_inquiries = $statement_inquiries->execute();

if ($execute_inquiries === false) {
    die("Execute failed for inquiries table: " . htmlspecialchars($statement_inquiries->error));
}

// Update the feedbacks table
$sql_feedbacks = "UPDATE feedbacks SET first_name = ?, last_name = ?, phone = ? WHERE email = ?";
$statement_feedbacks = $conn->prepare($sql_feedbacks);

if ($statement_feedbacks === false) {
    die("Prepare failed for feedbacks table: " . htmlspecialchars($conn->error));
}

$bind_feedbacks = $statement_feedbacks->bind_param("ssss", $first_name, $last_name, $phone, $email);

if ($bind_feedbacks === false) {
    die("Bind failed for feedbacks table: " . htmlspecialchars($statement_feedbacks->error));
}

$execute_feedbacks = $statement_feedbacks->execute();

if ($execute_feedbacks === false) {
    die("Execute failed for feedbacks table: " . htmlspecialchars($statement_feedbacks->error));
}

// Update the requests table
$sql_requests = "UPDATE requests SET first_name = ?, last_name = ?, phone = ? WHERE email = ?";
$statement_requests = $conn->prepare($sql_requests);

if ($statement_requests === false) {
    die("Prepare failed for requests table: " . htmlspecialchars($conn->error));
}

$bind_requests = $statement_requests->bind_param("ssss", $first_name, $last_name, $phone, $email);

if ($bind_requests === false) {
    die("Bind failed for requests table: " . htmlspecialchars($statement_requests->error));
}

$execute_requests = $statement_requests->execute();

if ($execute_requests === false) {
    die("Execute failed for requests table: " . htmlspecialchars($statement_requests->error));
}

header("Location: settings_profile.php");

$statement_users->close();
$statement_inquiries->close();
$statement_feedbacks->close();
$statement_requests->close();
$conn->close();
?>