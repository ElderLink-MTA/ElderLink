<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = "localhost";
    $user = "yuvalar2_MainUser";
    $password = "poch9M?Thuq";
    $db = "yuvalar2_ElderLink";

    $conn = new mysqli($host, $user, $password, $db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = $_POST['email'];
    $password = $_POST['validationTooltipPassword'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $statement = $conn->prepare($sql);
    $statement->bind_param("s", $email);
    $statement->execute();
    $result = $statement->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Generate a session token
            $session_token = bin2hex(random_bytes(32));

            // Store the session token in the database
            $update_sql = "UPDATE users SET session_token = ? WHERE email = ?";
            $update_statement = $conn->prepare($update_sql);
            $update_statement->bind_param("ss", $session_token, $email);
            $update_statement->execute();

            // Set session variables
            $_SESSION['email'] = $row['email'];
            $_SESSION['user_type'] = $row['type'];
            $_SESSION['session_token'] = $session_token;

            // Redirect based on user type
            if ($row['type'] == 'volunteer') {
                header("Location: volunteer/homepage_volunteer.php");
            } elseif ($row['type'] == 'elderly') {
                header("Location: elderly/homepage_elderly.php");
            }
            exit();
        } else {
            header("Location: ../index.html?error=incorrect_password");
        }
    } else {
        header("Location: ../index.html?error=email_not_found");
    }

    $conn->close();
} else {
    header("Location: ../index.html");
}
?>