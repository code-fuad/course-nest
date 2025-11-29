<?php
include "../database.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application List Window</title>
    <link rel="stylesheet" href="ApplicationList.css" />
</head>
<body>
    <div id="ListWindow">
        <div id="navbar">
            <div id="navphoto">
                <img src="application.png" alt="Apply Icon">
            </div>
            <div id="navtext">Student Application List</div>
        </div>

        <div id="List-Block">
            <?php
            $query = "SELECT Application_Id, First_name, Last_name, Student_contact FROM application WHERE approval_status = 'Pending'";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $appId = $row['Application_Id'];
                    $name = $row['First_name'] . " " . $row['Last_name'];
                    $contact = $row['Student_contact'];

                    echo "<div style='padding: 1vh 2vh; margin: 1vh; background-color: rgb(100,95,110); border-radius: 1vh; color: white;'>
                            <div><strong>Name:</strong> $name</div>
                            <div><strong>Contact:</strong> $contact</div>
                            <div style='margin-top: 1vh;'>
                                <a href='../11. Approval Profile/ApprovalProfile.php?application_id=$appId' style='padding: 0.5vh 1.5vh; background-color: rgb(109, 84, 181); color: white; text-decoration: none; border-radius: 0.7vh; font-weight: bold;'>View</a>
                            </div>
                          </div>";

                }
            } else {
                echo "<div style='padding: 2vh; color: white;'>No pending applications found.</div>";
            }
            ?>
        </div>
    </div>

    <button id="home-button" onclick="window.location.href='../09. Admin Dashboard/AdminDashboard.php'">
        <img src="home.png" alt="Home Icon">
    </button>
</body>
</html>
