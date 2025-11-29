<?php
session_start();
include "../database.php"; // must define $conn (mysqli)
if (!$conn) { die("Database connection failed."); }
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// tiny helper to check column availability
function column_exists(mysqli $conn, string $table, string $column): bool {
    $sql = "SELECT 1 FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    $stmt->bind_param("ss", $table, $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $ok  = (bool)$res->fetch_row();
    $stmt->close();
    return $ok;
}

// Require logged-in chairman
if (empty($_SESSION['chair_id'])) {
    header("Location: ../01.%20Login%20Page/Login.php");
    exit();
}
$chairId = (int)$_SESSION['chair_id'];

// 1) Pull chairman -> department
$deptId = 0;
$deptName = '';
$chairName = 'Chairman';
try {
    $sql = "SELECT c.chair_name, c.department_id, d.department_name
            FROM chairman c
            LEFT JOIN department d ON d.department_id = c.department_id
            WHERE c.chair_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $chairId);
    $stmt->execute();
    if ($row = $stmt->get_result()->fetch_assoc()) {
        $chairName = trim((string)($row['chair_name'] ?? 'Chairman')) ?: 'Chairman';
        $deptId    = (int)($row['department_id'] ?? 0);
        $deptName  = (string)($row['department_name'] ?? '');
    }
    $stmt->close();
} catch (Throwable $e) {
    // keep defaults (but table will be empty)
}

// 2) Build demand list (course requests) for chairman's department
$demand = []; // each row: ['course_id'=>..., 'course_name'=>..., 'cnt'=>...]
$mode = 'none';

if ($deptId) {
    try {
        if (column_exists($conn, 'course', 'department_id')) {
            // Preferred: filter by course.department_id
            $mode = 'course';
            $sql = "
                SELECT r.course_id, c.course_name, COUNT(*) AS cnt
                FROM request r
                JOIN course c ON c.course_id = r.course_id
                WHERE c.department_id = ?
                GROUP BY r.course_id, c.course_name
                ORDER BY r.course_id
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $deptId);
        } elseif (column_exists($conn, 'section', 'department_id')) {
            // Fallback: filter by sections belonging to chairman's department
            $mode = 'section';
            $sql = "
                SELECT r.course_id, c.course_name, COUNT(DISTINCT CONCAT(r.student_id,'|',r.course_id)) AS cnt
                FROM request r
                JOIN course  c ON c.course_id  = r.course_id
                JOIN section s ON s.course_id  = c.course_id
                WHERE s.department_id = ?
                GROUP BY r.course_id, c.course_name
                ORDER BY r.course_id
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $deptId);
        } else {
            // Last resort: consider requests coming from students of this department
            // (If student.Dept_ID missing, fall back to latest application.dept_name)
            $mode = 'student';
            $sql = "
                SELECT r.course_id, c.course_name, COUNT(*) AS cnt
                FROM request r
                JOIN course c ON c.course_id = r.course_id
                LEFT JOIN student s ON s.Student_Id = r.student_id
                LEFT JOIN application a ON a.Application_Id = (
                    SELECT a2.Application_Id
                    FROM application a2
                    WHERE TRIM(a2.First_name) = TRIM(s.First_name)
                      AND TRIM(a2.Last_name)  = TRIM(s.Last_name)
                    ORDER BY a2.Application_Id DESC
                    LIMIT 1
                )
                WHERE (s.Dept_ID = ?)
                   OR ((s.Dept_ID IS NULL OR s.Dept_ID = 0) AND a.dept_name = ?)
                GROUP BY r.course_id, c.course_name
                ORDER BY r.course_id
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $deptId, $deptName);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $demand[] = [
                'course_id'   => (string)$r['course_id'],
                'course_name' => (string)($r['course_name'] ?? ''),
                'cnt'         => (int)$r['cnt']
            ];
        }
        $stmt->close();
    } catch (Throwable $e) {
        // leave empty
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Request View</title>
  <link rel="stylesheet" href="RequestView.css"/>
</head>
<body>
  <!-- Keep your home button (CSS already provided) -->
  <button id="home-button" onclick="window.location.href='../22. Chairman Dashboard/ChairmanDashboard.php'">
    <img src="home.png" alt="Home Icon">
  </button>

  <div style="max-width:1100px; margin: 3vh auto; color:#fff; padding: 0 1rem;">
    <header style="margin-bottom:1.2rem;">
      <h2 style="margin:0; font-weight:800;">Requests — <?= h($deptName ?: 'Department') ?></h2>
      
      <!-- tiny helper text about the mode used (hidden from UI if you want) -->
      <div style="opacity:.55; font-size:.9rem; margin-top:.3rem;">
      </div>
    </header>

    <!-- Table: Course demand -->
    <div style="
      background: rgba(255,255,255,.03);
      border: 1px solid rgba(255,255,255,.14);
      border-radius: 12px;
      overflow:hidden;
      box-shadow: 0 10px 24px rgba(0,0,0,.25);
    ">
      <table id="DemandTable" style="width:100%; border-collapse:collapse; color:#fff;">
        <thead>
          <tr style="background: #6D54B5;">
            <th data-col="course"   style="text-align:left; padding:.9rem 1rem; cursor:pointer;">Course ID</th>
            <th data-col="name"     style="text-align:left; padding:.9rem 1rem; cursor:pointer;">Course Name</th>
            <th data-col="demand"   style="text-align:left; padding:.9rem 1rem; cursor:pointer;">Requests</th>
          </tr>
        </thead>
        <tbody id="DemandBody">
          <?php if (empty($demand)): ?>
            <tr><td colspan="3" style="padding:.9rem 1rem; color:#c9c3da;">No requests found for this department.</td></tr>
          <?php else: ?>
            <?php $i=0; foreach ($demand as $row): $i++; ?>
              <tr style="background: <?= ($i%2? 'rgba(255,255,255,.04)' : 'rgba(255,255,255,.06)') ?>;">
                <td style="padding:.75rem 1rem;"><?= h($row['course_id']) ?></td>
                <td style="padding:.75rem 1rem;"><?= h($row['course_name']) ?></td>
                <td style="padding:.75rem 1rem;"><?= h($row['cnt']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>

  <script>
  // Client-side sorting: click headers to sort (asc/desc).
  (function(){
    var tbody = document.getElementById('DemandBody');
    if (!tbody) return;

    function getRows(){
      return Array.prototype.slice.call(tbody.querySelectorAll('tr'));
    }
    function cellText(tr, idx){
      var td = tr.cells[idx];
      return (td ? (td.textContent || '').trim() : '');
    }
    function cmpStr(a,b){
      return a.localeCompare(b, undefined, {numeric:true, sensitivity:'base'});
    }
    function cmpNum(a,b){
      return (parseInt(a,10) || 0) - (parseInt(b,10) || 0);
    }

    var headers = document.querySelectorAll('#DemandTable thead th[data-col]');
    var current = { key: null, dir: 1 }; // 1 asc, -1 desc

    function sortBy(key){
      var colIdx = (key === 'course') ? 0 : (key === 'name' ? 1 : 2);
      var numeric = (key === 'demand');
      if (current.key === key) current.dir *= -1; else { current.key = key; current.dir = 1; }

      var rows = getRows();
      rows.sort(function(r1, r2){
        var a = cellText(r1, colIdx);
        var b = cellText(r2, colIdx);
        var base = numeric ? cmpNum(a,b) : cmpStr(a,b);
        return current.dir * base;
      });

      var frag = document.createDocumentFragment();
      rows.forEach(function(r){ frag.appendChild(r); });
      tbody.innerHTML = '';
      tbody.appendChild(frag);

      // aria-sort hint
      headers.forEach(function(h){ h.removeAttribute('aria-sort'); });
      var active = document.querySelector('#DemandTable thead th[data-col="'+key+'"]');
      if (active) active.setAttribute('aria-sort', current.dir === 1 ? 'ascending' : 'descending');
    }

    headers.forEach(function(h){
      h.addEventListener('click', function(){ sortBy(h.getAttribute('data-col')); });
    });

    // Default: show "most demanded" first
    sortBy('demand'); // asc
    sortBy('demand'); // then desc
  })();
  </script>
</body>
</html>
