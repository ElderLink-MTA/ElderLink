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
    $inputCity = $_POST['city-choice'];
    $inputStreet = $_POST['street'];
    $inputStreetNum = $_POST['street_num'];
    $inputReqType = $_POST['req_type'];
    $inputContent = $_POST['inputMessage'];
    
    $conn->begin_transaction();

    try {
        // Preparing an insert statement for the 'requests' table.
    $statement = $conn->prepare("
        INSERT INTO requests (first_name, last_name, email, phone, city, street, street_num, req_type, content, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
        $statement->bind_param("sssssssss", $user['first_name'], $user['last_name'], $user['email'], $inputPhone, $inputCity, $inputStreet, $inputStreetNum, $inputReqType, $inputContent);
        
        if ($statement->execute() === FALSE) {
            throw new Exception($conn->error);
        }

        // Get the last inserted request ID
        $req_id = $conn->insert_id;

        // Insert into the 'requests_times' table
        $dates = $_POST['dates'];
        $start_times = $_POST['start_times'];
        $end_times = $_POST['end_times'];

        $times_statement = $conn->prepare("INSERT INTO requests_times (date, start_time, end_time, req_id) VALUES (?, ?, ?, ?)");
        $times_statement->bind_param("sssi", $date, $start_time, $end_time, $req_id);

        for ($i = 0; $i < count($dates); $i++) {
            $date = DateTime::createFromFormat('d/m/Y', $dates[$i])->format('Y-m-d');
            $start_time = $start_times[$i];
            $end_time = $end_times[$i];

            if ($times_statement->execute() === FALSE) {
                throw new Exception($conn->error);
            }
        }

        $conn->commit();
        $success_message = "הבקשה הוגשה בהצלחה!";
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $error_message = "מצטערים, נתקלנו בבעיה: " . $e->getMessage();
    }
}

$conn->close();

?>

<!doctype html>
<html lang="he" data-bs-theme="auto">

<head>
  <title>
    ElderLink - פתיחת בקשה
  </title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <link rel="icon" href="../../images/favicon.png" type="image/x-icon">
  <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/list-groups/">
  <link rel="stylesheet" href="../../css/new_request.css">
  <link rel="stylesheet" href="../../css/settings-profile.css">

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
          <a href="new_request.php" class="nav__link nav__link--active">
            <i class="bi-plus-circle-fill nav__icon"></i>
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
              <a href="new_request.php" class="nav-link active">
                <i class="bi bi-plus-circle-fill me-2 sidebar__icon"></i>
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
          <a class="nav-link active" href="new_request.php" target="">פתיחת בקשה</a>
        </nav>
        <hr class="mt-0 mb-4">
        <div class="row">

          <div>
            <div class="card mb-4">
              <div class="card-header">פרטי הבקשה</div>
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

                  <div class="row gx-3 ">
                    <h6 class=" mt-0 opacity-75">פרטים אישיים</h6>
                    <hr>
                    <div class="col-md-6">
                      <label class="small mb-1" for="inputFirstName">שם פרטי</label>
                      <input class="form-control" disabled name="inputFirstName" id="inputFirstName" type="text"
                        placeholder="הכנס את שמך הפרטי" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                    </div>
                    <div class="col-md-6">
                      <label class="small mb-1" for="inputLastName">שם משפחה</label>
                      <input class="form-control" disabled name="inputLastName" id="inputLastName" type="text"
                        placeholder="הכנס את שם המשפחה שלך" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                    </div>
                  </div>
                  <div class="row gx-3 mb-4">
                    <div class="col-md-6">
                      <label class="small mb-1" for="inputEmailAddress">כתובת דואר
                        אלקטרוני</label>
                      <input class="form-control" name="inputEmailAddress" id="inputEmailAddress" type="text" disabled
                        placeholder="הכנס את כתובת הדואר האלקטרוני שלך"
                        value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                    <div class="col-md-6">
                      <label class="small mb-1" for="inputPhone">מספר טלפון</label>
                      <input class="form-control" name="inputPhone" id="inputPhone" type="tel"
                        placeholder="הכנס את מספר הטלפון שלך" value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                  </div>


                  <div class="row gx-3 mb-4">
                    <h6 class=" mt-2 opacity-75">פרטי המפגש</h6>
                    <hr>

                    <div class="col-md-2 autocomplete ">
                      <label class="small mb-1 " for="city-choice">עיר</label>
                      <input class="form-control" list="cities-data" id="city-choice" name="city-choice" type="text"
                        placeholder="הכנס את העיר בה יתקיים המפגש"/>
                      <datalist id="cities-data">
                        <option value="">טוען רשימת ערים...</option>
                      </datalist>
                    </div>

                    <div class="col-md-3 autocomplete">
                      <label class="small mb-1" for="street">רחוב</label>
                      <input class="form-control" name="street" list="streets-data" id="street-choice" type="text"
                        placeholder="הכנס את הרחוב בו יתקיים המפגש"/>
                                                          <datalist id="streets-data">
              <option value="">
            </datalist>
                    </div>
                    <div class="col-md-1">
                      <label class="small mb-1" for="street_num">מספר</label>
                      <input class="form-control" name="street_num" id="street_num" type="text"
                        placeholder="הכנס את מספר הטלפון שלך">
                    </div>
                    <div class="col-md-6">
                      <label class="small mb-1" for="req_type">אופי הבקשה</label>
                      <div class="row">
                        <?php
                            $selected_type = isset($_GET['req_type']) ? $_GET['req_type'] : '';
                        ?>

                        <div class="col-md-6">
                          <select class="form-control" name="req_type" id="req_type" onchange="showOtherOption(this)">
                            <option value="" disabled>בחר...</option>
                            <option value="עזרה טכנולוגית" <?php echo $selected_type=='עזרה טכנולוגית' ? 'selected' : ''
                              ; ?>>עזרה טכנולוגית</option>
                            <option value="סיוע בקניות" <?php echo $selected_type=='סיוע בקניות' ? 'selected' : '' ; ?>
                              >סיוע בקניות</option>
                            <option value="פנאי יחדיו" <?php echo $selected_type=='פנאי יחדיו' ? 'selected' : '' ; ?>
                              >פנאי יחדיו</option>
                            <option value="רכישת תרופות" <?php echo $selected_type=='רכישת תרופות' ? 'selected' : '' ;
                              ?>>רכישת תרופות</option>
                            <option value="ליווי לרופא" <?php echo $selected_type=='ליווי לרופא' ? 'selected' : '' ; ?>
                              >ליווי לרופא</option>
                            <option value="other" <?php echo $selected_type=='other' ? 'selected' : '' ; ?>>אחר</option>
                          </select>
                        </div>
                        <div class="col-md-6">
                          <input class="custom-textbox d-none" id="otherOption" type="text" onblur="updateSelectValue()"
                            maxlength="20" placeholder="הקלד את האופציה החדשה">
                        </div>
                      </div>
                    </div>
                    <div class="col-md-12 mt-4">
                      <label class="small mb-1" for="inputMessage">פרטים נוספים</label>
                      <textarea class="form-control" name="inputMessage" id="inputMessage" maxlength="150"
                        placeholder="כיצד נוכל לעזור לכם?"></textarea>
                    </div>
                    <h6 class="mt-5 opacity-75">זמני פגישות</h6>
                    <hr>
                    <p class="small text-muted">ניתן להוסיף זמני פגישה נוספים על מנת להציע למתנדב מספר חלונות זמן
                      אפשריים לבחירה.</p>
                    <div id="container">
                      <!-- Existing rows will be appended here -->
                    </div>
                    <div class="col-md-12 mt-1">
                      <a href="#" id="addRow" class="btn btn-primary btn-sm mb-4">הוסף עוד<i
                          class="bi bi-plus me-1"></i></a>
                    </div>
                    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
                    <script>
                      // Function to add a new row for meeting times
                      function addMeetingRow() {
                        const container = document.getElementById('container');
                        const row = document.createElement('div');
                        row.classList.add('row', 'gx-3', 'mb-4');

                        row.innerHTML = `
                                                    <div class="col-md-4">
                                                        <label class="small mb-1" for="meeting-date">תאריך</label>
                                                        <input class="form-control" name="dates[]" type="text" placeholder="הכנס תאריך" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="small mb-1" for="meeting-start-time">שעת התחלה</label>
                                                        <input class="form-control" name="start_times[]" type="text" placeholder="הכנס שעת התחלה" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="small mb-1" for="meeting-end-time">שעת סיום</label>
                                                        <input class="form-control" name="end_times[]" type="text" placeholder="הכנס שעת סיום" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button class="btn btn-danger btn-sm" style="margin-top:30px;" onclick="deleteRow(this)">מחק</button>
                                                    </div>
                                                `;

                        container.appendChild(row);

                        flatpickr(row.querySelector('[name="dates[]"]'), {
                          enableTime: false,
                          dateFormat: "d/m/Y",
                          minDate: "today"
                        });

                        flatpickr(row.querySelector('[name="start_times[]"]'), {
                          enableTime: true,
                          noCalendar: true,
                          dateFormat: "H:i",
                          minTime: "08:00",
                          maxTime: "20:00",
                          time_24hr: true
                        });

                        flatpickr(row.querySelector('[name="end_times[]"]'), {
                          enableTime: true,
                          noCalendar: true,
                          dateFormat: "H:i",
                          minTime: "08:00",
                          maxTime: "20:00",
                          time_24hr: true
                        });
                      }

                      document.getElementById('addRow').addEventListener('click', function (event) {
                        event.preventDefault();
                        addMeetingRow();
                      });

                      // Function to delete a row
                      function deleteRow(button) {
                        const row = button.closest('.row.gx-3.mb-4');
                        row.remove();
                      }

                      // Initialize the first row
                      addMeetingRow();
                    </script>
                  </div>
                  <div id="error-message" style="color: red; text-align: center; display: none;">
                  </div>
                  <!-- כפתור שמירת השינויים -->
                  <button class="btn btn-primary mt-2" id="submit" type="submit">שלח בקשה</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>


      <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
          <script src="../../js/cities_and_streets_api.js"></script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script src="../../js/new_request.js"></script>
    <script src="../../js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.enable.co.il/licenses/enable-L2324gcljoe8sb-0617/init.js"></script>


    <script>
      function showOtherOption(selectElement) {
        var otherOptionInput = document.getElementById('otherOption');
        if (selectElement.value === 'other') {
          otherOptionInput.classList.remove('d-none');
        } else {
          otherOptionInput.classList.add('d-none');
          otherOptionInput.value = '';
        }
      }

      function updateSelectValue() {
        const selectElement = document.getElementById('req_type');
        const otherOptionInput = document.getElementById('otherOption');
        if (selectElement.value === 'other' && otherOptionInput.value.trim() !== '') {
          selectElement.options[selectElement.selectedIndex].value = otherOptionInput.value.trim();
          selectElement.options[selectElement.selectedIndex].text = otherOptionInput.value.trim();
        }
      }
    </script>
    
</body>

</html>