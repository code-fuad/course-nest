<?php
session_start();
include "../database.php"; // must define $conn (mysqli)

if (!$conn) { die("Database connection failed."); }
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Require logged-in student
if (empty($_SESSION['student_id'])) {
    header("Location: ../01.%20Login%20Page/Login.php");
    exit();
}
$studentId = (int)$_SESSION['student_id'];

// 1) Get student name for Welcome line + to match in application table
$fullName = "Student";
$first = ""; $last = "";
try {
    $sql = "SELECT TRIM(First_name) AS fn, TRIM(Last_name) AS ln
            FROM student
            WHERE Student_Id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    if ($row = $stmt->get_result()->fetch_assoc()) {
        $first = (string)$row['fn'];
        $last  = (string)$row['ln'];
        $fullName = trim($first . ' ' . $last) ?: "Student";
    }
    $stmt->close();
} catch(Throwable $e) {
    // keep defaults
}

// 2) Find dept_name & major_name from application by matching name
$deptName = "";
$major    = "";
if ($first !== "" || $last !== "") {
    try {
        $sql = "SELECT dept_name, major_name
                FROM application
                WHERE TRIM(First_name) = ? AND TRIM(Last_name) = ?
                ORDER BY Application_Id DESC
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $first, $last);
        $stmt->execute();
        if ($a = $stmt->get_result()->fetch_assoc()) {
            $deptName = (string)($a['dept_name'] ?? "");
            $major    = (string)($a['major_name'] ?? "");
        }
        $stmt->close();
    } catch (Throwable $e) {
        // leave blank if not found/error
    }
}

/* 3) CGPA: pull completed_course for this student and compute average
      Scale: A(4.0), A-(3.7), B+(3.3), B(3.0), B-(2.7), C+(2.3), C(2.0), C-(1.7), D+(1.3), D(1.0), F(0)
      Unknown/empty grades count as 0 and are included in the course count, per your instruction.
*/
$cgpa_display = "0.00";
try {
    $sql = "
        SELECT 
            COUNT(*) AS total_courses,
            COALESCE(SUM(
                CASE UPPER(TRIM(grade))
                    WHEN 'A'  THEN 4.0
                    WHEN 'A-' THEN 3.7
                    WHEN 'B+' THEN 3.3
                    WHEN 'B'  THEN 3.0
                    WHEN 'B-' THEN 2.7
                    WHEN 'C+' THEN 2.3
                    WHEN 'C'  THEN 2.0
                    WHEN 'C-' THEN 1.7
                    WHEN 'D+' THEN 1.3
                    WHEN 'D'  THEN 1.0
                    WHEN 'F'  THEN 0.0
                    ELSE 0.0
                END
            ), 0) AS total_points
        FROM completed_course
        WHERE studentID = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $agg = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $total_courses = (int)($agg['total_courses'] ?? 0);
    $total_points  = (float)($agg['total_points'] ?? 0.0);
    $cgpa = $total_courses > 0 ? ($total_points / $total_courses) : 0.0;
    $cgpa_display = number_format($cgpa, 2, '.', '');
} catch (Throwable $e) {
    // keep default 0.00 on error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="StudentDashboard.css" />
</head>
<body>
    <div id = "Menu">
        <div id="Home-Div">
            <img src="home.png" alt="Home Icon">
        </div>

        <button id="Course-Button" onclick="window.location.href='../17. Completed Course Page/CompletedCourse.php'">
            <img src="course.png" alt="Course Icon">
            <span>Completed Course</span>
        </button>

        <div class="dropdown" id="AdvisingDropdown">
            <button id="Advising-Button">
                <img src="advising.png" alt="Advising Icon" id="advising-icon">
                <span>Course Advising</span>
            </button>
        <div class="dropdown-content">
            <div class="dropdown-option" id = "registration" onclick="window.location.href='../18. Course Registration Page/CourseRegistration.php'">
                <img src="registration.png" alt="Registration Icon" >
                <span>Course Registration</span>
            </div>
            <div class="dropdown-option" id = "offered" onclick="window.location.href='../19. Offered Course Page/OfferedCourse.php'">
                <img src="offered.png" alt="Offered Icon">
                <span>Offered Courses</span>
            </div>
            <div class="dropdown-option" id = "request" onclick="window.location.href='../20. Course Request/CourseRequest.php'">
                <img src="request.png" alt="Request Icon">
                <span>Course Request</span>
            </div>
        </div>
        </div>
        <a href="../01. Login Page/Login.php" id="LogOut-Link">
            <button id="LogOut-Button">
                <img src="log.jpg" alt="LogOut Icon"> 
                <span>Log Out</span>
            </button>
        </a>

    </div>
    
    <div id = "Info-Block">
        <div id = "Welcome-Board">
            <div id = "text-board">
                <div id = "space"></div>
                <div id = "date">Todays date </div>
                <div id = "Welcome-Row">
                    <div id = "Welcome">Welcome Back!</div>
                    <div id = "Name"><?= h($fullName) ?></div>
                </div>
            </div>
            <div id = "profile-picture">
                <div id = "space2"></div>
                <img src="profile.png" alt="Profile Icon" id = "profile-pic">
            </div>
        </div>
        <div id = "Content-Block">
            
            <div id = "Personal-Block">
                <div id = "FullName-Row">
                    <div id = "FullName"> Full Name </div>
                    <input type="text" id="FullNameField" value="<?= h($fullName) ?>" readonly>
                </div>
                <div id = "StudentID-Row">
                    <div id = "StudentID"> Student ID </div>
                    <input type="text" id="StudentIDField" value="<?= h($studentId) ?>" readonly>
                </div>
                <div id = "Department-Row">
                    <div id = "Department"> Department </div>
                    <input type="text" id="DepartmentField" value="<?= h($deptName) ?>" readonly>
                </div>
                <div id = "Major-Row">
                    <div id = "Major"> Major </div>
                    <input type="text" id="MajorField" value="<?= h($major) ?>" readonly>
                </div>
                <div id = "CGPA-Row">
                    <div id = "CGPA"> CGPA </div>
                    <input type="text" id="CGPAField" value="<?= h($cgpa_display) ?>" readonly>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // keep your dropdown behavior
        document.addEventListener("DOMContentLoaded", function () {
            const dropdown = document.getElementById("AdvisingDropdown");
            let hideTimeout;

            dropdown.addEventListener("mouseenter", () => {
                clearTimeout(hideTimeout);
                dropdown.classList.add("active");
            });

            dropdown.addEventListener("mouseleave", () => {
                hideTimeout = setTimeout(() => {
                    dropdown.classList.remove("active");
                }, 100);
            });
        });
    </script>

    <!-- Actual date & time in #date (no CSS/HTML changes) -->
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
