<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['req_id'])) {
    $req_id = $_POST['req_id'];
    
    $host = "localhost";
    $user = "yuvalar2_MainUser";
    $password = "poch9M?Thuq";
    $db = "yuvalar2_ElderLink";

    $conn = new mysqli($host, $user, $password, $db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Delete from requests table
    $delete_req_sql = "DELETE FROM requests WHERE req_id = '$req_id'";
    $conn->query($delete_req_sql);

    // Delete from requests_times table
    $delete_times_sql = "DELETE FROM requests_times WHERE req_id = '$req_id'";
    $conn->query($delete_times_sql);

    $conn->close();

    // Send response message
    echo "הבקשה בוטלה בהצלחה!";
} else {
    // Invalid request
    echo "Invalid request.";
}
?>