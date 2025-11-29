<?php
include "../database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['agree'])) {
    function sanitize($conn, $key) {
        return mysqli_real_escape_string($conn, $_POST[$key] ?? '');
    }

    $First_name = sanitize($conn, 'first_name');
    $Last_name = sanitize($conn, 'last_name');
    $Student_contact = sanitize($conn, 'student_contact');
    $Gender = sanitize($conn, 'gender');
    $Blood_group = sanitize($conn, 'blood_group');
    $DOB = sanitize($conn, 'dob');
    $Father_name = sanitize($conn, 'father_name');
    $Mother_name = sanitize($conn, 'mother_name');
    $Local_guardian = sanitize($conn, 'guardian_name');
    $Guardian_contact = sanitize($conn, 'guardian_contact');
    $Address = sanitize($conn, 'address');
    $SSC_year = sanitize($conn, 'ssc_year');
    $SSC_gpa = sanitize($conn, 'ssc_gpa');
    $SSC_roll = sanitize($conn, 'ssc_roll');
    $SSC_reg = sanitize($conn, 'ssc_reg');
    $SSC_in = sanitize($conn, 'ssc_in');
    $HSC_year = sanitize($conn, 'hsc_year');
    $HSC_gpa = sanitize($conn, 'hsc_gpa');
    $HSC_roll = sanitize($conn, 'hsc_roll');
    $HSC_reg = sanitize($conn, 'hsc_reg');
    $HSC_in = sanitize($conn, 'hsc_in');
    $dept_name = sanitize($conn, 'department');
    $major_name = sanitize($conn, 'major');

    // Upload handler
    function saveUpload($inputName) {
        if (!empty($_FILES[$inputName]['name'])) {
            $targetDir = "../uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir);
            $fileName = basename($_FILES[$inputName]["name"]);
            $targetFile = $targetDir . time() . "_" . $fileName;
            move_uploaded_file($_FILES[$inputName]["tmp_name"], $targetFile);
            return $targetFile;
        }
        return null;
    }

    $Student_picture = saveUpload('student_picture');
    $Student_signature = saveUpload('student_signature');
    $SSC_certificate = saveUpload('ssc_certificate');
    $SSC_transcript = saveUpload('ssc_transcript');
    $HSC_certificate = saveUpload('hsc_certificate');
    $HSC_transcript = saveUpload('hsc_transcript');

    $query = "INSERT INTO application (
        First_name, Last_name, Student_contact, Gender, Blood_group, DOB,
        Father_name, Mother_name, Local_guardian, Guardian_contact, Address,
        SSC_year, SSC_gpa, SSC_roll, SSC_reg, SSC_in,
        HSC_year, HSC_gpa, HSC_roll, HSC_reg, HSC_in,
        dept_name, major_name,
        Student_picture, Student_signature,
        SSC_certificate, SSC_transcript,
        HSC_certificate, HSC_transcript
    ) VALUES (
        '$First_name', '$Last_name', '$Student_contact', '$Gender', '$Blood_group', '$DOB',
        '$Father_name', '$Mother_name', '$Local_guardian', '$Guardian_contact', '$Address',
        '$SSC_year', '$SSC_gpa', '$SSC_roll', '$SSC_reg', '$SSC_in',
        '$HSC_year', '$HSC_gpa', '$HSC_roll', '$HSC_reg', '$HSC_in',
        '$dept_name', '$major_name',
        '$Student_picture', '$Student_signature',
        '$SSC_certificate', '$SSC_transcript',
        '$HSC_certificate', '$HSC_transcript'
    )";

    if (mysqli_query($conn, $query)) {
    $last_id = mysqli_insert_id($conn);
    header("Location: ../03. Application Success Page/ApplicationSuccess.php?id=$last_id");
    exit();
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply Window</title>
    <link rel="stylesheet" type="text/css" href="ApplyPage.css?v=1">
</head>
<body>

<form method="POST" enctype="multipart/form-data">
<div id="First-Column">
    <div id="Recovery">STUDENT APPLICATION</div>
    <div id="Personal">Personal Information</div>

    <div id="Name">
        <div id="FirstName"><input type="text" name="first_name" placeholder="First Name" required></div>
        <div id="LastName"><input type="text" name="last_name" placeholder="Last Name" required></div>
    </div>

    <div id="StudentContact"><input type="text" name="student_contact" placeholder="Student Contact No."></div>

    <div id="GenderField">
        <select name="gender" id="GenderSelect" required>
            <option value="" disabled selected hidden>Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>
    </div>

    <div id="BloodField">
        <select name="blood_group" id="BloodSelect">
            <option value="" disabled selected hidden>Blood Group</option>
            <option value="A+">A+</option><option value="A-">A-</option><option value="B+">B+</option><option value="B-">B-</option>
            <option value="AB+">AB+</option><option value="AB-">AB-</option><option value="O+">O+</option><option value="O-">O-</option>
        </select>
    </div>

    <div id="Identity"><input type="text" name="identity" placeholder="Birth Certificate/ Passport No."></div>
    <div id="DOB"><input type="date" name="dob" id="DOBInput"></div>
    <div id="Father"><input type="text" name="father_name" placeholder="Father's Name"></div>
    <div id="Mother"><input type="text" name="mother_name" placeholder="Mother's Name"></div>
    <div id="Guardian"><input type="text" name="guardian_name" placeholder="Local Guardian's Name"></div>
    <div id="GuardianContact"><input type="text" name="guardian_contact" placeholder="Local Guardian's Contact No."></div>
    <div id="Address"><input type="text" name="address" placeholder="Address"></div>
</div>

<div id="Second-Column">
    <div id="Academic">Academic Information</div>

    <div id="SSC">
        <div id="SSCyear"><input type="text" name="ssc_year" placeholder="SSC Year"></div>
        <div id="SSCgpa"><input type="text" name="ssc_gpa" placeholder="SSC GPA"></div>
    </div>

    <div id="SSCroll"><input type="text" name="ssc_roll" placeholder="SSC Roll Number"></div>
    <div id="SSCreg"><input type="text" name="ssc_reg" placeholder="SSC Registration Number"></div>
    <div id="SSCin"><input type="text" name="ssc_in" placeholder="SSC Institution Name"></div>

    <div id="HSC">
        <div id="HSCyear"><input type="text" name="hsc_year" placeholder="HSC Year"></div>
        <div id="HSCgpa"><input type="text" name="hsc_gpa" placeholder="HSC GPA"></div>
    </div>

    <div id="HSCroll"><input type="text" name="hsc_roll" placeholder="HSC Roll Number"></div>
    <div id="HSCreg"><input type="text" name="hsc_reg" placeholder="HSC Registration Number"></div>
    <div id="HSCin"><input type="text" name="hsc_in" placeholder="HSC Institution Name"></div>
    <div id="ECA"><input type="text" name="eca" placeholder="Extra Curricular Activities (If Any)"></div>
</div>

<div id="Third-Column">
    <div id="Degree">Degree Information</div>

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

    <div id="MajorField">
        <select name="major" id="MajorSelect">
            <option value="" disabled selected hidden>Major</option>
            <option value="E-Business">E-Business</option>
            <option value="Database Systems">Database Systems</option>
        </select>
    </div>

    <div id="Attachment">Attachment</div>

    <?php
    $attachments = [
        "student_picture" => "Insert Student Picture",
        "student_signature" => "Insert Signature Picture",
        "ssc_certificate" => "Insert SSC Certificate",
        "ssc_transcript" => "Insert SSC Transcript",
        "hsc_certificate" => "Insert HSC Certificate",
        "hsc_transcript" => "Insert HSC Transcript"
    ];

    foreach ($attachments as $name => $label) {
        echo "<div id='Insert'>
                <span class='InsertText' id='label_$name'>$label</span>
                <button id='AttachmentButton' type='button' onclick='triggerUpload(\"$name\")'>
                    <img src=\"attachment.png\" alt=\"Attach\">
                </button>
                <input type='file' name='$name' id='$name' style='display:none;' accept='image/*,.pdf' onchange='markSubmitted(\"$name\")'>
            </div>";
    }
    ?>

    <div id="Agreement">
        <input type="checkbox" name="agree" id="agree-checkbox" required>
        <label for="agree-checkbox">I agree to all terms & conditions</label>
    </div>

    <button id="Apply-Button" type="submit">Apply</button>

    <div id="Approval-Row">
        <div id="question">Already Applied?</div>
        <div id="ApprovalStatus"><a href="../04. Applicant Dashboard/ApplicantDashboard.php">Approval Status</a></div>
    </div>

    <div id="BackButtonContainer">
        <button id="BackButton" type="button" onclick="window.location.href='../01. Login Page/Login.php'">
            <img src="back.png" alt="Back">
        </button>
    </div>
</div>
</form>

<script>
function triggerUpload(id) {
    document.getElementById(id).click();
}
function markSubmitted(id) {
    const label = document.getElementById('label_' + id);
    if (label) {
        label.innerText = "Submitted";
        label.style.color = "lightgreen";
        label.style.fontWeight = "bold";
    }
}
</script>

</body>
</html>
