<?php
session_start();

// Check if session variables are set and session token matches the database
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

// Verify session token matches the email and fetch user details
$sql = "SELECT * FROM users WHERE email = '$email' AND session_token = '$session_token'";
$result = $conn->query($sql);

if ($result->num_rows != 1) {
    header("Location: ../../index.html");
    exit();
}

$user = $result->fetch_assoc();
$first_name = $user['first_name'];
$last_name = $user['last_name'];
$gender = $user['gender'];
$profile_picture = '../../uploads/' . $user['picture'];

$current_datetime = date("Y-m-d H:i:s");

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

// Fetch the active requests for the logged-in user where email_volunteer, date, start_time, and end_time are not NULL and date and end_time have not passed
$active_sql = "SELECT * FROM requests 
               WHERE email = '$email' 
               AND email_volunteer IS NOT NULL 
               AND date IS NOT NULL 
               AND start_time IS NOT NULL 
               AND end_time IS NOT NULL 
               AND CONCAT(date, ' ', end_time) > NOW() 
                  ORDER BY confirmed_at DESC";
$active_result = $conn->query($active_sql);

$requests = [];
while ($row = $active_result->fetch_assoc()) {
    // Fetch volunteer details
    $volunteer_email = $row['email_volunteer'];
    $volunteer_sql = "SELECT * FROM users WHERE email = '$volunteer_email'";
    $volunteer_result = $conn->query($volunteer_sql);
    $volunteer = $volunteer_result->fetch_assoc();
    
    $row['volunteer'] = $volunteer;
    $requests[] = $row;
}

$waiting_sql = "
    SELECT r.*, 
           MAX(rt.date) as max_date, 
           MAX(CONCAT(rt.date, ' ', rt.start_time)) as max_start_datetime
    FROM requests r
    JOIN requests_times rt ON r.req_id = rt.req_id
    WHERE r.email = '$email' 
    AND r.email_volunteer IS NULL 
    AND r.date IS NULL 
    AND r.start_time IS NULL 
    AND r.end_time IS NULL
    GROUP BY r.req_id
    HAVING max_start_datetime > NOW()
    ORDER BY confirmed_at DESC";

$requests_result = $conn->query($waiting_sql);
$waiting_requests = $requests_result->num_rows;

// Fetch the finished requests for the logged-in user where email_volunteer, date, start_time, and end_time are not NULL and date and end_time have passed
$finished_sql = "SELECT * FROM requests 
                 WHERE email = '$email' 
                 AND email_volunteer IS NOT NULL 
                 AND date IS NOT NULL 
                 AND start_time IS NOT NULL 
                 AND end_time IS NOT NULL 
                 AND CONCAT(date, ' ', end_time) < NOW()
                 ";
$finished_result = $conn->query($finished_sql);


                
// Function to count requests based on criteria
function countRequests($conn, $email, $finished) {
    $sql = "
        SELECT COUNT(*) AS request_count
        FROM requests
        WHERE email_volunteer = ? ";
    
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

$placed_requests_count = countRequests($conn, $email, false);

// Count finished requests
$finished_requests_count = countRequests($conn, $email, true);


$conn->close();
?>

<!doctype html>
<html lang="he" data-bs-theme="auto">

<head>
  <title>
    ElderLink - דף הבית
  </title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <link rel="icon" href="../../images/favicon.png" type="image/x-icon">
  <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/list-groups/">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <link rel="stylesheet" href="../../css/homepage.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@100..900&display=swap" rel="stylesheet">
  <title>Sidebars · Bootstrap v5.3</title>
  <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/sidebars/">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">

  <link href="../../css/bootstrap.min.css" rel="stylesheet">
  <link href="../../css/homepage_elderly.css" rel="stylesheet">

  <!-- Custom styles for this template -->
  <link href="sidebars.css" rel="stylesheet">
</head>

<body>

  <main class="d-flex flex-nowrap">
    <header>

      <div class="mobile-tab-bar">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.0/font/bootstrap-icons.css" rel="stylesheet">
        <nav class="nav">
          <a href="homepage_elderly.php" class="nav__link nav__link--active">
            <i class="bi bi-house-fill nav__icon"></i>
            <span class="nav__text">דף הבית</span>
          </a>
          <a href="new_request.php" class="nav__link">
            <i class="bi-plus-circle nav__icon"></i>
            <span class="nav__text">פתיחת בקשה</span>
          </a>
          <a href="placed_requests.php" class="nav__link">
            <i class="bi bi-basket3 nav__icon"></i>
            <span class="nav__text">סל בקשות</span>
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
          <a href="homepage_elderly.php">
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
          </div>
        </div>
      </div>

      <div class="main_nav">
        <div class="d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary" style="width: 280px;">
          <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.0/font/bootstrap-icons.css" rel="stylesheet">

          <a href="homepage_elderly.php"
            class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
            <img src="../../images/elderlink-high-resolution-logo-transparent.png" alt="logo" id="logo_page"
              class="logo_page">
          </a>
          <hr>
          <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
              <a href="homepage_elderly.php" class="nav-link active" aria-current="page">
                <i class="bi bi-house-fill me-2" width="20" height="20"></i>
                דף הבית
              </a>
            </li>
            <li>
              <a href="new_request.php" class="nav-link link-body-emphasis">
                <i class="bi bi-plus-circle me-2 sidebar__icon"></i>
                פתיחת בקשה
              </a>
            </li>
            <li>
              <a href="placed_requests.php" class="nav-link link-body-emphasis">
                <i class="bi bi-basket3 me-2" width="20" height="20"></i>
                סל בקשות
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
              <a href="settings_security.php" class="nav-link link-body-emphasis">
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

      <section class="hero p-0 rounded shadow-sm">
        <div class="container">
          <h1>היי
            <?php echo htmlspecialchars($first_name);?>, במה
            <?php echo htmlspecialchars($assist_text); ?> להסתייע היום?
          </h1>
          <div class="icon-grid">
            <a id="tech-help">
              <img src="../../images/client.png" alt="Icon 1">
              <span class="icon-title">עזרה טכנולוגית</span>
            </a>
            <a id="shopping-help">
              <img src="../../images/grocery.png" alt="Icon 2">
              <span class="icon-title">סיוע בקניות</span>
            </a>
            <a id="leisure-together">
              <img src="../../images/park.png" alt="Icon 3">
              <span class="icon-title">פנאי יחדיו</span>
            </a>
            <a id="medicine-purchase">
              <img src="../../images/prescription.png" alt="Icon 4">
              <span class="icon-title">רכישת תרופות</span>
            </a>
            <a id="doctor-escort">
              <img src="../../images/doctor.png" alt="Icon 5">
              <span class="icon-title">ליווי לרופא</span>
            </a>
          </div>

        </div>
        <br>
        <a href="new_request.php">
          <button class="button-29" role="button" style="width: 150px; margin-bottom: 20px; ">פתיחת בקשה</button>
        </a>
      </section>
      <section class="section-wave">

        <h3 class="hover" style="font-size:40px;">
          איך זה עובד?
        </h3>
        <div class="row">

          <div class="column">
            <a href="new_request.php" style="text-decoration: none; color: inherit;">
              <div class="card">
                <div class="icon-wrapper">
                  <i class="fa fa-plus" aria-hidden="true"></i>
                </div>
                <h3>פותחים בקשה</h3>

                <h7>
                  לחצו על פתיחת בקשה בתפריט, מלאו את כל פרטי המפגש והוסיפו חלונות זמן אפשריים (כמה שיותר יותר טוב!),
                  ולחצו על שלח בקשה. </h7>
              </div>
            </a>
          </div>

          <div class="column">
            <a href="waiting_requests.php" style="text-decoration: none; color: inherit;">
              <div class="card">
                <div class="icon-wrapper">
                  <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <h3>ממתינים לשיבוץ</h3>
                <h7>
                  שלב ההמתנה, בקשתכם כעת תחת בקשות ממתינות לשיבוץ בתוך סל הבקשות, ברגע שאחד המתנדבים ישובץ לבקשתכם תישלח
                  התראה.
                </h7>
              </div>
            </a>
          </div>
          <div class="column">
            <a href="placed_requests.php" style="text-decoration: none; color: inherit;">
              <div class="card">
                <div class="icon-wrapper">
                  <i class="fa-solid fa-handshake-angle"></i>
                </div>
                <h3>נפגשים עם המתנדב/ת</h3>
                <h7>
                  איזה כיף! בקשתכם שובצה למתנדב/ת. בכל עת, תוכלו לצפות בבקשה תחת בקשות פעילות בתוך סל הבקשות.
                </h7>
              </div>
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
      <div style="display: flex; align-items: center; margin-top: 50px;">
        <div style="flex: 1; border-top: 1px solid #dddddd;"></div>
        <h4 style="font-weight: 600; margin: 0 10px; color: #2b2b2b;">סל הבקשות [<?php echo count($requests)+$waiting_requests; ?>]
        </h4>
        <div style="flex: 1; border-top: 1px solid #dddddd;"></div>
      </div>

      <div class="outer-container">
        <div class="my-3 p-3  responsive-div">


          <h6 class="border-bottom pb-2 mb-0 gap-3">
            <span class="active-request-indicator"></span>

            בקשות פעילות [<?php echo count($requests); ?>]

          </h6>
          <div class="list-group-container w-100">
            <div class="list-group pt-3 gap-2 w-100">
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

                // Fetch requests for the elderly user that have not passed
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
                WHERE r.email = ? 
                AND (r.date > CURDATE() OR (r.date = CURDATE() AND r.end_time > CURTIME()))
                ORDER BY confirmed_at DESC
                limit 3";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $user_email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows == 0) {
                    echo '<h6 class="mb-5 mt-2">אין בקשות פעילות להצגה.</h6>';
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

                        echo '<a href="#" class="list-group-item list-group-item-action d-flex gap-2 p-2 mb-2 " aria-current="true">';
                        echo '<img src="' . $volunteer_profile_image_url . '" alt="תמונת פרופיל" width="44" height="44" class="rounded-circle flex-shrink-0">';
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
                        echo '<small class="opacity-75 text-nowrap text-decoration-underline">פרטי המתנדב</small>';
                        echo '<div class="d-flex gap-2">';

                
                        echo '  <p><b>מגדר: </b>' . htmlspecialchars($row['volunteer_gender']) . '</p>';
                        echo '<div class="vr"></div>';
                        echo ' <p><b>שפות: </b>' . htmlspecialchars($row['volunteer_language']) . '</p>';
                         echo '<div class="vr"></div>';
                                                echo ' <p>
                            <b>מספר הטלפון: </b>
                            <span style="cursor: pointer; color: blue; text-decoration: underline;" onclick="window.open(\'https://wa.me/' . htmlspecialchars($row['phone']) . '\', \'_blank\');">' . htmlspecialchars($row['phone']) . '</span>
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
              <p class="text-nowrap" style="color: #2b2b2b; font-weight: 600; text-align: center; font-size: 18px;">
                <a href="placed_requests.php">לכל הבקשות הפעילות</a>
              </p>

            </div>
          </div>

        </div>
        <div class="my-3 p-3  responsive-div">
          <h6 class="border-bottom pb-2 mb-0 gap-3">
            <span class="inactive-request-indicator"></span>
            בקשות ממתינות לשיבוץ [<?php echo $waiting_requests; ?>]
          </h6>
          <div class="list-group pt-3 gap-2">

            <?php

                // Fetch requests for the elderly user that have passed
                $sql = "
                    SELECT r.*, 
                           MAX(rt.date) as max_date, 
                           MAX(CONCAT(rt.date, ' ', rt.start_time)) as max_start_datetime
                    FROM requests r
                    JOIN requests_times rt ON r.req_id = rt.req_id
                    WHERE r.email = ? 
                    AND r.email_volunteer IS NULL 
                    AND r.date IS NULL 
                    AND r.start_time IS NULL 
                    AND r.end_time IS NULL
                    GROUP BY r.req_id
                    HAVING max_start_datetime > NOW()
                    ORDER BY req_id DESC
                    LIMIT 3";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows == 0) {
                    echo '<h6 class="mb-5 mt-2">אין בקשות ממתינות לשיבוץ להצגה.</h6>';
                } else {
                    while ($row = $result->fetch_assoc()) {
                        // Fetch possible time windows for the current request
                        $req_id = $row['req_id'];
                        $time_sql = "SELECT * FROM requests_times WHERE req_id = ?";
                        $time_stmt = $conn->prepare($time_sql);
                        $time_stmt->bind_param("i", $req_id);
                        $time_stmt->execute();
                        $time_result = $time_stmt->get_result();
                        
                        $times = [];
                        while ($time_row = $time_result->fetch_assoc()) {
                            $times[] = $time_row;
                        }
                
                        // Format date, start time, end time
                        $date = date("d/m/Y", strtotime($row['date']));
                        $start_time = date("H:i", strtotime($row['start_time']));
                        $end_time = date("H:i", strtotime($row['end_time']));
                
                        echo '<a href="#" class="list-group-item list-group-item-action d-flex gap-2 p-2 mb-2" aria-current="true">';
                        echo '<img src="../../images/anonymous.png" alt="תמונת פרופיל" width="44" height="44" class="rounded-circle flex-shrink-0">';
                        echo '<div class="d-flex w-100 justify-content-between">';
                        echo '<div class="justify-content-between">';
                        echo '<h6>טרם שובץ</h6>';
                        echo '<hr style="width:20%; margin-top: 5px; margin-bottom: 10px;">';
                        echo '<small class="opacity-75 text-nowrap text-decoration-underline">פרטי המפגש</small>';
                        echo '<div class="d-flex gap-3">';
                        echo '<p><b>אופי הבקשה: </b><br>' . htmlspecialchars($row['req_type']) . '</p>';
                        echo '<div class="vr"></div>';
                        echo '<p><b>עיר: </b><br>' . htmlspecialchars($row['city']) . '</p>';
                        echo '<div class="vr"></div>';
                        echo '<p><b>כתובת: </b><br>' . htmlspecialchars($row['street']) . ' ' . htmlspecialchars($row['street_num']) . '</p>';
                        echo '</div>';
                        echo '<div class="d-flex gap-0">';
                        echo '<p><b>הערות נוספות: </b><br>' . htmlspecialchars($row['content']) . '</p>';
                        echo '</div>';
                        echo '<div class="expanded-content">';
                
                        echo '<small class="opacity-75 text-nowrap text-decoration-underline">חלונות זמנים אפשריים</small>';
                        echo '<div class="d-flex gap-3">';
                      foreach (array_slice($times, 0, 1) as $index => $time) {
                            echo '<div class="gap-2" style="display: flex; flex-direction: row;">';
                            echo '<p>' . ($index + 1) . '.</p>';
                            echo '<p><b>תאריך: </b>' . date("d/m/Y", strtotime($time['date'])) . '</p><div class="vr"></div>';
                            echo '<p><b>שעת התחלה: </b>' . date("H:i", strtotime($time['start_time'])) . '</p><div class="vr"></div>';
                            echo '<p><b>שעת סיום: </b>' . date("H:i", strtotime($time['end_time'])) . '</p>';
                           
                            echo '</div>';
                
                                  echo '</div>';
                        }
                  
                                 echo '<p>ליתר הזמנים יש לעבור לדף בקשות ממתינות לשיבוץ.</p>';
                        echo '</div>';
                        echo '</div>';
                        echo '<small class="opacity-75 text-nowrap req-id-hide">בקשה #' . htmlspecialchars($row['req_id']) . '</small>';
                        echo '</div>';
                        echo '</a>';
                    }
                }
            ?>
            <p class="text-nowrap" style="color: #2b2b2b; font-weight: 600; text-align: center; font-size: 18px;">
              <a href="waiting_requests.php">לכל הבקשות שממתינות לשיבוץ</a>
            </p>
          </div>
        </div>
      </div>

      <script src="../../js/bootstrap.bundle.min.js"></script>
      <script src="https://cdn.enable.co.il/licenses/enable-L2324gcljoe8sb-0617/init.js"></script>
      <script src="../../js/homepage_elderly.js"></script>

</body>

</html>