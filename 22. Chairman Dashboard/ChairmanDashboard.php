<?php
session_start();
include "../database.php"; // must define $conn (mysqli)

if (!$conn) { die("Database connection failed."); }
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// Require logged-in chairman
if (empty($_SESSION['chair_id'])) {
    header("Location: ../01.%20Login%20Page/Login.php");
    exit();
}
$chairId = (int)$_SESSION['chair_id'];

// Fetch chairman profile + department + (optional) programs as "Major"
$chair = [
    'id'   => $chairId,
    'name' => 'Chairman',
    'dept' => '',
    'programs' => ''
];

try {
    $sql = "SELECT 
                c.chair_id,
                c.chair_name,
                d.department_name,
                c.gender,
                c.contact,
                GROUP_CONCAT(DISTINCT dp.programs ORDER BY dp.programs SEPARATOR ', ') AS programs
            FROM chairman AS c
            LEFT JOIN department AS d  ON d.department_id = c.department_id
            LEFT JOIN dept_program AS dp ON dp.department_id = c.department_id
            WHERE c.chair_id = ?
            GROUP BY c.chair_id, c.chair_name, d.department_name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $chairId);
    $stmt->execute();
    if ($row = $stmt->get_result()->fetch_assoc()) {
        $chair['name']     = (string)($row['chair_name'] ?? 'Chairman');
        $chair['dept']     = (string)($row['department_name'] ?? '');
        $chair['gender'] = (string)($row['gender'] ?? '');
        $chair['phone'] = (string)($row['contact'] ?? '');
    }
    $stmt->close();
} catch (Throwable $e) {
    // non-fatal; leave defaults
}

// helper for safe echo
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../22. Chairman Dashboard/ChairmanDashboard.css" />
</head>
<body>
    <div id = 'header'>
        <div id = 'headText'>
            <div id = 'date'>
                <h2 id = "date">Todays Time</h2>
            </div>
            <div id = 'name'>
                <span id = 'welcome'>Welcome Back! <?= h($chair['name']) ?></span>
            </div>
        </div>
        <div id = 'picture'>
            <img id = 'profile' src="profile.png" alt="Profile Picture">
        </div>
    </div>
    <div id = 'body'>
        <div id = 'infoBlock'>
            <div id = "FullName-Row">
                    <div id = "FullName"> Full Name </div>
                    <input type="text" id="FullNameField" value="<?= h($chair['name']) ?>" readonly>
                    
                </div>
            <div id = "StudentID-Row">
                    <div id = "StudentID"> Contact No. </div>
                    <input type="text" id="StudentIDField" value="<?= h($chair['phone']) ?>" readonly>
                    
                </div>
                <div id = "Department-Row">
                    <div id = "Department"> Department </div>
                    <input type="text" id="DepartmentField" value="<?= h($chair['dept']) ?>" readonly>
                    
                </div>
                <div id = "Major-Row">
                    <div id = "Major"> Gender </div>
                    <input type="text" id="MajorField" value="<?= h($chair['gender']) ?>" readonly>
                    
                </div>
        </div> 
        <div id = 'viewBlock' onclick="window.location.href='../23. Chairman View Page/ChairView.php'">
            <img id = 'search' src="view.png" alt="search">
            <span id = 'searchText'> View Profile </span>
        </div>
        <div id = 'requestBlock' onclick="window.location.href='../24. Request View Page/RequestView.php'">
            <img id = 'request' src="advising.png" alt="request">
            <span id = 'requestText'> Course Requests </span>
        </div>
        <button id="logOutBtn" onclick="window.location.href='../01. Login Page/Login.php'">
            <img src="log.jpg" alt="Log Out">
        </button>
    </div>
    <script>
      (function(){
        function updateDateTime(){
          var el = document.getElementById('date');
          if(!el) return;
          var now = new Date();
          var dateStr = now.toLocaleDateString(undefined, {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
          });
          var timeStr = now.toLocaleTimeString(undefined, {
            hour: '2-digit', minute: '2-digit', second: '2-digit'
          });
          el.textContent = dateStr + ' • ' + timeStr;
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);
      })();
    </script>

</body>
</html>