<?php
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

$sql = "SELECT email, day_p, req_type_p, city_p FROM users WHERE email = ? AND session_token = ?";
$statement = $conn->prepare($sql);
$statement->bind_param("ss", $email, $session_token);
$statement->execute();
$result = $statement->get_result();

if ($result->num_rows == 0) {
    header("Location: ../../index.html");
    exit();
}

$user_data = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetching selected days
    $selectedDays = isset($_POST['days']) ? implode(", ", $_POST['days']) : '';
    // Fetching selected areas
    $selectedAreas = isset($_POST['areas']) ? implode(", ", $_POST['areas']) : '';
    // Fetching selected cities
    $selectedCities = isset($_POST['cities']) ? implode(", ", $_POST['cities']) : '';
    $update_sql = "UPDATE users SET day_p = ?, req_type_p = ?, city_p = ? WHERE email = ?";
    $update_statement = $conn->prepare($update_sql);
    $update_statement->bind_param("ssss", $selectedDays, $selectedAreas, $selectedCities, $email);

if ($update_statement->execute() === FALSE) {
    $error_message = $conn->error;
} else {
    $success_message = '<p id="saveMessage" class="alert alert-success" style="display: block; padding: 0px; margin-bottom: 0px; width:150px; text-align:center;">השינויים נשמרו בהצלחה.</p>';
        // Fetch updated data
        $user_data['day_p'] = $selectedDays;
        $user_data['req_type_p'] = $selectedAreas;
        $user_data['city_p'] = $selectedCities;
    }
}

$conn->close();

function isChecked($field, $value) {
    return strpos($field, $value) !== false ? 'checked' : '';
}

function isSelected($field, $value) {
    return strpos($field, $value) !== false ? 'selected' : '';
}
?>

<!doctype html>
<html lang="he" data-bs-theme="auto">

<head>
    <title>
        ElderLink - העדפות מתנדב
      </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <link rel="icon" href="../../images/favicon.png" type="image/x-icon">
    <link rel="icon" href="../../images/favicon.png" type="image/x-icon">
    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/list-groups/">
    <link rel="stylesheet" href="../../css/settings-perfrences.css">
    <link rel="stylesheet" href="../../css/homepage.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@100..900&display=swap" rel="stylesheet">
    <title>Sidebars · Bootstrap v5.3</title>
    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/sidebars/">
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
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
                    <a href="settings_profile.php" class="nav__link nav__link--active">
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
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi p-1 bi-box-arrow-in-right me-2 top-bar-icon">
                        </i>
                    </a>
                    <a href="settings_security.php" class="d-flex align-items-center link-body-emphasis text-decoration-none "
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi p-1 bi-gear top-bar-icon">
                        </i>
                    </a>
                    <a href="notifications.php" class="d-flex align-items-center link-body-emphasis text-decoration-none "
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi p-1 bi-bell top-bar-icon">
                        </i>
                    </a>
                        <ul class="dropdown-menu text-small shadow">
                            <div
                                class="d-flex flex-column flex-md-row p-1  py-md-5 align-items-center justify-content-center">
                                <ul class="dropdown-menu d-block position-static mx-0 shadow w-220px"
                                    data-bs-theme="light">
                                    <p class="no-gap">המתנדב ערן יופה אישר את השיבוץ לבקשה שהוגשה.</p>
                                    <p class="opacity-50 no-gap">3 במרץ 2024 בשעה 20:55</p>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item dropdown-item-danger d-flex gap-2 align-items-center"
                                            href="#">
                                            <i class="bi bi-trash "></i>
                                            מחיקה
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div
                                class="d-flex flex-column flex-md-row p-1 py-md-5 align-items-center justify-content-center">
                                <ul class="dropdown-menu d-block position-static mx-0 shadow w-220px"
                                    data-bs-theme="light">
                                    <p>המתנדב ערן יופה אישר את השיבוץ לבקשה שהוגשה.</p>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item dropdown-item-danger d-flex align-items-center"
                                            href="#">
                                            <i class="bi bi-trash "></i>
                                            מחיקה
                                        </a>
                                    </li>
                                </ul>
                            </div>

                        </ul>
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
                            <a href="settings_profile.php" class="nav-link active">
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
            <form method="post">

                <div class="container-xl px-4 mt-2">
                    <nav class="nav nav-borders">
                        <a class="nav-link" href="settings_profile.php">פרופיל</a>
                        <a class="nav-link active" href="settings_preferences.php">העדפות מתנדב</a>

                </div>

                <hr class="mt-0 mb-4">
                                                                <?php if (isset($error_message)) : ?>
                        <p><?php echo $error_message; ?></p>
                    <?php endif; ?>
                    <?php if (isset($success_message)) : ?>
                        <p><?php echo $success_message; ?></p>
                    <?php endif; ?>
                <div class="row">
                 <div class="col-xl-4">
                <div class="card mb-4">
                    <div class="card-header">ימים פנויים בשבוע</div>
                    <div class="card-body">
                        <label class="small mb-2">אנא בחרו באילו ימים בשבוע תוכלו להתנדב</label>
                         <div class="form-check mb-2">
        <input class="form-check-input" style="float: right;" id="checkAll1" type="checkbox" value="All"
        <?php
            $all_days = "Sunday, Monday, Tuesday, Wednesday, Thursday, Friday, Saturday";
            if ($user_data['day_p'] == $all_days) {
                echo 'checked';
            }
        ?>>
        <label class="form-check-label" style="font-weight: bold;" for="checkAll1">בחר הכל</label>
    </div>
                        <div class="form-check mb-1 m-2">
                            <input class="form-check-input" name="days[]" id="checkDaysSunday" type="checkbox" value="Sunday" <?php echo isChecked($user_data['day_p'], 'Sunday'); ?>>
                            <label class="form-check-label" for="checkDaysSunday">יום ראשון</label>
                        </div>
                        <div class="form-check mb-1 m-2">
                            <input class="form-check-input" name="days[]" id="checkDaysMonday" type="checkbox" value="Monday" <?php echo isChecked($user_data['day_p'], 'Monday'); ?>>
                            <label class="form-check-label" for="checkDaysMonday">יום שני</label>
                        </div>
                        <div class="form-check mb-1 m-2">
                            <input class="form-check-input" name="days[]" id="checkDaysTuesday" type="checkbox" value="Tuesday" <?php echo isChecked($user_data['day_p'], 'Tuesday'); ?>>
                            <label class="form-check-label" for="checkDaysTuesday">יום שלישי</label>
                        </div>
                        <div class="form-check mb-1 m-2">
                            <input class="form-check-input" name="days[]" id="checkDaysWednesday" type="checkbox" value="Wednesday" <?php echo isChecked($user_data['day_p'], 'Wednesday'); ?>>
                            <label class="form-check-label" for="checkDaysWednesday">יום רביעי</label>
                        </div>
                        <div class="form-check mb-1 m-2">
                            <input class="form-check-input" name="days[]" id="checkDaysThursday" type="checkbox" value="Thursday" <?php echo isChecked($user_data['day_p'], 'Thursday'); ?>>
                            <label class="form-check-label" for="checkDaysThursday">יום חמישי</label>
                        </div>
                        <div class="form-check mb-1 m-2">
                            <input class="form-check-input" name="days[]" id="checkDaysFriday" type="checkbox" value="Friday" <?php echo isChecked($user_data['day_p'], 'Friday'); ?>>
                            <label class="form-check-label" for="checkDaysFriday">יום שישי</label>
                        </div>
                        <div class="form-check mb-1 m-2">
                            <input class="form-check-input" name="days[]" id="checkDaysSaturday" type="checkbox" value="Saturday" <?php echo isChecked($user_data['day_p'], 'Saturday'); ?>>
                            <label class="form-check-label" for="checkDaysSaturday">יום שבת</label>
                        </div>
                    </div>
                </div>
            </div>

                    <div class="col-xl-4">
                    <div class="card mb-4">
                        <div class="card-header">תחומים רלוונטים להתנדבות</div>
                        <div class="card-body">
                            <label class="small mb-2">אנא בחרו באילו תחומים תרצו להתנדב</label>
     <div class="form-check mb-2">
        <input class="form-check-input" id="checkAll2" type="checkbox"
        <?php
            $all_areas = "סיוע בקניות, ליווי לרופא, עזרה טכנולוגית, רכישת תרופות, פנאי יחדיו, other";
            if ($user_data['req_type_p'] == $all_areas) {
                echo 'checked';
            }
        ?>>
        <label class="form-check-label" style="font-weight: bold;" for="checkAll2">בחר הכל</label>
    </div>
                            <div class="form-check mb-1 m-2">
                                <input class="form-check-input" name="areas[]" id="checkAreasShoppingHelp" type="checkbox" value="סיוע בקניות" <?php echo isChecked($user_data['req_type_p'], 'סיוע בקניות'); ?>>
                                <label class="form-check-label" for="checkAreasShoppingHelp">סיוע בקניות</label>
                            </div>
                            <div class="form-check mb-1 m-2">
                                <input class="form-check-input" name="areas[]" id="checkAreasDoctor" type="checkbox" value="ליווי לרופא" <?php echo isChecked($user_data['req_type_p'], 'ליווי לרופא'); ?>>
                                <label class="form-check-label" for="checkAreasDoctor">ליווי לרופא</label>
                            </div>
                            <div class="form-check mb-1 m-2">
                                <input class="form-check-input" name="areas[]" id="checkAreasSocial" type="checkbox" value="עזרה טכנולוגית" <?php echo isChecked($user_data['req_type_p'], 'עזרה טכנולוגית'); ?>>
                                <label class="form-check-label" for="checkAreasSocial">עזרה טכנולוגית</label>
                            </div>
                            <div class="form-check mb-1 m-2">
                                <input class="form-check-input" name="areas[]" id="checkAreasTransportation" type="checkbox" value="רכישת תרופות" <?php echo isChecked($user_data['req_type_p'], 'רכישת תרופות'); ?>>
                                <label class="form-check-label" for="checkAreasTransportation">רכישת תרופות</label>
                            </div>
                            <div class="form-check mb-1 m-2">
                                <input class="form-check-input" name="areas[]" id="checkAreasCleaning" type="checkbox" value="פנאי יחדיו" <?php echo isChecked($user_data['req_type_p'], 'פנאי יחדיו'); ?>>
                                <label class="form-check-label" for="checkAreasCleaning">פנאי יחדיו</label>
                            </div>
                            <div class="form-check mb-1 m-2">
                                <input class="form-check-input" name="areas[]" id="checkAreasGarden" type="checkbox" value="other" <?php echo isChecked($user_data['req_type_p'], 'other'); ?>>
                                <label class="form-check-label" for="checkAreasGarden">אחר</label>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-xl-4">
                <div class="card mb-4">
                    <div class="card-header">ערים רלוונטיות</div>
                    <div class="card-body">
                        <div class="mb-0">
                            <label class="small mb-2">אנא בחרו באילו ערים הנכם מוכנים להתנדב</label>
                                <select name="cities[]" id="field2" multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="5">
                                    <option value="תל אביב - יפו" <?php echo isSelected($user_data['city_p'], 'תל אביב - יפו'); ?>>תל אביב - יפו</option>
                                        <option value="ירושלים" <?php echo isSelected($user_data['city_p'], 'ירושלים'); ?>>ירושלים</option>
                                    <option value="חיפה" <?php echo isSelected($user_data['city_p'], 'חיפה'); ?>>חיפה</option>
                                    <option value="באר שבע" <?php echo isSelected($user_data['city_p'], 'באר שבע'); ?>>באר שבע</option>
                                    <option value="חולון" <?php echo isSelected($user_data['city_p'], 'חולון'); ?>>חולון</option>
                                    <option value="אילת" <?php echo isSelected($user_data['city_p'], 'אילת'); ?>>אילת</option>
                                    <option value="נתניה" <?php echo isSelected($user_data['city_p'], 'נתניה'); ?>>נתניה</option>
                                    <option value="אשדוד" <?php echo isSelected($user_data['city_p'], 'אשדוד'); ?>>אשדוד</option>
                                    <option value="רמת גן" <?php echo isSelected($user_data['city_p'], 'רמת גן'); ?>>רמת גן</option>
                                    <option value="בני ברק" <?php echo isSelected($user_data['city_p'], 'בני ברק'); ?>>בני ברק</option>
                                    <option value="חדרה" <?php echo isSelected($user_data['city_p'], 'חדרה'); ?>>חדרה</option>
                                    <option value="עפולה" <?php echo isSelected($user_data['city_p'], 'עפולה'); ?>>עפולה</option>
                                    <option value="רעננה" <?php echo isSelected($user_data['city_p'], 'רעננה'); ?>>רעננה</option>
                                    <option value="פתח תקווה" <?php echo isSelected($user_data['city_p'], 'פתח תקווה'); ?>>פתח תקווה</option>
                                    <option value="ראשון לציון" <?php echo isSelected($user_data['city_p'], 'ראשון לציון'); ?>>ראשון לציון</option>
                                    <option value="כפר סבא" <?php echo isSelected($user_data['city_p'], 'כפר סבא'); ?>>כפר סבא</option>
                                    <option value="נצרת" <?php echo isSelected($user_data['city_p'], 'נצרת'); ?>>נצרת</option>
                                    <option value="בת ים" <?php echo isSelected($user_data['city_p'], 'בת ים'); ?>>בת ים</option>
                                    <option value="הרצליה" <?php echo isSelected($user_data['city_p'], 'הרצליה'); ?>>הרצליה</option>
                                    <option value="אופקים" <?php echo isSelected($user_data['city_p'], 'אופקים'); ?>>אופקים</option>
                                    <option value="קריית אתא" <?php echo isSelected($user_data['city_p'], 'קריית אתא'); ?>>קריית אתא</option>
                                    <option value="אור יהודה" <?php echo isSelected($user_data['city_p'], 'אור יהודה'); ?>>אור יהודה</option>
                                    <option value="קריית גת" <?php echo isSelected($user_data['city_p'], 'קריית גת'); ?>>קריית גת</option>
                                    <option value="אור עקיבא" <?php echo isSelected($user_data['city_p'], 'אור עקיבא'); ?>>אור עקיבא</option>
                                    <option value="בית שאן" <?php echo isSelected($user_data['city_p'], 'בית שאן'); ?>>בית שאן</option>
                                    <option value="נהריה" <?php echo isSelected($user_data['city_p'], 'נהריה'); ?>>נהריה</option>
                                    <option value="קריית שמונה" <?php echo isSelected($user_data['city_p'], 'קריית שמונה'); ?>>קריית שמונה</option>
                                    <option value="ביתר עילית" <?php echo isSelected($user_data['city_p'], 'ביתר עילית'); ?>>ביתר עילית</option>
                                    <option value="קריית מוצקין" <?php echo isSelected($user_data['city_p'], 'קריית מוצקין'); ?>>קריית מוצקין</option>
                                    <option value="קריית ביאליק" <?php echo isSelected($user_data['city_p'], 'קריית ביאליק'); ?>>קריית ביאליק</option>
                                    <option value="רמלה" <?php echo isSelected($user_data['city_p'], 'רמלה'); ?>>רמלה</option>
                                    <option value="ראש העין" <?php echo isSelected($user_data['city_p'], 'ראש העין'); ?>>ראש העין</option>
                                    <option value="בית שמש" <?php echo isSelected($user_data['city_p'], 'בית שמש'); ?>>בית שמש</option>
                                    <option value="טבריה" <?php echo isSelected($user_data['city_p'], 'טבריה'); ?>>טבריה</option>
                                    <option value="מודיעין-מכבים-רעות" <?php echo isSelected($user_data['city_p'], 'מודיעין-מכבים-רעות'); ?>>מודיעין-מכבים-רעות</option>
                                    <option value="עכו" <?php echo isSelected($user_data['city_p'], 'עכו'); ?>>עכו</option>
                                    <option value="קריית ים" <?php echo isSelected($user_data['city_p'], 'קריית ים'); ?>>קריית ים</option>
                                    <option value="דימונה" <?php echo isSelected($user_data['city_p'], 'דימונה'); ?>>דימונה</option>
                                    <option value="כרמיאל" <?php echo isSelected($user_data['city_p'], 'כרמיאל'); ?>>כרמיאל</option>
                                    <option value="אום אל-פחם" <?php echo isSelected($user_data['city_p'], 'אום אל-פחם'); ?>>אום אל-פחם</option>
                                    <option value="קריית מלאכי" <?php echo isSelected($user_data['city_p'], 'קריית מלאכי'); ?>>קריית מלאכי</option>
                                    <option value="צפת" <?php echo isSelected($user_data['city_p'], 'צפת'); ?>>צפת</option>
                                    <option value="כרמיה" <?php echo isSelected($user_data['city_p'], 'כרמיה'); ?>>כרמיה</option>
                                    <option value="נס ציונה" <?php echo isSelected($user_data['city_p'], 'נס ציונה'); ?>>נס ציונה</option>
                                    <option value="קריית טבעון" <?php echo isSelected($user_data['city_p'], 'קריית טבעון'); ?>>קריית טבעון</option>
                                    <option value="קריית עקרון" <?php echo isSelected($user_data['city_p'], 'קריית עקרון'); ?>>קריית עקרון</option>
                                    <option value="קריית יערים" <?php echo isSelected($user_data['city_p'], 'קריית יערים'); ?>>קריית יערים</option>
                                    <option value="גבעתיים" <?php echo isSelected($user_data['city_p'], 'גבעתיים'); ?>>גבעתיים</option>
                                    <option value="דיר אל-בלח" <?php echo isSelected($user_data['city_p'], 'דיר אל-בלח'); ?>>דיר אל-בלח</option>
                                    <option value="טמרה" <?php echo isSelected($user_data['city_p'], 'טמרה'); ?>>טמרה</option>
                                    <option value="קלנסווה" <?php echo isSelected($user_data['city_p'], 'קלנסווה'); ?>>קלנסווה</option>
                                    <option value="אבו גוש" <?php echo isSelected($user_data['city_p'], 'אבו גוש'); ?>>אבו גוש</option>
                                    <option value="גבעת שמואל" <?php echo isSelected($user_data['city_p'], 'גבעת שמואל'); ?>>גבעת שמואל</option>
                                    <option value="יהוד-מונוסון" <?php echo isSelected($user_data['city_p'], 'יהוד-מונוסון'); ?>>יהוד-מונוסון</option>
                                    <option value="ערד" <?php echo isSelected($user_data['city_p'], 'ערד'); ?>>ערד</option>
                                    <option value="בית דגן" <?php echo isSelected($user_data['city_p'], 'בית דגן'); ?>>בית דגן</option>
                                    <option value="קריית שרת" <?php echo isSelected($user_data['city_p'], 'קריית שרת'); ?>>קריית שרת</option>
                                    <option value="מעלה אדומים" <?php echo isSelected($user_data['city_p'], 'מעלה אדומים'); ?>>מעלה אדומים</option>
                                    <option value="גן יבנה" <?php echo isSelected($user_data['city_p'], 'גן יבנה'); ?>>גן יבנה</option>
                                    <option value="מודיעין עילית" <?php echo isSelected($user_data['city_p'], 'מודיעין עילית'); ?>>מודיעין עילית</option>
                                    <option value="מגדל העמק" <?php echo isSelected($user_data['city_p'], 'מגדל העמק'); ?>>מגדל העמק</option>
                                    <option value="קריית משה" <?php echo isSelected($user_data['city_p'], 'קריית משה'); ?>>קריית משה</option>
                                    <option value="יקנעם עילית" <?php echo isSelected($user_data['city_p'], 'יקנעם עילית'); ?>>יקנעם עילית</option>
                                    <option value="בת עין" <?php echo isSelected($user_data['city_p'], 'בת עין'); ?>>בת עין</option>
                                    <option value="יפיע" <?php echo isSelected($user_data['city_p'], 'יפיע'); ?>>יפיע</option>
                                    <option value="מעלות-תרשיחא" <?php echo isSelected($user_data['city_p'], 'מעלות-תרשיחא'); ?>>מעלות-תרשיחא</option>
                                    <option value="רמת השרון" <?php echo isSelected($user_data['city_p'], 'רמת השרון'); ?>>רמת השרון</option>
                                    <option value="כפר יונה" <?php echo isSelected($user_data['city_p'], 'כפר יונה'); ?>>כפר יונה</option>
                                
                                </select>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <input type="submit" value="שמור שינויים" class="btn btn-primary" class="ml-5">
            </form>
        </div>
        </div>
        <script src="../../js/bootstrap.bundle.min.js"></script>
        <script src="../../js/settings-perfrences.js"></script>
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

                // REST API URL
const api_url = "https://data.gov.il/api/3/action/datastore_search";
// Cities endpoint
const cities_resource_id = "5c78e9fa-c2e2-4771-93ff-7f400a12f7ba";
// Streets endpoint
const streets_resource_id = "a7296d1a-f8c9-4b70-96c2-6ebb4352f8e3";
// Field names
const city_name_key = "שם_ישוב";
const street_name_key = "שם_רחוב";
// dataset ids
const cities_data_id = "field2"; // Changed to match your select element id
const streets_data_id = "streets-data";
// input elements
const cities_input = document.getElementById("city-choice");
const streets_input = document.getElementById("street-choice");

/**
 * Get data from gov data API
 */
const getData = (resource_id, q = "", limit = "100") => {
    return axios.get(api_url, {
        params: { resource_id, q, limit },
        responseType: "json"
    });
};

/**
 * Parse records from data into 'option' elements,
 * use data from key 'field_name' as the option value
 */
const parseResponse = (records = [], field_name) => {
    const parsed = records
        .map((record) => {
            const value = record[field_name].trim();
            return <option value="${value}">${value}</option>;
        })
        .join('\n') || '';

    return Promise.resolve(parsed);
};

/**
 * Fetch data, parse, and populate Datalist
 */
const populateDataList = (id, resource_id, field_name, query, limit) => {
    const datalist_element = document.getElementById(id);
    if (!datalist_element) {
        console.log("Datalist with id", id, "doesn't exist in the document, aborting");
        return;
    }
    getData(resource_id, query, limit)
        .then((response) =>
            parseResponse(response?.data?.result?.records, field_name)
        )
        .then((html) => (datalist_element.innerHTML = html))
        .catch((error) => {
            console.log("Couldn't get list for", id, "query:", query, error);
        });
};

if (cities_input) {
    cities_input.addEventListener("change", (event) => {
        populateDataList(
            streets_data_id,
            streets_resource_id,
            street_name_key,
            {
                שם_ישוב: cities_input.value
            },
            32000
        );
    });
}

    // Add event listener to "Select All" checkboxes
    document.getElementById('checkAll1').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('input[name="days[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

    document.getElementById('checkAll2').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('input[name="areas[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

    document.getElementById('checkAll3').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('select[name="cities[]"] option');
        checkboxes.forEach(option => option.selected = this.checked);
    });

    document.getElementById('checkAll4').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('input[name="languages[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });
    
    
     // Get all day checkboxes
    const dayCheckboxes = document.querySelectorAll('.day-checkbox');
    // Get the "בחר הכל" checkbox
    const checkAllCheckbox = document.querySelector('.check-all');

    // Function to check if all day checkboxes are checked
    function checkAllChecked() {
        let allChecked = true;
        dayCheckboxes.forEach(function(checkbox) {
            if (!checkbox.checked) {
                allChecked = false;
            }
        });
        return allChecked;
    }

    // Event listener for any change in day checkboxes
    dayCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            if (checkAllChecked()) {
                checkAllCheckbox.checked = true;
            } else {
                checkAllCheckbox.checked = false;
            }
        });
    });

    // Event listener for "בחר הכל" checkbox
    checkAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        // Set all day checkboxes to the same state as "בחר הכל"
        dayCheckboxes.forEach(function(checkbox) {
            checkbox.checked = isChecked;
});
});
        </script>
</body>

</html>