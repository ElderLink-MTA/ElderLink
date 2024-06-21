<?php
session_start();

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

// Fetching all inputs' data.
$email = $_POST['email'];
$password = $_POST['validationTooltipPassword1'];
$firstName = $_POST['validationTooltipFirstName'];
$lastName = $_POST['validationTooltipLastName'];
$phone = $_POST['validationTooltiPhone'];
$dateOfBirth = $_POST['dateOfBirth'];
$gender = $_POST['gender'];
$type = $_POST['select'];
$city = $_POST['validationTooltipCity'];
$address = $_POST['validationTooltipAddress'];
$addressNum = $_POST['validationTooltipAddressNum'];

// Hash the password before storing it in the database.
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Generate a session token
$session_token = bin2hex(random_bytes(32));

// Set the default picture path
$defaultPicture = '../uploads/default-picture.jpg';

// Preparing an insert statement to 'users' table.
$statement = $conn->prepare("INSERT INTO users (email, password, first_name, last_name, phone, date_of_birth, gender, type, city, address, address_num, session_token, picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$statement->bind_param("sssssssssssss", $email, $hashedPassword, $firstName, $lastName, $phone, $dateOfBirth, $gender, $type, $city, $address, $addressNum, $session_token, $defaultPicture);

// Success message template
$successBox = '
    <section class="bg-white shadow-md py-4 px-2 w-100 submission-message">
        <div class="mb-4 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="currentColor" class="theme-color" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"></path>
                <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"></path>
            </svg>
        </div>
';

// Failure message template
$failureBox = '
    <section class="bg-white shadow-md py-4 px-2 w-100 submission-message">
        <div class="mb-4 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="text-danger" width="100" height="100" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"></path>
                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"></path>
            </svg>
        </div>
';

// If the statement failed to execute, show the error message
if ($statement->execute() == FALSE) {
    echo $failureBox . '
        <div class="text-center">
            <h1 class="p-0 mb-4">מצטערים, נתקלנו בבעיה</h1>
            <p class="mb-5 message-content">' . $conn->error . '</p>
            <a class="btn btn-outline-danger" href="javascript:history.back();">חזור אחורה</a>
        </div>
    </section>';
    exit();
} else {
    // Set session variables
    $_SESSION['email'] = $email;
    $_SESSION['user_type'] = $type;
    $_SESSION['session_token'] = $session_token;

    // Determine which page to redirect based on user type
    $redirectPage = "";
    if ($type == "volunteer") {
        $redirectPage = "volunteer/homepage_volunteer.php";
    } elseif ($type == "elderly") {
        $redirectPage = "elderly/homepage_elderly.php";
    }

    // Redirect to the appropriate page after a delay of 100 milliseconds
    echo '<script>
        setTimeout(function() {
            window.location.href = "' . $redirectPage . '";
        }, 100);
    </script>';
}

$conn->close();
?>