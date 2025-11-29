<?php
// ApplicantDashboard.php — controller + markup (CSS unchanged)

// OPTIONAL: show PHP errors during development
// ini_set('display_errors', 1); error_reporting(E_ALL);

session_start();

// ---- Update these with your real DB credentials if needed ----
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'coursenestdb';
// --------------------------------------------------------------

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicantId = trim($_POST['applicant_id'] ?? '');

    if ($applicantId === '' || !ctype_digit($applicantId)) {
        $flash = 'Please enter a valid numeric Applicant ID.';
    } else {
        $mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
        if ($mysqli->connect_errno) {
            $flash = 'Database connection failed.';
        } else {
            $sql = "SELECT approval_status FROM application WHERE Application_Id = ? LIMIT 1";
            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('i', $applicantId);
                $stmt->execute();
                $stmt->bind_result($status);
                if ($stmt->fetch()) {
                    $stmt->close();
                    $mysqli->close();

                    $statusNorm = strtolower(trim((string)$status));
                    if ($statusNorm === 'approved') {
                        header('Location: ../05. Approval Success Page\ApprovalSuccess.php');
                        exit;
                    }
                    if ($statusNorm === 'denied' || $statusNorm === 'rejected') {
                        header('Location: ../06. Approval Deny Page\ApprovalDeny.php');
                        exit;
                    }
                    if ($statusNorm === 'pending' || $statusNorm === '') {
                        $flash = 'Your application is still pending.';
                    } else {
                        header('Location: ApprovalDeny.php?applicant_id=');
                        exit;
                    }
                } else {
                    $flash = 'No application found for that ID.';
                    $stmt->close();
                    $mysqli->close();
                }
            } else {
                $flash = 'Query preparation failed.';
                $mysqli->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Applicant Dashboard</title>
  <link rel="stylesheet" href="ApplicantDashboard.css">
</head>
<body>
  <div id="Dashboard">Applicant Dashboard</div>

  <form method="post" action="">
    <div id="UserID-Row">
      <div id="UserID">Applicant ID</div>
      <div id="UserText">
        <input type="text" name="applicant_id" placeholder="Enter Applicant ID" required>
      </div>
    </div>

    <button id="Login-Button" type="submit">Continue</button>

    <?php if (!empty($flash)): ?>
      <!-- minimal inline style only for message placement; CSS file untouched -->
      <div style="margin-left:77.5vh;margin-top:1.2vh;color:#ffdede;font-size:1.8vh;">
        <?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>
  </form>

  <div id="BackButtonContainer">
    <button id="BackButton" type="button" onclick="window.location.href='../01. Login Page/Login.php'">
      <img src="back.png" alt="Back">
    </button>
  </div>
</body>
</html>
