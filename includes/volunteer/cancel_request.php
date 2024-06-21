<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['email']) || !isset($_SESSION['session_token'])) {
    echo "Unauthorized access";
    exit();
}

// Send SMS notification using Twilio
require_once '../twilio-php-main/src/Twilio/autoload.php';
use Twilio\Rest\Client;

// Send email notification using SendGrid
require '../../../../vendor/autoload.php';
use SendGrid\Mail\Mail;

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

    // Retrieve phone number, date, start_time, end_time, and email before updating the request
    $select_request_sql = "SELECT r.phone, r.date, r.start_time, r.end_time, r.email, r.first_name, r.email_volunteer, u.phone AS volunteer_phone, u.first_name AS volunteer_first_name
                       FROM requests r
                       LEFT JOIN users u ON r.email_volunteer = u.email
                       WHERE r.req_id = ?";
    $select_request_statement = $conn->prepare($select_request_sql);
    $select_request_statement->bind_param("i", $req_id);
    $select_request_statement->execute();
    $select_request_statement->bind_result($phone, $date, $start_time, $end_time, $email, $first_name, $email_volunteer, $volunteer_phone, $volunteer_first_name);
    $select_request_statement->fetch();
    $select_request_statement->close();

    // Format the date
    $formatted_date = date("d/m/Y", strtotime($date));
    $formatted_start_time = date("H:i", strtotime($start_time));
    $formatted_end_time = date("H:i", strtotime($end_time));

    // Update the request
    $sql = "UPDATE requests SET email_volunteer = NULL, cancelled_at = CURRENT_TIMESTAMP, email_volunteer_cancelled = ?, confirmed_at = NULL WHERE req_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $email_volunteer, $req_id);

    if ($stmt->execute()) {
        echo "Request updated successfully";

        // Check the value of sms_notification_elderly2 and email_notification_elderly2 for the user
        $select_user_sql = "SELECT sms_notification_elderly2, email_notification_elderly2 FROM users WHERE email = ?";
        $select_user_statement = $conn->prepare($select_user_sql);
        $select_user_statement->bind_param("s", $email);
        $select_user_statement->execute();
        $select_user_statement->bind_result($sms_notification_elderly2, $email_notification_elderly2);
        $select_user_statement->fetch();
        $select_user_statement->close();

        if ($sms_notification_elderly2 == 1) {
            $sid = "AC876333226ebe2795f848265f4bbfc0dd";
            $token = "1b4869f4cd22ae6df7827932aa67e437";
            $twilio = new Client($sid, $token);
            $phone_with_prefix = "+$phone"; 
            $message = $twilio->messages->create(
                "+972548147448",
                array(
                    "from" => "+15807413370",
                    "body" => ".
שלום $first_name, 
בקשה מספר $req_id ששלחת באתר ElderLink בוטלה, וחזרה כעת לבקשות ממתינות לשיבוץ.
אנו מאחלים לך יום מהנה ונעים, מצוות אתר ElderLink."
                )
            );
        }

        if ($email_notification_elderly2 == 1) {
            // Send email notification using SendGrid
            $apiKey = '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';
            $sendgrid = new \SendGrid($apiKey);

            $emailContent = new Mail();
            $emailContent->setFrom("idohershler@gmail.com", "ElderLink");
            $emailContent->setSubject("ביטול בקשה מספר $req_id");
            $emailContent->addTo("idohershler@gmail.com");
            $htmlContent = file_get_contents('email_template_cancel.php');
            $htmlContent = str_replace('$req_id', $req_id, $htmlContent);
            $htmlContent = str_replace('$formatted_date', $formatted_date, $htmlContent);
            $htmlContent = str_replace('$start_time', $formatted_start_time, $htmlContent);
            $htmlContent = str_replace('$end_time', $formatted_end_time, $htmlContent);
            $htmlContent = str_replace('$volunteer_phone', $volunteer_phone, $htmlContent);
            $htmlContent = str_replace('$volunteer_first_name', $volunteer_first_name, $htmlContent);
            $htmlContent = str_replace('$first_name', $first_name, $htmlContent);
            $emailContent->addContent("text/html", $htmlContent);
            $json = json_encode($emailContent);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.sendgrid.com/v3/mail/send');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ]);

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            echo 'Email sent successfully. Status code: ' . $httpcode . "\n";
            echo 'Response: ' . $response . "\n";
        }
    } else {
        echo "Error updating request: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>