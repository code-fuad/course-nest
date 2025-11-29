<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Maker window</title>
    <link rel="stylesheet" href="ScheduleMaker.css" />
</head>
<body>
    <div id = "Menu">
        <a href="../16. Student Dashboard/StudentDashboard.php" id="Home-Link">
            <button id="Home-Button">
                <img src="home.png" alt="Home Icon">
            </button>
        </a>

        <button id="Course-Button" onclick="window.location.href='../17. Completed Course Page/CompletedCourse.php'">
            <img src="course.png" alt="Course Icon">
            <span>Completed Course</span>
        </button>

        <div class="dropdown" id="AdvisingDropdown">
            <button id="Advising-Button">
                <img src="advising.png" alt="Advising Icon" id="advising-icon">
                <span>Course Advising</span>
            </button>
        <div class="dropdown-content">
            <div class="dropdown-option" id = "registration" onclick="window.location.href='../18. Course Registration Page/CourseRegistration.php'">
                <img src="registration.png" alt="Registration Icon" >
                <span>Course Registration</span>
            </div>
            <div class="dropdown-option" id = "offered" onclick="window.location.href='../19. Offered Course Page/OfferedCourse.php'">
                <img src="offered.png" alt="Offered Icon">
                <span>Offered Courses</span>
            </div>
            <div class="dropdown-option" id = "request" onclick="window.location.href='../20. Course Request/CourseRequest.php'">
                <img src="request.png" alt="Request Icon">
                <span>Course Request</span>
            </div>
            <div class="dropdown-option" id = "schedule">
                <img src="schedule.png" alt="Schedule Icon">
                <span>Course Schedule</span>
            </div>
        </div>
        </div>
        <a href="../01. Login Page/Login.php" id="LogOut-Link">
            <button id="LogOut-Button">
                <img src="log.jpg" alt="LogOut Icon"> 
                <span>Log Out</span>
            </button>
        </a>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
        const dropdown = document.getElementById("AdvisingDropdown");
        let hideTimeout;

        dropdown.addEventListener("mouseenter", () => {
        clearTimeout(hideTimeout);
        dropdown.classList.add("active");
        });

        dropdown.addEventListener("mouseleave", () => {
        hideTimeout = setTimeout(() => {
            dropdown.classList.remove("active");
        }, 100); // Delay of 0.5 seconds before hiding
        });
        });
    </script>
</body>
</html>