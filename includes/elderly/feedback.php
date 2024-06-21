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

$req_id = isset($_GET['req_id']) ? intval($_GET['req_id']) : 0;

// Fetch feedback details if feedback exists for the given req_id
$feedback_details = [];
$feedback_sql = "SELECT * FROM feedbacks WHERE req_id = ?";
$feedback_statement = $conn->prepare($feedback_sql);
$feedback_statement->bind_param("i", $req_id);
$feedback_statement->execute();
$feedback_result = $feedback_statement->get_result();

if ($feedback_result->num_rows > 0) {
    $feedback_details = $feedback_result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetching all inputs' data.
    $inputFirstName = $_POST['inputFirstName'];
    $inputLastName = $_POST['inputLastName'];
    $inputPhone = $_POST['inputPhone'];
    $inputMessage = $_POST['inputMessage'];
    $experience = $_POST['experience'];
    $contribution = $_POST['contribution'];
    $sociability = $_POST['sociability'];
    
        // Check if feedback already exists for the given req_id
    $check_sql = "SELECT * FROM feedbacks WHERE req_id = ?";
    $check_statement = $conn->prepare($check_sql);
    $check_statement->bind_param("i", $req_id);
    $check_statement->execute();
    $check_result = $check_statement->get_result();

    if ($check_result->num_rows > 0) {
        // Feedback exists, update the row
        $update_sql = "UPDATE feedbacks SET first_name = ?, last_name = ?, email = ?, phone = ?, content = ?, experience = ?, contribution = ?, sociability = ? WHERE req_id = ?";
        $update_statement = $conn->prepare($update_sql);
        $update_statement->bind_param("ssssssssi", $user['first_name'], $user['last_name'], $user['email'], $inputPhone, $inputMessage, $experience, $contribution, $sociability, $req_id);

        if ($update_statement->execute() === FALSE) {
            $error_message = $conn->error;
        } else {
                header("Location: finished_requests.php");
                exit();
        }
    } else {
        // Feedback does not exist, insert new row
        $insert_sql = "INSERT INTO feedbacks (req_id, first_name, last_name, email, phone, content, experience, contribution, sociability) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_statement = $conn->prepare($insert_sql);
        $insert_statement->bind_param("issssssss", $req_id, $user['first_name'], $user['last_name'], $user['email'], $inputPhone, $inputMessage, $experience, $contribution, $sociability);

        if ($insert_statement->execute() === FALSE) {
            $error_message = $conn->error;
        } else {
                header("Location: finished_requests.php");
                exit();
        }
    }
}

$conn->close();
?>

<!doctype html>
<html lang="he" data-bs-theme="auto">

<head>
  <title>
    ElderLink - משוב
  </title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <link rel="icon" href="../../images/favicon.png" type="image/x-icon">
  <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/list-groups/">
  <link rel="stylesheet" href="../../css/settings-profile.css">
  <link rel="stylesheet" href="../../css/feedback.css">

  <link rel="stylesheet" href="../../css/new_request.css">


  <link rel="stylesheet" href="../../css/homepage.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@100..900&display=swap" rel="stylesheet">
  <title>Sidebars · Bootstrap v5.3</title>
  <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/sidebars/">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">

  <link href="../../css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

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
          <a href="homepage_elderly.php" class="nav__link nav__link--active">
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
        <!-- ניווט בדף החשבון -->
        <nav class="nav nav-borders">
          <a class="nav-link active" href="feedback.php" target="">משוב</a>
        </nav>
        <hr class="mt-0 mb-4">
        <?php if (isset($error_message)) : ?>
        <p>
          <?php echo $error_message; ?>
        </p>
        <?php endif; ?>
        <?php if (isset($success_message)) : ?>
        <p>
          <?php echo $success_message; ?>
        </p>
        <?php endif; ?>
        <div class="row">
          <div>
            <!-- כרטיס פרטי החשבון -->
            <div class="card mb-4">
              <div class="card-header">משוב משתמש</div>
              <div class="card-body">
                <p class="small text-muted">המשוב שלכם חשוב לנו! נשמח לשמוע ממכם על החוויה והשירות שקיבלתם מהמתנדב.</p>
                <form method="post">
                  <input type="hidden" name="req_id" value="<?php echo $req_id; ?>">
                  <!-- שורת טופס -->
                  <div class="row gx-3 ">
                    <h6 class=" mt-0 opacity-75">פרטים אישיים</h6>
                    <hr>
                    <!-- קבוצת טופס (שם פרטי) -->
                    <div class="col-md-6">
                      <label class="small mb-1" for="inputFirstName">שם פרטי</label>
                      <input class="form-control" id="inputFirstName" name="inputFirstName" type="text"
                        placeholder="הכנס את שמך הפרטי" disabled
                        value="<?php echo htmlspecialchars($user['first_name']); ?>">
                    </div>
                    <!-- קבוצת טופס (שם משפחה) -->
                    <div class="col-md-6">
                      <label class="small mb-1" for="inputLastName">שם משפחה</label>
                      <input class="form-control" id="inputLastName" name="inputLastName" type="text"
                        placeholder="הכנס את שם המשפחה שלך" disabled
                        value="<?php echo htmlspecialchars($user['last_name']); ?>">
                    </div>
                  </div>
                  <!-- שורת טופס -->
                  <div class="row gx-3 mb-4">
                    <!-- קבוצת טופס (שם הארגון) -->
                    <div class="col-md-6">
                      <label class="small mb-1" for="inputEmailAddress">כתובת דואר אלקטרוני</label>
                      <input class="form-control" id="inputEmailAddress" type="email"
                        placeholder="הכנס את כתובת הדואר האלקטרוני שלך" disabled
                        value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                    <!-- קבוצת טופס (מיקום) -->
                    <div class="col-md-6">
                      <label class="small mb-1" for="inputPhone">מספר טלפון</label>
                      <input class="form-control" id="inputPhone" name="inputPhone" type="tel"
                        placeholder="הכנס את מספר הטלפון שלך" value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                  </div>
                  <div class="row gx-3 mb-4">
                    <h6 class=" mt-0 opacity-75">פרטי המשוב</h6>
                    <hr>
                    <link rel="stylesheet"
                      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
                    <div class="panel-container">
                      <strong>איך הייתה החוויה שלכם לאחר המפגש עם המתנדב?</strong>
                      <div class="ratings-container">
                        <div class="rating <?php echo ($feedback_details['experience'] == '3') ? 'active' : ''; ?>">
                          <input type="radio" name="experience" id="experience1" value="3" <?php echo
                            ($feedback_details['experience']=='3' ) ? 'checked' : '' ; ?>>
                          <label for="experience1">
                            <i class="fa-solid fa-face-grin-beam"></i><br>
                            <small>טובה</small>
                          </label>
                        </div>
                        <div class="rating <?php echo ($feedback_details['experience'] == '2') ? 'active' : ''; ?>">
                          <input type="radio" name="experience" id="experience2" value="2" <?php echo
                            ($feedback_details['experience']=='2' ) ? 'checked' : '' ; ?>>
                          <label for="experience2">
                            <i class="fa-solid fa-face-meh"></i><br>
                            <small>סבירה</small>
                          </label>
                        </div>
                        <div class="rating <?php echo ($feedback_details['experience'] == '1') ? 'active' : ''; ?>">
                          <input type="radio" name="experience" id="experience3" value="1" <?php echo
                            ($feedback_details['experience']=='1' ) ? 'checked' : '' ; ?>>
                          <label for="experience3">
                            <i class="fa-solid fa-face-sad-tear"></i><br>
                            <small>לא טובה</small>
                          </label>
                        </div>
                      </div>
                      <strong>אנא דרגו עד כמה המפגש סייע ותרם לכם.</strong>
                      <div class="container__items">
                        <input type="radio" name="contribution" id="st5A" value="5" <?php echo
                          ($feedback_details['contribution']=='5' ) ? 'checked' : '' ; ?>>
                        <label for="st5A">
                          <div class="star-stroke">
                            <div class="star-fill"></div>
                          </div>
                          <div class="label-description" data-content="מצוין"></div>
                        </label>
                        <input type="radio" name="contribution" id="st4A" value="4" <?php echo
                          ($feedback_details['contribution']=='4' ) ? 'checked' : '' ; ?>>
                        <label for="st4A">
                          <div class="star-stroke">
                            <div class="star-fill"></div>
                          </div>
                          <div class="label-description" data-content="טוב"></div>
                        </label>
                        <input type="radio" name="contribution" id="st3A" value="3" <?php echo
                          ($feedback_details['contribution']=='3' ) ? 'checked' : '' ; ?>>
                        <label for="st3A">
                          <div class="star-stroke">
                            <div class="star-fill"></div>
                          </div>
                          <div class="label-description" data-content="בסדר"></div>
                        </label>
                        <input type="radio" name="contribution" id="st2A" value="2" <?php echo
                          ($feedback_details['contribution']=='2' ) ? 'checked' : '' ; ?>>
                        <label for="st2A">
                          <div class="star-stroke">
                            <div class="star-fill"></div>
                          </div>
                          <div class="label-description" data-content="רע"></div>
                        </label>
                        <input type="radio" name="contribution" id="st1A" value="1" <?php echo
                          ($feedback_details['contribution']=='1' ) ? 'checked' : '' ; ?>>
                        <label for="st1A">
                          <div class="star-stroke">
                            <div class="star-fill"></div>
                          </div>
                          <div class="label-description" data-content="נורא"></div>
                        </label>
                      </div>
                      <strong style="margin-top: 50px;">אנא דרגו עד כמה חברותי ומסביר פנים היה המתנדב.</strong>
                      <div class="container__items">
                        <input type="radio" name="sociability" id="st5B" value="5" <?php echo
                          ($feedback_details['sociability']=='5' ) ? 'checked' : '' ; ?>>
                        <label for="st5B">
                          <div class="star-stroke">
                            <div class="star-fill"></div>
                          </div>
                          <div class="label-description" data-content="מצוין"></div>
                        </label>
                        <input type="radio" name="sociability" id="st4B" value="4" <?php echo
                          ($feedback_details['sociability']=='4' ) ? 'checked' : '' ; ?>>
                        <label for="st4B">
                          <div class="star-stroke">
                            <div class="star-fill"></div>
                          </div>
                          <div class="label-description" data-content="טוב"></div>
                        </label>
                        <input type="radio" name="sociability" id="st3B" value="3" <?php echo
                          ($feedback_details['sociability']=='3' ) ? 'checked' : '' ; ?>>
                        <label for="st3B">
                          <div class="star-stroke">
                            <div class="star-fill"></div>
                          </div>
                          <div class="label-description" data-content="בסדר"></div>
                        </label>
                        <input type="radio" name="sociability" id="st2B" value="2" <?php echo
                          ($feedback_details['sociability']=='2' ) ? 'checked' : '' ; ?>>
                        <label for="st2B">
                          <div class="star-stroke">
                            <div class="star-fill"></div>
                          </div>
                          <div class="label-description" data-content="רע"></div>
                        </label>
                        <input type="radio" name="sociability" id="st1B" value="1" <?php echo
                          ($feedback_details['sociability']=='1' ) ? 'checked' : '' ; ?>>
                        <label for="st1B">
                          <div class="star-stroke">
                            <div class="star-fill"></div>
                          </div>
                          <div class="label-description" data-content="נורא"></div>
                        </label>
                      </div>

                      <div class="col-md-12 mt-3">
                        <hr>
                        <label class="small mb-1" for="inputMessage">פרטים נוספים</label>
                        <textarea class="form-control" id="inputMessage" name="inputMessage" maxlength="200"
                          placeholder="האם יש עוד פרטים שתרצו לשתף בנוגע למפגש?"><?php echo isset($feedback_details['content']) ? $feedback_details['content'] : ''; ?></textarea>
                      </div>
                    </div>
                    <div id="errorMessage" class="alert alert-danger mt-1" style="display: none;">יש למלא את כל השדות
                      (מלבד התוכן).</div>
                  </div>
                  <!-- כפתור שמירת השינויים -->
                  <button class="btn btn-primary mt-1 " id="submit" type="submit">שלח</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>

      <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
      <script src="hhttps://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
      <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
      <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
      <script src="../../js/new_request.js"></script>
      <script src="../../js/bootstrap.bundle.min.js"></script>
      <script src="../../js/feedback.js"></script>
      <script src="https://cdn.enable.co.il/licenses/enable-L2324gcljoe8sb-0617/init.js"></script>

</body>

</html>