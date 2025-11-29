<?php
session_start();
include "../database.php"; // must create $conn (mysqli) in here

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

// Pull rows for this student from completed_course + course
$courses = [];
$error = null;
try {
    // completed_course(studentID, course_id, grade) + course(course_id, course_name, ...)
    // We'll display Credit=3 and Completed Credit=3 for each row regardless of course table values.
    $sql = "
        SELECT cc.course_id, c.course_name, cc.grade
        FROM completed_course AS cc
        JOIN course AS c ON c.course_id = cc.course_id
        WHERE cc.studentID = ?
        ORDER BY c.course_id
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $courses[] = [
            'course_id'   => $row['course_id'],
            'course_name' => $row['course_name'],
            'grade'       => $row['grade'] ?? '',
            // Force the credits to 3 as requested:
            'credit'      => 3,
            'completed'   => 3,
        ];
    }
    $stmt->close();
} catch (Throwable $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Completed Courses</title>
  <link rel="stylesheet" href="CompletedCourse.css" />
</head>
<body>
  <!-- Left menu (kept consistent with your styles/ids in CompletedCourse.css) -->
  <div id="Menu">
    <a id="Home-Link" href="../16. Student Dashboard/StudentDashboard.php">
      <button id="Home-Button">
        <img src="home.png" alt="Home Icon">
      </button>
    </a>

    <!-- Current page highlighted style is provided by #Course-Div in your CSS -->
    <div id="Course-Div">
      <img src="course.png" alt="Course Icon">
      <span>Completed Course</span>
    </div>

    <div class="dropdown" id="AdvisingDropdown">
      <button id="Advising-Button">
        <img src="advising.png" alt="Advising Icon" id="advising-icon">
        <span>Course Advising</span>
      </button>
      <div class="dropdown-content">
        <div class="dropdown-option" id="registration" onclick="window.location.href='../18. Course Registration Page/CourseRegistration.php'">
          <img src="registration.png" alt="Registration Icon">
          <span>Course Registration</span>
        </div>
        <div class="dropdown-option" id="offered" onclick="window.location.href='../19. Offered Course Page/OfferedCourse.php'">
          <img src="offered.png" alt="Offered Icon">
          <span>Offered Courses</span>
        </div>
        <div class="dropdown-option" id="request" onclick="window.location.href='../20. Course Request/CourseRequest.php'">
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

  <div class="container">
    <header class="header">
      <h1 class="h1">Completed Courses</h1>
    </header>

    <div class="table-wrap">
      <table class="course-table" aria-label="Completed courses">
        <thead>
          <tr>
            <th>Course ID</th>
            <th>Course Name</th>
            <th>Credit</th>
            <th>Completed Credit</th>
            <th>Grade</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($error): ?>
            <tr><td colspan="5" style="color:#ffb3b3;">Error: <?= h($error) ?></td></tr>
          <?php elseif (empty($courses)): ?>
            <tr><td colspan="5">No completed courses found for your account.</td></tr>
          <?php else: ?>
            <?php foreach ($courses as $c): ?>
              <tr>
                <td><?= h($c['course_id']) ?></td>
                <td><?= h($c['course_name']) ?></td>
                <td><?= h($c['credit']) ?></td>
                <td><?= h($c['completed']) ?></td>
                <td><?= h($c['grade']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>

  <script>
    // Keep your dropdown hover behavior consistent with other pages
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
</body>
</html>
