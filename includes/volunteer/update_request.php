<?php
session_start();
// Check if session variables are set and session token matches the database
if (!isset($_SESSION['email']) || !isset($_SESSION['session_token'])) {
    echo "Session expired or invalid.";
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

require_once '../twilio-php-main/src/Twilio/autoload.php';
use Twilio\Rest\Client;

// Send email notification using SendGrid
require '../../../../vendor/autoload.php';
use SendGrid\Mail\Mail;

$req_id = $_POST['req_id'];
$selected_time_slot = $_POST['selected_time_slot'];
$email_volunteer = $_SESSION['email'];

// Separate date, start time, and end time from the selected time slot
list($date, $time_range) = explode(" • ", $selected_time_slot);
list($start_time, $end_time) = explode(" - ", $time_range);

// Convert date from dd/mm/yyyy to yyyy-mm-dd for database
$date_parts = explode("/", $date);
$date = $date_parts[2] . "-" . $date_parts[1] . "-" . $date_parts[0];

// Update the requests table including confirmed_at
$sql = "UPDATE requests SET date = ?, start_time = ?, end_time = ?, email_volunteer = ?, confirmed_at = CURRENT_TIMESTAMP, cancelled_at = NULL, email_volunteer_cancelled = NULL WHERE req_id = ?";
$statement = $conn->prepare($sql);
$statement->bind_param("ssssi", $date, $start_time, $end_time, $email_volunteer, $req_id);
$statement->execute();

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


// Check if sms_notification_elderly1 is 1 for the user
$select_sms_notification_sql = "SELECT sms_notification_elderly1, email_notification_elderly1 FROM users WHERE email = ?";
$select_sms_notification_statement = $conn->prepare($select_sms_notification_sql);
$select_sms_notification_statement->bind_param("s", $email);
$select_sms_notification_statement->execute();
$select_sms_notification_statement->bind_result($sms_notification, $email_notification_elderly1);
$select_sms_notification_statement->fetch();
$select_sms_notification_statement->close();

$formatted_date = date("d/m/Y", strtotime($date));

if ($sms_notification == 1) {
    $sid    = "AC876333226ebe2795f848265f4bbfc0dd";
    $token  = "1b4869f4cd22ae6df7827932aa67e437";
    $twilio = new Client($sid, $token);
    $phone_with_prefix = "+$phone"; // Adding '+' prefix to the phone number
    $message = $twilio->messages->create(
        "+972548147448", // to
        array(
            "from" => "+15807413370",
            "body" => ".
שלום $first_name, 
בקשה מספר $req_id ששלחת באתר ElderLink שובצה בהצלחה למתנדב/ת בשם יובל.
תאריך המפגש: $formatted_date
שעת התחלה: $formatted_start_time
שעת סיום: $formatted_end_time

באפשרותך ליצור קשר עם המתנדב/ת בטלפון $volunteer_phone לתיאום סופי של הפרטים.

אנו מאחלים לך מפגש מהנה ונעים, מצוות אתר ElderLink."
        )
    );
}

if ($email_notification_elderly1 == 1) {
    // Send email notification using SendGrid
    $apiKey = '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';
    $sendgrid = new \SendGrid($apiKey);

   $emailContent = new Mail();
            $emailContent->setFrom("idohershler@gmail.com", "ElderLink");
            $emailContent->setSubject("שיבוץ בקשה מספר $req_id");
            $emailContent->addTo("idohershler@gmail.com");
            $htmlContent = file_get_contents('email_template_confirm.php');
            $htmlContent = str_replace('$req_id', $req_id, $htmlContent);
            $htmlContent = str_replace('$formatted_date', $formatted_date, $htmlContent);
            $htmlContent = str_replace('$start_time', $formatted_start_time, $htmlContent);
            $htmlContent = str_replace('$end_time', $formatted_end_time, $htmlContent);
            $htmlContent = str_replace('$volunteer_phone', $volunteer_phone, $htmlContent);
                        $htmlContent = str_replace('$volunteer_first_name', $volunteer_first_name, $htmlContent);
            $htmlContent = str_replace('$first_name', $first_name, $htmlContent);            
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

if ($statement->affected_rows > 0) {
    echo "הבקשה שובצה בהצלחה!";
} else {
    echo "Failed to update request: " . $conn->error;
}

$statement->close();
$conn->close();
?>
