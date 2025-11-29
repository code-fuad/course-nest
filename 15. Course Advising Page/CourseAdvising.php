<?php
session_start();
include "../database.php"; // must define $conn (mysqli)

// Optional dev debugging
// ini_set('display_errors', 1); error_reporting(E_ALL);

if (!$conn) {
    die("Database connection failed.");
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/* ------------------ Advising status: read & toggle ------------------ */
$advising_on = false;

// Ensure we have a row; if not, create 'no'
try {
    $res = $conn->query("SELECT status FROM advising_status LIMIT 1");
    if ($row = $res->fetch_assoc()) {
        $advising_on = (strtolower($row['status']) === 'yes');
    } else {
        $stmt = $conn->prepare("INSERT INTO advising_status (status) VALUES ('no')");
        $stmt->execute();
        $stmt->close();
        $advising_on = false;
    }
    $res->free();
} catch (Throwable $e) {
    // fall through; will show error in table if needed
}

// Handle AJAX toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_advising'])) {
    try {
        $new = $advising_on ? 'no' : 'yes';
        // Try update existing row
        $stmt = $conn->prepare("UPDATE advising_status SET status = ?");
        $stmt->bind_param("s", $new);
        $stmt->execute();
        $updated = $stmt->affected_rows;
        $stmt->close();

        // If no row was updated (table empty), insert one
        if ($updated === 0) {
            $stmt = $conn->prepare("INSERT INTO advising_status (status) VALUES (?)");
            $stmt->bind_param("s", $new);
            $stmt->execute();
            $stmt->close();
        }

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'status' => $new]);
        exit;
    } catch (Throwable $e) {
        header('Content-Type: application/json', true, 500);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

/* ------------------ Sections list ------------------ */
$sections = [];
$error = null;

try {
    // Pull every offered section. This auto-reflects any new inserts in `section`.
    $sql = "
        SELECT s.course_id, s.section_serial, s.time, s.days, s.classroom
        FROM section AS s
        ORDER BY 
            s.course_id,
            CASE WHEN s.section_serial REGEXP '^[0-9]+$' 
                 THEN CAST(s.section_serial AS UNSIGNED) 
                 ELSE 999999 END,
            s.section_serial
    ";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $sections[] = $row;
    }
    $res->free();
} catch (Throwable $e) {
    $error = $e->getMessage();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Course Advising · Offered Sections</title>
  <link rel="stylesheet" href="CourseAdvising.css" />
</head>
<body>
  <button id="home-button" onclick="window.location.href='../09. Admin Dashboard/AdminDashboard.php'">
        <img src="home.png" alt="Home Icon">
    </button>
  <!-- kept intact -->
   <div id = 'advising'>
            <div id = 'text'> Advising Enable </div> 
            <button id="Login-Button" type="submit"> Turn On </button>
        </div>

  <div id="list" class="container">
    <header class="header">
      <h1>Offered Sections</h1>
      <p>Course ID • Section • Time • Days • Room</p>

      <!-- Search bar styled to look like table rows (inline; no CSS file changes) -->
      <div style="max-width: 800px; margin: .8rem auto 0;">
        <input
          id="Search-Input"
          type="text"
          placeholder="Search Course / Section / Time / Days / Room"
          aria-label="Search offered sections"
          style="
            width:100%;
            padding:.9rem 1.1rem;             /* same as tbody td padding */
            background: var(--row);           /* matches odd row background */
            color:#fff;                       /* table text color */
            border:1px solid var(--line);     /* same line color */
            border-radius: 8px;               /* subtle rounding inside wrapper */
            outline: none;
            font: inherit;                    /* match page font */
            box-shadow:none;
          "
          onfocus="this.style.border='1px solid white'"
          onblur="this.style.border='1px solid var(--line)'"
          onmouseover="this.style.border='1px solid white'"
          onmouseout="this.style.border=(this===document.activeElement?'1px solid white':'1px solid var(--line)')"
        />
      </div>
    </header>

    <div class="table-wrap">
      <table class="course-table" aria-label="Offered course sections">
        <thead>
          <tr>
            <th data-col="course">Course ID</th>
            <th data-col="section">Section</th>
            <th data-col="time">Time</th>
            <th data-col="days">Days</th>
            <th data-col="room">Room</th>
          </tr>
        </thead>
        <tbody id="SectionsBody">
          <?php if ($error): ?>
            <tr>
              <td colspan="5" style="color:#ffb3b3;">Error loading sections: <?= h($error) ?></td>
            </tr>
          <?php elseif (empty($sections)): ?>
            <tr>
              <td colspan="5">No sections found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($sections as $s): ?>
              <tr>
                <td><?= h($s['course_id']) ?></td>
                <td><?= h($s['section_serial']) ?></td>
                <td><?= h($s['time']) ?></td>
                <td><?= h($s['days']) ?></td>
                <td><?= h($s['classroom']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>

  <script>
  // ---- Advising toggle (unchanged logic) ----
  (function(){
    var advisingOn = <?php echo $advising_on ? 'true' : 'false'; ?>;
    var btn = document.getElementById('Login-Button');

    function syncLabel(){
      if (!btn) return;
      btn.textContent = advisingOn ? ' Turn Off ' : ' Turn On ';
    }
    syncLabel();

    if (btn) {
      btn.addEventListener('click', function(ev){
        ev.preventDefault();
        var fd = new FormData();
        fd.append('toggle_advising','1');
        fetch(window.location.href, {
          method: 'POST',
          body: fd,
          credentials: 'same-origin'
        })
        .then(function(r){ return r.json(); })
        .then(function(j){
          if (j && j.ok) {
            advisingOn = (j.status === 'yes');
            syncLabel();
          } else {
            alert('Failed to toggle advising.');
          }
        })
        .catch(function(){
          alert('Network error while toggling advising.');
        });
      });
    }
  })();

  // ---- Client-side search & sort for the table ----
  (function(){
    var tbody = document.getElementById('SectionsBody');
    if (!tbody) return;

    // Cache original rows
    var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
    var currentSort = { key: null, dir: 1 }; // 1 = asc, -1 = desc

    function text(el) { return (el.textContent || '').trim(); }

    function parseTimeToMinutes(t) {
      // Ex: "09:40 AM - 11:10 AM" -> minutes of start time
      if (!t) return -1;
      var parts = t.split('-')[0].trim(); // start time portion
      var m = parts.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
      if (!m) return -1;
      var h = parseInt(m[1],10), min = parseInt(m[2],10), ap = m[3].toUpperCase();
      if (ap === 'PM' && h !== 12) h += 12;
      if (ap === 'AM' && h === 12) h = 0;
      return h*60 + min;
    }

    function daysRank(d) {
      // Order days roughly: S < M < T < W < R (Thu) < F < A (Sat)
      var map = { 'S':1,'M':2,'T':3,'W':4,'R':5,'F':6,'A':7 };
      if (!d) return 9999;
      var sum = 0, count = 0;
      for (var i=0;i<d.length;i++){
        var ch = d[i].toUpperCase();
        if (map[ch]) { sum += map[ch]; count++; }
      }
      return count ? (sum / count) : 9999;
    }

    function cmp(a, b, key) {
      if (key === 'course') {
        return text(a.cells[0]).localeCompare(text(b.cells[0]), undefined, {numeric:true, sensitivity:'base'});
      }
      if (key === 'section') {
        var av = text(a.cells[1]), bv = text(b.cells[1]);
        var an = /^\d+$/.test(av) ? parseInt(av,10) : Number.MAX_SAFE_INTEGER;
        var bn = /^\d+$/.test(bv) ? parseInt(bv,10) : Number.MAX_SAFE_INTEGER;
        if (an !== bn) return an - bn;
        return av.localeCompare(bv, undefined, {numeric:true, sensitivity:'base'});
      }
      if (key === 'time') {
        var ta = parseTimeToMinutes(text(a.cells[2]));
        var tb = parseTimeToMinutes(text(b.cells[2]));
        return ta - tb;
      }
      if (key === 'days') {
        var da = daysRank(text(a.cells[3]));
        var db = daysRank(text(b.cells[3]));
        if (da !== db) return da - db;
        return text(a.cells[3]).localeCompare(text(b.cells[3]));
      }
      if (key === 'room') {
        return text(a.cells[4]).localeCompare(text(b.cells[4]), undefined, {numeric:true, sensitivity:'base'});
      }
      return 0;
    }

    function sortBy(key) {
      if (currentSort.key === key) currentSort.dir *= -1;
      else { currentSort.key = key; currentSort.dir = 1; }

      var sorted = rows.slice().sort(function(r1, r2){
        return currentSort.dir * cmp(r1, r2, key);
      });

      var frag = document.createDocumentFragment();
      sorted.forEach(function(r){ frag.appendChild(r); });
      tbody.innerHTML = '';
      tbody.appendChild(frag);

      // Update header aria-sort (no CSS changes)
      var ths = document.querySelectorAll('thead th[data-col]');
      ths.forEach(function(th){ th.removeAttribute('aria-sort'); });
      var active = document.querySelector('thead th[data-col="'+key+'"]');
      if (active) active.setAttribute('aria-sort', currentSort.dir === 1 ? 'ascending' : 'descending');
    }

    // Click handlers for headers
    var ths = document.querySelectorAll('thead th[data-col]');
    ths.forEach(function(th){
      th.style.cursor = 'pointer'; // small visual hint; CSS file untouched
      th.addEventListener('click', function(){
        var key = th.getAttribute('data-col');
        sortBy(key);
      });
    });

    // Search filter
    var searchInput = document.getElementById('Search-Input');
    function applyFilter() {
      var q = (searchInput.value || '').trim().toLowerCase();
      rows.forEach(function(r){
        if (!q) { r.style.display = ''; return; }
        var hit = false;
        for (var i=0;i<r.cells.length;i++){
          var t = (r.cells[i].textContent || '').toLowerCase();
          if (t.indexOf(q) !== -1) { hit = true; break; }
        }
        r.style.display = hit ? '' : 'none';
      });
    }
    if (searchInput) {
      searchInput.addEventListener('input', applyFilter);
    }
  })();
  </script>
</body>
</html>
