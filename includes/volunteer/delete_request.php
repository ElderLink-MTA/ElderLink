<?php
session_start();

error_reporting(E_ALL); 
ini_set('display_errors', 1);

if (!isset($_SESSION['email']) || !isset($_SESSION['session_token'])) {
    echo "Unauthorized access";
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $req_id = $_POST['req_id'];

    $sql = "DELETE FROM requests WHERE req_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $req_id);
    
    if ($stmt->execute()) {
        echo "Request deleted successfully";
    } else {
        echo "Error deleting request: " . $conn->error;
    }
    
    $stmt->close();
}

$conn->close();
?>
