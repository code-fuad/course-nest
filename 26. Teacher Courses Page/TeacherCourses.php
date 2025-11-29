<?php
session_start();
include "../database.php"; // must define $conn (mysqli)

if (!$conn) { die("Database connection failed."); }
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Require logged-in teacher
if (empty($_SESSION['teacher_id'])) {
    header("Location: ../01.%20Login%20Page/Login.php");
    exit();
}
$teacherId = (int)$_SESSION['teacher_id'];

// Get teacher name (optional header)
$teacherName = "Teacher";
try {
    $stmt = $conn->prepare("SELECT teacher_name FROM teacher WHERE teacher_id = ?");
    $stmt->bind_param("i", $teacherId);
    $stmt->execute();
    if ($r = $stmt->get_result()->fetch_assoc()) {
        $teacherName = trim((string)$r['teacher_name']) ?: "Teacher";
    }
    $stmt->close();
} catch(Throwable $e) { /* non-fatal */ }

// 1) Sections this teacher teaches
$courses = [];     // section_id => meta + []students
$sectionIds = [];
try {
    $sql = "
        SELECT 
            s.section_id,
            s.section_serial,
            s.time,
            s.days,
            s.classroom,
            s.course_id,
            c.course_name
        FROM teaches t
        JOIN section s     ON s.section_id = t.section_id
        LEFT JOIN course c ON c.course_id = s.course_id
        WHERE t.teacher_id = ?
        ORDER BY 
            s.course_id,
            CASE WHEN s.section_serial REGEXP '^[0-9]+$'
                 THEN CAST(s.section_serial AS UNSIGNED)
                 ELSE 999999 END,
            s.section_serial
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $teacherId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $sid = (int)$row['section_id'];
        $sectionIds[] = $sid;
        $courses[$sid] = [
            'section_id'     => $sid,
            'section_serial' => $row['section_serial'],
            'time'           => $row['time'],
            'days'           => $row['days'],
            'classroom'      => $row['classroom'],
            'course_id'      => $row['course_id'],
            'course_name'    => $row['course_name'] ?? '',
            'students'       => []
        ];
    }
    $stmt->close();
} catch(Throwable $e) {
    // leave empty on error
}

// 2) Students for those sections (single query)
if (!empty($sectionIds)) {
    $placeholders = implode(',', array_fill(0, count($sectionIds), '?'));
    $types = str_repeat('i', count($sectionIds));
    $sql = "
        SELECT 
            tk.section_id,
            st.Student_Id,
            TRIM(COALESCE(st.First_name,'')) AS fn,
            TRIM(COALESCE(st.Last_name,''))  AS ln
        FROM takes tk
        JOIN student st ON st.Student_Id = tk.student_id
        WHERE tk.section_id IN ($placeholders)
        ORDER BY st.Student_Id
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$sectionIds);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $sid = (int)$row['section_id'];
        if (!isset($courses[$sid])) continue;
        $name = trim(($row['fn'] ?? '') . ' ' . ($row['ln'] ?? ''));
        $courses[$sid]['students'][] = [
            'id'   => (int)$row['Student_Id'],
            'name' => ($name !== '') ? $name : '—'
        ];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Teacher · My Courses</title>
  <link rel="stylesheet" href="TeacherCourses.css" />
</head>
<body>
  <button id="home-button" onclick="window.location.href='../25. Teacher Dashboard/TeacherDashboard.php'">
    <img src="home.png" alt="Home Icon">
    
  </button>
 
  <div class="tc-container">
    <header class="tc-header">
      <h1>My Courses</h1>
    </header>

    <?php if (empty($courses)): ?>
      <div class="tc-empty">No assigned sections found.</div>
    <?php else: ?>
      <!-- Flashcards -->
      <div class="tc-card-grid" id="CardsGrid">
        <?php foreach ($courses as $sec): 
          $secId = (int)$sec['section_id'];
        ?>
        <div id="course" class="tc-card course-card"
             data-sec="<?= $secId ?>"
             data-course-id="<?= h($sec['course_id']) ?>"
             data-course-name="<?= h($sec['course_name']) ?>"
             data-section="<?= h($sec['section_serial']) ?>"
             data-days="<?= h($sec['days']) ?>"
             data-time="<?= h($sec['time']) ?>"
             data-room="<?= h($sec['classroom']) ?>">
          <div class="tc-card-head">
            <div class="tc-card-title">
              <div class="tc-course-line">
                <span class="tc-course-id"><?= h($sec['course_id']) ?></span>
                <?php if (!empty($sec['course_name'])): ?>
                  <span class="tc-dot">•</span>
                <?php endif; ?>
              </div>
              <div class="tc-meta">
                <span class="tc-badge">Sec <?= h($sec['section_serial']) ?></span>
                <span class="tc-sep">•</span>
                <span class="tc-badge"><?= h($sec['days']) ?> <?= h($sec['time']) ?></span>
                <span class="tc-sep">•</span>
                <span class="tc-badge">Room <?= h($sec['classroom']) ?></span>
              </div>
            </div>
            <button type="button" class="tc-toggle" aria-hidden="true" tabindex="-1">Select</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- One persistent students table -->
      <section class="tc-selected" style="margin-top:2rem;">
        <div class="tc-students-title" id="SelectedTitle"></div>
        <div class="tc-table-wrap">
          <table class="tc-student-table" aria-label="Enrolled students" id="StudentsTable">
            <thead>
              <tr>
                <th>Student ID</th>
                <th>Name</th>
              </tr>
            </thead>
            <tbody id="StudentsTbody">
              <tr><td colspan="2" style="padding:.9rem 1rem; color: var(--muted);">No course selected.</td></tr>
            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>

    
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function(){
    // All server data to JS
    var CoursesData = <?php
      echo json_encode(array_values($courses), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
    ?>;

    // Build a quick map by section_id for instant lookups
    var bySection = {};
    CoursesData.forEach(function(c){ bySection[String(c.section_id)] = c; });

    var cards = document.querySelectorAll('.course-card');
    var tbody = document.getElementById('StudentsTbody');
    var title = document.getElementById('SelectedTitle');

    function clearActive(){
      cards.forEach(function(c){ c.classList.remove('active'); });
    }

    function setTableForSection(secId, metaFromCard){
      var sec = bySection[String(secId)];
      if (!sec) return;

      // Title text (Course · Name — Sec — Days/Time — Room)
    //   var line = (metaFromCard.courseId || sec.course_id) + 
    //              (metaFromCard.courseName ? ' · ' + metaFromCard.courseName : '') +
    //              ' — Sec ' + (metaFromCard.section || sec.section_serial) +
    //              ' — ' + (metaFromCard.days || sec.days) + ' ' + (metaFromCard.time || sec.time) +
    //              ' — Room ' + (metaFromCard.room || sec.classroom);
    //   title.textContent = line;

      // Rebuild rows
      var rows = sec.students;
      var frag = document.createDocumentFragment();

      if (!rows || rows.length === 0) {
        var tr = document.createElement('tr');
        var td = document.createElement('td');
        td.colSpan = 2;
        td.textContent = 'No students enrolled yet.';
        td.style.padding = '.9rem 1rem';
        td.style.color = 'var(--muted)';
        tr.appendChild(td);
        frag.appendChild(tr);
      } else {
        rows.forEach(function(st){
          var tr = document.createElement('tr');
          var td1 = document.createElement('td');
          var td2 = document.createElement('td');
          td1.textContent = st.id;
          td2.textContent = st.name || '—';
          tr.appendChild(td1); tr.appendChild(td2);
          frag.appendChild(tr);
        });
      }
      tbody.innerHTML = '';
      tbody.appendChild(frag);
    }

    // Attach handlers to cards
    cards.forEach(function(card){
      card.addEventListener('click', function(){
        clearActive();
        card.classList.add('active');
        var secId = card.getAttribute('data-sec');
        setTableForSection(secId, {
          courseId:   card.getAttribute('data-course-id'),
          courseName: card.getAttribute('data-course-name'),
          section:    card.getAttribute('data-section'),
          days:       card.getAttribute('data-days'),
          time:       card.getAttribute('data-time'),
          room:       card.getAttribute('data-room')
        });
      });
    });

    // Optional: auto-select the first card
    if (cards.length) { cards[0].click(); }
  });
  </script>
</body>
</html>
