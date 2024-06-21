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

$current_datetime = date("Y-m-d H:i:s");

// Fetch the active requests for the logged-in user where email_volunteer, date, start_time, and end_time are not NULL and date and end_time have not passed
$active_sql = "SELECT COUNT(*) as active_count FROM requests 
               WHERE email = '$email' 
               AND email_volunteer IS NOT NULL 
               AND date IS NOT NULL 
               AND start_time IS NOT NULL 
               AND end_time IS NOT NULL 
               AND cancelled_at IS NULL 
               AND CONCAT(date, ' ', end_time) > NOW()
                ORDER BY date DESC, end_time DESC;";
$active_result = $conn->query($active_sql);
$active_count = $active_result->fetch_assoc()['active_count'];

$waiting_sql = "
    SELECT r.*, 
           MAX(rt.date) as max_date, 
           MAX(CONCAT(rt.date, ' ', rt.start_time)) as max_start_datetime
    FROM requests r
    JOIN requests_times rt ON r.req_id = rt.req_id
    WHERE r.email = '$email' 
    AND r.email_volunteer IS NULL 
    GROUP BY r.req_id
    HAVING max_start_datetime > NOW()
    ORDER BY date DESC, end_time DESC;";

$requests_result = $conn->query($waiting_sql);
$waiting_requests_count = $requests_result->num_rows;

// Fetch the finished requests for the logged-in user where email_volunteer, date, start_time, and end_time are not NULL and date and end_time have passed
$finished_sql = "SELECT * FROM requests 
                 WHERE email = '$email' 
                 AND email_volunteer IS NOT NULL 
                 AND date IS NOT NULL 
                 AND start_time IS NOT NULL 
                 AND end_time IS NOT NULL 
                 AND CONCAT(date, ' ', end_time) < NOW()
                 ORDER BY date DESC, end_time DESC;";
$finished_result = $conn->query($finished_sql);

$requests = [];
while ($row = $finished_result->fetch_assoc()) {
    // Fetch volunteer details
    $volunteer_email = $row['email_volunteer'];
    $volunteer_sql = "SELECT * FROM users WHERE email = '$volunteer_email'";
    $volunteer_result = $conn->query($volunteer_sql);
    $volunteer = $volunteer_result->fetch_assoc();
    
    $row['volunteer'] = $volunteer;
    $requests[] = $row;
}


function getRequestCounts($conn, $email) {
    $counts = [
        'week' => ['opened' => 0, 'canceled' => 0],
        'month' => ['opened' => 0, 'canceled' => 0],
        'year' => ['opened' => 0, 'canceled' => 0]
    ];

    $periods = [
        'week' => "YEARWEEK(created_at, 1) = YEARWEEK(NOW(), 1)",
        'month' => "YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())",
        'year' => "YEAR(created_at) = YEAR(NOW())"
    ];

    foreach ($periods as $period => $condition) {
        // Query to count opened requests
        $sql_opened = "SELECT COUNT(*) AS count FROM requests WHERE email = ? AND $condition";
        $stmt_opened = $conn->prepare($sql_opened);
        if ($stmt_opened) {
            $stmt_opened->bind_param("s", $email);
            $stmt_opened->execute();
            $result_opened = $stmt_opened->get_result();
            if ($result_opened->num_rows > 0) {
                $counts[$period]['opened'] = $result_opened->fetch_assoc()['count'];
            }
            $stmt_opened->close();
        }

        // Query to count canceled requests
        $sql_canceled = "SELECT COUNT(*) AS count FROM requests WHERE email = ? AND $condition AND cancelled_at IS NOT NULL";
        $stmt_canceled = $conn->prepare($sql_canceled);
        if ($stmt_canceled) {
            $stmt_canceled->bind_param("s", $email);
            $stmt_canceled->execute();
            $result_canceled = $stmt_canceled->get_result();
            if ($result_canceled->num_rows > 0) {
                $counts[$period]['canceled'] = $result_canceled->fetch_assoc()['count'];
            }
            $stmt_canceled->close();
        }
    }

    return $counts;
}

// Use the function to get the counts
$counts = getRequestCounts($conn, $email);
    
// Get the most recent confirmation date and time
$sql_recent_confirmation = "
SELECT created_at 
FROM requests 
WHERE email = ? 

ORDER BY created_at DESC 
LIMIT 1";

$stmt_recent_confirmation = $conn->prepare($sql_recent_confirmation);
$stmt_recent_confirmation->bind_param("s", $email);
$stmt_recent_confirmation->execute();
$result_recent_confirmation = $stmt_recent_confirmation->get_result();

$recent_confirmation = null;
if ($result_recent_confirmation->num_rows > 0) {
    $row_recent_confirmation = $result_recent_confirmation->fetch_assoc();
    $recent_confirmation = $row_recent_confirmation['created_at'];
}
$stmt_recent_confirmation->close();
$conn->close();
?>

<!doctype html>
<html lang="he" data-bs-theme="auto">

<head>
  <title>
    ElderLink - בקשות שהסתיימו
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

  <!-- Custom styles for this template -->
  <link href="sidebars.css" rel="stylesheet">
</head>

<body>

  <main class="d-flex flex-nowrap">
    <header>

      <div class="mobile-tab-bar">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.0/font/bootstrap-icons.css" rel="stylesheet">
        <nav class="nav">
          <a href="homepage_elderly.php" class="nav__link">
            <i class="bi bi-house nav__icon"></i>
            <span class="nav__text">דף הבית</span>
          </a>
          <a href="new_request.php" class="nav__link">
            <i class="bi-plus-circle nav__icon"></i>
            <span class="nav__text">פתיחת בקשה</span>
          </a>
          <a href="placed_requests.php" class="nav__link nav__link--active">
            <i class="bi bi-basket3-fill nav__icon"></i>
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
            <i class="top-bar-icon fa fa-comment"></i>
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
              <a href="homepage_elderly.php" class="nav-link link-body-emphasis" aria-current="page">
                <i class="bi bi-house me-2" width="20" height="20"></i>
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
              <a href="placed_requests.php" class="nav-link active">
                <i class="bi bi-basket3-fill me-2" width="20" height="20"></i>
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
      <div class="container-xl px-4 mt-2">
        <!-- ניווט בדף החשבון -->
        <nav class="nav nav-borders">
          <a class="nav-link" href="placed_requests.php">
            <span class="active-request-indicator"></span>
            בקשות פעילות (<?php echo $active_count; ?>)
          </a>
          <a class="nav-link " href="waiting_requests.php">
            <span class="wait-request-indicator"></span>
            בקשות ממתינות לשיבוץ (<?php echo $waiting_requests_count; ?>)
          </a>
          <a class="nav-link active" href="finished_requests.php">
            <span class="inactive-request-indicator"></span>
            בקשות שהסתיימו (<?php echo $finished_result->num_rows; ?>)
          </a>
        </nav>
        <hr class="mt-0 mb-1">
        <div class="row">
          <div class="col-xl-9">
            <div class="wrapper w-100">
              <div>
                <div class="list-group pt-3 gap-2">
                  <?php foreach ($requests as $request) { 
                                $profile_picture = '../../uploads/' . $request['volunteer']['picture']; ?>
                  <a href="#" class="list-group-item list-group-item-action d-flex W p-2" aria-current="true">
                    <img src="<?php echo $profile_picture; ?>" alt="תמונת פרופיל" width="44" height="44"
                      class="rounded-circle flex-shrink-0" style="margin-left: 10px;">
                    <div class="d-flex w-100 justify-content-between">
                      <div class="justify-content-between">
                        <h6>
                          <?php echo $request['volunteer']['first_name'] . ' ' . $request['volunteer']['last_name']; ?>
                          <small class="opacity-75 text-nowrap"> ● בקשה מס':
                            <?php echo $request['req_id']; ?>
                          </small>
                        </h6>
                        <hr style="width:20%; margin-top: 5px; margin-bottom: 10px;">
                        <small class="opacity-75 text-nowrap text-decoration-underline">פרטי המפגש</small>
                        <div style="display: flex; flex-direction: row;">
                          <div class="d-flex gap-2">
                            <p><b>תאריך: </b>
                              <?php echo date("d/m/Y", strtotime($request['date'])); ?>
                            </p>
                            <p>&bull;</p>
                            <p><b>שעת התחלה: </b>
                              <?php echo date("H:i", strtotime($request['start_time'])); ?>
                            </p>
                            <p>&bull;</p>
                            <p><b>שעת סיום: </b>
                              <?php echo date("H:i", strtotime($request['end_time'])); ?>
                            </p>
                          </div>
                        </div>
                        <div style="display: flex; flex-direction: row;">
                          <div class="d-flex gap-2">
                            <p><b>סוג הבקשה: </b>
                              <?php echo $request['req_type']; ?>
                            </p>
                            <p>&bull;</p>
                            <p><b>עיר: </b>
                              <?php echo $request['city']; ?>
                            </p>
                            <p>&bull;</p>
                            <p><b>כתובת: </b>
                              <?php echo $request['street'] . ' ' . $request['street_num']; ?>
                            </p>
                          </div>
                        </div>
                        <p><b>הערות נוספות: </b>
                          <?php echo $request['content']; ?>
                        </p>
                        <small class="opacity-75 text-nowrap text-decoration-underline">פרטי המתנדב</small>
                        <div style="display: flex; flex-direction: row;">
                          <div class="d-flex gap-2">
                            <p><b>מספר הטלפון: </b>
                              <span style="cursor: pointer; color: blue; text-decoration: underline;"
                                onclick="window.open('https://wa.me/<?php echo htmlspecialchars($request['volunteer']['phone']); ?>', '_blank');">
                                <?php echo htmlspecialchars($request['volunteer']['phone']); ?>
                              </span>
                            </p>
                            <p>&bull;</p>
                            <p><b>תאריך לידה: </b>
                              <?php echo date("d/m/Y", strtotime($request['volunteer']['date_of_birth'])); ?>
                            </p>
                          </div>
                        </div>
                        <div style="display: flex; flex-direction: row;">
                          <div class="d-flex gap-2">
                            <p><b>מגדר: </b>
                              <?php echo $request['volunteer']['gender']; ?>
                            </p>
                            <p>&bull;</p>
                            <p><b>שפות: </b>
                              <?php echo $request['volunteer']['language']; ?>
                            </p>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-2 text-center" style="width: 300;">
                        <button onclick="window.location.href='feedback.php?req_id=<?php echo $request['req_id']; ?>'"
                          class="btn btn-sm btn-outline-secondary">הגשת משוב</button>
                      </div>
                    </div>
                  </a>
                      <?php } 
                  if ($finished_result->num_rows == 0) {
                                                     echo '<div class="list-group pt-3 gap-2">
                                    <div style="            display: flex;
            justify-content: center;
            align-items: center;">
                                        <img src="../../images/old-woman.png" alt="Cart" width="350" height="350">
                                    </div>
                                    <h4 style="text-align:center; font-weight: bold; ">טרם הסתיים מפגש שלכם.</h4>
                                    <p style="text-align:center;">כל מפגש שלכם שחלף זמנו, יעבור ישירות לדף הזה.</p>
                            </div>';
                  }
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
                <p>הבקשה האחרונה נפתחה בתאריך <br>
                  <?php echo date("d/m/Y", strtotime($recent_confirmation)); ?> בשעה
                  <?php echo date("H:i", strtotime($recent_confirmation)); ?>.
                </p>
                <?php else: ?>
                <p><b>אין בקשות מאושרות.</b></p>
                <?php endif; ?>
                <hr>

                <p><b>בקשות שנפתחו השבוע: </b><br>
                  <?php echo $counts['week']['opened']; ?>
                </p>
                <p><b>בקשות שנפתחו החודש: </b><br>
                  <?php echo $counts['month']['opened']; ?>
                </p>
                <p><b>בקשות שנפתחו השנה: </b><br>
                  <?php echo $counts['year']['opened']; ?>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>



    <script src="../../js/bootstrap.bundle.min.js"></script>
    <script src="../../js/settings-profile.js"></script>
    <script src="https://cdn.enable.co.il/licenses/enable-L2324gcljoe8sb-0617/init.js"></script>
</body>

</html>