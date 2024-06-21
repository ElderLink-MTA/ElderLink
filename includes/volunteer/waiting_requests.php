<?php
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['session_token'])) {
    echo "Session expired or invalid.";
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

$req_id = $_POST['req_id'];
$date = $_POST['date'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$email_volunteer = $_POST['email_volunteer'];

$sql = "UPDATE requests SET date = ?, start_time = ?, end_time = ?, email_volunteer = ? WHERE req_id = ?";
$statement = $conn->prepare($sql);
$statement->bind_param("ssssi", $date, $start_time, $end_time, $email_volunteer, $req_id);
$statement->execute();

?>

  <?php
                $request_count = 0;
                $user_email = $_SESSION['email']; 
                $sql_preferences = "SELECT city_p, req_type_p, day_p, language FROM users WHERE email = '$user_email'";
                $result_preferences = $conn->query($sql_preferences);
                $user_preferences = $result_preferences->fetch_assoc();
                $city_p = isset($user_preferences['city_p']) ? $user_preferences['city_p'] : null;
                $req_type_p = isset($user_preferences['req_type_p']) ? $user_preferences['req_type_p'] : null;
                $day_p = isset($user_preferences['day_p']) ? $user_preferences['day_p'] : null;
                $logged_in_user_lang_p = isset($user_preferences['language']) ? $user_preferences['language'] : null;
                $day_of_week_mapping = [
                    'Sunday' => 1, 
                    'Monday' => 2,
                    'Tuesday' => 3,
                    'Wednesday' => 4,
                    'Thursday' => 5,
                    'Friday' => 6,
                    'Saturday' => 7
                ];
                
                $days_filter = null;
                if ($day_p) {
                    $days_p_array = explode(', ', $day_p);
                    $days_filter = array_map(function($day) use ($day_of_week_mapping) {
                        return $day_of_week_mapping[$day];
                    }, $days_p_array);
                    $days_filter = implode(',', $days_filter);
                }
                
                // Build the base SQL query
                $sql = "SELECT r.req_id, r.first_name, r.last_name, r.email, r.phone, r.city, r.street, r.street_num, r.req_type, r.content,
                               GROUP_CONCAT(CONCAT(rt.date, ' &bull; ', DATE_FORMAT(rt.start_time, '%H:%i'), ' - ', DATE_FORMAT(rt.end_time, '%H:%i')) SEPARATOR '<br>') AS time_slots,
                               u.language AS req_user_lang_p
                        FROM requests r
                        LEFT JOIN requests_times rt ON r.req_id = rt.req_id
                        INNER JOIN users u ON r.email = u.email
                        WHERE r.email_volunteer IS NULL
                          AND (rt.date > CURDATE() OR (rt.date = CURDATE() AND rt.start_time >= CURTIME()))";
                
                // Add city filter if provided
                if ($city_p) {
                    $cities = explode(', ', $city_p);
                    $city_conditions = array_map(function($city) {
                        return "r.city = '$city'";
                    }, $cities);
                    $sql .= " AND (" . implode(' OR ', $city_conditions) . ")";
                }
                
                // Add request type filter if provided
                if ($req_type_p) {
                    $req_types = explode(', ', $req_type_p);
                    $other_included = in_array('other', $req_types);
                    $req_type_conditions = array_map(function($req_type) {
                        return "r.req_type = '$req_type'";
                    }, $req_types);
                
                    if ($other_included) {
                        $specified_types = ["'עזרה טכנולוגית'", "'ליווי לרופא'", "'סיוע בקניות'", "'רכישת תרופות'", "'פנאי יחדיו'"];
                        $req_type_conditions[] = "(r.req_type NOT IN (" . implode(', ', $specified_types) . "))";
                    }
                
                    $sql .= " AND (" . implode(' OR ', $req_type_conditions) . ")";
                }
                
                // Add day filter if provided
                if ($days_filter) {
                    $sql .= " AND DAYOFWEEK(rt.date) IN ($days_filter)";
                }
                
                // Add language filter
                $sql .= " GROUP BY r.req_id";
                $result = $conn->query($sql);
                
                // Check if there are any requests
                if ($result->num_rows > 0) {
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                       
                        // Fetch user's picture filename based on email
                        $user_email = $row['email'];
                        $sql_user = "SELECT picture FROM users WHERE email = '$user_email'";
                        $result_user = $conn->query($sql_user);
                        $user_picture = ''; // Initialize user picture variable
                        if ($result_user->num_rows > 0) {
                            $user_row = $result_user->fetch_assoc();
                            $user_picture_filename = $user_row['picture'];
                            $user_picture_path = "../../uploads/" . $user_picture_filename;
                            // Check if the picture file exists
                            if (file_exists($user_picture_path)) {
                                $user_picture = $user_picture_path;
                            }
                        }
                
                 $has_requests = false;
                // Add language filter
                if ($logged_in_user_lang_p && $row['req_user_lang_p']) {
                    $logged_in_user_langs = explode(',', $logged_in_user_lang_p);
                    $req_user_langs = explode(',', $row['req_user_lang_p']);
                
                    $has_common_lang = false;
                    foreach ($logged_in_user_langs as $lang) {
                        $lang = trim($lang); // Remove any leading/trailing whitespace
                        if (in_array($lang, $req_user_langs)) {
                            $has_common_lang = true;
                            break;
                        }
                    }
                
                    if (!$has_common_lang) {
                        continue; // Skip this request if no common languages
                         $has_requests = true;
                    }

                    // Check if there were any requests with matching languages
                  $request_count++;
                }
                        

                    }
                }
                ?>

<!doctype html>
<html lang="he" data-bs-theme="auto">

<head>
    <title>
        ElderLink - בקשות ממתינות
      </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <link rel="icon" href="../../images/favicon.png" type="image/x-icon">
    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/list-groups/">
            <link rel="stylesheet" href="../../css/settings-profile.css">
    <link rel="stylesheet" href="../../css/homepage.css">
    <link rel="stylesheet" href="../../css/waiting_requests.css">
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
                    <a href="homepage_volunteer.php" class="nav__link ">
                        <i class="bi bi-house nav__icon"></i>
                        <span class="nav__text">דף הבית</span>
                    </a>
                    <a href="waiting_requests.php" class="nav__link nav__link--active">
                        <i class="bi-hourglass-split nav__icon"></i>
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
                            <a href="waiting_requests.php" class="nav-link active">
                                <i class="bi bi-hourglass-split me-2" width="20" height="20"></i>
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
                        <a class="nav-link active" href="waiting_requests.php"> 
                            בקשות ממתינות (<?php echo $request_count; ?>)
                        </a>
                    </nav>
                <hr class="mt-0 mb-1">
                <br>
                
                <div class="alert alert-primary" role="alert">
                    <i class="bi bi-heart"></i>
             <strong>מתנדב/ת יקר/ה! </strong>בכל עת, הינך יכול/ה לשנות את סינון הבקשות בדף <a
                    href="settings_preferences.php">העדפות מתנדב.</a></div>
                    
                                <div class="sort-dropdown">
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="sortDropdown" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        סדר לפי
                    </button>
                    <div class="dropdown-menu" aria-labelledby="sortDropdown" style="text-align:right;">
                        <a class="dropdown-item" href="#" onclick="showContent('newest')">בקשות: החדשות ביותר</a>
                        <a class="dropdown-item" href="#" onclick="showContent('oldest')">בקשות: הישנות ביותר</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" onclick="showContent('custom')">מותאם בשבילך <i class="bi bi-magic"></i></a>
                            <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" onclick="showContent('distance')">קרוב אליך <i class="bi bi-geo-alt"></i></a>
                    </div>
                </div>
            </div>

      <div class="outer-container">
        <div id="newest" class="my-3 p-3  wrapper-homepage" >
          <h6 class="border-bottom pb-2 mb-0 gap-3">
            החדשות ביותר
          </h6>
            <div class="list-group-container">
                
             
<?php
$request_count = 0;
$user_email = $_SESSION['email'];
$sql_preferences = "SELECT city_p, req_type_p, day_p, language FROM users WHERE email = '$user_email'";
$result_preferences = $conn->query($sql_preferences);
$user_preferences = $result_preferences->fetch_assoc();
$city_p = isset($user_preferences['city_p']) ? $user_preferences['city_p'] : null;
$req_type_p = isset($user_preferences['req_type_p']) ? $user_preferences['req_type_p'] : null;
$day_p = isset($user_preferences['day_p']) ? $user_preferences['day_p'] : null;
$logged_in_user_lang_p = isset($user_preferences['language']) ? $user_preferences['language'] : null;
$day_of_week_mapping = [
    'Sunday' => 1, 
    'Monday' => 2,
    'Tuesday' => 3,
    'Wednesday' => 4,
    'Thursday' => 5,
    'Friday' => 6,
    'Saturday' => 7
];

$days_filter = null;
if ($day_p) {
    $days_p_array = explode(', ', $day_p);
    $days_filter = array_map(function($day) use ($day_of_week_mapping) {
        return $day_of_week_mapping[$day];
    }, $days_p_array);
    $days_filter = implode(',', $days_filter);
}

// Build the base SQL query
$sql = "SELECT r.req_id, r.first_name, r.last_name, r.email, r.phone, r.city, r.street, r.street_num, r.req_type, r.content,
               GROUP_CONCAT(CONCAT(DATE_FORMAT(rt.date, '%d/%m/%Y'), ' &bull; ', DATE_FORMAT(rt.end_time, '%H:%i'), ' - ', DATE_FORMAT(rt.start_time, '%H:%i')) SEPARATOR '<br>') AS time_slots
,
               u.language AS req_user_lang_p
        FROM requests r
        LEFT JOIN requests_times rt ON r.req_id = rt.req_id
        INNER JOIN users u ON r.email = u.email
        WHERE r.email_volunteer IS NULL
          AND (rt.date > CURDATE() OR (rt.date = CURDATE() AND rt.start_time >= CURTIME()))";

// Add city filter if provided
if ($city_p) {
    $cities = explode(', ', $city_p);
    $city_conditions = array_map(function($city) {
        return "r.city = '$city'";
    }, $cities);
    $sql .= " AND (" . implode(' OR ', $city_conditions) . ")";
}

// Add request type filter if provided
if ($req_type_p) {
    $req_types = explode(', ', $req_type_p);
    $other_included = in_array('other', $req_types);
    $req_type_conditions = array_map(function($req_type) {
        return "r.req_type = '$req_type'";
    }, $req_types);

    if ($other_included) {
        $specified_types = ["'עזרה טכנולוגית'", "'ליווי לרופא'", "'סיוע בקניות'", "'רכישת תרופות'", "'פנאי יחדיו'"];
        $req_type_conditions[] = "(r.req_type NOT IN (" . implode(', ', $specified_types) . "))";
    }

    $sql .= " AND (" . implode(' OR ', $req_type_conditions) . ")";
}

// Add day filter if provided
if ($days_filter) {
    $sql .= " AND DAYOFWEEK(rt.date) IN ($days_filter)";
}

// Add language filter
$sql .= " GROUP BY r.req_id";
$sql .= " ORDER BY r.req_id DESC"; // Order by req_id in descending order
$result = $conn->query($sql);

// Check if there are any requests
if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        // Fetch user's picture filename based on email
        $user_email = $row['email'];
        $sql_user = "SELECT picture FROM users WHERE email = '$user_email'";
        $result_user = $conn->query($sql_user);
        $user_picture = ''; // Initialize user picture variable
        if ($result_user->num_rows > 0) {
            $user_row = $result_user->fetch_assoc();
            $user_picture_filename = $user_row['picture'];
            $user_picture_path = "../../uploads/" . $user_picture_filename;
            // Check if the picture file exists
            if (file_exists($user_picture_path)) {
                $user_picture = $user_picture_path;
            }
        }

        $has_requests = false;
        // Add language filter
        if ($logged_in_user_lang_p && $row['req_user_lang_p']) {
            $logged_in_user_langs = explode(',', $logged_in_user_lang_p);
            $req_user_langs = explode(',', $row['req_user_lang_p']);

            $has_common_lang = false;
            foreach ($logged_in_user_langs as $lang) {
                $lang = trim($lang); // Remove any leading/trailing whitespace
                if (in_array($lang, $req_user_langs)) {
                    $has_common_lang = true;
                    break;
                }
            }

            if (!$has_common_lang) {
                continue; // Skip this request if no common languages
                $has_requests = true;
            }
        }

        // Generate HTML for each request dynamically
        echo '   <div class="list-group pt-3 gap-2" style="width:49%;" >';
        echo '<a href="#" class="list-group-item list-group-item-action d-flex gap-2 p-2 mb-2" aria-current="true" >';
        echo '<img src="' . $user_picture . '" alt="תמונת פרופיל" width="44" height="44" class="rounded-circle flex-shrink-0">';
        echo '<div class="d-flex w-100 justify-content-between">';
        echo '<div class="justify-content-between">';
        echo '<h6>' . $row["first_name"] . ' ' . $row["last_name"] . '</h6>';
        echo '<hr style="width:20%; margin-top: 5px; margin-bottom: 10px;">';
        echo '<small class="opacity-75 text-nowrap text-decoration-underline">פרטי המפגש</small>';
        echo '<div class="d-flex gap-3">';
        echo '<p><b>אופי הבקשה: </b><br>' . $row["req_type"] . '</p>';
        echo '<div class="vr"></div>';
        echo '<p><b>עיר: </b><br>' . $row["city"] . '</p>';
        echo '<div class="vr"></div>';
        echo '<p><b>כתובת: </b><br>' . $row["street"] . ' ' . $row["street_num"] . '</p>';
        echo '</div>';
        echo '<p><b>הערות נוספות: </b>' . $row["content"] . '</p>';
        echo '<div class="expanded-content">';
        echo '<small class="opacity-75 text-nowrap text-decoration-underline">חלונות זמנים אפשריים</small>';
        echo '<select class="form-select mt-2" aria-label="חלונות זמן אפשריים" style="font-size: 0.83rem;" id="time-slots-' . $row["req_id"] . '" onclick="handleTimeSlotClick(event)">';
        echo '<option selected disabled>בחר חלון זמן...</option>';
        echo '<option>' . str_replace('<br>', '</option><option>', $row["time_slots"]) . '</option>';
        echo '</select>';
        echo '<button class="btn btn-success ms-2 mt-4" onclick="showConfirmationPopup(' . $row["req_id"] . ')">אישור הבקשה</button>';
        echo '</div>';
        echo '</div>';
        echo '<small class="opacity-75 text-nowrap">בקשה #' . $row["req_id"] . '</small>';
        echo '</div>';
        echo '</a>';
        echo '</div>';
    }
}
?>

                                </div>
          </div>
                  <div id="oldest" class="my-3 p-3  wrapper-homepage" style="display: none;">
            
            
          <h6 class="border-bottom pb-2 mb-0 gap-3">
              
            הישנות ביותר
          </h6>
            <div class="list-group-container ">
             
<?php

$request_count = 0;
$user_email = $_SESSION['email']; 
$sql_preferences = "SELECT city_p, req_type_p, day_p, language FROM users WHERE email = '$user_email'";
$result_preferences = $conn->query($sql_preferences);
$user_preferences = $result_preferences->fetch_assoc();
$city_p = isset($user_preferences['city_p']) ? $user_preferences['city_p'] : null;
$req_type_p = isset($user_preferences['req_type_p']) ? $user_preferences['req_type_p'] : null;
$day_p = isset($user_preferences['day_p']) ? $user_preferences['day_p'] : null;
$logged_in_user_lang_p = isset($user_preferences['language']) ? $user_preferences['language'] : null;
$day_of_week_mapping = [
    'Sunday' => 1, 
    'Monday' => 2,
    'Tuesday' => 3,
    'Wednesday' => 4,
    'Thursday' => 5,
    'Friday' => 6,
    'Saturday' => 7
];

$days_filter = null;
if ($day_p) {
    $days_p_array = explode(', ', $day_p);
    $days_filter = array_map(function($day) use ($day_of_week_mapping) {
        return $day_of_week_mapping[$day];
    }, $days_p_array);
    $days_filter = implode(',', $days_filter);
}

// Build the base SQL query
$sql = "SELECT r.req_id, r.first_name, r.last_name, r.email, r.phone, r.city, r.street, r.street_num, r.req_type, r.content,
               GROUP_CONCAT(CONCAT(DATE_FORMAT(rt.date, '%d/%m/%Y'), ' &bull; ', DATE_FORMAT(rt.end_time, '%H:%i'), ' - ', DATE_FORMAT(rt.start_time, '%H:%i')) SEPARATOR '<br>') AS time_slots,
               u.language AS req_user_lang_p
        FROM requests r
        LEFT JOIN requests_times rt ON r.req_id = rt.req_id
        INNER JOIN users u ON r.email = u.email
        WHERE r.email_volunteer IS NULL
          AND (rt.date > CURDATE() OR (rt.date = CURDATE() AND rt.start_time >= CURTIME()))";

// Add city filter if provided
if ($city_p) {
    $cities = explode(', ', $city_p);
    $city_conditions = array_map(function($city) {
        return "r.city = '$city'";
    }, $cities);
    $sql .= " AND (" . implode(' OR ', $city_conditions) . ")";
}

// Add request type filter if provided
if ($req_type_p) {
    $req_types = explode(', ', $req_type_p);
    $other_included = in_array('other', $req_types);
    $req_type_conditions = array_map(function($req_type) {
        return "r.req_type = '$req_type'";
    }, $req_types);

    if ($other_included) {
        $specified_types = ["'עזרה טכנולוגית'", "'ליווי לרופא'", "'סיוע בקניות'", "'רכישת תרופות'", "'פנאי יחדיו'"];
        $req_type_conditions[] = "(r.req_type NOT IN (" . implode(', ', $specified_types) . "))";
    }

    $sql .= " AND (" . implode(' OR ', $req_type_conditions) . ")";
}

// Add day filter if provided
if ($days_filter) {
    $sql .= " AND DAYOFWEEK(rt.date) IN ($days_filter)";
}

// Add language filter
$sql .= " GROUP BY r.req_id";
$sql .= " ORDER BY r.req_id asc"; // Order by req_id in descending order
$result = $conn->query($sql);

// Check if there are any requests
if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        // Fetch user's picture filename based on email
        $user_email = $row['email'];
        $sql_user = "SELECT picture FROM users WHERE email = '$user_email'";
        $result_user = $conn->query($sql_user);
        $user_picture = ''; // Initialize user picture variable
        if ($result_user->num_rows > 0) {
            $user_row = $result_user->fetch_assoc();
            $user_picture_filename = $user_row['picture'];
            $user_picture_path = "../../uploads/" . $user_picture_filename;
            // Check if the picture file exists
            if (file_exists($user_picture_path)) {
                $user_picture = $user_picture_path;
            }
        }

        $has_requests = false;
        // Add language filter
        if ($logged_in_user_lang_p && $row['req_user_lang_p']) {
            $logged_in_user_langs = explode(',', $logged_in_user_lang_p);
            $req_user_langs = explode(',', $row['req_user_lang_p']);

            $has_common_lang = false;
            foreach ($logged_in_user_langs as $lang) {
                $lang = trim($lang); // Remove any leading/trailing whitespace
                if (in_array($lang, $req_user_langs)) {
                    $has_common_lang = true;
                    break;
                }
            }

            if (!$has_common_lang) {
                continue; // Skip this request if no common languages
                $has_requests = true;
            }
        }

        // Generate HTML for each request dynamically
        echo '   <div class="list-group pt-3 gap-2" style="width:49%;">';
        echo '<a href="#" class="list-group-item list-group-item-action d-flex gap-2 p-2 mb-2 " aria-current="true">';
        echo '<img src="' . $user_picture . '" alt="תמונת פרופיל" width="44" height="44" class="rounded-circle flex-shrink-0">';
        echo '<div class="d-flex w-100 justify-content-between">';
        echo '<div class="justify-content-between">';
        echo '<h6>' . $row["first_name"] . ' ' . $row["last_name"] . '</h6>';
        echo '<hr style="width:20%; margin-top: 5px; margin-bottom: 10px;">';
        echo '<small class="opacity-75 text-nowrap text-decoration-underline">פרטי המפגש</small>';
        echo '<div class="d-flex gap-3">';
        echo '<p><b>אופי הבקשה: </b><br>' . $row["req_type"] . '</p>';
        echo '<div class="vr"></div>';
        echo '<p><b>עיר: </b><br>' . $row["city"] . '</p>';
        echo '<div class="vr"></div>';
        echo '<p><b>כתובת: </b><br>' . $row["street"] . ' ' . $row["street_num"] . '</p>';
        echo '</div>';
        echo '<p><b>הערות נוספות: </b>' . $row["content"] . '</p>';
        echo '<div class="expanded-content">';
        echo '<small class="opacity-75 text-nowrap text-decoration-underline">חלונות זמנים אפשריים</small>';
        echo '<select class="form-select mt-2" aria-label="חלונות זמן אפשריים" style="font-size: 0.83rem;" id="time-slots-' . $row["req_id"] . '" onclick="handleTimeSlotClick(event)">';
        echo '<option selected disabled>בחר חלון זמן...</option>';
        echo '<option>' . str_replace('<br>', '</option><option>', $row["time_slots"]) . '</option>';
        echo '</select>';
        echo '<button class="btn btn-success ms-2 mt-4" onclick="showConfirmationPopup(' . $row["req_id"] . ')">אישור הבקשה</button>';
        echo '</div>';
        echo '</div>';
        echo '<small class="opacity-75 text-nowrap">בקשה #' . $row["req_id"] . '</small>';
        echo '</div>';
        echo '</a>';
        echo '</div>';
    }
}
?>

                                </div>
       

        </div>
                          <div id="distance" class="my-3 p-3  wrapper-homepage" style="display: none;">
            
            
          <h6 class="border-bottom pb-2 mb-0 gap-3">
              
            קרוב אליך
          </h6>
            <div class="list-group-container ">
             
             
             
<?php
$request_count = 0;
$user_email = $_SESSION['email']; // Assuming the user's email is stored in the session
$sql_preferences = "SELECT city_p, req_type_p, day_p, language, city, address, address_num FROM users WHERE email = '$user_email'";
$result_preferences = $conn->query($sql_preferences);
$user_preferences = $result_preferences->fetch_assoc();
$city_p = isset($user_preferences['city_p']) ? $user_preferences['city_p'] : null;
$req_type_p = isset($user_preferences['req_type_p']) ? $user_preferences['req_type_p'] : null;
$day_p = isset($user_preferences['day_p']) ? $user_preferences['day_p'] : null;
$logged_in_user_lang_p = isset($user_preferences['language']) ? $user_preferences['language'] : null;

$user_city = $user_preferences['city'];
$user_address = $user_preferences['address'];
$user_address_num = $user_preferences['address_num'];
$user_full_address = "$user_address $user_address_num, $user_city";
$day_of_week_mapping = [
    'Sunday' => 1, 
    'Monday' => 2,
    'Tuesday' => 3,
    'Wednesday' => 4,
    'Thursday' => 5,
    'Friday' => 6,
    'Saturday' => 7
];

$days_filter = null;
if ($day_p) {
    $days_p_array = explode(', ', $day_p);
    $days_filter = array_map(function($day) use ($day_of_week_mapping) {
        return $day_of_week_mapping[$day];
    }, $days_p_array);
    $days_filter = implode(',', $days_filter);
}

// Build the base SQL query
$sql = "SELECT r.req_id, r.first_name, r.last_name, r.email, r.phone, r.city, r.street, r.street_num, r.req_type, r.content,
               GROUP_CONCAT(CONCAT(DATE_FORMAT(rt.date, '%d/%m/%Y'), ' &bull; ', DATE_FORMAT(rt.end_time, '%H:%i'), ' - ', DATE_FORMAT(rt.start_time, '%H:%i')) SEPARATOR '<br>') AS time_slots,
               u.language AS req_user_lang_p
        FROM requests r
        LEFT JOIN requests_times rt ON r.req_id = rt.req_id
        INNER JOIN users u ON r.email = u.email
        WHERE r.email_volunteer IS NULL
          AND (rt.date > CURDATE() OR (rt.date = CURDATE() AND rt.start_time >= CURTIME()))";

// Add city filter if provided
if ($city_p) {
    $cities = explode(', ', $city_p);
    $city_conditions = array_map(function($city) {
        return "r.city = '$city'";
    }, $cities);
    $sql .= " AND (" . implode(' OR ', $city_conditions) . ")";
}

// Add request type filter if provided
if ($req_type_p) {
    $req_types = explode(', ', $req_type_p);
    $other_included = in_array('other', $req_types);
    $req_type_conditions = array_map(function($req_type) {
        return "r.req_type = '$req_type'";
    }, $req_types);

    if ($other_included) {
        $specified_types = ["'עזרה טכנולוגית'", "'ליווי לרופא'", "'סיוע בקניות'", "'רכישת תרופות'", "'פנאי יחדיו'"];
        $req_type_conditions[] = "(r.req_type NOT IN (" . implode(', ', $specified_types) . "))";
    }

    $sql .= " AND (" . implode(' OR ', $req_type_conditions) . ")";
}

// Add day filter if provided
if ($days_filter) {
    $sql .= " AND DAYOFWEEK(rt.date) IN ($days_filter)";
}

// Add language filter
$sql .= " GROUP BY r.req_id";

$result = $conn->query($sql);

$requests = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}

function calculateDistance($origin, $destination) {
    $apiKey = 'AIzaSyB2fyypixqUHF2IJnHMqO1o1ACghv6wd08';
    $origin = urlencode($origin);
    $destination = urlencode($destination);
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$origin&destinations=$destination&key=$apiKey";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if ($data['status'] === 'OK') {
        $distance = $data['rows'][0]['elements'][0]['distance']['value']; // distance in meters
        return $distance;
    }

    return PHP_INT_MAX; // return a large number if unable to calculate distance
}

foreach ($requests as &$request) {
    $request_address = "{$request['street']} {$request['street_num']}, {$request['city']}";
    $request['distance'] = calculateDistance($user_full_address, $request_address);
}

usort($requests, function($a, $b) {
    return $a['distance'] - $b['distance'];
});

// Generate HTML for each request dynamically
foreach ($requests as $row) {
    $user_email = $row['email'];
    $sql_user = "SELECT picture FROM users WHERE email = '$user_email'";
    $result_user = $conn->query($sql_user);
    $user_picture = ''; // Initialize user picture variable
    if ($result_user->num_rows > 0) {
        $user_row = $result_user->fetch_assoc();
        $user_picture_filename = $user_row['picture'];
        $user_picture_path = "../../uploads/" . $user_picture_filename;
        // Check if the picture file exists
        if (file_exists($user_picture_path)) {
            $user_picture = $user_picture_path;
        }
    }

    $has_requests = false;
    // Add language filter
    if ($logged_in_user_lang_p && $row['req_user_lang_p']) {
        $logged_in_user_langs = explode(',', $logged_in_user_lang_p);
        $req_user_langs = explode(',', $row['req_user_lang_p']);

        $has_common_lang = false;
        foreach ($logged_in_user_langs as $lang) {
            $lang = trim($lang); // Remove any leading/trailing whitespace
            if (in_array($lang, $req_user_langs)) {
                $has_common_lang = true;
                break;
            }
        }

        if (!$has_common_lang) {
            continue; // Skip this request if no common languages
            $has_requests = true;
        }
    }

        echo '   <div class="list-group pt-3 gap-2" style="width:49%;">';
        echo '<a href="#" class="list-group-item list-group-item-action d-flex gap-2 p-2 mb-2 " aria-current="true">';
        echo '<img src="' . $user_picture . '" alt="תמונת פרופיל" width="44" height="44" class="rounded-circle flex-shrink-0">';
        echo '<div class="d-flex w-100 justify-content-between">';
        echo '<div class="justify-content-between">';
        echo '<h6>' . $row["first_name"] . ' ' . $row["last_name"] . '</h6>';
        echo '<hr style="width:20%; margin-top: 5px; margin-bottom: 10px;">';
        echo '<small class="opacity-75 text-nowrap text-decoration-underline">פרטי המפגש</small>';
        echo '<div class="d-flex gap-3">';
        echo '<p><b>אופי הבקשה: </b><br>' . $row["req_type"] . '</p>';
        echo '<div class="vr"></div>';
        echo '<p><b>עיר: </b><br>' . $row["city"] . '</p>';
        echo '<div class="vr"></div>';
        echo '<p><b>כתובת: </b><br>' . $row["street"] . ' ' . $row["street_num"] . '</p>';
        echo '</div>';
        echo '<p><b>הערות נוספות: </b>' . $row["content"] . '</p>';
        echo '<div class="expanded-content">';
        echo '<small class="opacity-75 text-nowrap text-decoration-underline">חלונות זמנים אפשריים</small>';
        echo '<select class="form-select mt-2" aria-label="חלונות זמן אפשריים" style="font-size: 0.83rem;" id="time-slots-' . $row["req_id"] . '" onclick="handleTimeSlotClick(event)">';
        echo '<option selected disabled>בחר חלון זמן...</option>';
        echo '<option>' . str_replace('<br>', '</option><option>', $row["time_slots"]) . '</option>';
        echo '</select>';
        echo '<button class="btn btn-success ms-2 mt-4" onclick="showConfirmationPopup(' . $row["req_id"] . ')">אישור הבקשה</button>';
        echo '</div>';
        echo '</div>';
        echo '<small class="opacity-75 text-nowrap">בקשה #' . $row["req_id"] . '</small>';
        echo '</div>';
        echo '</a>';
        echo '</div>';
}
?>

                                </div>
       

        </div>
              <div id="custom" class="my-3 p-3   wrapper-homepage" style="display: none;">
          <h6 class="border-bottom pb-2 mb-0 gap-3">

           מותאם בשבילך
          </h6>
             <div class="list-group-container ">

<?php
$request_count = 0;
$user_email = $_SESSION['email']; // Assuming the user's email is stored in the session
$sql_preferences = "SELECT city_p, req_type_p, day_p, language FROM users WHERE email = '$user_email'";
$result_preferences = $conn->query($sql_preferences);
$user_preferences = $result_preferences->fetch_assoc();
$city_p = isset($user_preferences['city_p']) ? $user_preferences['city_p'] : null;
$req_type_p = isset($user_preferences['req_type_p']) ? $user_preferences['req_type_p'] : null;
$day_p = isset($user_preferences['day_p']) ? $user_preferences['day_p'] : null;
$logged_in_user_lang_p = isset($user_preferences['language']) ? $user_preferences['language'] : null;
$day_of_week_mapping = [
    'Sunday' => 1, 'Monday' => 2, 'Tuesday' => 3, 'Wednesday' => 4,
    'Thursday' => 5, 'Friday' => 6, 'Saturday' => 7
];

$days_filter = null;
if ($day_p) {
    $days_p_array = explode(', ', $day_p);
    $days_filter = array_map(function($day) use ($day_of_week_mapping) {
        return $day_of_week_mapping[$day];
    }, $days_p_array);
    $days_filter = implode(',', $days_filter);
}

// Fetch past requests for the logged-in user
$sql_past_requests = "SELECT req_type, city FROM requests WHERE email_volunteer = '$user_email'";
$result_past_requests = $conn->query($sql_past_requests);

$past_requests = [];
if ($result_past_requests->num_rows > 0) {
    while ($row = $result_past_requests->fetch_assoc()) {
        $past_requests[] = $row;
    }
}

// Function to score requests based on past preferences
function score_request($request, $past_requests) {
    $score = 0;
    foreach ($past_requests as $past_request) {
        if ($request['req_type'] == $past_request['req_type']) {
            $score += 2; // Higher weight for matching request type
        }
        if ($request['city'] == $past_request['city']) {
            $score += 1;
        }
    }
    return $score;
}

// Build the base SQL query
$sql = "SELECT r.req_id, r.first_name, r.last_name, r.email, r.phone, r.city, r.street, r.street_num, r.req_type, r.content,
              GROUP_CONCAT(CONCAT(DATE_FORMAT(rt.date, '%d/%m/%Y'), ' &bull; ', DATE_FORMAT(rt.end_time, '%H:%i'), ' - ', DATE_FORMAT(rt.start_time, '%H:%i')) SEPARATOR '<br>') AS time_slots,
               u.language AS req_user_lang_p
        FROM requests r
        LEFT JOIN requests_times rt ON r.req_id = rt.req_id
        INNER JOIN users u ON r.email = u.email
        WHERE r.email_volunteer IS NULL
          AND (rt.date > CURDATE() OR (rt.date = CURDATE() AND rt.start_time >= CURTIME()))";

// Add city filter if provided
if ($city_p) {
    $cities = explode(', ', $city_p);
    $city_conditions = array_map(function($city) {
        return "r.city = '$city'";
    }, $cities);
    $sql .= " AND (" . implode(' OR ', $city_conditions) . ")";
}

// Add request type filter if provided
if ($req_type_p) {
    $req_types = explode(', ', $req_type_p);
    $other_included = in_array('other', $req_types);
    $req_type_conditions = array_map(function($req_type) {
        return "r.req_type = '$req_type'";
    }, $req_types);

    if ($other_included) {
        $specified_types = ["'עזרה טכנולוגית'", "'ליווי לרופא'", "'סיוע בקניות'", "'רכישת תרופות'", "'פנאי יחדיו'"];
        $req_type_conditions[] = "(r.req_type NOT IN (" . implode(', ', $specified_types) . "))";
    }

    $sql .= " AND (" . implode(' OR ', $req_type_conditions) . ")";
}

// Add day filter if provided
if ($days_filter) {
    $sql .= " AND DAYOFWEEK(rt.date) IN ($days_filter)";
}

// Add language filter
$sql .= " GROUP BY r.req_id";
$sql .= " ORDER BY r.req_id DESC"; // Order by req_id in descending order
$result = $conn->query($sql);

// Check if there are any requests
if ($result->num_rows > 0) {
    $requests = [];
    while ($row = $result->fetch_assoc()) {
        // Calculate the score for each request
        $row['score'] = score_request($row, $past_requests);
        $requests[] = $row;
    }

    // Sort requests by score in descending order
    usort($requests, function($a, $b) {
        return $b['score'] - $a['score'];
    });

    // Output data of each row
    foreach ($requests as $row) {
        // Fetch user's picture filename based on email
        $user_email = $row['email'];
        $sql_user = "SELECT picture FROM users WHERE email = '$user_email'";
        $result_user = $conn->query($sql_user);
        $user_picture = ''; // Initialize user picture variable
        if ($result_user->num_rows > 0) {
            $user_row = $result_user->fetch_assoc();
            $user_picture_filename = $user_row['picture'];
            $user_picture_path = "../../uploads/" . $user_picture_filename;
            // Check if the picture file exists
            if (file_exists($user_picture_path)) {
                $user_picture = $user_picture_path;
            }
        }

        // Add language filter
        if ($logged_in_user_lang_p && $row['req_user_lang_p']) {
            $logged_in_user_langs = explode(',', $logged_in_user_lang_p);
            $req_user_langs = explode(',', $row['req_user_lang_p']);

            $has_common_lang = false;
            foreach ($logged_in_user_langs as $lang) {
                $lang = trim($lang); // Remove any leading/trailing whitespace
                if (in_array($lang, $req_user_langs)) {
                    $has_common_lang = true;
                    break;
                }
            }

            if (!$has_common_lang) {
                continue; // Skip this request if no common languages
            }
        }

        // Generate HTML for each request dynamically
        echo '   <div class="list-group pt-3 gap-2" style="width:49%;">';
        echo '<a href="#" class="list-group-item list-group-item-action d-flex gap-2 p-2 mb-2 " aria-current="true">';
        echo '<img src="' . $user_picture . '" alt="תמונת פרופיל" width="44" height="44" class="rounded-circle flex-shrink-0">';
        echo '<div class="d-flex w-100 justify-content-between">';
        echo '<div class="justify-content-between">';
        echo '<h6>' . $row["first_name"] . ' ' . $row["last_name"] . '</h6>';
        echo '<hr style="width:20%; margin-top: 5px; margin-bottom: 10px;">';
        echo '<small class="opacity-75 text-nowrap text-decoration-underline">פרטי המפגש</small>';
        echo '<div class="d-flex gap-3">';
        echo '<p><b>אופי הבקשה: </b><br>' . $row["req_type"] . '</p>';
        echo '<div class="vr"></div>';
        echo '<p><b>עיר: </b><br>' . $row["city"] . '</p>';
        echo '<div class="vr"></div>';
        echo '<p><b>כתובת: </b><br>' . $row["street"] . ' ' . $row["street_num"] . '</p>';
        echo '</div>';
        echo '<p><b>הערות נוספות: </b>' . $row["content"] . '</p>';
        echo '<div class="expanded-content">';
        echo '<small class="opacity-75 text-nowrap text-decoration-underline">חלונות זמנים אפשריים</small>';
        echo '<select class="form-select mt-2" aria-label="חלונות זמן אפשריים" style="font-size: 0.83rem;" id="time-slots-' . $row["req_id"] . '" onclick="handleTimeSlotClick(event)">';
        echo '<option selected disabled>בחר חלון זמן...</option>';
        echo '<option>' . str_replace('<br>', '</option><option>', $row["time_slots"]) . '</option>';
        echo '</select>';
        echo '<button class="btn btn-success ms-2 mt-4" onclick="showConfirmationPopup(' . $row["req_id"] . ')">אישור הבקשה</button>';
        echo '</div>';
        echo '</div>';
        echo '<small class="opacity-75 text-nowrap">בקשה #' . $row["req_id"] . '</small>';
        echo '</div>';
        echo '</a>';
         echo '</div>';
    }
}

// Close the database connection
$conn->close();
?>
           
            </div>
            </div>
            </div>
        </div>
        </div>
    </main>



    <script src="../../js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>
    <script src="../../js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.enable.co.il/licenses/enable-L2324gcljoe8sb-0617/init.js"></script>

    <script>
        // JavaScript function to handle expanding div
        function toggleExpand(event) {
            event.preventDefault();
            const listItem = this.closest('.list-group-item');
            listItem.classList.toggle('expanded');
        }

        // Add event listener to the entire list-group-item box
        document.querySelectorAll('.list-group-item').forEach(box => {
            box.addEventListener('click', toggleExpand);
        });
        
function showConfirmationPopup(reqId) {
    // Display a confirmation dialog
    var confirmation = confirm("האם את/ה בטוח/ה שאת/ה רוצה לאשר את הבקשה?");
    if (confirmation) {
        // Get the selected time slot from the dropdown
        var selectedTimeSlot = document.getElementById('time-slots-' + reqId).value;

        // Send an AJAX request to the server
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "update_request.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                // Handle the response from the server
                var response = xhr.responseText;
                alert(response);
                // Check if the response indicates success
                if (response.trim() === "הבקשה שובצה בהצלחה!") {
                    // Redirect the user to placed_requests.php
                    window.location.href = "placed_requests.php";
                }
            }
        };
        // Prepare the data to be sent
        var data = "req_id=" + reqId + "&selected_time_slot=" + encodeURIComponent(selectedTimeSlot);
        // Send the request
        xhr.send(data);
    }
}


function handleTimeSlotClick(event) {
    event.stopPropagation(); // Prevent event from bubbling up
}



function showContent(id) {
    // Hide all sections
    document.querySelectorAll('.wrapper-homepage').forEach(function(el) {
        el.style.display = 'none';
    });
    
    // Show the selected section
    document.getElementById(id).style.display = 'block';
}

    </script>
</body>

</html>