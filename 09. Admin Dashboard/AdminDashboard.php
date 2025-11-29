<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="AdminDashboard.css?v=4">
</head>
<body>
    <div id = 'AdminDisplay'>
        <div id = 'AdminPicture'>
            <img src="admin.png" alt="Admin Picture">
        </div>
        <div id = 'AdminText'>
            <div id = 'Welcome'>WELCOME! ADMIN</div>
            <div id = 'Date'>31th July,2025</div>
        </div>
    </div>
    <div id = 'Option'>
        <div id = 'FirstRow'>
            <div id="Application">
                <div id="Block" onclick="window.location.href='../10. Application List Page/ApplicationList.php'">
                    <div id="apply-photo">
                        <img src="application.png" alt="Application Picture">
                    </div>
                    <div id="applytext">Student Application</div>
                </div>
            </div>
            <div id = 'System'>
                <div id = 'Block' onclick="window.location.href='../12. Create System Element/SystemElement.php'">
                    <div id = 'system-photo'>
                        <img src="system.png" alt="System Picture">
                    </div>
                    <div id = "systemtext">Create System Element</div>
                </div>
            </div>
            <div id = 'Course'onclick="window.location.href='../15. Course Advising Page/CourseAdvising.php'">
                <div id = 'Block'>
                    <div id = 'course-photo'>
                        <img src="course.png" alt="Course Picture">
                    </div>
                    <div id = "coursetext">Course Advising</div>
                </div>
            </div>      
        </div>
    </div>
    <button id="LogButton" onclick="window.location.href='../01. Login Page/Login.php'">
        <img src="log.jpg" alt="Log Out Icon">
    </button>
</body>
</html>