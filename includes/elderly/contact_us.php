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

$sql = "SELECT first_name, last_name, email, phone FROM users WHERE email = ? AND session_token = ?";
$statement = $conn->prepare($sql);
$statement->bind_param("ss", $email, $session_token);
$statement->execute();
$result = $statement->get_result();

if ($result->num_rows == 0) {
    header("Location: ../../index.html");
    exit();
}

$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetching all inputs' data.
    $inputFirstName = $_POST['inputFirstName'];
    $inputLastName = $_POST['inputLastName'];
    $inputEmailAddress = $_POST['inputEmailAddress'];
    $inputPhone = $_POST['inputPhone'];
    $inputSubject = $_POST['inputSubject'];
    $inputMessage = $_POST['inputMessage'];
    
    // Preparing an insert statement to 'inquiries' table.
    $statement = $conn->prepare("INSERT INTO inquiries (first_name, last_name, email, phone, subject, content) VALUES (?, ?, ?, ?, ?, ?)");
    $statement->bind_param("ssssss", $user['first_name'], $user['last_name'], $user['email'], $inputPhone,$inputSubject, $inputMessage);

    if ($statement->execute() === FALSE) {
        $error_message = $conn->error;
    } else {
        $success_message = "הפנייה נשלחה בהצלחה!";
    }
}

$conn->close();
?>

<!doctype html>
<html lang="he" data-bs-theme="auto">

<head>
  <title>
    ElderLink - צור קשר
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
          <a href="placed_requests.php" class="nav__link">
            <i class="bi bi-basket3 nav__icon"></i>
            <span class="nav__text">סל בקשות</span>
          </a>
          <a href="settings_profile.php" class="nav__link">
            <i class="bi bi-person-circle nav__icon"></i>
            <span class="nav__text">פרופיל</span>
          </a>
          <a href="contact_us.php" class="nav__link nav__link--active">
            <i class="bi bi-chat-right-text-fill nav__icon"></i>
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
            <a href="contact_us.php" class="nav-link active">
              <i class="bi bi-chat-right-text-fill me-2" width="20" height="20"></i>
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
          <a class="nav-link active" href="contact_us.php" target="">צור קשר</a>
        </nav>
        <hr class="mt-0 mb-4">
        <div class="row">
          <div>
            <div class="card mb-4">
              <div class="card-header">פרטי הפניה</div>
              <div class="card-body">
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                  <strong>מצטערים, נתקלנו בבעיה:</strong>
                  <?php echo htmlspecialchars($error_message); ?>
                </div>
                <?php elseif (isset($success_message)): ?>
                <div class="alert alert-success">
                  <?php echo htmlspecialchars($success_message); ?>
                </div>
                <?php endif; ?>

                <form method="post" action="">
                  <div class="row gx-3 mb-4">
                    <div class="col-md-6">
                      <label class="small mb-1" for="inputFirstName">שם פרטי</label>
                      <input class="form-control" disabled id="inputFirstName" name="inputFirstName" type="text"
                        placeholder="הכנס את שמך הפרטי" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                    </div>
                    <div class="col-md-6">
                      <label class="small mb-1" for="inputLastName">שם משפחה</label>
                      <input class="form-control" disabled id="inputLastName" name="inputLastName" type="text"
                        placeholder="הכנס את שם המשפחה שלך" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                    </div>
                  </div>
                  <div class="row gx-3 mb-4">
                    <div class="col-md-6">
                      <label class="small mb-1" for="inputEmailAddress">כתובת דואר אלקטרוני</label>
                      <input class="form-control" disabled id="inputEmailAddress" name="inputEmailAddress" type="email"
                        placeholder="הכנס את כתובת הדואר האלקטרוני שלך"
                        value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                    <div class="col-md-6">
                      <label class="small mb-1" for="inputPhone">טלפון</label>
                      <input class="form-control" id="inputPhone" name="inputPhone" type="tel"
                        placeholder="הכנס את מספר הטלפון שלך" value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                  </div>
                  <div class="mb-0 mt-3">
                    <label class="small mb-2">אנא בחר את נושא הפנייה</label><br>
                    <select name="inputSubject" id="inputSubject" style="width: 200px;">
                      <option value="תמיכה טכנית">תמיכה טכנית</option>
                      <option value="בעיה אישית עם מתנדב">בעיה אישית עם מתנדב</option>
                      <option value="משוב על האתר">משוב על האתר</option>
                      <option value="אחר">אחר</option>
                    </select>
                  </div>
                  <div class="col-md-12 mt-2">
                    <label class="small mb-1" for="inputMessage">תוכן הפניה</label>
                    <textarea class="form-control" id="inputMessage" name="inputMessage"
                      placeholder="הכנס את תוכן הפניה שלך" required></textarea>
                  </div>
                  <button class="btn btn-primary mt-4" type="submit">שלח</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>

      <script src="../../js/bootstrap.bundle.min.js"></script>
      <script src="../../js/settings-profile.js"></script>
      <script src="../../js/settings-perfrences.js"></script>
      <script src="https://cdn.enable.co.il/licenses/enable-L2324gcljoe8sb-0617/init.js"></script>

</body>

</html>