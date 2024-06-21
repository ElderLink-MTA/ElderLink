<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['email']) || !isset($_SESSION['session_token'])) {
    echo json_encode(["status" => "error", "message" => "Not authenticated."]);
    exit();
}

$host = "localhost";
$user = "yuvalar2_MainUser";
$password = "poch9M?Thuq";
$db = "yuvalar2_ElderLink";

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = trim($_POST['newPassword']);
    $currentPassword = trim($_POST['currentPassword']);
    $confirmPassword = trim($_POST['confirmPassword']);
    $email = $_SESSION['email'];

    // Check if new password and confirm password match
    if ($newPassword !== $confirmPassword) {
        echo json_encode(["status" => "error", "message" => "הסיסמאות החדשות אינן תואמות."]);
        exit();
    }

    // Check if the new password is the same as the current password
    if ($newPassword === $currentPassword) {
        echo json_encode(["status" => "error", "message" => "סיסמה חדשה חייבת להיות שונה מהסיסמה הנוכחית."]);
        exit();
    }

    // Fetch the current password from the database
    $sql = "SELECT password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error preparing statement: " . $conn->error);
        echo json_encode(["status" => "error", "message" => "שגיאה בהכנת הבקשה למסד הנתונים."]);
        exit();
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $dbPassword = $row['password'];
    $stmt->close();

    // Verify the current password
    if (!password_verify($currentPassword, $dbPassword)) {
        echo json_encode(["status" => "error", "message" => "הסיסמה הנוכחית שגויה."]);
        exit();
    }

    // Hash the new password
    $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update the password in the database
    $sql = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error preparing update statement: " . $conn->error);
        echo json_encode(["status" => "error", "message" => "שגיאה בהכנת הבקשה למסד הנתונים."]);
        exit();
    }
    $stmt->bind_param("ss", $hashedNewPassword, $email);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "הסיסמה שונתה בהצלחה."]);
    } else {
        echo json_encode(["status" => "error", "message" => "שגיאה בעדכון הסיסמה."]);
    }
    $stmt->close();
    $conn->close();
}
?>
