<?php
session_start();
include "../database.php"; // must define $conn (mysqli)

if (!$conn) { die("Database connection failed."); }
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

// Require logged-in teacher
if (empty($_SESSION['teacher_id'])) {
    header("Location: ../01.%20Login%20Page/Login.php");
    exit();
}
$teacherId = (int)$_SESSION['teacher_id'];

// Fetch teacher profile + department
$teacher = [
    'id'          => $teacherId,
    'name'        => 'Teacher',
    'dept'        => '',
    'designation' => '',
    'type'        => '',
    'contact'     => '',
    'gender'      => '',
    'address'     => '',
    'dob'         => ''
];

try {
    $sql = "SELECT 
                t.teacher_id,
                t.teacher_name,
                t.designation,
                t.teacher_type,
                t.contact,
                t.gender,
                t.address,
                t.dob,
                d.department_name
            FROM teacher AS t
            LEFT JOIN department AS d ON d.department_id = t.department_id
            WHERE t.teacher_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $teacherId);
    $stmt->execute();
    if ($row = $stmt->get_result()->fetch_assoc()) {
        $teacher['name']        = (string)($row['teacher_name'] ?? 'Teacher');
        $teacher['dept']        = (string)($row['department_name'] ?? '');
        $teacher['designation'] = (string)($row['designation'] ?? '');
        $teacher['type']        = (string)($row['teacher_type'] ?? '');
        $teacher['contact']     = (string)($row['contact'] ?? '');
        $teacher['gender']      = (string)($row['gender'] ?? '');
        $teacher['address']     = (string)($row['address'] ?? '');
        $teacher['dob']         = (string)($row['dob'] ?? '');
    }
    $stmt->close();
} catch (Throwable $e) {
    // Leave defaults on error
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
    <link rel="stylesheet" href="../25. Teacher Dashboard/TeacherDashboard.css" />
</head>
<body>
    <div id = 'header'>
        <div id = 'headText'>
            <div id = 'date'>
                <h2 id = "date">Todays Time</h2>
            </div>
            <div id = 'name'>
                <span id = 'welcome'>Welcome Back! <?= h($teacher['name']) ?></span>
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
                    <input type="text" id="FullNameField" value="<?= h($teacher['name']) ?>" readonly>
                    
                </div>
            <div id = "StudentID-Row">
                    <div id = "StudentID"> Contact No. </div>
                    <input type="text" id="StudentIDField" value="<?= h($teacher['contact']) ?>" readonly>
                    
                </div>
                <div id = "Department-Row">
                    <div id = "Department"> Department </div>
                    <input type="text" id="DepartmentField" value="<?= h($teacher['dept']) ?>" readonly>
                    
                </div>
                <div id = "Major-Row">
                    <div id = "Major"> Gender </div>
                    <input type="text" id="MajorField" value="<?= h($teacher['gender']) ?>" readonly>
                    
                </div>
                <div id = "CGPA-Row">
                    <div id = "CGPA"> Designation </div>
                    <input type="text" id="CGPAField" value="<?= h($teacher['designation']) ?>" readonly>
                </div>
    
        </div> 
        <div id = 'viewBlock' onclick="window.location.href='../26. Teacher Courses Page/TeacherCourses.php'">
            <img id = 'search' src="advising.png" alt="search">
            <span id = 'searchText'> My Courses </span>
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