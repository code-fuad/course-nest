<?php
// -------------------- DB CONNECTION (edit if needed) --------------------
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'coursenestdb';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (Throwable $e) {
    http_response_code(500);
    echo "Database connection failed: " . htmlspecialchars($e->getMessage());
    exit;
}

// -------------------- HELPERS --------------------
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// -------------------- PAGE STATE --------------------
$departments = [];
$successInfo = null;  // ['chair_id'=>..., 'password'=>...]
$errorMsg = null;

// Load department list for dropdown
try {
    $res = $conn->query("SELECT department_name FROM department ORDER BY department_name ASC");
    while ($row = $res->fetch_assoc()) {
        $departments[] = $row['department_name'];
    }
    $res->free();
} catch (Throwable $e) {
    $errorMsg = "Failed to load departments: " . h($e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chair_name  = trim($_POST['chair_name'] ?? '');
    $gender      = trim($_POST['gender'] ?? '');
    $contact     = trim($_POST['contact'] ?? '');
    $salary_in   = trim($_POST['salary'] ?? '');
    $dept_name   = trim($_POST['department_name'] ?? '');
    $password_in = trim($_POST['password'] ?? '');

    // Validate required inputs (password required as per your request)
    if ($chair_name === '' || $gender === '' || $contact === '' || $salary_in === '' || $dept_name === '' || $password_in === '') {
        $errorMsg = "All fields are required, including Password.";
    } else {
        $salary = (float)$salary_in;

        try {
            $conn->begin_transaction();

            // 1) Find department_id by department_name
            $stmt = $conn->prepare("SELECT department_id FROM department WHERE department_name = ?");
            $stmt->bind_param("s", $dept_name);
            $stmt->execute();
            $deptRes = $stmt->get_result();
            $deptRow = $deptRes->fetch_assoc();
            $stmt->close();

            if (!$deptRow) {
                throw new Exception("Department not found: " . $dept_name);
            }
            $department_id = (int)$deptRow['department_id'];

            // 2) Insert into chairman including the password column
            // contact is BIGINT in DB; bind as string ("s") to avoid overflow on 32-bit PHP
            $stmt = $conn->prepare(
                "INSERT INTO chairman (chair_name, password, gender, contact, salary, department_id)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sssddi", $chair_name, $password_in, $gender, $contact, $salary, $department_id);
            // Note: using "d" for salary (double/float). If you prefer string, change to "s" and pass (string)$salary.
            $stmt->execute();
            $newChairId = $conn->insert_id;
            $stmt->close();

            // 3) Fetch back the stored password (from table) and show both ID+password
            $stmt = $conn->prepare("SELECT password FROM chairman WHERE chair_id = ?");
            $stmt->bind_param("i", $newChairId);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $stmt->close();

            $conn->commit();

            $successInfo = [
                'chair_id' => $newChairId,
                'password' => $row['password'] ?? $password_in
            ];

            // Clear POSTed values after success (so form resets)
            $_POST = [];

        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            // 1062 = duplicate key (likely because department_id is UNIQUE: one chairman per department)
            if ((int)$e->getCode() === 1062) {
                $errorMsg = "This department already has a chairman.";
            } else {
                $errorMsg = "Insert failed: " . h($e->getMessage());
            }
        } catch (Throwable $e) {
            $conn->rollback();
            $errorMsg = "Error: " . h($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Chairman</title>
    <link rel="stylesheet" href="CreateChairman.css"><!-- keep your CSS untouched -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<!-- Keep IDs so your CSS applies -->
<button id="home-button" type="button" onclick="window.location.href='../09. Admin Dashboard/AdminDashboard.php'">
    <img alt="Home" src="data:image/svg+xml;utf8,<?xml version='1.0' encoding='UTF-8'?><svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path fill='white' d='M12 3l8 6v12h-5v-7H9v7H4V9l8-6z'/></svg>">
</button>

<div id="block">
    <div id="header">Create Chairman</div>

    <?php if ($successInfo): ?>
        <div style="color:#dfffe0;background:#184d2a;border:1px solid #2b7a3d;padding:12px;margin:0 6vh 2vh 6vh;border-radius:8px;">
            <strong>Success!</strong><br>
            Chairman ID: <span style="font-weight:700;"><?= h($successInfo['chair_id']) ?></span><br>
            Password: <span style="font-weight:700;"><?= h($successInfo['password']) ?></span>
        </div>
    <?php elseif ($errorMsg): ?>
        <div style="color:#ffdede;background:#5c1f1f;border:1px solid #a34040;padding:12px;margin:0 6vh 2vh 6vh;border-radius:8px;">
            <strong>Error:</strong> <?= h($errorMsg) ?>
        </div>
    <?php endif; ?>

    <form method="post" autocomplete="off" novalidate>
        <!-- Name, Contact, Salary inputs (IDs kept for your CSS) -->
        <div id="Name">
            <input type="text" name="chair_name" placeholder="Chairman Name" required value="<?= h($_POST['chair_name'] ?? '') ?>">
            <input type="tel"  name="contact"    placeholder="Contact Number" required value="<?= h($_POST['contact'] ?? '') ?>">
            <input type="number" step="0.01" name="salary" placeholder="Salary" required value="<?= h($_POST['salary'] ?? '') ?>">
        </div>

        <!-- Gender select (IDs kept) -->
        <div id="GenderField">
            <select id="GenderSelect" name="gender" required>
                <option value="" disabled <?= empty($_POST['gender'])?'selected':''; ?>>Select Gender</option>
                <option value="Male"   <?= (($_POST['gender'] ?? '')==='Male')?'selected':''; ?>>Male</option>
                <option value="Female" <?= (($_POST['gender'] ?? '')==='Female')?'selected':''; ?>>Female</option>
                <option value="Other"  <?= (($_POST['gender'] ?? '')==='Other')?'selected':''; ?>>Other</option>
            </select>
        </div>

        <!-- Department dropdown (ID kept) -->
        <select id="DepartmentSelect" name="department_name" required>
            <option value="" disabled <?= empty($_POST['department_name'])?'selected':''; ?>>Select Department</option>
            <?php foreach ($departments as $dn): ?>
                <option value="<?= h($dn) ?>" <?= (($_POST['department_name'] ?? '')===$dn)?'selected':''; ?>>
                    <?= h($dn) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Password row (IDs kept) -->
        <div id="Password-Row">
           
        </div>

        <div id="PasswordField">
            <input id="password" type="password" name="password" placeholder="Enter Password" required>
            <button id="toggle-password" type="button" aria-label="Toggle password visibility">
                <img alt="Toggle" src="data:image/svg+xml;utf8,<?xml version='1.0' encoding='UTF-8'?><svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path fill='white' d='M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z'/></svg>">
            </button>
        </div>

        <button id="Apply-Button" type="submit">Create</button>
    </form>
</div>

<script>
// Toggle password visibility (keeps your CSS intact)
document.getElementById('toggle-password')?.addEventListener('click', function(){
    const input = document.getElementById('password');
    if (!input) return;
    input.type = (input.type === 'password') ? 'text' : 'password';
});
</script>

</body>
</html>
