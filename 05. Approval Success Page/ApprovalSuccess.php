<?php
// Keep your CSS/HTML structure intact; only adding backend logic.

// OPTIONAL (dev): show PHP errors
// ini_set('display_errors', 1); error_reporting(E_ALL);

// Try to reuse your project connection if present; otherwise fall back to local creds
$conn = null;
@include_once __DIR__ . '/../database.php';
if (!isset($conn) || !$conn) {
    $DB_HOST = 'localhost';
    $DB_USER = 'root';
    $DB_PASS = '';
    $DB_NAME = 'coursenestdb';
    $conn = @mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
}
if (!$conn) {
    die('Database connection failed.');
}

/**
 * Ensure student table has a Student_password column.
 * Your schema dump shows no password field in `student`, so we add one if missing.
 * (MariaDB 10.4 supports IF NOT EXISTS.)
 */
mysqli_query(
    $conn,
    "ALTER TABLE student ADD COLUMN IF NOT EXISTS Student_password VARCHAR(255) NULL"
);

// Handle form submit
$successMessage = '';
$errorMessage   = '';
$showStudentId  = '';
$showPassword   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicantId = trim($_POST['applicant_id'] ?? '');
    $pwd         = $_POST['password'] ?? '';
    $repwd       = $_POST['repassword'] ?? '';

    // Basic validation (keep UI identical; no CSS changes)
    if ($applicantId === '' || !ctype_digit($applicantId)) {
        $errorMessage = 'Please enter a valid numeric Applicant ID.';
    } elseif (strlen($pwd) < 6) {
        $errorMessage = 'Password must be at least 6 characters.';
    } elseif ($pwd !== $repwd) {
        $errorMessage = 'Passwords do not match.';
    } else {
        // 1) Get applicant's name from `application`
        $stmtApp = mysqli_prepare($conn, "SELECT First_name, Last_name FROM application WHERE Application_Id = ? LIMIT 1");
        if (!$stmtApp) {
            $errorMessage = 'Failed to prepare application query.';
        } else {
            mysqli_stmt_bind_param($stmtApp, 'i', $applicantId);
            mysqli_stmt_execute($stmtApp);
            mysqli_stmt_bind_result($stmtApp, $firstName, $lastName);
            if (mysqli_stmt_fetch($stmtApp)) {
                mysqli_stmt_close($stmtApp);

                // 2) Find matching student by First_name + Last_name
                $stmtStu = mysqli_prepare(
                    $conn,
                    "SELECT Student_Id FROM student WHERE First_name = ? AND Last_name = ? ORDER BY Student_Id DESC LIMIT 1"
                );
                if (!$stmtStu) {
                    $errorMessage = 'Failed to prepare student lookup.';
                } else {
                    mysqli_stmt_bind_param($stmtStu, 'ss', $firstName, $lastName);
                    mysqli_stmt_execute($stmtStu);
                    mysqli_stmt_bind_result($stmtStu, $studentId);
                    if (mysqli_stmt_fetch($stmtStu)) {
                        mysqli_stmt_close($stmtStu);

                        // 3) Update password (hash for security), but show plain text on screen
                        $hash = password_hash($pwd, PASSWORD_BCRYPT);
                        $stmtUpd = mysqli_prepare(
                            $conn,
                            "UPDATE student SET Student_password = ? WHERE Student_Id = ?"
                        );
                        if (!$stmtUpd) {
                            $errorMessage = 'Failed to prepare password update.';
                        } else {
                            mysqli_stmt_bind_param($stmtUpd, 'si', $hash, $studentId);
                            if (mysqli_stmt_execute($stmtUpd)) {
                                $successMessage = 'Password updated successfully.';
                                $showStudentId  = (string)$studentId;
                                $showPassword   = $pwd; // show plain password as requested
                            } else {
                                $errorMessage = 'Could not update password.';
                            }
                            mysqli_stmt_close($stmtUpd);
                        }
                    } else {
                        $errorMessage = 'Matching student not found for that applicant name.';
                        mysqli_stmt_close($stmtStu);
                    }
                }
            } else {
                $errorMessage = 'Application not found for that Applicant ID.';
                mysqli_stmt_close($stmtApp);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Application Approved</title>
  <link rel="stylesheet" href="ApprovalSuccess.css">
</head>
<body>
  <div id="Approval-Row">
    <img id="VerifyIcon" src="tick.png" alt="Approved">
    <div id="ApprovalText">Your Application Has Been Approved</div>
  </div>

  <!-- Form wraps the existing inputs; IDs and structure preserved -->
  <form method="post" action="">
    <div id="StudentID-Row">
      <div id="Text">Applicant ID:</div>
      <div id="BankText">
        <!-- keep existing id="bankid"; add name for backend -->
        <input type="text" id="bankid" name="applicant_id" placeholder="Enter Applicant ID"
               value="<?php echo isset($_POST['applicant_id']) ? htmlspecialchars($_POST['applicant_id'], ENT_QUOTES, 'UTF-8') : ''; ?>">
      </div>
    </div>

    <div id="Text-line">Set up your student portal password</div>

    <div id="Password-Row">
      <div id="Password">Password</div>
      <div id="PasswordField">
        <input type="password" id="password" name="password" placeholder="Create a password">
        <button id="toggle-password" type="button" onclick="togglePwd('password')">
          <img src="eye.png" alt="Show/Hide">
        </button>
      </div>
    </div>

    <div id="RePassword-Row">
      <div id="RePassword">Re-enter Password</div>
      <div id="RePasswordField">
        <input type="password" id="repassword" name="repassword" placeholder="Re-enter password">
        <button id="toggle-repassword" type="button" onclick="togglePwd('repassword')">
          <img src="eye.png" alt="Show/Hide">
        </button>
      </div>
    </div>

    <button id="Confirm-Button" type="submit">Confirm</button>
  </form>

  <!-- Result / feedback (kept visually minimal; no CSS changes) -->
  <?php if (!empty($errorMessage)): ?>
    <div style="margin-left:73vh;margin-top:1.2vh;color:#ffdede;font-size:1.8vh;">
      <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($successMessage)): ?>
    <div style="margin-left:73vh;margin-top:1.2vh;color:#d6f5d6;font-size:1.8vh;">
      <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
      <?php if ($showStudentId !== ''): ?>
        <div style="margin-top:0.6vh;">
          <span style="color:#ccc;">Student ID:</span>
          <span style="color:#fff;font-weight:bold;"><?php echo htmlspecialchars($showStudentId, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div style="margin-top:0.3vh;">
          <span style="color:#ccc;">Password:</span>
          <span style="color:#fff;font-weight:bold;"><?php echo htmlspecialchars($showPassword, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div id="BackButtonContainer">
    <button id="BackButton" type="button" onclick="location.href='ApplicantDashboard.php'">
      <img src="back.png" alt="Back">
    </button>
  </div>

  <script>
    function togglePwd(id){
      const el = document.getElementById(id);
      if(!el) return;
      el.type = (el.type === 'password') ? 'text' : 'password';
    }
  </script>
</body>
</html>
