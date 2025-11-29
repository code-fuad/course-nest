<?php
// ================== CourseRegistration.php (remove on drop + add on take; day+time clash) ==================
session_start();
include "../database.php"; // must define $conn (mysqli)

if (!$conn) { die("Database connection failed."); }
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function column_exists(mysqli $conn, string $table, string $col): bool {
    $sql = "SELECT 1 FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=? LIMIT 1";
    $st = $conn->prepare($sql);
    if (!$st) return false;
    $st->bind_param("ss", $table, $col);
    $st->execute();
    $ok = (bool)$st->get_result()->fetch_row();
    $st->close();
    return $ok;
}
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

// Seats column (optional)
$seatsCol = null;
if (column_exists($conn, 'section', 'seats'))               $seatsCol = 'seats';
elseif (column_exists($conn, 'section', 'available_seats')) $seatsCol = 'available_seats';

// ---------- Load current selections (ALREADY) BEFORE handling save ----------
$already = []; // [section_id => true]
try {
    $st = $conn->prepare("SELECT section_id FROM takes WHERE student_id = ?");
    $st->bind_param("i", $studentId);
    $st->execute();
    $res = $st->get_result();
    while ($row = $res->fetch_assoc()) $already[(int)$row['section_id']] = true;
    $st->close();
} catch (Throwable $e) {}

// ---------- SAVE handler: apply diff (delete dropped, add newly selected) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_selection'])) {
    $raw = $_POST['section_id'] ?? [];
    if (!is_array($raw)) $raw = [$raw];

    // normalize & unique
    $newSelected = [];
    foreach ($raw as $v) {
        $id = (int)$v;
        if ($id > 0) $newSelected[$id] = true;
    }

    // compute diffs
    $prevIds = array_keys($already);
    $newIds  = array_keys($newSelected);

    $toAdd = array_values(array_diff($newIds, $prevIds));
    $toDel = array_values(array_diff($prevIds, $newIds));

    if (empty($toAdd) && empty($toDel)) {
        js_alert_and_reload("Nothing to save.");
    }

    try {
        $conn->begin_transaction();

        // Prepare statements
        $delTakes = $conn->prepare("DELETE FROM takes WHERE student_id = ? AND section_id = ?");
        $insTakes = $conn->prepare("INSERT INTO takes (student_id, section_id) VALUES (?, ?)");
        if ($seatsCol) {
            // add: check + dec
            $lockSeat = $conn->prepare("SELECT {$seatsCol} AS seats FROM section WHERE section_id = ? FOR UPDATE");
            $decSeat  = $conn->prepare("UPDATE section SET {$seatsCol} = {$seatsCol} - 1 WHERE section_id = ?");
            // delete: inc
            $incSeat  = $conn->prepare("UPDATE section SET {$seatsCol} = {$seatsCol} + 1 WHERE section_id = ?");
        }

        $added = 0; $removed = 0; $skippedAdd = [];

        // 1) Process deletions first (free up seats if configured)
        foreach ($toDel as $sid) {
            // delete row from takes
            $delTakes->bind_param("ii", $studentId, $sid);
            $delTakes->execute();

            // increment seats back
            if ($seatsCol) { $incSeat->bind_param("i", $sid); $incSeat->execute(); }

            $removed++;
        }

        // 2) Process additions
        foreach ($toAdd as $sid) {
            if ($seatsCol) {
                // lock and check seats
                $lockSeat->bind_param("i", $sid);
                $lockSeat->execute();
                $r = $lockSeat->get_result()->fetch_assoc();
                $cur = isset($r['seats']) ? (int)$r['seats'] : 0;
                if ($cur <= 0) { $skippedAdd[] = $sid; continue; }
            }

            // insert into takes
            $insTakes->bind_param("ii", $studentId, $sid);
            $insTakes->execute();

            // decrement seats
            if ($seatsCol) { $decSeat->bind_param("i", $sid); $decSeat->execute(); }

            $added++;
        }

        // Close statements
        if (isset($lockSeat)) $lockSeat->close();
        if (isset($decSeat))  $decSeat->close();
        if (isset($incSeat))  $incSeat->close();
        $delTakes->close();
        $insTakes->close();

        $conn->commit();

        $msg = "Saved.\nAdded: {$added}\nRemoved: {$removed}";
        if ($skippedAdd) $msg .= "\nSkipped (no seats): " . implode(', ', $skippedAdd);
        js_alert_and_reload($msg);

    } catch (Throwable $e) {
        $conn->rollback();
        js_alert_and_reload("Error while saving: " . $e->getMessage());
    }
}

// ---------- Offered sections (+course name) ----------
$sections = [];
try {
    $cols = "s.section_id, s.course_id, s.section_serial, s.time, s.days, s.classroom";
    if ($seatsCol) $cols .= ", s.{$seatsCol} AS seats";
    $sql = "
        SELECT $cols, c.course_name
        FROM section s
        LEFT JOIN course c ON c.course_id = s.course_id
        ORDER BY s.course_id,
                 CASE WHEN s.section_serial REGEXP '^[0-9]+$' THEN CAST(s.section_serial AS UNSIGNED) ELSE 999999 END,
                 s.section_serial
    ";
    $res = $conn->query($sql);
    while ($r = $res->fetch_assoc()) {
        $sid = (int)$r['section_id'];
        $sections[$sid] = [
            'section_id'     => $sid,
            'course_id'      => (string)$r['course_id'],
            'course_name'    => (string)($r['course_name'] ?? ''),
            'section_serial' => (string)$r['section_serial'],
            'time'           => (string)$r['time'],
            'days'           => (string)$r['days'],
            'classroom'      => (string)$r['classroom'],
            'seats'          => isset($r['seats']) ? (int)$r['seats'] : null
        ];
    }
    $res->close();
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Course Registration</title>
  <link rel="stylesheet" href="../18. Course Registration Page/CourseRegistration.css"/>
</head>
<body>

  <!-- ======= KEEPING YOUR MENU EXACTLY AS-IS ======= -->
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
        <div class="dropdown-option" id="registration">
          <img src="registration.png" alt="Registration Icon"><span>Course Registration</span>
        </div>
        <div class="dropdown-option" id="offered" onclick="window.location.href='../19. Offered Course Page/OfferedCourse.php'">
          <img src="offered.png" alt="Offered Icon"><span>Offered Courses</span>
        </div>
        <div class="dropdown-option" id="request" onclick="window.location.href='../20. Course Request/CourseRequest.php'">
          <img src="request.png" alt="Request Icon"><span>Course Request</span>
        </div>
        
      </div>
    </div>


    <a href="../01. Login Page/Login.php" id="LogOut-Link">
      <button id="LogOut-Button"><img src="log.jpg" alt="LogOut Icon"><span>Log Out</span></button>
    </a>
  </div>

  <!-- Optional title your CSS targets -->
  

  <!-- ===== Stacked sections (same markup) ===== -->
  <div class="cr-container">

    <!-- PICKER (top) -->
    <section id="coursePicker" class="cr-section">
      <div class="cr-card">
        <div class="cr-card-title">Course Picker</div>
        <div class="cr-table-wrap">
          <table class="cr-table" aria-label="Offered sections">
            <thead>
              <tr>
                <th>Course ID</th>
                <th>Name</th>
                <th>Sec</th>
                <th>Days</th>
                <th>Time</th>
                <th>Room</th>
                <?php if ($seatsCol): ?><th>Seats</th><?php endif; ?>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="PickerBody"></tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- KEEPER (below) -->
    <section id="courseKeeper" class="cr-section">
      <div class="cr-card">
        <div class="cr-card-title">Course Keeper</div>
        <div class="cr-table-wrap">
          <table class="cr-table" aria-label="Selected sections">
            <thead>
              <tr>
                <th>Course ID</th>
                <th>Name</th>
                <th>Sec</th>
                <th>Days</th>
                <th>Time</th>
                <th>Room</th>
                <?php if ($seatsCol): ?><th>Seats*</th><?php endif; ?>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="KeeperBody"></tbody>
          </table>
        </div>

        <form id="SaveForm" method="POST" class="cr-save">
          <input type="hidden" name="save_selection" value="1"/>
          <div id="SelectedHolder"></div>
          <button type="submit" class="cr-btn">Save</button>
          
        </form>
      </div>
    </section>
  </div>

  <script>
  // ===== Dropdown hover (unchanged) =====
  document.addEventListener("DOMContentLoaded", function () {
    var dropdown = document.getElementById("AdvisingDropdown");
    var hideTimeout;
    if (dropdown) {
      dropdown.addEventListener("mouseenter", function(){ clearTimeout(hideTimeout); dropdown.classList.add("active"); });
      dropdown.addEventListener("mouseleave", function(){ hideTimeout = setTimeout(function(){ dropdown.classList.remove("active"); }, 100); });
    }
  });

  // ===== Data from PHP =====
  const ALL = <?php echo json_encode(array_values($sections), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
  const ALREADY = <?php echo json_encode(array_keys($already)); ?>;
  const SHOW_SEATS = <?php echo $seatsCol ? 'true' : 'false'; ?>;

  // ===== Robust day + time clash detection =====
  function dayMask(daysStr){
    if (!daysStr) return 0;
    let m = 0;
    const s = String(daysStr).toUpperCase();
    // bit mapping: S=1, T=2, M=4, W=8, R=16, A=32  (Sun, Tue, Mon, Wed, Thu, Sat)
    for (const ch of s){
      if (ch==='S') m |= 1;
      else if (ch==='T') m |= 2;
      else if (ch==='M') m |= 4;
      else if (ch==='W') m |= 8;
      else if (ch==='R') m |= 16;
      else if (ch==='A') m |= 32;
    }
    return m;
  }
  function parseTimeToMin(t){
    if (!t) return null;
    let s = String(t).trim().toUpperCase();
    s = s.replace(/\.(\d{2})/g, ':$1').replace(/\s+/g,'');
    const m = /^(\d{1,2}):(\d{2})(AM|PM)?$/.exec(s);
    if (!m) return null;
    let hh = parseInt(m[1],10), mm = parseInt(m[2],10);
    if (m[3] === 'AM' && hh === 12) hh = 0;
    if (m[3] === 'PM' && hh < 12) hh += 12;
    if (hh<0||hh>23||mm<0||mm>59) return null;
    return hh*60 + mm;
  }
  function parseRange(str){
    if (!str) return null;
    let s = String(str).trim().replace(/[–—−]/g,'-').replace(/\s+/g,'');
    const parts = s.split('-');
    if (parts.length !== 2) return null;
    const a = parseTimeToMin(parts[0]);
    const b = parseTimeToMin(parts[1]);
    if (a==null || b==null || b<=a) return null;
    return [a,b];
  }
  function timeOverlap(r1, r2){
    if (!r1 || !r2) return false;
    return (r1[0] < r2[1]) && (r2[0] < r1[1]);
  }

  // Precompute mask and time range once
  const byId = {};
  ALL.forEach(s => {
    s._mask  = dayMask(s.days);
    s._range = parseRange(s.time);
    byId[s.section_id] = s;
  });

  function clashes(a, b){
    if (!a || !b) return false;
    if ((a._mask & b._mask) === 0) return false;  // must share at least one day
    return timeOverlap(a._range, b._range);       // and time overlap
  }

  // ===== DOM handles =====
  const pickerBody = document.getElementById('PickerBody');
  const keeperBody = document.getElementById('KeeperBody');
  const selectedHolder = document.getElementById('SelectedHolder');

  // ===== State =====
  const selected = new Set(ALREADY.map(String));

  // ===== Renderers =====
  function trFor(sec, mode){
    const tr = document.createElement('tr');
    tr.dataset.sid = String(sec.section_id);

    function td(txt){
      const el = document.createElement('td');
      el.textContent = (txt ?? '');
      return el;
    }

    tr.appendChild(td(sec.course_id));
    tr.appendChild(td(sec.course_name || ''));
    tr.appendChild(td(sec.section_serial));
    tr.appendChild(td(sec.days));
    tr.appendChild(td(sec.time));
    tr.appendChild(td(sec.classroom));
    if (SHOW_SEATS){ tr.appendChild(td(sec.seats!=null ? sec.seats : '')); }

    const action = document.createElement('td');
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'cr-mini-btn';
    if (mode === 'picker') {
      btn.textContent = 'Take';
      btn.addEventListener('click', ()=>takeSection(sec.section_id));
    } else {
      btn.textContent = 'Drop';
      btn.addEventListener('click', ()=>dropSection(sec.section_id));
    }
    action.appendChild(btn);
    tr.appendChild(action);

    return tr;
  }

  function renderAll(){
    pickerBody.innerHTML = '';
    keeperBody.innerHTML = '';
    selectedHolder.innerHTML = '';

    const picked = [];
    const available = [];

    ALL.forEach(s => {
      const sid = String(s.section_id);

      if (selected.has(sid)) { picked.push(s); return; }
      if (SHOW_SEATS && s.seats!=null && s.seats<=0) return;

      // hide conflicts immediately: must share a day AND overlap in time with any selected
      for (const sel of selected) {
        const S = byId[sel];
        if (clashes(S, s)) return;
      }

      available.push(s);
    });

    available.forEach((s, i) => {
      const tr = trFor(s, 'picker');
      if (i % 2) tr.classList.add('cr-row-alt');
      pickerBody.appendChild(tr);
    });

    picked.forEach((s, i) => {
      const tr = trFor(s, 'keeper');
      if (i % 2) tr.classList.add('cr-row-alt');
      keeperBody.appendChild(tr);

      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'section_id[]';
      input.value = String(s.section_id);
      selectedHolder.appendChild(input);
    });
  }

  function takeSection(id){ selected.add(String(id)); renderAll(); }
  function dropSection(id){ selected.delete(String(id)); renderAll(); }

  // init
  renderAll();
  </script>
</body>
</html>
