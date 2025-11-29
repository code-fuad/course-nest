<?php
session_start();
include "../database.php"; // must define $conn (mysqli)
if (!$conn) { die("Database connection failed."); }
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Require logged-in chairman
if (empty($_SESSION['chair_id'])) {
    header("Location: ../01.%20Login%20Page/Login.php");
    exit();
}
$chairId = (int)$_SESSION['chair_id'];

// 1) Pull chairman + department
$deptId = null;
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
    // If this fails, we can't filter correctly
}

// 2) Teachers of this department
$teachers = [];
if ($deptId) {
    try {
        $sql = "SELECT 
                    t.teacher_id,
                    t.teacher_name,
                    t.designation,
                    t.teacher_type,
                    t.contact,
                    t.gender,
                    t.address,
                    t.dob
                FROM teacher t
                WHERE t.department_id = ?
                ORDER BY t.teacher_name";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $deptId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) { $teachers[] = $r; }
        $stmt->close();
    } catch (Throwable $e) {}
}

// 3) Students of this department
// If student.Dept_ID is NULL/0, fall back to latest application row by matching First_name/Last_name;
// include only if that latest application dept_name equals this chairman's dept name.
$students = [];
if ($deptId) {
    try {
        $sql = "
        SELECT 
            s.Student_Id,
            TRIM(COALESCE(s.First_name,'')) AS fn,
            TRIM(COALESCE(s.Last_name,''))  AS ln,
            CASE 
                WHEN s.Dept_ID IS NOT NULL AND s.Dept_ID <> 0 THEN d.department_name
                ELSE a.dept_name
            END AS resolved_dept,
            a.major_name
        FROM student s
        LEFT JOIN department d ON d.department_id = s.Dept_ID
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
        ORDER BY s.Student_Id
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $deptId, $deptName);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) { $students[] = $r; }
        $stmt->close();
    } catch (Throwable $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Chair View</title>
  <link rel="stylesheet" href="ChairView.css" />
</head>
<body>
  <button id="home-button" onclick="window.location.href='../22. Chairman Dashboard/ChairmanDashboard.php'">
    <img src="home.png" alt="Home Icon">
  </button>

  <div style="padding: 2vh 3vh; color: #fff;">
    <h2 style="margin: 0 0 1rem 0; font-weight: 700;">
    </h2>
    <div style="opacity:.85; margin-bottom: 2rem;">
    </div>

    <!-- Two tables side-by-side (no CSS file changes; using a small inline flex wrapper) -->
    <div style="display:flex; gap:2rem; align-items:flex-start; justify-content:center; flex-wrap:wrap;">
      <!-- Teachers table -->
      <div style="flex:1 1 520px; max-width: 720px; min-width: 360px;">
        <h3 style="margin:0 0 .7rem 0;">Teachers — <?= h($deptName) ?></h3>
        <div style="
          background: rgba(255,255,255,.03);
          border: 1px solid rgba(255,255,255,.14);
          border-radius: 12px;
          overflow:hidden;
          box-shadow: 0 10px 24px rgba(0,0,0,.25);
        ">
          <table style="width:100%; border-collapse:collapse; color:#fff;">
            <thead>
              <tr style="background: #6D54B5;">
                <th style="text-align:left; padding:.9rem 1rem;">ID</th>
                <th style="text-align:left; padding:.9rem 1rem;">Name</th>
                <th style="text-align:left; padding:.9rem 1rem;">Designation</th>
                <th style="text-align:left; padding:.9rem 1rem;">Type</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($teachers)): ?>
                <tr><td colspan="4" style="padding: .9rem 1rem; color:#c9c3da;">No teachers found.</td></tr>
              <?php else: ?>
                <?php $i=0; foreach ($teachers as $t): $i++; ?>
                  <tr style="background: <?= ($i%2? 'rgba(255,255,255,.04)' : 'rgba(255,255,255,.06)') ?>;">
                    <td style="padding:.75rem 1rem;"><?= h($t['teacher_id']) ?></td>
                    <td style="padding:.75rem 1rem;"><?= h($t['teacher_name']) ?></td>
                    <td style="padding:.75rem 1rem;"><?= h($t['designation']) ?></td>
                    <td style="padding:.75rem 1rem;"><?= h($t['teacher_type']) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Students table -->
      <div style="flex:1 1 520px; max-width: 720px; min-width: 360px;">
        <h3 style="margin:0 0 .7rem 0;">Students — <?= h($deptName) ?></h3>
        <div style="
          background: rgba(255,255,255,.03);
          border: 1px solid rgba(255,255,255,.14);
          border-radius: 12px;
          overflow:hidden;
          box-shadow: 0 10px 24px rgba(0,0,0,.25);
        ">
          <table style="width:100%; border-collapse:collapse; color:#fff;">
            <thead>
              <tr style="background: #6D54B5;">
                <th style="text-align:left; padding:.9rem 1rem;">Student ID</th>
                <th style="text-align:left; padding:.9rem 1rem;">Name</th>
                <th style="text-align:left; padding:.9rem 1rem;">Department</th>
                <th style="text-align:left; padding:.9rem 1rem;">Major</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($students)): ?>
                <tr><td colspan="4" style="padding: .9rem 1rem; color:#c9c3da;">No students found.</td></tr>
              <?php else: ?>
                <?php $i=0; foreach ($students as $s): $i++; 
                      $name = trim(($s['fn'] ?? '').' '.($s['ln'] ?? '')); ?>
                  <tr style="background: <?= ($i%2? 'rgba(255,255,255,.04)' : 'rgba(255,255,255,.06)') ?>;">
                    <td style="padding:.75rem 1rem;"><?= h($s['Student_Id']) ?></td>
                    <td style="padding:.75rem 1rem;"><?= h($name ?: '—') ?></td>
                    <td style="padding:.75rem 1rem;"><?= h($s['resolved_dept'] ?: '') ?></td>
                    <td style="padding:.75rem 1rem;"><?= h($s['major_name'] ?: '') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
