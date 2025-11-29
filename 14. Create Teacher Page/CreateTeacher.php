<?php
// ===== CreateTeacher.php TOP-OF-FILE HANDLER (NO HTML/CSS CHANGES) =====
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Reuse your existing DB if available; else fallback to local creds.
if (!isset($conn)) {
    @include __DIR__ . '/../database.php';
}
if (!isset($conn) || !$conn) {
    $conn = new mysqli('localhost', 'root', '', 'coursenestdb');
    $conn->set_charset('utf8mb4');
}

function pfirst(array $names): string
{
    foreach ($names as $n) {
        if (isset($_POST[$n])) {
            $v = trim((string)$_POST[$n]);
            if ($v !== '') return $v;
        }
    }
    return '';
}
function js_alert_and_reload(string $msg): never
{
    $self = $_SERVER['REQUEST_URI'] ?? $_SERVER['PHP_SELF'];
    $target = strtok($self, '?'); // clean reload (drops querystring)
    echo "<script>alert(" . json_encode($msg) . "); window.location.href=" . json_encode($target) . ";</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read values (we'll send these via JS so your HTML stays unchanged)
    $teacher_name = pfirst(['teacher_name', 'name', 'Name']);
    $contact      = pfirst(['contact', 'phone', 'mobile', 'contact_number', 'Contact']);
    $address      = pfirst(['address', 'addr', 'Address']);
    $gender       = pfirst(['gender']); // will come from JS as first gender select
    $dob          = pfirst(['dob', 'date_of_birth']);
    $salary_in    = pfirst(['salary', 'teacher_salary', 'Salary']);
    $dept_name    = pfirst(['department_name', 'department', 'dept_name', 'dept']);
    $designation  = pfirst(['designation']);    // will be set by JS
    $teacher_type = pfirst(['teacher_type']);    // will be set by JS
    $password     = pfirst(['password', 'pass', 'teacher_password']);

    if (
        $teacher_name === '' || $contact === '' || $address === '' || $gender === '' ||
        $salary_in === '' || $dept_name === '' || $designation === '' || $teacher_type === '' || $password === ''
    ) {
        js_alert_and_reload("All fields (including password) are required.");
    }

    $salary = (float)$salary_in;

    try {
        $conn->begin_transaction();

        // 1) department_id from department name
        $stmt = $conn->prepare("SELECT department_id FROM department WHERE department_name = ?");
        $stmt->bind_param("s", $dept_name);
        $stmt->execute();
        $dept = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$dept) throw new Exception("Department not found: {$dept_name}");
        $department_id = (int)$dept['department_id'];

        // 2) chair_id for that department
        $stmt = $conn->prepare("SELECT chair_id FROM chairman WHERE department_id = ?");
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $chair = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$chair) throw new Exception("No chairman found for the selected department.");
        $chair_id = (int)$chair['chair_id'];

        // 3) Determine password column name in teacher table ('password' vs 'teacher_password')
        $pwcol = 'password';
        try {
            $chk = $conn->query("SHOW COLUMNS FROM teacher LIKE 'password'");
            if (!$chk || $chk->num_rows === 0) {
                $pwcol = 'teacher_password';
            }
            if ($chk) $chk->free();
        } catch (Throwable $e) {
            // leave default
        }

        // 4) Compute next teacher_id if not AUTO_INCREMENT
        $nextId = 2001;
        $res = $conn->query("SELECT COALESCE(MAX(teacher_id), 2000) + 1 AS next_id FROM teacher");
        if ($row = $res->fetch_assoc()) $nextId = (int)$row['next_id'];
        $res->free();

        // 5) Insert row
        $sql = "INSERT INTO teacher 
            (teacher_id, teacher_name, `$pwcol`, teacher_type, contact, gender, salary, address, department_id, chair_id, designation, dob)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // contact is BIGINT in DB → bind as string to avoid overflow on 32-bit PHP
        $dobParam = ($dob !== '') ? $dob : null;
        $stmt->bind_param(
            "isssssdsiiss",
            $nextId,
            $teacher_name,
            $password,
            $teacher_type,
            $contact,
            $gender,
            $salary,
            $address,
            $department_id,
            $chair_id,
            $designation,
            $dobParam
        );
        $stmt->execute();
        $newId = $nextId;
        $stmt->close();

        $conn->commit();

        js_alert_and_reload("Teacher created successfully!\nID: {$newId}\nPassword: {$password}");
    } catch (Throwable $e) {
        $conn->rollback();
        js_alert_and_reload("Error: " . $e->getMessage());
    }
}
// ===== End TOP-OF-FILE HANDLER =====
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- keeping your original CSS path -->
    <link rel="stylesheet" href="../14. Create Teacher Page/CreateTeacher.css" />
</head>

<body>
    <button id="home-button" onclick="window.location.href='../09. Admin Dashboard/AdminDashboard.php'">
        <img src="home.png" alt="Home Icon">
    </button>

    <div id='block'>
        <div id='header'>
            Register Teacher
        </div>

        <div id='form'>
            <div id="Name"><input type="text" name="Name" placeholder="Teacher Name"></div>
            <div id="Name"><input type="text" name="Contact" placeholder="Teacher Contact No."></div>
            <div id="Name"><input type="text" name="Address" placeholder="Address"></div>

            <div id="GenderField">
                <select name="gender" id="GenderSelect" required>
                    <option value="" disabled selected hidden>Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>

            <div id="DOB"><input type="date" name="dob" id="DOBInput"></div>

            <div id="Name"><input type="text" name="Salary" placeholder="Teacher Salary"></div>

            <div id="DepartmentField">
                <select name="department" id="DepartmentSelect">
                    <option value="" disabled selected hidden>Department</option>
                    <?php
                    $deptQuery = "SELECT department_name FROM department";
                    $deptResult = mysqli_query($conn, $deptQuery);
                    while ($row = mysqli_fetch_assoc($deptResult)) {
                        echo "<option value='" . htmlspecialchars($row['department_name']) . "'>" . htmlspecialchars($row['department_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- NOTE: You kept these as gender too; we'll map them via JS -->
            <div id="GenderField">
                <select name="gender" id="GenderSelect" required>
                    <option value="" disabled selected hidden>Designation</option>
                    <option value="Lecturer">Lecturer</option>
                    <option value="Assistant Professor">Assistant Professor</option>
                    <option value="Associate Professor">Associate Professor</option>
                    <option value="Professor">Professor</option>
                </select>
            </div>

            <div id="GenderField">
                <select name="gender" id="GenderSelect" required>
                    <option value="" disabled selected hidden>Teacher Type</option>
                    <option value="PartTime">Part Time</option>
                    <option value="FullTime">Full Time</option>
                </select>
            </div>

            <div id="PasswordField">
                <input type="password" name="password" id="password-input" placeholder="Enter Password" required>
                <button id="toggle-password" type="button">
                    <img src="eye.png" alt="Toggle Visibility">
                </button>
            </div>

            <button id="Apply-Button" type="submit">Register</button>
        </div>
    </div>

    <script>
        // Keep your existing toggle
        document.addEventListener("DOMContentLoaded", function() {
            const passwordInput = document.getElementById("password-input");
            const toggleButton = document.getElementById("toggle-password");
            if (toggleButton && passwordInput) {
                toggleButton.addEventListener("click", function() {
                    passwordInput.type = (passwordInput.type === "password") ? "text" : "password";
                });
            }
        });

        // ===== Add a hidden auto-form submit so we don't need to modify your HTML =====
        (function() {
            const btn = document.getElementById('Apply-Button');
            if (!btn) return;

            btn.addEventListener('click', function(e) {
                e.preventDefault(); // avoid default button behavior

                // Grab values from your current inputs/selects
                const nameInput = document.querySelector('input[name="Name"]');
                const contactInp = document.querySelector('input[name="Contact"]');
                const addressInp = document.querySelector('input[name="Address"]');
                const salaryInput = document.querySelector('input[name="Salary"]');
                const dobInput = document.querySelector('input[name="dob"]');
                const deptSel = document.getElementById('DepartmentSelect');
                const passInput = document.getElementById('password-input');

                // There are three selects named "gender": [0]=gender, [1]=designation, [2]=teacher_type
                const genderSelects = document.querySelectorAll('select[name="gender"]');
                const genderSel = genderSelects[0];
                const desigSel = genderSelects[1];
                const typeSel = genderSelects[2];

                // Build a hidden form with the names the PHP handler expects
                const payload = {
                    teacher_name: (nameInput?.value || '').trim(),
                    contact: (contactInp?.value || '').trim(),
                    address: (addressInp?.value || '').trim(),
                    salary: (salaryInput?.value || '').trim(),
                    dob: (dobInput?.value || '').trim(),
                    department_name: (deptSel?.value || '').trim(),
                    gender: (genderSel?.value || ''), // real gender
                    designation: (desigSel?.value || ''), // map to designation
                    teacher_type: (typeSel?.value || ''), // map to teacher_type
                    password: (passInput?.value || '').trim()
                };

                // Simple required check (so the server doesn't bounce it)
                for (const [k, v] of Object.entries(payload)) {
                    if (!v) {
                        alert("Please fill all fields before submitting.");
                        return;
                    }
                }

                const f = document.createElement('form');
                f.method = 'POST';
                f.style.display = 'none';

                for (const [k, v] of Object.entries(payload)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = k;
                    input.value = v;
                    f.appendChild(input);
                }
                document.body.appendChild(f);
                f.submit(); // this will trigger the PHP handler, which shows the success alert
            });
        })();
        // ===== End hidden auto-form submit =====
    </script>
</body>

</html>