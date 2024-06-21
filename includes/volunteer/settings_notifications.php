<?php
require '../check_session.php';

session_start();
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

$sql = "SELECT email,phone, sms_notification_volunteer,email_notification_volunteer FROM users WHERE email = ? AND session_token = ?";
$statement = $conn->prepare($sql);
$statement->bind_param("ss", $email, $session_token);
$statement->execute();
$result = $statement->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $email = $row['email'];
    $sms_notification = $row['sms_notification_volunteer'];
    $phone = $row['phone'];
    $email_notification = $row['email_notification_volunteer'];
} else {
    header("Location: ../../index.html");
    exit();
}

// Update sms_notification if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sms_notification = isset($_POST['sms_notification_volunteer']) ? 1 : 0;
    $email_notification = isset($_POST['email_notification_volunteer']) ? 1 : 0;
    $update_sql = "UPDATE users SET sms_notification_volunteer = ?,email_notification_volunteer = ? WHERE email = ? AND session_token = ?";
    $update_statement = $conn->prepare($update_sql);
    $update_statement->bind_param("isss", $sms_notification, $email_notification, $email, $session_token);
    $update_statement->execute();
}
?>

<!doctype html>
<html lang="he" data-bs-theme="auto">

<head>
    <title>
        ElderLink - הגדרות-התראות
    </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <link rel="icon" href="../../images/favicon.png" type="image/x-icon">
    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/list-groups/">

    <link rel="stylesheet" href="../../css/homepage.css">
    <link rel="stylesheet" href="../../css/settings-notification.css">

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
                            <i class="bi p-1 bi-gear-fill top-bar-icon text-primary">
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
                            <a href="settings_security.php" class="nav-link active">
                                <i class="bi bi-gear-fill me-2" width="20" height="20"></i>
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
                    <a class="nav-link" href="settings_security.php">אבטחה</a>
                    <a class="nav-link active" href="settings_notifications.php">התראות</a>
                </nav>
                <hr class="mt-0 mb-4">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card card-header-actions mb-4">
                            <div class="card-header">
                                עדכוני SMS
                                <div class="form-check form-switch">
                                    <label class="form-check-label" for="smsToggleSwitch"></label>
                                </div>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="small mb-1" for="inputNotificationSms">מספר SMS ברירת מחדל</label>
                                        <input class="form-control" id="inputNotificationSms" type="tel"
                                            value="<?php echo htmlspecialchars($phone); ?>" disabled="">
                                    </div>
                                    <div class="mb-0">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" id="checkSmsComment"
                                                name="sms_notification_volunteer" type="checkbox" <?php echo ($sms_notification ? 'checked' : ''); ?>>
                                            <label class="form-check-label" for="checkSmsComment">הודעה בדבר ביטול
                                                המפגש</label>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-header-actions mb-4">
                            <div class="card-header">
                                עדכוני דואר אלקטרוני
                                <div class="form-check form-switch">
                                    <label class="form-check-label" for="smsToggleSwitch"></label>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="small mb-1" for="inputNotificationEmail"> דואר אלקטרוני ברירת
                                        מחדל</label>
                                    <input class="form-control" id="inputNotificationEmail" type="email"
                                        value="<?php echo htmlspecialchars($email); ?>" disabled="">
                                </div>
                                <div class="mb-0">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" id="checkSmsComment"
                                            name="email_notification_volunteer" type="checkbox" <?php echo
                                                ($email_notification ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="checkSmsComment">הודעה בדבר ביטול
                                            המפגש</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary" style="width:150px;" type="submit"> שמור שינויים</button>
                    </form>
                </div>
            </div>
        </div>

        <script src="../../js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.enable.co.il/licenses/enable-L2324gcljoe8sb-0617/init.js"></script>

</body>

</html>