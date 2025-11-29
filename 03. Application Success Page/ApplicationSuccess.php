<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Success Window</title>
    <link rel="stylesheet" type="text/css" href="ApplicationSuccess.css?v=2">
</head>
<body>
    <div id = "Verify-Row">
        <img src="tick.png" alt="Tick Icon" id="VerifyIcon">
        <div id="VerifyText">Application Successful</div>
    </div>

    <div id="TestPass-Row">
    <div id="Text">Your Application ID : </div>
    <div id="TestPass"><?php echo htmlspecialchars($_GET['id'] ?? ''); ?></div>
    </div>

    <div id = "Text-line">(Preserve the application ID to chheck your application approval)</div>

    <div id="BackButtonContainer">
        <button id="BackButton" onclick="window.location.href='../01. Login Page/Login.php'">
            <img src="back.png" alt="Back">
        </button>
    </div>
    
</body>
</html>