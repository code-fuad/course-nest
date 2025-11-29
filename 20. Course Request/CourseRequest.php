<?php
session_start();
include "../database.php"; // must define $conn (mysqli)

if (!$conn) { die("Database connection failed."); }
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function js_alert_and_reload(string $msg): never {
    $self = $_SERVER['REQUEST_URI'] ?? $_SERVER['PHP_SELF'];
    $target = strtok($self, '?');
    echo "<script>alert(" . json_encode($msg) . "); window.location.href=" . json_encode($target) . ";</script>";
    exit;
}

// Require logged-in student
if (empty($_SESSION['student_id'])) {
    header("Location: ../01.%20Login%20Page/Login.php");
    exit();
}
$studentId = (int)$_SESSION['student_id'];

// Handle submission: insert into request(student_id, course_id)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = $_POST['course_id'] ?? [];
    if (!is_array($raw)) $raw = [$raw];

    // Normalize & de-dup (keep only non-empty)
    $wanted = [];
    foreach ($raw as $v) {
        $v = strtoupper(trim((string)$v));
        if ($v !== '') $wanted[$v] = true; // unique set by key
    }
    $wanted = array_keys($wanted);

    if (empty($wanted)) {
        js_alert_and_reload("Please choose at least one course.");
    }

    try {
        // Validate that these course_ids exist in course table
        // (Your schema: course(course_id, course_name, ...) )
        $placeholders = implode(',', array_fill(0, count($wanted), '?'));
        $types = str_repeat('s', count($wanted));
        $sql = "SELECT course_id FROM course WHERE course_id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$wanted);
        $stmt->execute();
        $res = $stmt->get_result();
        $valid = [];
        while ($r = $res->fetch_assoc()) $valid[] = $r['course_id'];
        $stmt->close();

        if (empty($valid)) {
            js_alert_and_reload("No valid course ids were selected.");
        }

        // Insert only (student_id, course_id) combos that do NOT already exist
        $conn->begin_transaction();

        $check = $conn->prepare("SELECT 1 FROM request WHERE student_id = ? AND course_id = ? LIMIT 1");
        $ins   = $conn->prepare("INSERT INTO request (student_id, course_id) VALUES (?, ?)");

        $added = 0; $skipped = [];
        foreach ($valid as $cid) {
            $check->bind_param("is", $studentId, $cid);
            $check->execute();
            $exists = $check->get_result()->fetch_row();
            if ($exists) { $skipped[] = $cid; continue; }

            $ins->bind_param("is", $studentId, $cid);
            $ins->execute();
            $added++;
        }

        $check->close();
        $ins->close();
        $conn->commit();

        $msg = "Request saved.\nAdded: {$added}";
        if (!empty($skipped)) $msg .= "\nSkipped (already requested): " . implode(', ', $skipped);
        js_alert_and_reload($msg);

    } catch (Throwable $e) {
        if ($conn->errno) { $conn->rollback(); }
        js_alert_and_reload("Error: " . $e->getMessage());
    }
}

// Load courses for suggestions (datalist); show code + name
$courses = [];
try {
    $res = $conn->query("SELECT course_id, course_name FROM course ORDER BY course_id");
    while ($row = $res->fetch_assoc()) $courses[] = $row;
    $res->free();
} catch (Throwable $e) {
    // still render page even if suggestions fail
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Course Request</title>
  <link rel="stylesheet" href="../20. Course Request/CourseRequest.css" />
</head>
<body>
  <!-- Left menu (ids kept for your CSS) -->
  <div id="Menu">
    <a href="../16. Student Dashboard/StudentDashboard.php" id="Home-Link">
      <button id="Home-Button"><img src="home.png" alt="Home Icon"></button>
    </a>

    <button id="Course-Button" onclick="window.location.href='../17. Completed Course Page/CompletedCourse.php'">
      <img src="course.png" alt="Course Icon"><span>Completed Course</span>
    </button>

    <div class="dropdown" id="AdvisingDropdown">
      <button id="Advising-Button">
        <img src="advising.png" alt="Advising Icon" id="advising-icon">
        <span>Course Advising</span>
      </button>
      <div class="dropdown-content">
        <div class="dropdown-option" id="registration" onclick="window.location.href='../18. Course Registration Page/CourseRegistration.php'">
          <img src="registration.png" alt="Registration Icon"><span>Course Registration</span>
        </div>
        <div class="dropdown-option" id="offered" onclick="window.location.href='../19. Offered Course Page/OfferedCourse.php'">
          <img src="offered.png" alt="Offered Icon"><span>Offered Courses</span>
        </div>
        <div class="dropdown-option" id="request">
          <img src="request.png" alt="Request Icon"><span>Course Request</span>
        </div>
        
      </div>
    </div>

    <a href="../01. Login Page/Login.php" id="LogOut-Link">
      <button id="LogOut-Button"><img src="log.jpg" alt="LogOut Icon"><span>Log Out</span></button>
    </a>
  </div>

  <!-- Page title (keeps your id for styling) -->
  <div id="title">Course Request</div>

  <!-- Central input area -->
  <div id="course">
    <!-- datalist with suggestions (keeps #course input look) -->
    <datalist id="course-list">
      <?php foreach ($courses as $c): ?>
        <option value="<?= h($c['course_id']) ?>" label="<?= h($c['course_name']) ?>"></option>
      <?php endforeach; ?>
    </datalist>

    <!-- at least one input row (JS will add more) -->
    <input type="text" list="course-list" name="course_id[]" placeholder="Course ID (e.g., CSE101)" />

    <!-- plus button (image-only, CSS already styles it) -->
    <button id="addButton" type="button" title="Add more">
      <img id="plusImage" src="plus.png" alt="Add">
    </button>

    <!-- submit -->
    <button id="Apply-Button" type="button">Send Request</button>
  </div>

  <div id="Approval-Row"></div>

  <script>
  // Keep your dropdown hover behavior (same pattern you used elsewhere)
  document.addEventListener("DOMContentLoaded", function () {
    var dropdown = document.getElementById("AdvisingDropdown");
    var hideTimeout;
    if (dropdown) {
      dropdown.addEventListener("mouseenter", function(){ clearTimeout(hideTimeout); dropdown.classList.add("active"); });
      dropdown.addEventListener("mouseleave", function(){ hideTimeout = setTimeout(function(){ dropdown.classList.remove("active"); }, 100); });
    }
  });

  // Add more fields with the ➕ button
  (function(){
    var addBtn = document.getElementById('addButton');
    var container = document.getElementById('course');

    function addField(){
      var inp = document.createElement('input');
      inp.type = 'text';
      inp.setAttribute('list', 'course-list');  // use the same datalist
      inp.name = 'course_id[]';
      inp.placeholder = 'Course ID (e.g., CSE101)';
      container.insertBefore(inp, addBtn); // insert before the plus button to keep alignment
    }

    if (addBtn) addBtn.addEventListener('click', addField);
  })();

  // Submit via hidden form (no HTML/CSS changes)
  (function(){
    var submitBtn = document.getElementById('Apply-Button');

    function gatherCourseIds(){
      var inputs = document.querySelectorAll('#course input[list="course-list"][name="course_id[]"]');
      var values = [];
      inputs.forEach(function(inp){
        var v = (inp.value || '').trim().toUpperCase();
        if (v) values.push(v);
      });
      return values;
    }

    function postCourses(values){
      // Build hidden form
      var form = document.createElement('form');
      form.method = 'POST';
      form.style.display = 'none';

      values.forEach(function(v){
        var i = document.createElement('input');
        i.type = 'hidden';
        i.name = 'course_id[]';
        i.value = v;
        form.appendChild(i);
      });

      document.body.appendChild(form);
      form.submit();
    }

    if (submitBtn) {
      submitBtn.addEventListener('click', function(e){
        e.preventDefault();
        var vals = gatherCourseIds();
        if (!vals.length) { alert('Please choose at least one course.'); return; }
        postCourses(vals);
      });
    }
  })();
  </script>
</body>
</html>
