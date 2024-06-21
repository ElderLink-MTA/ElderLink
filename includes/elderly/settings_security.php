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


$query = "SELECT shared_data, 2fa, phone FROM users WHERE email = '$email'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $shared_data = $row['shared_data'];
     $twoFactor = $row['2fa'];
     $phone = $row['phone'];
}
?>

<!doctype html>
<html lang="he" data-bs-theme="auto">

<head>
    <title>
        ElderLink - הגדרות-אבטחה
      </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <link rel="icon" href="../../images/favicon.png" type="image/x-icon">
    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/list-groups/">

    <link rel="stylesheet" href="../../css/homepage.css">
    <link rel="stylesheet" href="../../css/settings-security.css">

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
                    <a href="homepage_elderly.php" class="nav__link">
                        <i class="bi bi-house nav__icon"></i>
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
                            <i class="bi p-1 bi-gear-fill top-bar-icon text-primary">
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


        <div class="container" >
            <div class="container-xl px-4 mt-2">
                <nav class="nav nav-borders">
                    <a class="nav-link active" href="settings_security.php">אבטחה</a>
                    <a class="nav-link" href="settings_notifications.php">התראות</a>
                </nav>
                <hr class="mt-0 mb-4">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-header">שינוי סיסמה</div>
                            <div class="card-body">
                                <form id="passwordForm" action="update_password.php" method="post">
                                    <div class="mb-3">
                                        <label class="small mb-1" for="currentPassword">סיסמה נוכחית</label>
                                        <input class="form-control" id="currentPassword" name="currentPassword" type="password" placeholder="הזן סיסמה נוכחית">
                                        <div class="text-danger small" id="currentPasswordError"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="small mb-1" for="newPassword">סיסמה חדשה</label>
                                        <input class="form-control" id="newPassword" name="newPassword" type="password" placeholder="הזן סיסמה חדשה">
                                        <div class="text-danger small" id="newPasswordError"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="small mb-1" for="confirmPassword">אישור סיסמה</label>
                                        <input class="form-control" id="confirmPassword" name="confirmPassword" type="password" placeholder="אישור סיסמה חדשה">
                                        <div class="text-danger small" id="confirmPasswordError"></div>
                                    </div>
                                    <input class="btn btn-primary" type="submit" value="שמור">
                                    <div class="text-success small" id="successMessage"></div>
                                </form>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-header">העדפות אבטחה</div>
                            <div class="card-body">
                                <h5 class="mb-1">שיתוף נתונים</h5>
                                <p class="small text-muted">שיתוף נתוני השימוש יכול לעזור לנו לשפר את מוצרינו ולשרת את המשתמשים שלנו בצורה טובה יותר כאשר הם משתמשים באפליקציה שלנו. כאשר אתה מסכים לשיתוף נתוני השימוש איתנו, דיווחי קריסה וניתוחי שימוש יישלחו אוטומטית לצוות הפיתוח שלנו לבדיקה.</p>
                                <form id="usageForm" method="POST" action="update_choice.php">
                                    <div class="form-check">
                                        <input class="form-check-input" id="radioUsage1" type="radio" name="radioUsage" value="1" <?php if($shared_data == 1) echo 'checked'; ?>>
                                        <label class="form-check-label" for="radioUsage1">כן, שתף נתונים ודיווחי קריסה עם מפתחי האפליקציה</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" id="radioUsage2" type="radio" name="radioUsage" value="0" <?php if($shared_data == 0) echo 'checked'; ?>>
                                        <label class="form-check-label" for="radioUsage2">לא, הגבל את השיתוף שלי עם מפתחי האפליקציה</label>
                                    </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-header">אימות דו-שלבי</div>
                            <div class="card-body">
                                <p>הוסף רמה נוספת של אבטחה לחשבון שלך על ידי הפעלת אימות דו-גורמי. אנו נשלח לך הודעת טקסט לאימות נסיונות ההתחברות שלך במכשירים ודפדפנים שאינם מוכרים.</p>
                              
                                    <div class="form-check">
                                        <input class="form-check-input"  type="radio" id="twoFactorOn" name="twoFactor" value="1" <?php if($twoFactor == 1) echo 'checked'; ?>>
                                        <label class="form-check-label" for="twoFactorOn">מופעל</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" id="twoFactorOff" type="radio" name="twoFactor" value="0" <?php if($twoFactor == 0) echo 'checked'; ?>>
                                        <label class="form-check-label" for="twoFactorOff">כבוי</label>
                                    </div>
    
                                    <div class="mt-3">
                                        <label class="small mb-1" for="twoFactorSMS">מספר טלפון לטקסט</label>
                                           <input class="form-control" id="twoFactorSMS" disabled type="tel" placeholder="הזן מספר טלפון" value="<?php echo htmlspecialchars($phone); ?>">
                                    </div>
                               
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-header">מחיקת חשבון</div>
                            <div class="card-body">
                                <p>מחיקת החשבון שלך היא פעולה קבועה ולא ניתן לבטל אותה. אם אתה בטוח שברצונך למחוק את החשבון שלך, בחר בכפתור למטה.</p>
                                        <button class="btn btn-danger" type="button" id="deleteAccountBtn">אני מבין, מחק את חשבוני</button>
                            </div>
                        </div>
                </div>
                 <input class="btn btn-primary mt-2" type="submit" value="שמירה">
                    </form>
            </div>
        </div>

        <script src="../../js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.enable.co.il/licenses/enable-L2324gcljoe8sb-0617/init.js"></script>
        <script src="../../js/settings-security.js"></script>
        
       <script>
            
</script>

</body>

</html>