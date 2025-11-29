<?php
session_start();
include "../database.php"; // must create $conn (mysqli) in here

// OPTIONAL (dev): show PHP errors
// ini_set('display_errors', 1); error_reporting(E_ALL);

if (!$conn) {
    die("Database connection failed.");
}

$error = "";

// Utility: quick column-exists check
function column_exists(mysqli $conn, string $table, string $column): bool {
    $sql = "SELECT COUNT(*) AS c
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return false;
    mysqli_stmt_bind_param($stmt, "ss", $table, $column);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    return $row && (int)$row['c'] > 0;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $role     = isset($_POST["role"]) ? trim($_POST["role"]) : "";
    $user_id  = isset($_POST["user_id"]) ? trim($_POST["user_id"]) : "";
    $password = isset($_POST["password"]) ? $_POST["password"] : "";

    if ($role === "") {
        $error = "Please select a role.";
    } elseif ($user_id === "" || $password === "") {
        $error = "User ID and Password are required.";
    } else {
        $role = strtolower($role);

        if ($role === "admin") {
            $sql = "SELECT admin_password FROM admin WHERE admin_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $user_id);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                if ($res && ($row = mysqli_fetch_assoc($res))) {
                    if (hash_equals($row["admin_password"], $password)) {
                        session_regenerate_id(true);
                        $_SESSION["admin_id"] = $user_id;
                        header("Location: ../09.%20Admin%20Dashboard/AdminDashboard.php");
                        exit();
                    }
                }
                mysqli_stmt_close($stmt);
            }
            $error = "Invalid Admin ID or Password!";

        } elseif ($role === "student") {
            // Accept either hashed Student_password or a plaintext password column if you’ve added one
            $hasStudentPwd = column_exists($conn, "student", "Student_password");
            $hasPlainPwd   = column_exists($conn, "student", "password");

            if ($hasStudentPwd) {
                $sql = "SELECT Student_password FROM student WHERE Student_Id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "s", $user_id);
                    mysqli_stmt_execute($stmt);
                    $res = mysqli_stmt_get_result($stmt);
                    if ($res && ($row = mysqli_fetch_assoc($res))) {
                        if (!empty($row["Student_password"]) && password_verify($password, $row["Student_password"])) {
                            session_regenerate_id(true);
                            $_SESSION["student_id"] = $user_id;
                            header("Location: ../16.%20Student%20Dashboard/StudentDashboard.php");
                            exit();
                        }
                    }
                    mysqli_stmt_close($stmt);
                }
                $error = "Invalid Student ID or Password!";
            } elseif ($hasPlainPwd) {
                $sql = "SELECT password FROM student WHERE Student_Id = ? AND password = ?";
                $stmt = mysqli_prepare($conn, $sql);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ss", $user_id, $password);
                    mysqli_stmt_execute($stmt);
                    $res = mysqli_stmt_get_result($stmt);
                    if ($res && mysqli_num_rows($res) === 1) {
                        session_regenerate_id(true);
                        $_SESSION["student_id"] = $user_id;
                        header("Location: ../16.%20Student%20Dashboard/StudentDashboard.php");
                        exit();
                    }
                    mysqli_stmt_close($stmt);
                }
                $error = "Invalid Student ID or Password!";
            } else {
                $error = "Student login not configured: add a Student_password (hashed) or password column to student table.";
            }

        } elseif ($role === "chairman") {
            $hasChairPwd = column_exists($conn, "chairman", "password");
            if ($hasChairPwd) {
                $sql = "SELECT password FROM chairman WHERE chair_id = ? AND password = ?";
                $stmt = mysqli_prepare($conn, $sql);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ss", $user_id, $password);
                    mysqli_stmt_execute($stmt);
                    $res = mysqli_stmt_get_result($stmt);
                    if ($res && mysqli_num_rows($res) === 1) {
                        session_regenerate_id(true);
                        $_SESSION["chair_id"] = $user_id;
                        header("Location: ../22. Chairman Dashboard/ChairmanDashboard.php");
                        
                        exit();
                    }
                    mysqli_stmt_close($stmt);
                }
                $error = "Invalid Chairman ID or Password!";
            } else {
                $error = "Chairman login not configured: add a password column to chairman table.";
            }

        } elseif ($role === "teacher") {
            $hasTeacherPwd = column_exists($conn, "teacher", "teacher_password");
            $hasPlainPwd   = column_exists($conn, "teacher", "password");

            if ($hasTeacherPwd) {
                $sql = "SELECT teacher_password FROM teacher WHERE teacher_id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "s", $user_id);
                    mysqli_stmt_execute($stmt);
                    $res = mysqli_stmt_get_result($stmt);
                    if ($res && ($row = mysqli_fetch_assoc($res))) {
                        $stored = (string)$row["teacher_password"];
                        if (password_verify($password, $stored) || hash_equals($stored, $password)) {
                            session_regenerate_id(true);
                            $_SESSION["teacher_id"] = $user_id;
                            header("Location: ../25. Teacher Dashboard/TeacherDashboard.php");
                            exit();
                            
                        }
                    }
                    mysqli_stmt_close($stmt);
                }
                $error = "Invalid Teacher ID or Password!";
            } elseif ($hasPlainPwd) {
                $sql = "SELECT password FROM teacher WHERE teacher_id = ? AND password = ?";
                $stmt = mysqli_prepare($conn, $sql);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ss", $user_id, $password);
                    mysqli_stmt_execute($stmt);
                    $res = mysqli_stmt_get_result($stmt);
                    if ($res && mysqli_num_rows($res) === 1) {
                        session_regenerate_id(true);
                        $_SESSION["teacher_id"] = $user_id;
                        header("Location: ../25. Teacher Dashboard/TeacherDashboard.php");
                        exit();
                    }
                    mysqli_stmt_close($stmt);
                }
                $error = "Invalid Teacher ID or Password!";
            } else {
                $error = "Teacher login not configured: add a teacher_password (hashed) or password column to teacher table.";
            }

        } else {
            $error = "Unknown role selected.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In Window</title>
    <link rel="stylesheet" href="../01.%20Login%20Page/Login.css" />
</head>
<body>
    <div id="Image-Block"></div>

    <div id="Info-Block">
        <div id="Login"> LOG IN! </div>

        <?php if (!empty($error)): ?>
            <div style="color:red; font-weight:bold; margin-left:5vh; margin-bottom:2vh;">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <!-- Role flash cards (unchanged styling) -->
        <div id="RoleCards" style="display:flex; gap:2vh; margin-left:5vh; margin-bottom:2vh; flex-wrap:wrap;">
            <button type="button" class="role-card" data-role="student"
                style="padding:2vh 3vh; border-radius:1vh; border:2px solid #6D54B5; background:transparent; color:#fff; cursor:pointer;">
                Student
            </button>
            <button type="button" class="role-card" data-role="chairman"
                style="padding:2vh 3vh; border-radius:1vh; border:2px solid #6D54B5; background:transparent; color:#fff; cursor:pointer;">
                Chairman
            </button>
            <button type="button" class="role-card" data-role="teacher"
                style="padding:2vh 3vh; border-radius:1vh; border:2px solid #6D54B5; background:transparent; color:#fff; cursor:pointer;">
                Teacher
            </button>
            <button type="button" class="role-card" data-role="admin"
                style="padding:2vh 3vh; border-radius:1vh; border:2px solid #6D54B5; background:transparent; color:#fff; cursor:pointer;">
                Admin
            </button>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="role" id="role-input" value="<?php echo isset($_POST['role']) ? htmlspecialchars($_POST['role'], ENT_QUOTES, 'UTF-8') : ''; ?>">

            <div id="UserID-Row">
                <div id="UserID"> User ID </div>
                <div id="UserText">
                    <input type="text" name="user_id" id="user-id-input" placeholder="Enter User ID" required>
                </div>
            </div>

            <div id="Password-Row">
                <div id="Password"> Password   </div>
                <div id="PasswordField">
                    <input type="password" name="password" id="password-input" placeholder="Enter Password" required>
                    <button id="toggle-password" type="button">
                        <img src="eye.png" alt="Toggle Visibility">
                    </button>
                </div>
            </div>

            <div id="ForgotPassword">
                <a href="../07. Forgot Password Page/ForgotPassword.php">Forgot Password</a>
            </div>

            <button id="Login-Button" type="submit"> Log In </button>
        </form>

        <div id="Apply-Row">
            <div id="question">Not a student?</div>
            <div id="ApplyNow">
                <a href="../02.%20Apply%20Page/ApplyPage.php">Apply Now</a>
            </div>
        </div>
    </div>

    <script>
    // Toggle password visibility + role selector (unchanged visuals)
    document.addEventListener("DOMContentLoaded", function () {
        const passwordInput = document.getElementById("password-input");
        const toggleButton  = document.getElementById("toggle-password");
        const roleCards     = document.querySelectorAll(".role-card");
        const roleInput     = document.getElementById("role-input");
        const userIdLabel   = document.getElementById("UserID");

        if (roleInput.value) {
            highlightRole(roleInput.value);
            setUserIdLabel(roleInput.value);
        }

        toggleButton.addEventListener("click", function () {
            passwordInput.type = (passwordInput.type === "password") ? "text" : "password";
        });

        roleCards.forEach(btn => {
            btn.addEventListener("click", () => {
                const role = btn.dataset.role;
                roleInput.value = role;
                highlightRole(role);
                setUserIdLabel(role);
            });
        });

        function highlightRole(role) {
            roleCards.forEach(b => b.style.boxShadow = "none");
            const active = Array.from(roleCards).find(b => b.dataset.role === role);
            if (active) active.style.boxShadow = "0 0 0 3px rgba(180,165,225,0.9)";
        }
        function setUserIdLabel(role) {
            if (role === "admin") userIdLabel.textContent = "User ID";
            else if (role === "chairman") userIdLabel.textContent = "User ID";
            else if (role === "teacher") userIdLabel.textContent = "User ID";
            else userIdLabel.textContent = "User ID";
        }
    });
    </script>
</body>
</html>
