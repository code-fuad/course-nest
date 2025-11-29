<?php
// ApprovalDeny.php — reads applicant_id if passed; CSS unchanged
$applicantId = isset($_GET['applicant_id']) ? htmlspecialchars($_GET['applicant_id'], ENT_QUOTES, 'UTF-8') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Application Denied</title>
  <link rel="stylesheet" href="ApprovalDeny.css">
</head>
<body>
  <div id="Deny-Row">
    <img id="CrossIcon" src="cross.png" alt="Denied">
    <div id="DenyText">Your Application Has Been Denied</div>
  </div>

  <div id="Consolation">
    <?php if ($applicantId !== ''): ?>
      Applicant ID: <?php echo $applicantId; ?> —
    <?php endif; ?>
    Please contact admissions for further details.
  </div>

  <div id="BackButtonContainer">
    <button id="BackButton" type="button" onclick="location.href='ApplicantDashboard.php'">
      <img src="back.png" alt="Back">
    </button>
  </div>
</body>
</html>
