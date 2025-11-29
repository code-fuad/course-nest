<?php
include "../database.php";

// Handle approval and decline action
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && isset($_POST['application_id'])) {
    $application_id = intval($_POST['application_id']);
    $status = $_POST['action'] === 'approve' ? 'Approved' : 'Declined';

    // Fixed: Use correct DB column name with unchanged POST name
    $update_query = "UPDATE application SET approval_status = '$status' WHERE Application_Id = $application_id";
    mysqli_query($conn, $update_query);
}

// Fetch application data
if (isset($_GET['application_id'])) {
    $application_id = intval($_GET['application_id']);

    $sql = "SELECT * FROM application WHERE Application_Id = $application_id";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $application = mysqli_fetch_assoc($result);
    } else {
        echo "<script>alert('Application not found');</script>";
        exit;
    }
} else {
    echo "<script>alert('No application ID provided');</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Profile Window</title>
    <link rel="stylesheet" type="text/css" href="ApprovalProfile.css?v=3">
    <script>
        function confirmAndSubmit(action) {
            if (confirm("Are you sure?")) {
                document.getElementById('actionInput').value = action;
                document.getElementById('approvalForm').submit();
            }
        }

        window.onload = function () {
            const approveBtn = document.getElementById('Apply-Button');
            const declineBtn = document.getElementById('Decline-Button');

            if (approveBtn) {
                approveBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    confirmAndSubmit('approve');
                });
            }

            if (declineBtn) {
                declineBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    confirmAndSubmit('decline');
                });
            }
        };
    </script>
</head>
<body>

<!-- Hidden form for submitting approval status -->
<form id="approvalForm" method="POST" style="display: none;">
    <input type="hidden" name="application_id" value="<?php echo $application['Application_Id']; ?>">
    <input type="hidden" name="action" id="actionInput" value="">
</form>

<div id="First-Column">
    <div id="Recovery">STUDENT APPLICATION</div>
    <div id="Personal">Personal Information</div>

    <div id="Name">
        <div id="FirstName">
            <span id = "FirstNameText">First Name:</span>
            <input type="text" class = "FirstNameField" value="<?php echo htmlspecialchars($application['First_name']); ?>" readonly>
        </div>
        <div id="LastName">
            <input type="text" value="<?php echo htmlspecialchars($application['Last_name']); ?>" readonly>
        </div>
    </div>

    <div id="StudentContact">
        <input type="text" value="<?php echo htmlspecialchars($application['Student_contact']); ?>" readonly>
    </div>

    <div id="GenderField">
        <input type="text" value="<?php echo htmlspecialchars($application['Gender']); ?>" readonly>
    </div>

    <div id="BloodGroup">
        <input type="text" value="<?php echo htmlspecialchars($application['Blood_group']); ?>" readonly>
    </div>

    <div id="DOB">
        <input type="text" value="<?php echo htmlspecialchars($application['DOB']); ?>" readonly>
    </div>

    <div id="Father">
        <input type="text" value="<?php echo htmlspecialchars($application['Father_name']); ?>" readonly>
    </div>

    <div id="Mother">
        <input type="text" value="<?php echo htmlspecialchars($application['Mother_name']); ?>" readonly>
    </div>

    <div id="Guardian">
        <input type="text" value="<?php echo htmlspecialchars($application['Local_guardian']); ?>" readonly>
    </div>

    <div id="GuardianContact">
        <input type="text" value="<?php echo htmlspecialchars($application['Guardian_contact']); ?>" readonly>
    </div>

    <div id="Address">
        <input type="text" value="<?php echo htmlspecialchars($application['Address']); ?>" readonly>
    </div>
</div>

<div id="Second-Column">
    <div id="Academic">Academic Information</div>

    <div id="SSC">
        <div id="SSCyear">
            <input type="text" value="<?php echo htmlspecialchars($application['SSC_year']); ?>" readonly>
        </div>
        <div id="SSCgpa">
            <input type="text" value="<?php echo htmlspecialchars($application['SSC_gpa']); ?>" readonly>
        </div>
    </div>

    <div id="SSCroll">
        <input type="text" value="<?php echo htmlspecialchars($application['SSC_roll']); ?>" readonly>
    </div>

    <div id="SSCreg">
        <input type="text" value="<?php echo htmlspecialchars($application['SSC_reg']); ?>" readonly>
    </div>

    <div id="SSCin">
        <input type="text" value="<?php echo htmlspecialchars($application['SSC_in']); ?>" readonly>
    </div>

    <div id="HSC">
        <div id="HSCyear">
            <input type="text" value="<?php echo htmlspecialchars($application['HSC_year']); ?>" readonly>
        </div>
        <div id="HSCgpa">
            <input type="text" value="<?php echo htmlspecialchars($application['HSC_gpa']); ?>" readonly>
        </div>
    </div>

    <div id="HSCroll">
        <input type="text" value="<?php echo htmlspecialchars($application['HSC_roll']); ?>" readonly>
    </div>

    <div id="HSCreg">
        <input type="text" value="<?php echo htmlspecialchars($application['HSC_reg']); ?>" readonly>
    </div>

    <div id="HSCin">
        <input type="text" value="<?php echo htmlspecialchars($application['HSC_in']); ?>" readonly>
    </div>
</div>

<div id="Third-Column">
    <div id="Degree">Degree Information</div>

    <div id="Department">
        <input type="text" value="<?php echo htmlspecialchars($application['dept_name']); ?>" readonly>
    </div>

    <div id="Major">
        <input type="text" value="<?php echo htmlspecialchars($application['major_name']); ?>" readonly>
    </div>

    <div id="Attachment">Attachment</div>

    <a class="insert-link" href="<?php echo $application['Student_picture']; ?>" target="_blank">
        <span class="insert-text">See  Student Picture</span>
        <button class="attachment-button" type="button"></button>
    </a>

    <a class="insert-link" href="<?php echo $application['Student_signature']; ?>" target="_blank">
        <span class="insert-text">See  Signature Picture</span>
        <button class="attachment-button" type="button"></button>
    </a>

    <a class="insert-link" href="<?php echo $application['SSC_certificate']; ?>" target="_blank">
        <span class="insert-text">See  SSC Certificate</span>
        <button class="attachment-button" type="button"></button>
    </a>

    <a class="insert-link" href="<?php echo $application['SSC_transcript']; ?>" target="_blank">
        <span class="insert-text">See  SSC Transcript</span>
        <button class="attachment-button" type="button"></button>
    </a>

    <a class="insert-link" href="<?php echo $application['HSC_certificate']; ?>" target="_blank">
        <span class="insert-text">See  HSC Certificate</span>
        <button class="attachment-button" type="button"></button>
    </a>

    <a class="insert-link" href="<?php echo $application['HSC_transcript']; ?>" target="_blank">
        <span class="insert-text">See  HSC Transcript</span>
        <button class="attachment-button" type="button"></button>
    </a>

    <div id="Button-Row">
        <button id="Apply-Button">Approve</button>
        <button id="Decline-Button">Decline</button>
    </div>

    <div id="BackButtonContainer">
        <button id="BackButton" onclick="window.location.href='../08. Application List Page/ApplicationList.php'">
            <img src="back.png" alt="Back">
        </button>
    </div>
</div>

</body>
</html>
