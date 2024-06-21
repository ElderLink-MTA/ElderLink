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

$sql = "SELECT first_name, last_name, email, phone, date_of_birth, picture, language,city,address,address_num FROM users WHERE email = ? AND session_token = ?";
$statement = $conn->prepare($sql);
$statement->bind_param("ss", $email, $session_token);
$statement->execute();
$result = $statement->get_result();

if ($result->num_rows == 0) {
    header("Location: ../../index.html");
    exit();
}

$user = $result->fetch_assoc();

// Set the default image URL and filename
$default_image_url = "https://static.vecteezy.com/system/resources/thumbnails/009/292/244/small/default-avatar-icon-of-social-media-user-vector.jpg";
$default_image_filename = "default-avatar-icon-of-social-media-user-vector.jpg";

// Set the profile picture URL based on whether it's NULL or not
if ($user['picture'] != NULL) {
    // Construct the URL to the profile picture
    $profile_image_url = "../../uploads/" . $user['picture'];
} else {
    // If the filename is NULL, use the default image URL
    $profile_image_url = $default_image_url;
}

// Fetch the user's languages and convert to an array
$user_languages = !empty($user['language']) ? explode(',', $user['language']) : [];

$conn->close();
?>

<!doctype html>
<html lang="he" data-bs-theme="auto">

<head>
  <title>
    ElderLink - פרופיל
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

      <div class="container-xl px-4 mt-2">
        <nav class="nav nav-borders">
          <a class="nav-link active" href="settings_profile.php">פרופיל</a>
        </nav>
        <hr class="mt-0 mb-4">
        <div class="row">

          <div class="col-xl-8">
            <div class="card mb-4">
              <div class="card-header">פרטי החשבון</div>
              <div class="card-body">
                <form method="post" action="update_profile.php" enctype="multipart/form-data">
                  <div class="row gx-3 mb-3">
                    <div class="col-md-6">
                      <label class="small mb-1" for="inputFirstName">שם פרטי</label>
                      <input class="form-control" id="inputFirstName" name="first_name" type="text" maxlength="30"
                        placeholder="הכנס את שמך הפרטי" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                    </div>
                    <div class="col-md-6">
                      <label class="small mb-1" for="inputLastName">שם משפחה</label>
                      <input class="form-control" id="inputLastName" name="last_name" type="text" maxlength="30"
                        placeholder="הכנס את שם המשפחה שלך" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                    </div>
                  </div>
                  <div class="row gx-3 mb-3">
                    <div class="col-md-5">
                      <label class="small mb-1" for="inputCity">עיר מגורים</label>
                      <input class="form-control" id="inputCity" list="cities-data" name="city" maxlength="30"
                        type="text" placeholder="הכנס את עיר מגוריך"
                        value="<?php echo htmlspecialchars($user['city']); ?>">
                      <datalist id="cities-data">
                        <option value="">טוען רשימת ערים...</option>
                      </datalist>
                    </div>


                    <div class="col-md-5">
                      <label class="small mb-1" for="inputAddress">שם רחוב</label>
                      <input class="form-control" id="inputAddress" name="address" type="text" maxlength="30"
                        pattern="[\u0590-\u05FF\s]+" placeholder="הכנס את הרחוב שלך"
                        value="<?php echo htmlspecialchars($user['address']); ?>">
                    </div>
                    <div class="col-md-2">
                      <label class="small mb-1" for="inputAddressNum">מספר רחוב</label>
                      <input class="form-control" id="inputAddressNum" name="address_num" type="text" maxlength="3"
                        pattern="[0-9]*" placeholder="הכנס את מספר הרחוב"
                        value="<?php echo htmlspecialchars($user['address_num']); ?>">
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="small mb-1" for="inputEmailAddress">כתובת דואר אלקטרוני</label>
                    <input class="form-control" id="inputEmailAddress" disabled name="email" type="email"
                      placeholder="הכנס את כתובת הדואר האלקטרוני שלך"
                      value="<?php echo htmlspecialchars($user['email']); ?>">
                  </div>
                  <div class="row gx-3 mb-3">
                    <div class="col-md-6">
                      <label class="small mb-1" for="inputPhone">מספר טלפון</label>
                      <input class="form-control" id="validationTooltiPhone" name="phone" type="tel"
                        placeholder="הכנס את מספר הטלפון שלך" minlength="9" maxlength="12"
                        value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                    <div class="col-md-6">
                      <label class="small mb-1" for="inputBirthday">תאריך לידה</label>
                      <input class="form-control" id="dateOfBirth" type="date" name="date_of_birth"
                        value="<?php echo htmlspecialchars($user['date_of_birth']); ?>">
                    </div>
                    <div class="mb-0 mt-3">
                      <label class="small mb-2">אנא בחרו באילו שפות אתם דוברים</label><br>
                      <select name="languages[]" id="field2" multiple style="width: 285px;">
                        <option value="עברית" <?php if (in_array("עברית", $user_languages)) echo 'selected' ; ?>>עברית
                        </option>
                        <option value="אנגלית" <?php if (in_array("אנגלית", $user_languages)) echo 'selected' ; ?>
                          >English</option>
                        <option value="רוסית" <?php if (in_array("רוסית", $user_languages)) echo 'selected' ; ?>>Pусский
                        </option>
                        <option value="ערבית" <?php if (in_array("ערבית", $user_languages)) echo 'selected' ; ?>>العربية
                        </option>
                        <option value="צרפתית" <?php if (in_array("צרפתית", $user_languages)) echo 'selected' ; ?>
                          >Français</option>
                        <option value="ספרדית" <?php if (in_array("ספרדית", $user_languages)) echo 'selected' ; ?>
                          >Español</option>
                      </select>
                    </div>
                  </div>
                  <button class="btn btn-primary" type="submit">שמור שינויים</button>
                </form>
              </div>
            </div>

          </div>

          <div class="col-xl-4">
            <form method="post" action="update_profile_picture.php" enctype="multipart/form-data">
              <div class="card mb-4 mb-xl-0">
                <div class="card-header">תמונת פרופיל</div>
                <div class="card-body text-center">
                  <img class="img-account-profile rounded-circle mb-2" src="<?php echo $profile_image_url; ?>"
                    alt="Profile Picture" id="profileImage">
                  <div class="small font-italic text-muted mb-4" style="text-align: center;">JPG או PNG בלבד, עד 5
                    מגה-בייט</div>
                  <input class="form-control" id="inputProfilePicture" name="picture" type="file"
                    accept="image/jpeg, image/png">
                  <button class="btn btn-primary mt-4" type="submit">שמור תמונת פרופיל</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
    <script src="../../js/bootstrap.bundle.min.js"></script>
    <script src="../../js/settings-profile.js"></script>
    <script src="../../js/settings-perfrences.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../../js/new_request.js"></script>
    <script src="../../js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.enable.co.il/licenses/enable-L2324gcljoe8sb-0617/init.js"></script>

</body>

</html>