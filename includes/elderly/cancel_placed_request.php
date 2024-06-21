<?php
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['session_token'])) {
    header("Location: ../../index.html");
    exit();
}

// Send SMS notification using Twilio
require_once '../twilio-php-main/src/Twilio/autoload.php';
use Twilio\Rest\Client;

// Send email notification using SendGrid
require '../../../../vendor/autoload.php';
use SendGrid\Mail\Mail;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];

    $host = "localhost";
    $user = "yuvalar2_MainUser";
    $password = "poch9M?Thuq";
    $db = "yuvalar2_ElderLink";

    $conn = new mysqli($host, $user, $password, $db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Start a transaction
    $conn->begin_transaction();
    
    try {
        $select_request_sql = "SELECT r.phone, r.date, r.start_time, r.end_time, r.email, r.first_name, r.email_volunteer, u.phone AS volunteer_phone, u.first_name AS volunteer_first_name
                               FROM requests r
                               LEFT JOIN users u ON r.email_volunteer = u.email
                               WHERE r.req_id = ?";
        $select_request_statement = $conn->prepare($select_request_sql);
        $select_request_statement->bind_param("i", $request_id);
        $select_request_statement->execute();
        $select_request_statement->bind_result($phone, $date, $start_time, $end_time, $email, $first_name, $email_volunteer, $volunteer_phone, $volunteer_first_name);
        $select_request_statement->fetch();
        $select_request_statement->close();


        // Format the date
        $formatted_date = date("d/m/Y", strtotime($date));

        // Update the cancelled_at column to the current timestamp
        $sql = "UPDATE requests SET cancelled_at = NOW() WHERE req_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $request_id);
        $stmt->execute();

        if (!$stmt->execute()) {
            throw new Exception("Error canceling request in requests table: " . $stmt->error);
        }

        // Delete the related rows from the requests_times table
        $sql = "DELETE FROM requests_times WHERE req_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $request_id);
        if (!$stmt->execute()) {
            throw new Exception("Error canceling request in requests_times table: " . $stmt->error);
        }

        $conn->commit();

        // Check if sms_notification_volunteer or email_notification_volunteer is set to 1 for the volunteer
        $select_user_sql = "SELECT sms_notification_volunteer, email_notification_volunteer FROM users WHERE email = ?";
        $select_user_statement = $conn->prepare($select_user_sql);
        $select_user_statement->bind_param("s", $email_volunteer);
        $select_user_statement->execute();
        $select_user_statement->bind_result($sms_notification_volunteer, $email_notification_volunteer);
        $select_user_statement->fetch();
        $select_user_statement->close();

        // Send SMS if sms_notification_volunteer is set to 1
        if ($sms_notification_volunteer == 1) {
            $sid    = "AC876333226ebe2795f848265f4bbfc0dd";
            $token  = "1b4869f4cd22ae6df7827932aa67e437";
            $twilio = new Client($sid, $token);

            $phone_with_prefix = "+$phone"; // Adding '+' prefix to the phone number
            $message = $twilio->messages
                ->create("+972548147448", 
                    array(
                        "from" => "+15807413370",
                        "body" => ".
שלום $volunteer_first_name, 
בקשה מספר $request_id שאישרת באתר ElderLink בוטלה על ידי המבוגר/ת.
אנו מאחלים לך יום מהנה ונעים, מצוות אתר ElderLink."
                    )
                );
        }

        // Send email if email_notification_volunteer is set to 1
        if ($email_notification_volunteer == 1) {
            // Send email notification using SendGrid
            $apiKey = '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';
            $sendgrid = new \SendGrid($apiKey);

             $emailContent = new Mail();
            $emailContent->setFrom("idohershler@gmail.com", "ElderLink");
            $emailContent->setSubject("ביטול בקשה מספר $request_id");
            $emailContent->addTo("idohershler@gmail.com");
            $htmlContent = file_get_contents('email_template_cancel.php');
            $htmlContent = str_replace('$req_id', $request_id, $htmlContent);
            $htmlContent = str_replace('$formatted_date', $formatted_date, $htmlContent);
            $htmlContent = str_replace('$start_time', $formatted_start_time, $htmlContent);
            $htmlContent = str_replace('$end_time', $formatted_end_time, $htmlContent);
            $htmlContent = str_replace('$volunteer_phone', $volunteer_phone, $htmlContent);
                        $htmlContent = str_replace('$volunteer_first_name', $volunteer_first_name, $htmlContent);
            $emailContent->addContent("text/html", $htmlContent);

            // Convert email object to JSON
            $json = json_encode($emailContent);

            // Setup cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.sendgrid.com/v3/mail/send');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ]);

            // Send the email
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

        }

        echo "הבקשה בוטלה בהצלחה!";
    } catch (Exception $e) {
        // Rollback the transaction if any query fails
        $conn->rollback();
        echo $e->getMessage();
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request";
}
?>