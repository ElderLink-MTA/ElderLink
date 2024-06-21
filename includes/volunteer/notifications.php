<?php
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

// Fetch the user's picture using the session_token
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
        $sql .= "AND (date > CURDATE() OR (date = CURDATE() AND end_time > CURTIME())) ";
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

// Now $userPicture contains the picture filename or NULL if not set
// You can use $userPicture to display the user's picture or perform any further actions as needed

// For example, you can use it to display the picture in an <img> tag
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
        ElderLink - התראות
      </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.122.0">
    <link rel="icon" href="../../images/favicon.png" type="image/x-icon">
    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/list-groups/">
        <link rel="stylesheet" href="../../css/settings-profile.css">

        <link rel="stylesheet" href="../../css/notifications.css">

    <link rel="stylesheet" href="../../css/homepage.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@100..900&display=swap" rel="stylesheet">
    <title>Sidebars · Bootstrap v5.3</title>
    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/sidebars/">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">

    <link href="../../css/bootstrap.min.css" rel="stylesheet">


    <!-- Custom styles for this template -->
    <link href="sidebars.css" rel="stylesheet">
</head>

<body>

    <main class="d-flex flex-nowrap">
        <header>

            <div class="mobile-tab-bar">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.0/font/bootstrap-icons.css"
                    rel="stylesheet">
                <nav class="nav">
                    <a href="homepage_volunteer.php" class="nav__link ">
                        <i class="bi bi-house nav__icon"></i>
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
                            <i class="bi p-1 bi-bell-fill top-bar-icon text-primary">
                            </i>
                        </a>
                        <i class="top-bar-icon fa fa-comment"></i>
                    </div>
                </div>
            </div>

            <div class="main_nav ">
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
                            <a href="placed_requests.php" class="nav-link link-body-emphasis">
                                <i class="bi bi-calendar-check me-2" width="20" height="20"></i>
                                בקשות ששובצו
                            </a>
                        </li>
                        <li>
                            <a href="notifications.php" class="nav-link active">
                                <i class="bi bi-bell-fill me-2" style="color: white;" width="20" height="20"></i>
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


            <link rel="stylesheet"
                href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/5.3.45/css/materialdesignicons.css"
                integrity="sha256-NAxhqDvtY0l4xn+YVa6WjAcmd94NNfttjNsDmNatFVc=" crossorigin="anonymous" />

 <div class="container" >
           <div class="container-xl px-4 mt-2 " style="    
    overflow-y: auto; ">
                <div class="list-group app">
                 
                <nav class="nav nav-borders">
                    <a class="nav-link active" href="notifications.php" target=""> התראות</a>
                </nav>
                    
                        
                    </div>
                     <hr class="mt-0 mb-2">
                   

                    <div class="body">
                       <?php
// Start the session
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

       u2.gender AS elderly_gender, 
       u2.date_of_birth AS elderly_date_of_birth, 
       u2.language AS elderly_language,
       u2.phone AS elderly_phone,
       u2.picture AS elderly_picture
FROM requests r
JOIN users u2 ON r.email = u2.email
WHERE r.email_volunteer = ? 
AND (r.date > CURDATE() OR (r.date = CURDATE() AND r.end_time > CURTIME()))
AND r.cancelled_at IS NOT NULL
AND r.email_volunteer_cancelled IS NULL
ORDER BY r.cancelled_at DESC
limit 6";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

        
                         if ($result->num_rows == 0) {
                                                     echo '<div class="list-group pt-3 gap-2">
                                    <div style="            display: flex;
            justify-content: center;
            align-items: center;">
                                        <img src="../../images/notification.png" alt="Cart" width="350" height="350">
                                    </div>
                                    <h4 style="text-align:center; font-weight: bold; ">לא קיימות התראות.</h4>
                              
                            </div>';
} else {
    while ($row = $result->fetch_assoc()) {
        $volunteer_profile_image_url = $row['elderly_picture'] ? '../../uploads/' . htmlspecialchars($row['elderly_picture']) : 'default_volunteer_image_url'; // Replace with actual default image URL if needed
        $elderly_profile_image_url = $row['elderly_picture'] ? '../../uploads/' . htmlspecialchars($row['elderly_picture']) : 'default_elderly_image_url'; // Replace with actual default image URL if needed

        // Format date, start time, end time, and date of birth
        $date = date("d-m-Y", strtotime($row['date']));
        $start_time = date("H:i", strtotime($row['start_time']));
        $end_time = date("H:i", strtotime($row['end_time']));

        $elderly_date_of_birth = date("d-m-Y", strtotime($row['elderly_date_of_birth']));

         echo '
            <div class="list-group">
            <div class="list-group-item list-group-item-action d-flex W p-2 notification readed private-message ">
                  <div class="avatar"><img src="' . $volunteer_profile_image_url . '"></div>
                <div class="text">
                    <div class="text-top">
                        <p style="font-size: 16px;">
                            <i class="bi bi-x-circle" style="color: red;"></i>
                            <span class="profil-name">המבוגר/ת ' . htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']) . '</span> ביטל\ה את המפגש שמספרו - ' . htmlspecialchars($row['req_id']) . '
                        </p>
                    </div>
                    <div class="text-bottom">
                        <div class="inside">
                            <div class="justify-content-between">
                                <small class="opacity-75 text-nowrap text-decoration-underline">פרטי המפגש</small>
                                <div style="display: flex; flex-direction: row;">
                                    <div class="d-flex gap-4">
                                        <div>
                                            <p><b>תאריך: </b><br>' . htmlspecialchars($date) . '</p>
                                        </div>
                                        <div class="d-flex" style="height: 40px;">
                                            <div class="vr"></div>
                                        </div>
                                        <div>
                                            <p><b>שעת התחלה: </b><br>' . htmlspecialchars($start_time) . '</p>
                                        </div>
                                        <div class="d-flex" style="height: 40px;">
                                            <div class="vr"></div>
                                        </div>
                                        <div>
                                            <p><b>שעת סיום: </b><br>' . htmlspecialchars($end_time) . '</p>
                                        </div>
                                        <div class="d-flex" style="height: 40px;">
                                            <div class="vr"></div>
                                        </div>
                                        <div>
                                            <p><b>סוג הבקשה: </b><br>' . htmlspecialchars($row['req_type']) . '</p>
                                        </div>
                                        <div class="d-flex" style="height: 40px;">
                                            <div class="vr"></div>
                                        </div>
                                        <div>
                                            <p><b>עיר: </b><br>' . htmlspecialchars($row['city']) . '</p>
                                        </div>
                                        <div class="d-flex" style="height: 40px;">
                                            <div class="vr"></div>
                                        </div>
                                        <div>
                                            <p><b>כתובת: </b><br>' . htmlspecialchars($row['street']) . ' ' . htmlspecialchars($row['street_num']) . '</p>
                                        </div>
                                    </div>
                                </div>
                                <p><b>הערות נוספות: </b>' . htmlspecialchars($row['content']) . '</p>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>';
    }
}

$stmt->close();
$conn->close();
?>

 
                       
                            </div>
                        </div>
          
                    </div>
                </div>
            </div>
             </div>




        <script src="../../js/bootstrap.bundle.min.js"></script>
        <script src="../../js/settings-perfrences.js"></script>
        <script src="../../js/notifications.js"></script>
        <script src="https://cdn.enable.co.il/licenses/enable-L2324gcljoe8sb-0617/init.js"></script>

        <script>
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');

            // Attach event listeners to each checkbox
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    // Display the save message
                    document.getElementById('saveMessage').style.display = 'block';

                    // Hide the message after 3 seconds
                    setTimeout(function () {
                        document.getElementById('saveMessage').style.display = 'none';
                    }, 3000);
                });
            });

            // Select the select element
            const selectElement = document.getElementById('field2');

            // Attach an event listener to the select element
            selectElement.addEventListener('change', function () {
                // Display the save message
                document.getElementById('saveMessage').style.display = 'block';

                // Hide the message after 3 seconds
                setTimeout(function () {
                    document.getElementById('saveMessage').style.display = 'none';
                }, 3000);
            });


            /* global bootstrap: false */
            (() => {
                'use strict'
                const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.forEach(tooltipTriggerEl => {
                    new bootstrap.Tooltip(tooltipTriggerEl)
                })
            })()


                (() => {
                    'use strict'

                    document.querySelector('#navbarSideCollapse').addEventListener('click', () => {
                        document.querySelector('.offcanvas-collapse').classList.toggle('open')
                    })
                })()



        </script>
</body>

</html>