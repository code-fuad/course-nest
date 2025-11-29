<?php
// PasswordRecovery.php — role & id come via URL; CSS/markup IDs preserved.

// OPTIONAL (dev): show PHP errors
// ini_set('display_errors', 1); error_reporting(E_ALL);

session_start();
include "../database.php"; // must define $conn (mysqli)

if (!$conn) {
    die("Database connection failed.");
}

// --- Read role & id from URL ---
$role = isset($_GET['role']) ? strtolower(trim($_GET['role'])) : '';
$uid  = isset($_GET['id'])   ? trim($_GET['id']) : '';

$validRoles = ['student','teacher','chairman'];
$preError = '';
if ($role === '' || $uid === '' || !in_array($role, $validRoles, true)) {
    $preError = "Invalid or missing role/ID. Please go back and try again.";
}

// Helpers
function id_exists(mysqli $conn, string $table, string $col, string $id): bool {
    $sql = "SELECT 1 FROM {$table} WHERE {$col} = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return false;
    mysqli_stmt_bind_param($stmt, "s", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $found = $res && mysqli_num_rows($res) === 1;
    mysqli_stmt_close($stmt);
    return $found;
}

function ensure_column(mysqli $conn, string $table, string $col, string $type): void {
    // Add column if it doesn't exist (both teacher & chairman tables in your dump have no password column by default)
    // and student table has no Student_password column by default. :contentReference[oaicite:6]{index=6}
    $sql = "SELECT COUNT(*) AS c
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return;
    mysqli_stmt_bind_param($stmt, "ss", $table, $col);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);

    if (!$row || (int)$row['c'] === 0) {
        mysqli_query($conn, "ALTER TABLE {$table} ADD COLUMN {$col} {$type}");
    }
}

$successMessage = '';
$errorMessage   = '';

// Process form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($preError)) {
    $password   = $_POST['password']   ?? '';
    $repassword = $_POST['repassword'] ?? '';

    if (strlen($password) < 6) {
        $errorMessage = 'Password must be at least 6 characters.';
    } elseif ($password !== $repassword) {
        $errorMessage = 'Passwords do not match.';
    } else {
        if ($role === 'student') {
            // Student_Id in student table; set hashed Student_password
            if (!id_exists($conn, 'student', 'Student_Id', $uid)) {
                $errorMessage = 'No student found with that ID.';
            } else {
                ensure_column($conn, 'student', 'Student_password', 'VARCHAR(255) NULL');
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = mysqli_prepare($conn, "UPDATE student SET Student_password = ? WHERE Student_Id = ?");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ss", $hash, $uid);
                    if (mysqli_stmt_execute($stmt)) {
                        $successMessage = 'Password updated successfully for Student.';
                    } else {
                        $errorMessage = 'Could not update student password.';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $errorMessage = 'Failed to prepare student password update.';
                }
            }

        } elseif ($role === 'teacher') {
            // teacher_id in teacher table; set plaintext password (to match your current login check)
            if (!id_exists($conn, 'teacher', 'teacher_id', $uid)) {
                $errorMessage = 'No teacher record found for that ID.';
            } else {
                ensure_column($conn, 'teacher', 'password', 'VARCHAR(255) NULL');
                $stmt = mysqli_prepare($conn, "UPDATE teacher SET password = ? WHERE teacher_id = ?");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ss", $password, $uid);
                    if (mysqli_stmt_execute($stmt)) {
                        $successMessage = 'Password updated successfully for Teacher.';
                    } else {
                        $errorMessage = 'Could not update teacher password.';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $errorMessage = 'Failed to prepare teacher password update.';
                }
            }

        } else { // chairman
            // chair_id in chairman table; set plaintext password (to match your current login pattern)
            // Your dump shows chairman table but no password col; we create it once. :contentReference[oaicite:7]{index=7}
            if (!id_exists($conn, 'chairman', 'chair_id', $uid)) {
                $errorMessage = 'No chairman record found for that ID.';
            } else {
                ensure_column($conn, 'chairman', 'password', 'VARCHAR(255) NULL');
                $stmt = mysqli_prepare($conn, "UPDATE chairman SET password = ? WHERE chair_id = ?");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ss", $password, $uid);
                    if (mysqli_stmt_execute($stmt)) {
                        $successMessage = 'Password updated successfully for Chairman.';
                    } else {
                        $errorMessage = 'Could not update chairman password.';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $errorMessage = 'Failed to prepare chairman password update.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Password Recovery</title>
  <link rel="stylesheet" href="PasswordRecovery.css">
</head>
<body>
  <div id="Image-Block"></div>

  <div id="Info-Block">
    <div id="Recovery">Password Recovery</div>

    <!-- Verify row (matches your CSS) -->
    <div id="Verify-Row">
      <img id="VerifyIcon" src="tick.png" alt="Verify">
      <div id="VerifyText">
        <?php if (!empty($preError)): ?>
            <?php echo htmlspecialchars($preError, ENT_QUOTES, 'UTF-8'); ?>
        <?php else: ?>
            <?php
              $labelRole = ucfirst($role);
              echo htmlspecialchars("Verified identity: {$labelRole} (ID: {$uid})", ENT_QUOTES, 'UTF-8');
            ?>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($errorMessage)): ?>
      <div style="color:#ffb3b3; font-weight:bold; margin-left:5vh; margin-top:1.5vh;">
        <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
      <div style="color:#c7f7c7; font-weight:bold; margin-left:5vh; margin-top:1.5vh;">
        <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <!-- Password form (IDs/structure exactly as your CSS expects) -->
    <form method="post" action="">
      <!-- Keep role & id across postbacks (even though we read from GET, this preserves values if needed) -->
      <input type="hidden" name="role" value="<?php echo htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="uid"  value="<?php echo htmlspecialchars($uid,  ENT_QUOTES, 'UTF-8'); ?>">

      <div id="Password-Row">
        <div id="Password">Password</div>
        <div id="PasswordField">
          <input type="password" id="password" name="password" placeholder="Create a password" required>
          <button id="toggle-password" type="button">
            <img src="eye.png" alt="Show/Hide">
          </button>
        </div>
      </div>

      <div id="RePassword-Row">
        <div id="RePassword">Re-enter Password</div>
        <div id="RePasswordField">
          <input type="password" id="repassword" name="repassword" placeholder="Re-enter password" required>
          <button id="toggle-repassword" type="button">
            <img src="eye.png" alt="Show/Hide">
          </button>
        </div>
      </div>

      <button id="Confirm-Button" type="submit">Confirm</button>
    </form>

    <div id="BackButtonContainer">
      <button id="BackButton" type="button" onclick="window.location.href='../01. Login Page/Login.php'">
        <img src="back.png" alt="Back">
      </button>
    </div>
  </div>

  <script>
    // Toggle visibility for both fields (keeps your IDs)
    document.addEventListener('DOMContentLoaded', function () {
      const pw = document.getElementById('password');
      const rp = document.getElementById('repassword');
      const t1 = document.getElementById('toggle-password');
      const t2 = document.getElementById('toggle-repassword');

      function toggle(el){ el.type = (el.type === 'password') ? 'text' : 'password'; }

      if (t1) t1.addEventListener('click', function(e){ e.preventDefault(); if(pw) toggle(pw); });
      if (t2) t2.addEventListener('click', function(e){ e.preventDefault(); if(rp) toggle(rp); });
    });
  </script>
</body>
</html>
