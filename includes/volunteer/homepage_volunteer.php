<?php
require '../check_session.php';
?>

<?php
session_start();

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
$user = $result->fetch_assoc();
$first_name = $user['first_name'];
$gender = $user['gender'];
$req_id = $_POST['req_id'];
$date = $_POST['date'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$email_volunteer = $_POST['email_volunteer'];

switch ($gender) {
    case 'זכר':
        $assist_text = "תרצה";
        break;
    case 'נקבה':
        $assist_text = "תרצי";
        break;
    default:
        $assist_text = "תרצו";
        break;
}
// Update the requests table
$sql = "UPDATE requests SET date = ?, start_time = ?, end_time = ?, email_volunteer = ? WHERE req_id = ?";
$statement = $conn->prepare($sql);
$statement->bind_param("ssssi", $date, $start_time, $end_time, $email_volunteer, $req_id);
$statement->execute();
?>

<?php

$request_count = 0;
$user_email = $_SESSION['email'];
$sql_preferences = "SELECT city_p, req_type_p, day_p, language FROM users WHERE email = '$user_email'";
$result_preferences = $conn->query($sql_preferences);
$user_preferences = $result_preferences->fetch_assoc();
$city_p = isset($user_preferences['city_p']) ? $user_preferences['city_p'] : null;
$req_type_p = isset($user_preferences['req_type_p']) ? $user_preferences['req_type_p'] : null;
$day_p = isset($user_preferences['day_p']) ? $user_preferences['day_p'] : null;
$logged_in_user_lang_p = isset($user_preferences['language']) ? $user_preferences['language'] : null;

// Convert day_p to a format that can be used in SQL
$day_of_week_mapping = [
    'Sunday' => 1, // MySQL DAYOFWEEK() returns 1 for Sunday, 2 for Monday, ...
    'Monday' => 2,
    'Tuesday' => 3,
    'Wednesday' => 4,
    'Thursday' => 5,
    'Friday' => 6,
    'Saturday' => 7
];

$days_filter = null;
if ($day_p) {
    $days_p_array = explode(', ', $day_p);
    $days_filter = array_map(function ($day) use ($day_of_week_mapping) {
        return $day_of_week_mapping[$day];
    }, $days_p_array);
    $days_filter = implode(',', $days_filter);
}

// Build the base SQL query
$sql = "SELECT r.req_id, r.first_name, r.last_name, r.email, r.phone, r.city, r.street, r.street_num, r.req_type, r.content,
                               GROUP_CONCAT(CONCAT(rt.date, ' &bull; ', DATE_FORMAT(rt.start_time, '%H:%i'), ' - ', DATE_FORMAT(rt.end_time, '%H:%i')) SEPARATOR '<br>') AS time_slots,
                               u.language AS req_user_lang_p
                        FROM requests r
                        LEFT JOIN requests_times rt ON r.req_id = rt.req_id
                        INNER JOIN users u ON r.email = u.email
                        WHERE r.email_volunteer IS NULL
                          AND (rt.date > CURDATE() OR (rt.date = CURDATE() AND rt.start_time >= CURTIME()))";

// Add city filter if provided
if ($city_p) {
    $cities = explode(', ', $city_p);
    $city_conditions = array_map(function ($city) {
        return "r.city = '$city'";
    }, $cities);
    $sql .= " AND (" . implode(' OR ', $city_conditions) . ")";
}

// Add request type filter if provided
if ($req_type_p) {
    $req_types = explode(', ', $req_type_p);
    $other_included = in_array('other', $req_types);
    $req_type_conditions = array_map(function ($req_type) {
        return "r.req_type = '$req_type'";
    }, $req_types);

    if ($other_included) {
        $specified_types = ["'עזרה טכנולוגית'", "'ליווי לרופא'", "'סיוע בקניות'", "'רכישת תרופות'", "'פנאי יחדיו'"];
        $req_type_conditions[] = "(r.req_type NOT IN (" . implode(', ', $specified_types) . "))";
    }

    $sql .= " AND (" . implode(' OR ', $req_type_conditions) . ")";
}

// Add day filter if provided
if ($days_filter) {
    $sql .= " AND DAYOFWEEK(rt.date) IN ($days_filter)";
}

// Add language filter
$sql .= " GROUP BY r.req_id";
$result = $conn->query($sql);

// Check if there are any requests
if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {

        // Fetch user's picture filename based on email
        $user_email = $row['email'];
        $sql_user = "SELECT picture FROM users WHERE email = '$user_email'";
        $result_user = $conn->query($sql_user);
        $user_picture = ''; // Initialize user picture variable
        if ($result_user->num_rows > 0) {
            $user_row = $result_user->fetch_assoc();
            $user_picture_filename = $user_row['picture'];
            $user_picture_path = "../../uploads/" . $user_picture_filename;
            // Check if the picture file exists
            if (file_exists($user_picture_path)) {
                $user_picture = $user_picture_path;
            }
        }

        $has_requests = false;
        // Add language filter
        if ($logged_in_user_lang_p && $row['req_user_lang_p']) {
            $logged_in_user_langs = explode(',', $logged_in_user_lang_p);
            $req_user_langs = explode(',', $row['req_user_lang_p']);

            $has_common_lang = false;
            foreach ($logged_in_user_langs as $lang) {
                $lang = trim($lang); // Remove any leading/trailing whitespace
                if (in_array($lang, $req_user_langs)) {
                    $has_common_lang = true;
                    break;
                }
            }

            if (!$has_common_lang) {
                continue; // Skip this request if no common languages
                $has_requests = true;
            }

            // Check if there were any requests with matching languages
            $request_count++;
        }
    }
}

// Function to count requests based on criteria
function countRequests($conn, $email, $finished)
{
    $sql = "
        SELECT COUNT(*) AS request_count
        FROM requests
        WHERE email_volunteer = ? ";

    if ($finished) {
        $sql .= "AND (date < CURDATE() OR (date = CURDATE() AND end_time <= CURTIME())) ";
    } else {
        $sql .= "AND (date > CURDATE() OR (date = CURDATE() AND end_time > CURTIME())) AND cancelled_at IS NULL";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['request_count'];
}

$placed_requests_count = countRequests($conn, $email, false);

// Count finished requests
$finished_requests_count = countRequests($conn, $email, true);

?>

<!doctype html>
<html lang="he" data-bs-theme="auto">

<head>
    <title>
        ElderLink - דף בית מתנדב
    </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <link rel="icon" href="../../images/favicon.png" type="image/x-icon">
    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/list-groups/">
    <link rel="stylesheet" href="../../css/homepage.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@100..900&display=swap" rel="stylesheet">
    <title>Sidebars · Bootstrap v5.3</title>
    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/sidebars/">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link href="sidebars.css" rel="stylesheet">
    
</head>
<body>
    <main class="d-flex flex-nowrap">
        <header>
            <div class="mobile-tab-bar">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.0/font/bootstrap-icons.css"
                    rel="stylesheet">
                <nav class="nav">
                    <a href="homepage_volunteer.php" class="nav__link nav__link--active">
                        <i class="bi bi-house-fill nav__icon"></i>
                        <span class="nav__text">דף הבית</span>
                    </a>
                    <a href="waiting_requests.php" class="nav__link">
                        <i class="bi-hourglass nav__icon"></i>
                        <span class="nav__text">בקשות ממתינות</span>
                    </a>
                    <a href="placed_requests.php" class="nav__link">
                        <i class="bi bi-calendar-check nav__icon"></i>
                        <span class="nav__text">בקשות ששובצו</span>
                    </a>
                    <a href="settings_profile.php" class="nav__link">
                        <i class="bi bi-person-circle nav__icon"></i>
                        <span class="nav__text">פרופיל</span>
                    </a>
                    <a href="contact_us.php" class="nav__link">
                        <i class="bi bi-chat-right-text nav__icon"></i>
                        <span class="nav__text">צור קשר</span>
                    </a>
                </nav>

                <div class="top-bar">
                    <a href="homepage_volunteer.php">
                        <img src="../../images/elderlink-high-resolution-logo-transparent.png" alt="logo"
                            id="logo_top-bar" class="logo_top-bar">
                    </a>
                    <div class="top-bar-icons">
                        <a href="../logout.php"
                            class="d-flex align-items-center link-body-emphasis text-decoration-none "
                            aria-expanded="false">
                            <i class="bi p-1 bi-box-arrow-in-right me-2 top-bar-icon">
                            </i>
                        </a>
                        <a href="settings_security.php"
                            class="d-flex align-items-center link-body-emphasis text-decoration-none "
                            aria-expanded="false">
                            <i class="bi p-1 bi-gear top-bar-icon">
                            </i>
                        </a>
                        <a href="notifications.php"
                            class="d-flex align-items-center link-body-emphasis text-decoration-none "
                            aria-expanded="false">
                            <i class="bi p-1 bi-bell top-bar-icon">
                            </i>
                        </a>
                        <i class="top-bar-icon fa fa-comment"></i>
                    </div>
                </div>
            </div>




            <div class="main_nav">
                <div class="d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary" style="width: 280px;">
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.0/font/bootstrap-icons.css"
                        rel="stylesheet">

                    <a href="homepage_volunteer.php"
                        class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
                        <img src="../../images/elderlink-high-resolution-logo-transparent.png" alt="logo" id="logo_page"
                            class="logo_page">
                    </a>
                    <hr>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="homepage_volunteer.php" class="nav-link active" aria-current="page">
                                <i class="bi bi-house-fill me-2" width="20" height="20"></i>
                                דף הבית
                            </a>
                        </li>
                        <li>
                            <a href="waiting_requests.php" class="nav-link link-body-emphasis">
                                <i class="bi bi-hourglass me-2" width="20" height="20"></i>
                                בקשות ממתינות
                            </a>
                        </li>
                        <li>
                            <a href="placed_requests.php" class="nav-link link-body-emphasis">
                                <i class="bi bi-calendar-check me-2" width="20" height="20"></i>
                                בקשות ששובצו
                            </a>
                        </li>
                        <li>
                            <a href="notifications.php" class="nav-link link-body-emphasis">
                                <i class="bi bi-bell me-2" width="20" height="20"></i>
                                התראות
                            </a>
                        </li>

                        <li>
                            <a href="settings_profile.php" class="nav-link link-body-emphasis">
                                <i class="bi bi-person-circle me-2" width="20" height="20"></i>
                                פרופיל
                            </a>
                        </li>
                        <li>
                            <a href="contact_us.php" class="nav-link link-body-emphasis">
                                <i class="bi bi-chat-right-text me-2" width="20" height="20"></i>
                                צור קשר
                            </a>
                        </li>
                        <hr>
                        <li>
                            <a href="settings_security.php" class="nav-link link-body-emphasis bi">
                                <i class="bi bi-gear me-2" width="20" height="20"></i>
                                הגדרות
                            </a>
                        </li>
                        <li>
                            <a href="../logout.php" class="nav-link link-body-emphasis">
                                <i class="bi bi-box-arrow-in-right me-2" width="20" height="20"></i>
                                התנתקות
                            </a>
                        </li>
                    </ul>


                </div>
            </div>
        </header>

        <div class="container">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>שלום מתנדב יקר! </strong>על מנת לסנן את הבקשות הממתינות לפי בחירתך, אנו ממליצים לך למלא את <a
                    href="settings_preferences.php">העדפות מתנדב.</a><br>

                בנוסף, אנו ממליצים לעדכן את השפות שהינך דובר/ת, ואת תמונת הפרופיל <a href="settings_profile.php">בדף
                    פרופיל.</a>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <section class="hero  p-0  rounded shadow-sm">
                <div class="container">
                    <h1>היי <?php echo htmlspecialchars($first_name); ?>, במה
                        <?php echo htmlspecialchars($assist_text); ?> לסייע היום?</h1>
                    <div class="icon-grid">
                        <a href="#">
                            <img src="../../images/client.png" alt="Icon 1">
                            <span class="icon-title">עזרה טכנולוגית</span>
                        </a>
                        <a href="#">
                            <img src="../../images/grocery.png" alt="Icon 2">
                            <span class="icon-title">סיוע בקניות</span>
                        </a>
                        <a href="#">
                            <img src="../../images/park.png" alt="Icon 3">
                            <span class="icon-title">פנאי יחדיו</span>
                        </a>
                        <a href="#">
                            <img src="../../images/prescription.png" alt="Icon 4">
                            <span class="icon-title">רכישת תרופות </span>
                        </a>
                        <a href="#">
                            <img src="../../images/doctor.png" alt="Icon 5">
                            <span class="icon-title">ליווי לרופא</span>
                        </a>
                    </div>
                </div>
            </section>

            <style>
                .responsive-div {
                    width: 100%;
                }

                @media (min-width: 768px) {
                    .responsive-div {
                        width: 49%;
                    }

                }

                @media (max-width: 768px) {

                    .req-id-hide {
                        display: none;
                    }
                }
            </style>

            <div style="display: flex; align-items: center; margin-top: 30px;">
                <div style="flex: 1; border-top: 1px solid #dddddd;"></div>
                <h4 style="font-weight: 600; margin: 0 10px; color: #2b2b2b;">בקשות ששובצו
                    [<?php echo $placed_requests_count + $finished_requests_count; ?>] </h4>
                <div style="flex: 1; border-top: 1px solid #dddddd;"></div>
            </div>

            <div class="outer-container">
                <div class="my-3 p-3  responsive-div">

                    <h6 class="border-bottom pb-2 mb-0 gap-3">
                        <span class="active-request-indicator"></span>
                        בקשות פעילות [<?php echo $placed_requests_count; ?>]
                    </h6>
                    <div class="list-group-container w-100">
                        <div class="list-group pt-3 gap-2 w-100">

                            <?php
                            error_reporting(E_ALL);
                            ini_set('display_errors', 1);

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

                            // Function to fetch user email from session token
                            function getUserEmail($session_token, $conn)
                            {
                                $sql = "SELECT email FROM users WHERE session_token = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("s", $session_token);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($result->num_rows > 0) {
                                    $row = $result->fetch_assoc();
                                    return $row['email'];
                                } else {
                                    return null;
                                }
                            }

                            // Fetch the current user's email
                            $user_email = getUserEmail($_SESSION['session_token'], $conn);

                            // Fetch requests for the current user that have not passed
                            $sql = "
SELECT r.*, 
       u1.gender AS volunteer_gender, 
       u1.date_of_birth AS volunteer_date_of_birth, 
       u1.language AS volunteer_language,
       u1.picture AS volunteer_picture,
       u2.gender AS elderly_gender, 
       u2.date_of_birth AS elderly_date_of_birth, 
       u2.language AS elderly_language,
       u2.phone AS elderly_phone,
       u2.picture AS elderly_picture
FROM requests r
JOIN users u1 ON r.email_volunteer = u1.email
JOIN users u2 ON r.email = u2.email
WHERE r.email_volunteer = ? 
AND (r.date > CURDATE() OR (r.date = CURDATE() AND r.end_time > CURTIME()))
                                    AND r.cancelled_at IS NULL
                                    ORDER BY r.confirmed_at DESC
limit 3";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("s", $user_email);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result->num_rows == 0) {
                                echo 'אין בקשות פעילות להצגה.';
                            } else {
                                while ($row = $result->fetch_assoc()) {
                                    $volunteer_profile_image_url = $row['volunteer_picture'] ? '../../uploads/' . htmlspecialchars($row['volunteer_picture']) : 'default_volunteer_image_url'; // Replace with actual default image URL if needed
                                    $elderly_profile_image_url = $row['elderly_picture'] ? '../../uploads/' . htmlspecialchars($row['elderly_picture']) : 'default_elderly_image_url'; // Replace with actual default image URL if needed
                            
                                    // Format date, start time, end time, and date of birth
                                    $date = date("d/m/Y", strtotime($row['date']));
                                    $start_time = date("H:i", strtotime($row['start_time']));
                                    $end_time = date("H:i", strtotime($row['end_time']));
                                    $volunteer_date_of_birth = date("d-m-Y", strtotime($row['volunteer_date_of_birth']));
                                    $elderly_date_of_birth = date("d/m/Y", strtotime($row['elderly_date_of_birth']));

                                    echo '<a href="#" class="list-group-item list-group-item-action d-flex gap-2 p-2 mb-2" aria-current="true">';
                                    echo '<img src="' . $elderly_profile_image_url . '" alt="תמונת פרופיל" width="44" height="44" class="rounded-circle flex-shrink-0">';
                                    echo '<div class="d-flex w-100 justify-content-between">';
                                    echo '<div class="justify-content-between">';
                                    echo '<h6>' . htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']) . '</h6>';
                                    echo '<hr style="width:20%; margin-top: 5px; margin-bottom: 10px;">';
                                    echo '<small class="opacity-75 text-nowrap text-decoration-underline">פרטי המפגש</small>';
                                    echo '<div class="d-flex gap-3">';
                                    echo '<p><b>אופי הבקשה: </b><br>' . htmlspecialchars($row['req_type']) . '</p>';
                                    echo '<div class="vr"></div>';
                                    echo '<p><b>עיר: </b><br>' . htmlspecialchars($row['city']) . '</p>';
                                    echo '<div class="vr"></div>';
                                    echo '<p><b>כתובת: </b><br>' . htmlspecialchars($row['street']) . ' ' . htmlspecialchars($row['street_num']) . '</p>';
                                    echo '</div>';
                                    echo '<div class="d-flex gap-3">';

                                    echo '<p><b>תאריך: </b><br>' . htmlspecialchars((new DateTime($row['date']))->format('d/m/Y')) . '</p>';
                                    echo '<div class="vr"></div>';
                                    echo '<p><b>שעת התחלה: </b><br>' . htmlspecialchars((new DateTime($row['start_time']))->format('H:i')) . '</p>';
                                    echo '<div class="vr"></div>';
                                    echo '<p><b>שעת סיום: </b><br>' . htmlspecialchars((new DateTime($row['end_time']))->format('H:i')) . '</p>';
                                    echo '</div>';

                                    echo '<div class="expanded-content">';
                                    echo '<p><b>הערות נוספות: </b>' . htmlspecialchars($row['content']) . '</p>';
                                    echo '<small class="opacity-75 text-nowrap text-decoration-underline">פרטי המבוגר</small>';
                                    echo '<div class="d-flex gap-2">';


                                    echo '  <p><b>מגדר: </b>' . htmlspecialchars($row['elderly_gender']) . '</p>';
                                    echo '<div class="vr"></div>';
                                    ;
                                    echo ' <p><b>שפות: </b>' . htmlspecialchars($row['elderly_language']) . '</p>';
                                    echo '<div class="vr"></div>';
                                    echo '<p>
                <b>מספר הטלפון: </b>
                <span style="cursor: pointer; color: blue; text-decoration: underline;" onclick="window.open(\'https://wa.me/' . htmlspecialchars($row['elderly_phone']) . '\', \'_blank\');">' . htmlspecialchars($row['elderly_phone']) . '</span>
              </p>';
                                    echo '</div>';
                                    echo '</div>';
                                    echo '</div>';
                                    echo '<small class="opacity-75 text-nowrap req-id-hide">בקשה #' . htmlspecialchars($row['req_id']) . '</small>';
                                    echo '</div>';
                                    echo '</a>';
                                }
                            }

                            ?>
                            <p class="text-nowrap"
                                style="color: #2b2b2b; font-weight: 600; text-align: center; font-size: 18px;">
                                <a href="placed_requests.php">לכל הבקשות הפעילות</a>
                            </p>

                        </div>
                    </div>

                </div>
                <div class="my-3 p-3   responsive-div">
                    <h6 class="border-bottom pb-2 mb-0 gap-3">
                        <span class="inactive-request-indicator"></span>

                        בקשות שהסתיימו [<?php echo $finished_requests_count; ?>]
                    </h6>
                    <div class="list-group pt-3 gap-2">

                        <?php


                        // Fetch the current user's email
                        $user_email = getUserEmail($_SESSION['session_token'], $conn);

                        // Fetch requests for the current user that have not passed
                        $sql = "
SELECT r.*, 
       u1.gender AS volunteer_gender, 
       u1.date_of_birth AS volunteer_date_of_birth, 
       u1.language AS volunteer_language,
       u1.picture AS volunteer_picture,
       u2.gender AS elderly_gender, 
       u2.date_of_birth AS elderly_date_of_birth, 
       u2.language AS elderly_language,
       u2.phone AS elderly_phone,
       u2.picture AS elderly_picture
FROM requests r
JOIN users u1 ON r.email_volunteer = u1.email
JOIN users u2 ON r.email = u2.email
WHERE r.email_volunteer = ? 
AND (r.date < CURDATE() OR (r.date = CURDATE() AND r.end_time < CURTIME()))
limit 3";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $user_email);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows == 0) {
                            echo 'אין בקשות שהסתיימו להצגה.';
                        } else {
                            while ($row = $result->fetch_assoc()) {
                                $volunteer_profile_image_url = $row['volunteer_picture'] ? '../../uploads/' . htmlspecialchars($row['volunteer_picture']) : 'default_volunteer_image_url'; // Replace with actual default image URL if needed
                                $elderly_profile_image_url = $row['elderly_picture'] ? '../../uploads/' . htmlspecialchars($row['elderly_picture']) : 'default_elderly_image_url'; // Replace with actual default image URL if needed
                        
                                // Format date, start time, end time, and date of birth
                                $date = date("d/m/Y", strtotime($row['date']));
                                $start_time = date("H:i", strtotime($row['start_time']));
                                $end_time = date("H:i", strtotime($row['end_time']));
                                $volunteer_date_of_birth = date("d-m-Y", strtotime($row['volunteer_date_of_birth']));
                                $elderly_date_of_birth = date("d/m/Y", strtotime($row['elderly_date_of_birth']));

                                echo '<a href="#" class="list-group-item list-group-item-action d-flex gap-2 p-2 mb-2" aria-current="true">';
                                echo '<img src="' . $elderly_profile_image_url . '" alt="תמונת פרופיל" width="44" height="44" class="rounded-circle flex-shrink-0">';
                                echo '<div class="d-flex w-100 justify-content-between">';
                                echo '<div class="justify-content-between">';
                                echo '<h6>' . htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']) . '</h6>';
                                echo '<hr style="width:20%; margin-top: 5px; margin-bottom: 10px;">';
                                echo '<small class="opacity-75 text-nowrap text-decoration-underline">פרטי המפגש</small>';
                                echo '<div class="d-flex gap-3">';
                                echo '<p><b>אופי הבקשה: </b><br>' . htmlspecialchars($row['req_type']) . '</p>';
                                echo '<div class="vr"></div>';
                                echo '<p><b>עיר: </b><br>' . htmlspecialchars($row['city']) . '</p>';
                                echo '<div class="vr"></div>';
                                echo '<p><b>כתובת: </b><br>' . htmlspecialchars($row['street']) . ' ' . htmlspecialchars($row['street_num']) . '</p>';
                                echo '</div>';
                                echo '<div class="d-flex gap-3">';
                                echo '<p><b>תאריך: </b><br>' . htmlspecialchars((new DateTime($row['date']))->format('d/m/Y')) . '</p>';
                                echo '<div class="vr"></div>';
                                echo '<p><b>שעת התחלה: </b><br>' . htmlspecialchars((new DateTime($row['start_time']))->format('H:i')) . '</p>';
                                echo '<div class="vr"></div>';
                                echo '<p><b>שעת סיום: </b><br>' . htmlspecialchars((new DateTime($row['end_time']))->format('H:i')) . '</p>';
                                echo '</div>';
                                echo '<div class="expanded-content">';
                                echo '<p><b>הערות נוספות: </b>' . htmlspecialchars($row['content']) . '</p>';
                                echo '<small class="opacity-75 text-nowrap text-decoration-underline">פרטי המבוגר</small>';
                                echo '<div class="d-flex gap-2">';
                                echo '  <p><b>מגדר: </b>' . htmlspecialchars($row['elderly_gender']) . '</p>';
                                echo '<div class="vr"></div>';
                                ;
                                echo ' <p><b>שפות: </b>' . htmlspecialchars($row['elderly_language']) . '</p>';
                                echo '<div class="vr"></div>';
                                echo '<p>
                <b>מספר הטלפון: </b>
                <span style="cursor: pointer; color: blue; text-decoration: underline;" onclick="window.open(\'https://wa.me/' . htmlspecialchars($row['elderly_phone']) . '\', \'_blank\');">' . htmlspecialchars($row['elderly_phone']) . '</span>
              </p>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';

                                echo '<small class="opacity-75 text-nowrap req-id-hide">בקשה #' . htmlspecialchars($row['req_id']) . '</small>';

                                echo '</div>';
                                echo '</a>';
                            }
                        }

                        ?>
                        <p class="text-nowrap"
                            style="color: #2b2b2b; font-weight: 600; text-align: center; font-size: 18px;">
                            <a href="finished_requests.php">לכל הבקשות שהסתיימו</a>
                        </p>
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; margin-top: 50px;">
                <div style="flex: 1; border-top: 1px solid #dddddd;"></div>
                <h4 style="font-weight: 600; margin: 0 10px; color: #2b2b2b;"> בקשות ממתינות
                    [<?php echo $request_count; ?>]
                </h4>
                <div style="flex: 1; border-top: 1px solid #dddddd;"></div>
            </div>
            <br>
            <div class="outer-container">
                <div class="my-3 p-3  responsive-div">

                    <h6 class="border-bottom pb-2 mb-0 gap-3">

                        קרוב אליך
                        <i class="bi bi-geo-alt"></i>
                    </h6>
                    <div class="list-group-container ">
                        <div class="list-group pt-3 gap-2 w-100">
                            <?php
                            $request_count = 0;
                            $user_email = $_SESSION['email']; // Assuming the 
                            $sql_preferences = "SELECT city_p, req_type_p, day_p, language, city, address, address_num FROM users WHERE email = '$user_email'";
                            $result_preferences = $conn->query($sql_preferences);
                            $user_preferences = $result_preferences->fetch_assoc();

                            $city_p = isset($user_preferences['city_p']) ? $user_preferences['city_p'] : null;
                            $req_type_p = isset($user_preferences['req_type_p']) ? $user_preferences['req_type_p'] : null;
                            $day_p = isset($user_preferences['day_p']) ? $user_preferences['day_p'] : null;
                            $logged_in_user_lang_p = isset($user_preferences['language']) ? $user_preferences['language'] : null;

                            $user_city = $user_preferences['city'];
                            $user_address = $user_preferences['address'];
                            $user_address_num = $user_preferences['address_num'];
                            $user_full_address = "$user_address $user_address_num, $user_city";

                            // Convert day_p to a format that can be used in SQL
                            $day_of_week_mapping = [
                                'Sunday' => 1,  
                                'Monday' => 2,
                                'Tuesday' => 3,
                                'Wednesday' => 4,
                                'Thursday' => 5,
                                'Friday' => 6,
                                'Saturday' => 7
                            ];

                            $days_filter = null;
                            if ($day_p) {
                                $days_p_array = explode(', ', $day_p);
                                $days_filter = array_map(function ($day) use ($day_of_week_mapping) {
                                    return $day_of_week_mapping[$day];
                                }, $days_p_array);
                                $days_filter = implode(',', $days_filter);
                            }

                            // Build the base SQL query
                            $sql = "SELECT r.req_id, r.first_name, r.last_name, r.email, r.phone, r.city, r.street, r.street_num, r.req_type, r.content,
               GROUP_CONCAT(CONCAT(rt.date, ' &bull; ', DATE_FORMAT(rt.start_time, '%H:%i'), ' - ', DATE_FORMAT(rt.end_time, '%H:%i')) SEPARATOR '<br>') AS time_slots,
               u.language AS req_user_lang_p
        FROM requests r
        LEFT JOIN requests_times rt ON r.req_id = rt.req_id
        INNER JOIN users u ON r.email = u.email
        WHERE r.email_volunteer IS NULL
          AND (rt.date > CURDATE() OR (rt.date = CURDATE() AND rt.start_time >= CURTIME()))";

                            // Add city filter if provided
                            if ($city_p) {
                                $cities = explode(', ', $city_p);
                                $city_conditions = array_map(function ($city) {
                                    return "r.city = '$city'";
                                }, $cities);
                                $sql .= " AND (" . implode(' OR ', $city_conditions) . ")";
                            }

                            // Add request type filter if provided
                            if ($req_type_p) {
                                $req_types = explode(', ', $req_type_p);
                                $other_included = in_array('other', $req_types);
                                $req_type_conditions = array_map(function ($req_type) {
                                    return "r.req_type = '$req_type'";
                                }, $req_types);

                                if ($other_included) {
                                    $specified_types = ["'עזרה טכנולוגית'", "'ליווי לרופא'", "'סיוע בקניות'", "'רכישת תרופות'", "'פנאי יחדיו'"];
                                    $req_type_conditions[] = "(r.req_type NOT IN (" . implode(', ', $specified_types) . "))";
                                }

                                $sql .= " AND (" . implode(' OR ', $req_type_conditions) . ")";
                            }

                            // Add day filter if provided
                            if ($days_filter) {
                                $sql .= " AND DAYOFWEEK(rt.date) IN ($days_filter)";
                            }

                            // Add language filter
                            $sql .= " GROUP BY r.req_id";

                            $result = $conn->query($sql);

                            $requests = [];
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $requests[] = $row;
                                }
                            }

                            function calculateDistance($origin, $destination)
                            {
                                $apiKey = 'AIzaSyB2fyypixqUHF2IJnHMqO1o1ACghv6wd08';
                                $origin = urlencode($origin);
                                $destination = urlencode($destination);
                                $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$origin&destinations=$destination&key=$apiKey";
                                $response = file_get_contents($url);
                                $data = json_decode($response, true);

                                if ($data['status'] === 'OK') {
                                    $distance = $data['rows'][0]['elements'][0]['distance']['value']; // distance in meters
                                    return $distance;
                                }

                                return PHP_INT_MAX; // return a large number if unable to calculate distance
                            }

                            foreach ($requests as &$request) {
                                $request_address = "{$request['street']} {$request['street_num']}, {$request['city']}";
                                $request['distance'] = calculateDistance($user_full_address, $request_address);
                            }

                            usort($requests, function ($a, $b) {
                                return $a['distance'] - $b['distance'];
                            });

                            // Keep only the top 3 closest requests
                            $requests = array_slice($requests, 0, 3);

                            // Generate HTML for each request dynamically
                            foreach ($requests as $row) {
                                $user_email = $row['email'];
                                $sql_user = "SELECT picture FROM users WHERE email = '$user_email'";
                                $result_user = $conn->query($sql_user);
                                $user_picture = ''; // Initialize user picture variable
                                if ($result_user->num_rows > 0) {
                                    $user_row = $result_user->fetch_assoc();
                                    $user_picture_filename = $user_row['picture'];
                                    $user_picture_path = "../../uploads/" . $user_picture_filename;
                                    // Check if the picture file exists
                                    if (file_exists($user_picture_path)) {
                                        $user_picture = $user_picture_path;
                                    }
                                }

                                $has_requests = false;
                                // Add language filter
                                if ($logged_in_user_lang_p && $row['req_user_lang_p']) {
                                    $logged_in_user_langs = explode(',', $logged_in_user_lang_p);
                                    $req_user_langs = explode(',', $row['req_user_lang_p']);

                                    $has_common_lang = false;
                                    foreach ($logged_in_user_langs as $lang) {
                                        $lang = trim($lang); // Remove any leading/trailing whitespace
                                        if (in_array($lang, $req_user_langs)) {
                                            $has_common_lang = true;
                                            break;
                                        }
                                    }

                                    if (!$has_common_lang) {
                                        continue; // Skip this request if no common languages
                                        $has_requests = true;
                                    }
                                }

                                // Generate HTML for each request dynamically
                                echo '<a href="#" class="list-group-item list-group-item-action d-flex gap-2 p-2 mb-2 " aria-current="true">';
                                echo '<img src="' . $user_picture . '" alt="תמונת פרופיל" width="44" height="44" class="rounded-circle flex-shrink-0">';
                                echo '<div class="d-flex w-100 justify-content-between">';
                                echo '<div class="justify-content-between">';
                                echo '<h6>' . $row["first_name"] . ' ' . $row["last_name"] . '</h6>';
                                echo '<hr style="width:20%; margin-top: 5px; margin-bottom: 10px;">';
                                echo '<small class="opacity-75 text-nowrap text-decoration-underline">פרטי המפגש</small>';
                                echo '<div class="d-flex gap-3">';
                                echo '<p><b>אופי הבקשה: </b><br>' . $row["req_type"] . '</p>';
                                echo '<div class="vr"></div>';
                                echo '<p><b>עיר: </b><br>' . $row["city"] . '</p>';
                                echo '<div class="vr"></div>';
                                echo '<p><b>כתובת: </b><br>' . $row["street"] . ' ' . $row["street_num"] . '</p>';
                                echo '</div>';
                                echo '<p><b>הערות נוספות: </b>' . $row["content"] . '</p>';
                                echo '<div class="expanded-content">';
                                echo '<small class="opacity-75 text-nowrap text-decoration-underline">חלונות זמנים אפשריים</small>';
                                echo '<select class="form-select mt-2" aria-label="חלונות זמן אפשריים" style="font-size: 0.83rem;" id="time-slots-' . $row["req_id"] . '" onclick="handleTimeSlotClick(event)">';
                                echo '<option selected disabled>בחר חלון זמן...</option>';
                                echo '<option>' . str_replace('<br>', '</option><option>', $row["time_slots"]) . '</option>';
                                echo '</select>';
                                echo '<button class="btn btn-success ms-2 mt-4" onclick="showConfirmationPopup(' . $row["req_id"] . ')">אישור הבקשה</button>';
                                echo '</div>';
                                echo '</div>';
                                echo '<small class="opacity-75 text-nowrap req-id-hide">בקשה #' . $row["req_id"] . '</small>';
                                echo '</div>';
                                echo '</a>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="my-3 p-3   responsive-div">
                    <h6 class="border-bottom pb-2 mb-0 gap-3">

                        מותאם בשבילך
                        <i class="bi bi-magic"></i>
                    </h6>
                    <div class="list-group pt-3 gap-2 w-100">

                        <?php
                        $request_count = 0;
                        $user_email = $_SESSION['email']; 
                        $sql_preferences = "SELECT city_p, req_type_p, day_p, language FROM users WHERE email = '$user_email'";
                        $result_preferences = $conn->query($sql_preferences);
                        $user_preferences = $result_preferences->fetch_assoc();
                        $city_p = isset($user_preferences['city_p']) ? $user_preferences['city_p'] : null;
                        $req_type_p = isset($user_preferences['req_type_p']) ? $user_preferences['req_type_p'] : null;
                        $day_p = isset($user_preferences['day_p']) ? $user_preferences['day_p'] : null;
                        $logged_in_user_lang_p = isset($user_preferences['language']) ? $user_preferences['language'] : null;

                        $day_of_week_mapping = [
                            'Sunday' => 1,
                            'Monday' => 2,
                            'Tuesday' => 3,
                            'Wednesday' => 4,
                            'Thursday' => 5,
                            'Friday' => 6,
                            'Saturday' => 7
                        ];

                        $days_filter = null;
                        if ($day_p) {
                            $days_p_array = explode(', ', $day_p);
                            $days_filter = array_map(function ($day) use ($day_of_week_mapping) {
                                return $day_of_week_mapping[$day];
                            }, $days_p_array);
                            $days_filter = implode(',', $days_filter);
                        }

                        // Fetch past requests for the logged-in user
                        $sql_past_requests = "SELECT req_type, city FROM requests WHERE email_volunteer = '$user_email'";
                        $result_past_requests = $conn->query($sql_past_requests);

                        $past_requests = [];
                        if ($result_past_requests->num_rows > 0) {
                            while ($row = $result_past_requests->fetch_assoc()) {
                                $past_requests[] = $row;
                            }
                        }

                        // Function to score requests based on past preferences
                        function score_request($request, $past_requests)
                        {
                            $score = 0;
                            foreach ($past_requests as $past_request) {
                                if ($request['req_type'] == $past_request['req_type']) {
                                    $score += 2; // Higher weight for matching request type
                                }
                                if ($request['city'] == $past_request['city']) {
                                    $score += 1;
                                }
                            }
                            return $score;
                        }

                        // Build the base SQL query
                        $sql = "SELECT r.req_id, r.first_name, r.last_name, r.email, r.phone, r.city, r.street, r.street_num, r.req_type, r.content,
               GROUP_CONCAT(CONCAT(rt.date, ' &bull; ', DATE_FORMAT(rt.start_time, '%H:%i'), ' - ', DATE_FORMAT(rt.end_time, '%H:%i')) SEPARATOR '<br>') AS time_slots,
               u.language AS req_user_lang_p
        FROM requests r
        LEFT JOIN requests_times rt ON r.req_id = rt.req_id
        INNER JOIN users u ON r.email = u.email
        WHERE r.email_volunteer IS NULL
          AND (rt.date > CURDATE() OR (rt.date = CURDATE() AND rt.start_time >= CURTIME()))";

                        // Add city filter if provided
                        if ($city_p) {
                            $cities = explode(', ', $city_p);
                            $city_conditions = array_map(function ($city) {
                                return "r.city = '$city'";
                            }, $cities);
                            $sql .= " AND (" . implode(' OR ', $city_conditions) . ")";
                        }

                        // Add request type filter if provided
                        if ($req_type_p) {
                            $req_types = explode(', ', $req_type_p);
                            $other_included = in_array('other', $req_types);
                            $req_type_conditions = array_map(function ($req_type) {
                                return "r.req_type = '$req_type'";
                            }, $req_types);

                            if ($other_included) {
                                $specified_types = ["'עזרה טכנולוגית'", "'ליווי לרופא'", "'סיוע בקניות'", "'רכישת תרופות'", "'פנאי יחדיו'"];
                                $req_type_conditions[] = "(r.req_type NOT IN (" . implode(', ', $specified_types) . "))";
                            }

                            $sql .= " AND (" . implode(' OR ', $req_type_conditions) . ")";
                        }

                        // Add day filter if provided
                        if ($days_filter) {
                            $sql .= " AND DAYOFWEEK(rt.date) IN ($days_filter)";
                        }

                        // Add language filter
                        $sql .= " GROUP BY r.req_id";
                        $sql .= " ORDER BY r.req_id DESC limit 3"; // Order by req_id in descending order
                        $result = $conn->query($sql);

                        // Check if there are any requests
                        if ($result->num_rows > 0) {
                            $requests = [];
                            while ($row = $result->fetch_assoc()) {
                                // Calculate the score for each request
                                $row['score'] = score_request($row, $past_requests);
                                $requests[] = $row;
                            }

                            // Sort requests by score in descending order
                            usort($requests, function ($a, $b) {
                                return $b['score'] - $a['score'];
                            });

                            // Output data of each row
                            foreach ($requests as $row) {
                                // Fetch user's picture filename based on email
                                $user_email = $row['email'];
                                $sql_user = "SELECT picture FROM users WHERE email = '$user_email'";
                                $result_user = $conn->query($sql_user);
                                $user_picture = ''; // Initialize user picture variable
                                if ($result_user->num_rows > 0) {
                                    $user_row = $result_user->fetch_assoc();
                                    $user_picture_filename = $user_row['picture'];
                                    $user_picture_path = "../../uploads/" . $user_picture_filename;
                                    // Check if the picture file exists
                                    if (file_exists($user_picture_path)) {
                                        $user_picture = $user_picture_path;
                                    }
                                }

                                // Add language filter
                                if ($logged_in_user_lang_p && $row['req_user_lang_p']) {
                                    $logged_in_user_langs = explode(',', $logged_in_user_lang_p);
                                    $req_user_langs = explode(',', $row['req_user_lang_p']);

                                    $has_common_lang = false;
                                    foreach ($logged_in_user_langs as $lang) {
                                        $lang = trim($lang); // Remove any leading/trailing whitespace
                                        if (in_array($lang, $req_user_langs)) {
                                            $has_common_lang = true;
                                            break;
                                        }
                                    }

                                    if (!$has_common_lang) {
                                        continue; // Skip this request if no common languages
                                    }
                                }

                                // Generate HTML for each request dynamically
                                echo '<a href="#" class="list-group-item list-group-item-action d-flex gap-2 p-2 mb-2 " aria-current="true">';
                                echo '<img src="' . $user_picture . '" alt="תמונת פרופיל" width="44" height="44" class="rounded-circle flex-shrink-0">';
                                echo '<div class="d-flex w-100 justify-content-between">';
                                echo '<div class="justify-content-between">';
                                echo '<h6>' . $row["first_name"] . ' ' . $row["last_name"] . '</h6>';
                                echo '<hr style="width:20%; margin-top: 5px; margin-bottom: 10px;">';
                                echo '<small class="opacity-75 text-nowrap text-decoration-underline">פרטי המפגש</small>';
                                echo '<div class="d-flex gap-3">';
                                echo '<p><b>אופי הבקשה: </b><br>' . $row["req_type"] . '</p>';
                                echo '<div class="vr"></div>';
                                echo '<p><b>עיר: </b><br>' . $row["city"] . '</p>';
                                echo '<div class="vr"></div>';
                                echo '<p><b>כתובת: </b><br>' . $row["street"] . ' ' . $row["street_num"] . '</p>';
                                echo '</div>';
                                echo '<p><b>הערות נוספות: </b>' . $row["content"] . '</p>';
                                echo '<div class="expanded-content">';
                                echo '<small class="opacity-75 text-nowrap text-decoration-underline">חלונות זמנים אפשריים</small>';
                                echo '<select class="form-select mt-2" aria-label="חלונות זמן אפשריים" style="font-size: 0.83rem;" id="time-slots-' . $row["req_id"] . '" onclick="handleTimeSlotClick(event)">';
                                echo '<option selected disabled>בחר חלון זמן...</option>';
                                echo '<option>' . str_replace('<br>', '</option><option>', $row["time_slots"]) . '</option>';
                                echo '</select>';
                                echo '<button class="btn btn-success ms-2 mt-4" onclick="showConfirmationPopup(' . $row["req_id"] . ')">אישור הבקשה</button>';
                                echo '</div>';
                                echo '</div>';
                                echo '<small class="opacity-75 text-nowrap req-id-hide">בקשה #' . $row["req_id"] . '</small>';
                                echo '</div>';
                                echo '</a>';
                            }
                        }
                        ?>
                    </div>
                </div>

            </div>
            <div class="outer-container">
                <div class="my-3 p-3  responsive-div">
                    <h6 class="border-bottom pb-2 mb-0 gap-3">
                        החדשות ביותר
                        <i class="bi bi-arrow-clockwise"></i>
                    </h6>
                    <div class="list-group-container ">
                        <div class="list-group pt-3 gap-2 w-100">
                            <?php
                            $request_count = 0;
                            $user_email = $_SESSION['email']; 
                            $sql_preferences = "SELECT city_p, req_type_p, day_p, language FROM users WHERE email = '$user_email'";
                            $result_preferences = $conn->query($sql_preferences);
                            $user_preferences = $result_preferences->fetch_assoc();

                            $city_p = isset($user_preferences['city_p']) ? $user_preferences['city_p'] : null;
                            $req_type_p = isset($user_preferences['req_type_p']) ? $user_preferences['req_type_p'] : null;
                            $day_p = isset($user_preferences['day_p']) ? $user_preferences['day_p'] : null;
                            $logged_in_user_lang_p = isset($user_preferences['language']) ? $user_preferences['language'] : null;

                            // Convert day_p to a format that can be used in SQL
                            $day_of_week_mapping = [
                                'Sunday' => 1, 
                                'Monday' => 2,
                                'Tuesday' => 3,
                                'Wednesday' => 4,
                                'Thursday' => 5,
                                'Friday' => 6,
                                'Saturday' => 7
                            ];

                            $days_filter = null;
                            if ($day_p) {
                                $days_p_array = explode(', ', $day_p);
                                $days_filter = array_map(function ($day) use ($day_of_week_mapping) {
                                    return $day_of_week_mapping[$day];
                                }, $days_p_array);
                                $days_filter = implode(',', $days_filter);
                            }

                            // Build the base SQL query
                            $sql = "SELECT r.req_id, r.first_name, r.last_name, r.email, r.phone, r.city, r.street, r.street_num, r.req_type, r.content,
               GROUP_CONCAT(CONCAT(rt.date, ' &bull; ', DATE_FORMAT(rt.start_time, '%H:%i'), ' - ', DATE_FORMAT(rt.end_time, '%H:%i')) SEPARATOR '<br>') AS time_slots,
               u.language AS req_user_lang_p
        FROM requests r
        LEFT JOIN requests_times rt ON r.req_id = rt.req_id
        INNER JOIN users u ON r.email = u.email
        WHERE r.email_volunteer IS NULL
          AND (rt.date > CURDATE() OR (rt.date = CURDATE() AND rt.start_time >= CURTIME()))
          ";

                            // Add city filter if provided
                            if ($city_p) {
                                $cities = explode(', ', $city_p);
                                $city_conditions = array_map(function ($city) {
                                    return "r.city = '$city'";
                                }, $cities);
                                $sql .= " AND (" . implode(' OR ', $city_conditions) . ")";
                            }

                            // Add request type filter if provided
                            if ($req_type_p) {
                                $req_types = explode(', ', $req_type_p);
                                $other_included = in_array('other', $req_types);
                                $req_type_conditions = array_map(function ($req_type) {
                                    return "r.req_type = '$req_type'";
                                }, $req_types);

                                if ($other_included) {
                                    $specified_types = ["'עזרה טכנולוגית'", "'ליווי לרופא'", "'סיוע בקניות'", "'רכישת תרופות'", "'פנאי יחדיו'"];
                                    $req_type_conditions[] = "(r.req_type NOT IN (" . implode(', ', $specified_types) . "))";
                                }

                                $sql .= " AND (" . implode(' OR ', $req_type_conditions) . ")";
                            }

                            // Add day filter if provided
                            if ($days_filter) {
                                $sql .= " AND DAYOFWEEK(rt.date) IN ($days_filter)";
                            }

                            // Add language filter
                            $sql .= " GROUP BY r.req_id";
                            $sql .= " ORDER BY r.req_id DESC limit 3"; // Order by req_id in descending order
                            $result = $conn->query($sql);

                            // Check if there are any requests
                            if ($result->num_rows > 0) {
                                // Output data of each row
                                while ($row = $result->fetch_assoc()) {
                                    // Fetch user's picture filename based on email
                                    $user_email = $row['email'];
                                    $sql_user = "SELECT picture FROM users WHERE email = '$user_email'";
                                    $result_user = $conn->query($sql_user);
                                    $user_picture = ''; // Initialize user picture variable
                                    if ($result_user->num_rows > 0) {
                                        $user_row = $result_user->fetch_assoc();
                                        $user_picture_filename = $user_row['picture'];
                                        $user_picture_path = "../../uploads/" . $user_picture_filename;
                                        // Check if the picture file exists
                                        if (file_exists($user_picture_path)) {
                                            $user_picture = $user_picture_path;
                                        }
                                    }

                                    $has_requests = false;
                                    // Add language filter
                                    if ($logged_in_user_lang_p && $row['req_user_lang_p']) {
                                        $logged_in_user_langs = explode(',', $logged_in_user_lang_p);
                                        $req_user_langs = explode(',', $row['req_user_lang_p']);

                                        $has_common_lang = false;
                                        foreach ($logged_in_user_langs as $lang) {
                                            $lang = trim($lang); // Remove any leading/trailing whitespace
                                            if (in_array($lang, $req_user_langs)) {
                                                $has_common_lang = true;
                                                break;
                                            }
                                        }

                                        if (!$has_common_lang) {
                                            continue; // Skip this request if no common languages
                                            $has_requests = true;
                                        }
                                    }

                                    // Generate HTML for each request dynamically
                                    echo '<a href="#" class="list-group-item list-group-item-action d-flex gap-2 p-2 mb-2 " aria-current="true">';
                                    echo '<img src="' . $user_picture . '" alt="תמונת פרופיל" width="44" height="44" class="rounded-circle flex-shrink-0">';
                                    echo '<div class="d-flex w-100 justify-content-between">';
                                    echo '<div class="justify-content-between">';
                                    echo '<h6>' . $row["first_name"] . ' ' . $row["last_name"] . '</h6>';
                                    echo '<hr style="width:20%; margin-top: 5px; margin-bottom: 10px;">';
                                    echo '<small class="opacity-75 text-nowrap text-decoration-underline">פרטי המפגש</small>';
                                    echo '<div class="d-flex gap-3">';
                                    echo '<p><b>אופי הבקשה: </b><br>' . $row["req_type"] . '</p>';
                                    echo '<div class="vr"></div>';
                                    echo '<p><b>עיר: </b><br>' . $row["city"] . '</p>';
                                    echo '<div class="vr"></div>';
                                    echo '<p><b>כתובת: </b><br>' . $row["street"] . ' ' . $row["street_num"] . '</p>';
                                    echo '</div>';
                                    echo '<p><b>הערות נוספות: </b>' . $row["content"] . '</p>';
                                    echo '<div class="expanded-content">';
                                    echo '<small class="opacity-75 text-nowrap text-decoration-underline">חלונות זמנים אפשריים</small>';
                                    echo '<select class="form-select mt-2" aria-label="חלונות זמן אפשריים" style="font-size: 0.83rem;" id="time-slots-' . $row["req_id"] . '" onclick="handleTimeSlotClick(event)">';
                                    echo '<option selected disabled>בחר חלון זמן...</option>';
                                    echo '<option>' . str_replace('<br>', '</option><option>', $row["time_slots"]) . '</option>';
                                    echo '</select>';
                                    echo '<button class="btn btn-success ms-2 mt-4" onclick="showConfirmationPopup(' . $row["req_id"] . ')">אישור הבקשה</button>';
                                    echo '</div>';
                                    echo '</div>';
                                    echo '<small class="opacity-75 text-nowrap req-id-hide">בקשה #' . $row["req_id"] . '</small>';
                                    echo '</div>';
                                    echo '</a>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="my-3 p-3  responsive-div">


                    <h6 class="border-bottom pb-2 mb-0 gap-3">

                        הישנות ביותר
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </h6>
                    <div class="list-group-container ">
                        <div class="list-group pt-3 gap-2 w-100">
                            <?php
                            $request_count = 0;
                            $user_email = $_SESSION['email']; 
                            $sql_preferences = "SELECT city_p, req_type_p, day_p, language FROM users WHERE email = '$user_email'";
                            $result_preferences = $conn->query($sql_preferences);
                            $user_preferences = $result_preferences->fetch_assoc();

                            $city_p = isset($user_preferences['city_p']) ? $user_preferences['city_p'] : null;
                            $req_type_p = isset($user_preferences['req_type_p']) ? $user_preferences['req_type_p'] : null;
                            $day_p = isset($user_preferences['day_p']) ? $user_preferences['day_p'] : null;
                            $logged_in_user_lang_p = isset($user_preferences['language']) ? $user_preferences['language'] : null;

                            // Convert day_p to a format that can be used in SQL
                            $day_of_week_mapping = [
                                'Sunday' => 1, 
                                'Monday' => 2,
                                'Tuesday' => 3,
                                'Wednesday' => 4,
                                'Thursday' => 5,
                                'Friday' => 6,
                                'Saturday' => 7
                            ];

                            $days_filter = null;
                            if ($day_p) {
                                $days_p_array = explode(', ', $day_p);
                                $days_filter = array_map(function ($day) use ($day_of_week_mapping) {
                                    return $day_of_week_mapping[$day];
                                }, $days_p_array);
                                $days_filter = implode(',', $days_filter);
                            }

                            // Build the base SQL query
                            $sql = "SELECT r.req_id, r.first_name, r.last_name, r.email, r.phone, r.city, r.street, r.street_num, r.req_type, r.content,
               GROUP_CONCAT(CONCAT(rt.date, ' &bull; ', DATE_FORMAT(rt.start_time, '%H:%i'), ' - ', DATE_FORMAT(rt.end_time, '%H:%i')) SEPARATOR '<br>') AS time_slots,
               u.language AS req_user_lang_p
        FROM requests r
        LEFT JOIN requests_times rt ON r.req_id = rt.req_id
        INNER JOIN users u ON r.email = u.email
        WHERE r.email_volunteer IS NULL
          AND (rt.date > CURDATE() OR (rt.date = CURDATE() AND rt.start_time >= CURTIME()))
          ";

                            // Add city filter if provided
                            if ($city_p) {
                                $cities = explode(', ', $city_p);
                                $city_conditions = array_map(function ($city) {
                                    return "r.city = '$city'";
                                }, $cities);
                                $sql .= " AND (" . implode(' OR ', $city_conditions) . ")";
                            }

                            // Add request type filter if provided
                            if ($req_type_p) {
                                $req_types = explode(', ', $req_type_p);
                                $other_included = in_array('other', $req_types);
                                $req_type_conditions = array_map(function ($req_type) {
                                    return "r.req_type = '$req_type'";
                                }, $req_types);

                                if ($other_included) {
                                    $specified_types = ["'עזרה טכנולוגית'", "'ליווי לרופא'", "'סיוע בקניות'", "'רכישת תרופות'", "'פנאי יחדיו'"];
                                    $req_type_conditions[] = "(r.req_type NOT IN (" . implode(', ', $specified_types) . "))";
                                }

                                $sql .= " AND (" . implode(' OR ', $req_type_conditions) . ")";
                            }

                            // Add day filter if provided
                            if ($days_filter) {
                                $sql .= " AND DAYOFWEEK(rt.date) IN ($days_filter)";
                            }

                            // Add language filter
                            $sql .= " GROUP BY r.req_id";
                            $sql .= " ORDER BY r.req_id ASC limit 3"; // Order by req_id in descending order
                            $result = $conn->query($sql);

                            // Check if there are any requests
                            if ($result->num_rows > 0) {
                                // Output data of each row
                                while ($row = $result->fetch_assoc()) {
                                    // Fetch user's picture filename based on email
                                    $user_email = $row['email'];
                                    $sql_user = "SELECT picture FROM users WHERE email = '$user_email'";
                                    $result_user = $conn->query($sql_user);
                                    $user_picture = ''; // Initialize user picture variable
                                    if ($result_user->num_rows > 0) {
                                        $user_row = $result_user->fetch_assoc();
                                        $user_picture_filename = $user_row['picture'];
                                        $user_picture_path = "../../uploads/" . $user_picture_filename;
                                        // Check if the picture file exists
                                        if (file_exists($user_picture_path)) {
                                            $user_picture = $user_picture_path;
                                        }
                                    }

                                    $has_requests = false;
                                    // Add language filter
                                    if ($logged_in_user_lang_p && $row['req_user_lang_p']) {
                                        $logged_in_user_langs = explode(',', $logged_in_user_lang_p);
                                        $req_user_langs = explode(',', $row['req_user_lang_p']);

                                        $has_common_lang = false;
                                        foreach ($logged_in_user_langs as $lang) {
                                            $lang = trim($lang); // Remove any leading/trailing whitespace
                                            if (in_array($lang, $req_user_langs)) {
                                                $has_common_lang = true;
                                                break;
                                            }
                                        }

                                        if (!$has_common_lang) {
                                            continue; // Skip this request if no common languages
                                            $has_requests = true;
                                        }
                                    }

                                    // Generate HTML for each request dynamically
                                    echo '<a href="#" class="list-group-item list-group-item-action d-flex gap-2 p-2 mb-2 " aria-current="true">';
                                    echo '<img src="' . $user_picture . '" alt="תמונת פרופיל" width="44" height="44" class="rounded-circle flex-shrink-0">';
                                    echo '<div class="d-flex w-100 justify-content-between">';
                                    echo '<div class="justify-content-between">';
                                    echo '<h6>' . $row["first_name"] . ' ' . $row["last_name"] . '</h6>';
                                    echo '<hr style="width:20%; margin-top: 5px; margin-bottom: 10px;">';
                                    echo '<small class="opacity-75 text-nowrap text-decoration-underline">פרטי המפגש</small>';
                                    echo '<div class="d-flex gap-3">';
                                    echo '<p><b>אופי הבקשה: </b><br>' . $row["req_type"] . '</p>';
                                    echo '<div class="vr"></div>';
                                    echo '<p><b>עיר: </b><br>' . $row["city"] . '</p>';
                                    echo '<div class="vr"></div>';
                                    echo '<p><b>כתובת: </b><br>' . $row["street"] . ' ' . $row["street_num"] . '</p>';
                                    echo '</div>';
                                    echo '<p><b>הערות נוספות: </b>' . $row["content"] . '</p>';
                                    echo '<div class="expanded-content">';
                                    echo '<small class="opacity-75 text-nowrap text-decoration-underline">חלונות זמנים אפשריים</small>';
                                    echo '<select class="form-select mt-2" aria-label="חלונות זמן אפשריים" style="font-size: 0.83rem;" id="time-slots-' . $row["req_id"] . '" onclick="handleTimeSlotClick(event)">';
                                    echo '<option selected disabled>בחר חלון זמן...</option>';
                                    echo '<option>' . str_replace('<br>', '</option><option>', $row["time_slots"]) . '</option>';
                                    echo '</select>';
                                    echo '<button class="btn btn-success ms-2 mt-4" onclick="showConfirmationPopup(' . $row["req_id"] . ')">אישור הבקשה</button>';
                                    echo '</div>';
                                    echo '</div>';
                                    echo '<small class="opacity-75 text-nowrap req-id-hide">בקשה #' . $row["req_id"] . '</small>';
                                    echo '</div>';
                                    echo '</a>';
                                }
                            }
                            $conn->close();
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-nowrap" style="color: #2b2b2b; font-weight: 600; text-align: center; font-size: 18px;">
                <a href="waiting_requests.php">לכל הבקשות הממתינות</a>
            </p>
            <br>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
                integrity="sha384-u1FJ0rLQRlhUp5+P1IeSLbbZkU5vFrX5TsLwIhfIrh2N/Tzh7qU8Jg1syp5o8V/9"
                crossorigin="anonymous"></script>
            <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>
            <script src="../../js/bootstrap.bundle.min.js"></script>
            <script src="../../js/bootstrap.min.js"></script>
            <script src="../../js/homepage_volunteer.js"></script>
            <script src="https://cdn.enable.co.il/licenses/enable-L2324gcljoe8sb-0617/init.js"></script>
</body>

</html>