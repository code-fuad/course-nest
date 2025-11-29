<?php
session_start();
include "../database.php"; // must define $conn (mysqli)

if (!$conn) { die("Database connection failed."); }
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$sections = [];
$error = null;

try {
    // Same data as CourseAdvising: show all offered sections
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Offered Course Window</title>
    <link rel="stylesheet" href="../19. Offered Course Page/OfferedCourse.css" />
</head>
<body>
    <div id="Menu">
        <a href="../16. Student Dashboard/StudentDashboard.php" id="Home-Link">
            <button id="Home-Button">
                <img src="home.png" alt="Home Icon">
            </button>
        </a>

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
                <div class="dropdown-option" id="registration" onclick="window.location.href='../18. Course Registration Page/CourseRegistration.php'">
                    <img src="registration.png" alt="Registration Icon">
                    <span>Course Registration</span>
                </div>
                <div class="dropdown-option" id="offered">
                    <img src="offered.png" alt="Offered Icon">
                    <span>Offered Courses</span>
                </div>
                <div class="dropdown-option" id="request" onclick="window.location.href='../20. Course Request/CourseRequest.php'">
                    <img src="request.png" alt="Request Icon">
                    <span>Course Request</span>
                </div>
                
            </div>

            <!-- Offered Courses Table -->
            <div class="container">
                <header class="header">
                    <h1>Offered Courses</h1>
                    

                    <!-- Search input (same look/feel as Course Advising; no CSS file changes) -->
                    <div style="max-width: 800px; margin: .8rem auto 0;">
                        <input
                            id="Search-Input"
                            type="text"
                            placeholder="Search Course / Section / Time / Days / Room"
                            aria-label="Search offered sections"
                            style="
                                width:100%;
                                padding:.9rem 1.1rem;
                                background: var(--row);
                                color:#fff;
                                border:1px solid var(--line);
                                border-radius: 8px;
                                outline: none;
                                font: inherit;
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
        </div>

        <a href="../01. Login Page/Login.php" id="LogOut-Link">
            <button id="LogOut-Button">
                <img src="log.jpg" alt="LogOut Icon">
                <span>Log Out</span>
            </button>
        </a>
    </div>

    <script>
        // Keep your dropdown hover behavior
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

        // ---- Client-side search & sort (same behavior as Course Advising) ----
        (function(){
            var tbody = document.getElementById('SectionsBody');
            if (!tbody) return;

            var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
            var currentSort = { key: null, dir: 1 }; // 1 = asc, -1 = desc

            function text(el) { return (el.textContent || '').trim(); }

            function parseTimeToMinutes(t) {
                // "09:40 AM - 11:10 AM" -> minutes of start time
                if (!t) return -1;
                var parts = t.split('-')[0].trim();
                var m = parts.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
                if (!m) return -1;
                var h = parseInt(m[1],10), min = parseInt(m[2],10), ap = m[3].toUpperCase();
                if (ap === 'PM' && h !== 12) h += 12;
                if (ap === 'AM' && h === 12) h = 0;
                return h*60 + min;
            }

            function daysRank(d) {
                // Rough ordering for combined day strings like "MW", "ST", "R"
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

                // a11y hint; no CSS file change
                var ths = document.querySelectorAll('thead th[data-col]');
                ths.forEach(function(th){ th.removeAttribute('aria-sort'); });
                var active = document.querySelector('thead th[data-col="'+key+'"]');
                if (active) active.setAttribute('aria-sort', currentSort.dir === 1 ? 'ascending' : 'descending');
            }

            // Sort on header click
            var ths = document.querySelectorAll('thead th[data-col]');
            ths.forEach(function(th){
                th.style.cursor = 'pointer'; // visual hint only
                th.addEventListener('click', function(){
                    sortBy(th.getAttribute('data-col'));
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
