<?php
require '../check_session.php';
session_start();
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
$sql_select = "SELECT picture FROM users WHERE session_token = ?";
$statement_select = $conn->prepare($sql_select);
$statement_select->bind_param("s", $session_token);
$statement_select->execute();
$result = $statement_select->get_result();
$row = $result->fetch_assoc();
$userPicture = $row['picture'];

// Function to count requests based on criteria
function countRequests($conn, $email, $finished) {
    $sql = "
        SELECT COUNT(*) AS request_count
        FROM requests
        WHERE email_volunteer = ? ";
    
    // Add condition based on $finished parameter
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

// Count placed requests
$placed_requests_count = countRequests($conn, $email, false);

// Count finished requests
$finished_requests_count = countRequests($conn, $email, true);

// Close the connection
$conn->close();

if ($userPicture !== NULL) {
    $profile_image_url = "../../uploads/" . $userPicture;
} else {
    $profile_image_url = "https://static.vecteezy.com/system/resources/thumbnails/009/292/244/small/default-avatar-icon-of-social-media-user-vector.jpg";
}
?>

<!doctype html>
<html lang="he" data-bs-theme="auto">

<head>
    <title>
        ElderLink - בקשות פעילות
      </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <link rel="icon" href="../../images/favicon.png" type="image/x-icon">
    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/list-groups/">
    <link rel="stylesheet" href="../../css/settings-profile.css">
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
                    <a href="homepage_volunteer.php" class="nav__link">
                        <i class="bi bi-house nav__icon"></i>
                        <span class="nav__text">דף הבית</span>
                    </a>
                    <a href="waiting_requests.php" class="nav__link">
                        <i class="bi-hourglass nav__icon"></i>
                        <span class="nav__text">בקשות ממתינות</span>
                    </a>
                    <a href="placed_requests.php" class="nav__link nav__link--active">
                        <i class="bi bi-calendar-check-fill nav__icon"></i>
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
                        <img src="../../images/elderlink-high-resolution-logo-transparent.png" alt="logo" id="logo_top-bar"
                            class="logo_top-bar">
                    </a>
                    <div class="top-bar-icons">
                        <a href="../logout.php" class="d-flex align-items-center link-body-emphasis text-decoration-none "
                            aria-expanded="false">
                            <i class="bi p-1 bi-box-arrow-in-right me-2 top-bar-icon">
                            </i>
                        </a>
                        <a href="settings_security.php" class="d-flex align-items-center link-body-emphasis text-decoration-none "
                            aria-expanded="false">
                            <i class="bi p-1 bi-gear top-bar-icon">
                            </i>
                        </a>
                        <a href="notifications.php" class="d-flex align-items-center link-body-emphasis text-decoration-none "
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
                            <a href="homepage_volunteer.php" class="nav-link link-body-emphasis" aria-current="page">
                                <i class="bi bi-house me-2" width="20" height="20"></i>
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
                            <a href="placed_requests.php" class="nav-link active">
                                <i class="bi bi-calendar-check-fill me-2" width="20" height="20"></i>
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
                        <a href="contact_us.php" class="nav-link link-body-emphasis">
                            <i class="bi bi-chat-right-text me-2" width="20" height="20"></i>
                            צור קשר
                        </a>
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
            <div class="container-xl px-4 mt-2">
                <nav class="nav nav-borders">
                    <a class="nav-link active" href="placed_requests.php">
                        <span class="active-request-indicator"></span>
                        בקשות פעילות (<?php echo $placed_requests_count; ?>)
                    </a>
                    <a class="nav-link" href="finished_requests.php">
                        <span class="inactive-request-indicator"></span>
                        בקשות שהסתיימו (<?php echo $finished_requests_count; ?>)
                    </a>

                </nav>
                <hr class="mt-0 mb-1">
                <div class="row">
                    <div class="col-xl-9">
                        <div class="">
                            <div>
                                <div class="list-group pt-3 gap-2">
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
                                    function getUserEmail($session_token, $conn) {
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
                                    ";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("s", $user_email);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                        if ($result->num_rows == 0) {
                                       echo '<div class="list-group pt-3 gap-2">
                                    <div style="            display: flex;
            justify-content: center;
            align-items: center;">
                                        <img src="../../images/cart.png" alt="Cart" width="350" height="350">
                                    </div>
                                    <h4 style="text-align:center; font-weight: bold; ">מה התרומה הבאה שלכם?</h4>
                                    <p style="text-align:center;">עדיין אין לכם בקשות פעילות. ברגע שאשרו בקשה, היא תועבר לכאן.</p>
                                </div>';
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
                                    
                                        echo '
                                        <a href="#" class="list-group-item list-group-item-action d-flex W p-2" aria-current="true">
                                            <img src="' . $elderly_profile_image_url . '" alt="תמונת פרופיל" width="44" height="44" class="rounded-circle flex-shrink-0" style="margin-left: 10px;">
                                            <div class="d-flex w-100 justify-content-between">
                                                <div class="justify-content-between">
                                                    <h6>' . htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']) . '
                                                        <small class="opacity-75 text-nowrap"> ● בקשה מס\': ' . htmlspecialchars($row['req_id']) . '</small>
                                                    </h6>
                                                    <hr style="width:20%; margin-top: 5px; margin-bottom: 10px;">
                                                    <small class="opacity-75 text-nowrap text-decoration-underline">פרטי המפגש</small>
                                                    <div style="display: flex; flex-direction: row;">
                                                        <div class="d-flex gap-2">
                                                            <p><b>תאריך: </b>' . htmlspecialchars($date) . '</p>
                                                            <p>&bull;</p>
                                                            <p><b>שעת התחלה: </b>' . htmlspecialchars($start_time) . '</p>
                                                            <p>&bull;</p>
                                                            <p><b>שעת סיום: </b>' . htmlspecialchars($end_time) . '</p>
                                                        </div>
                                                    </div>
                                                    <div style="display: flex; flex-direction: row;">
                                                        <div class="d-flex gap-2">
                                                            <p><b>סוג הבקשה: </b>' . htmlspecialchars($row['req_type']) . '</p>
                                                            <p>&bull;</p>
                                                            <p><b>עיר: </b>' . htmlspecialchars($row['city']) . '</p>
                                                            <p>&bull;</p>
                                                            <p><b>כתובת: </b>' . htmlspecialchars($row['street']) . ' ' . htmlspecialchars($row['street_num']) . '</p>
                                                        </div>
                                                    </div>
                                                    <p><b>הערות נוספות: </b>' . htmlspecialchars($row['content']) . '</p>
                                                    <small class="opacity-75 text-nowrap text-decoration-underline">פרטי המבוגר</small>
                                                    <div style="display: flex; flex-direction: row;">
                                                        <div class="d-flex gap-2">
                                                            <p>
                                                                <b>מספר הטלפון: </b>
                                                                <span style="cursor: pointer; color: blue; text-decoration: underline;" onclick="window.open(\'https://wa.me/' . htmlspecialchars($row['elderly_phone']) . '\', \'_blank\');">' . htmlspecialchars($row['elderly_phone']) . '</span>
                                                            </p>
                                                            <p>&bull;</p>
                                                            <p><b>תאריך לידה: </b>' . htmlspecialchars($elderly_date_of_birth) . '</p>
                                                        </div>
                                                    </div>
                                                    <div style="display: flex; flex-direction: row;">
                                                        <div class="d-flex gap-2">
                                                            <p><b>מגדר: </b>' . htmlspecialchars($row['elderly_gender']) . '</p>
                                                            <p>&bull;</p>
                                                            <p><b>שפות: </b>' . htmlspecialchars($row['elderly_language']) . '</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 text-center" style="width: 200;">
                                                    <button class="btn btn-sm btn-danger" onclick="confirmCancel(' . htmlspecialchars($row['req_id']) . ')">ביטול</button>
                                                </div>
                                            </div>
                                        </a>
                                        ';
                                    }
                                  }
                                    $stmt->close();
                                    
                                    function getRequestCounts($conn, $user_email) {
        $counts = ['week' => ['confirmed' => 0, 'canceled' => 0], 'month' => ['confirmed' => 0, 'canceled' => 0], 'year' => ['confirmed' => 0, 'canceled' => 0]];

        $periods = [
            'week' => "YEARWEEK(NOW(), 1) = YEARWEEK(confirmed_at, 1)",
            'month' => "YEAR(NOW()) = YEAR(confirmed_at) AND MONTH(NOW()) = MONTH(confirmed_at)",
            'year' => "YEAR(NOW()) = YEAR(confirmed_at)"
        ];

        foreach ($periods as $period => $condition) {
            $sql_confirmed = "SELECT COUNT(*) AS count FROM requests WHERE email_volunteer = ? AND $condition AND confirmed_at IS NOT NULL";
            $sql_canceled = "SELECT COUNT(*) AS count FROM requests WHERE email_volunteer = ? AND $condition AND cancelled_at IS NOT NULL";
            
            $stmt_confirmed = $conn->prepare($sql_confirmed);
            $stmt_canceled = $conn->prepare($sql_canceled);
            
            $stmt_confirmed->bind_param("s", $user_email);
            $stmt_canceled->bind_param("s", $user_email);
            
            $stmt_confirmed->execute();
            $stmt_canceled->execute();
            
            $result_confirmed = $stmt_confirmed->get_result();
            $result_canceled = $stmt_canceled->get_result();
            
            if ($result_confirmed->num_rows > 0) {
                $counts[$period]['confirmed_at'] = $result_confirmed->fetch_assoc()['count'];
            }
            
            $stmt_confirmed->close();
            $stmt_canceled->close();
        }

        return $counts;
    }

    $counts = getRequestCounts($conn, $user_email);
    
    // Get the most recent confirmation date and time
$sql_recent_confirmation = "
SELECT confirmed_at 
FROM requests 
WHERE email_volunteer = ? 
AND confirmed_at IS NOT NULL 
AND cancelled_at IS NULL 
ORDER BY confirmed_at DESC 
LIMIT 1";

$stmt_recent_confirmation = $conn->prepare($sql_recent_confirmation);
$stmt_recent_confirmation->bind_param("s", $user_email);
$stmt_recent_confirmation->execute();
$result_recent_confirmation = $stmt_recent_confirmation->get_result();

$recent_confirmation = null;
if ($result_recent_confirmation->num_rows > 0) {
    $row_recent_confirmation = $result_recent_confirmation->fetch_assoc();
    $recent_confirmation = $row_recent_confirmation['confirmed_at'];
}
$stmt_recent_confirmation->close();

                                    
                                    $conn->close();
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 mt-3">
                        <div class="card mb-4 mb-xl-0">
                            <div class="card-header">מידע כללי</div>
                           <div class="card-body text-center">
                                           <?php if ($recent_confirmation): ?>
                <p>הבקשה האחרונה אושרה בתאריך <br><?php echo date("d/m/Y", strtotime($recent_confirmation)); ?> בשעה <?php echo date("H:i", strtotime($recent_confirmation)); ?>.</p>
            <?php else: ?>
                <p><b>אין בקשות מאושרות.</b></p>
            <?php endif; ?>
             <hr>
            <p><b>בקשות שאושרו השבוע: </b><br><?php echo $counts['week']['confirmed_at']; ?></p>
            <p><b>בקשות שאושרו החודש: </b><br><?php echo $counts['month']['confirmed_at']; ?></p>
            <p><b>בקשות שאושרו השנה: </b><br><?php echo $counts['year']['confirmed_at']; ?></p>
           
        </div>
                        </div>
                    </div>
                </div>

                <script src="../../js/bootstrap.bundle.min.js"></script>
                <script src="../../js/settings-profile.js"></script>
                <script src="https://cdn.enable.co.il/licenses/enable-L2324gcljoe8sb-0617/init.js"></script>


                <script>

                        function confirmCancel(reqId) {
                        if (confirm("האם את/ה בטוח/ה שברצונך לבטל את הבקשה?")) {
                            var xhr = new XMLHttpRequest();
                            xhr.open("POST", "cancel_request.php", true);
                            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                            xhr.onreadystatechange = function () {
                                if (xhr.readyState === 4 && xhr.status === 200) {
                                    // Handle success - you can refresh the page or remove the item from the DOM
                                    alert("הבקשה בוטלה בהצלחה!");
                                    location.reload(); // Simple page reload
                                }
                            };
                            xhr.send("req_id=" + reqId);
                        }
                    }

                </script>
</body>

</html>