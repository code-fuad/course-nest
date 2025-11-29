<?php
// ForgotPassword.php — add role flash cards; keep CSS/layout intact.

session_start();
include "../database.php"; // should create $conn (mysqli)

if (!$conn) {
    die("Database connection failed.");
}

$error = "";

// Small helper to check if a given ID exists in a table/column
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $role = isset($_POST["role"]) ? trim($_POST["role"]) : "";
    $identifier = isset($_POST["identifier"]) ? trim($_POST["identifier"]) : "";

    if ($role === "") {
        $error = "Please select Student, Teacher or Chairman.";
    } elseif ($identifier === "") {
        $error = "Please enter your ID.";
    } else {
        $role = strtolower($role);

        // Match according to your instruction:
        // - student -> student table (Student_Id)
        // - teacher -> teacher table (teacher_id)
        // - chairman -> teacher table (teacher_id)
        if ($role === "student") {
            if (id_exists($conn, "student", "Student_Id", $identifier)) {
                header("Location: ../08. Password Recovery Page/PasswordRecovery.php?role=student&id=" . urlencode($identifier));
                exit();
            } else {
                $error = "No student found with that ID.";
            }
        } elseif ($role === "teacher") {
            if (id_exists($conn, "teacher", "teacher_id", $identifier)) {
                header("Location: ../08. Password Recovery Page/PasswordRecovery.php?role=teacher&id=" . urlencode($identifier));
                exit();
            } else {
                $error = "No teacher found with that ID.";
            }
        } elseif ($role === "chairman") {
            // As requested: chairman uses TEACHER table matching
            if (id_exists($conn, "chairman", "chair_id", $identifier)) {
                header("Location: ../08. Password Recovery Page/PasswordRecovery.php?role=chairman&id=" . urlencode($identifier));
                exit();
            } else {
                $error = "No matching Chairman record for that Chairman ID.";
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="ForgotPassword.css">
</head>
<body>
    <div id="Image-Block"></div>

    <div id="Info-Block">
        <div id="Recovery">Password Recovery</div>

        <?php if (!empty($error)): ?>
            <div style="color:#ffb3b3; font-weight:bold; margin-left:5vh; margin-bottom:2vh;">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <!-- Flash cards (Student, Teacher, Chairman). No Admin card. -->
        <div id="RoleCards" style="display:flex; gap:2vh; margin-left:5vh; margin-bottom:2vh; flex-wrap:wrap;">
            <button type="button" class="role-card" data-role="student"
                style="padding:2vh 3vh; border-radius:1vh; border:2px solid #6D54B5; background:transparent; color:#fff; cursor:pointer;">
                Student
            </button>
            <button type="button" class="role-card" data-role="teacher"
                style="padding:2vh 3vh; border-radius:1vh; border:2px solid #6D54B5; background:transparent; color:#fff; cursor:pointer;">
                Teacher
            </button>
            <button type="button" class="role-card" data-role="chairman"
                style="padding:2vh 3vh; border-radius:1vh; border:2px solid #6D54B5; background:transparent; color:#fff; cursor:pointer;">
                Chairman
            </button>
        </div>

        <form method="POST" action="">
            <!-- Hidden role field populated by card click -->
            <input type="hidden" name="role" id="role-input" value="<?php echo isset($_POST['role']) ? htmlspecialchars($_POST['role'], ENT_QUOTES, 'UTF-8') : ''; ?>">

            <!-- Your CSS already styles this input via #UserIDField -->
            <div id="UserIDField">
                <input type="text" name="identifier" id="identifier-input" placeholder="Enter your ID"
                       value="<?php echo isset($_POST['identifier']) ? htmlspecialchars($_POST['identifier'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            </div>

            <!-- Keep a second input block unused to preserve layout compat (won't break CSS) -->
            <div id="IdentityField" style="display:none;">
                <input type="text" placeholder="(Not required)">
            </div>

            <button id="Verify-Button" type="submit">Verify & Continue</button>
        </form>

        <div id="BackButtonContainer">
            <button id="BackButton" type="button" onclick="history.back()">
                <img src="back.png" alt="Back">
            </button>
        </div>
    </div>

    <script>
    // Minimal JS to handle role selection highlight (keeps your CSS intact)
    document.addEventListener("DOMContentLoaded", function () {
        const roleInput  = document.getElementById("role-input");
        const roleCards  = document.querySelectorAll(".role-card");

        // Restore highlight if POST failed
        if (roleInput.value) highlightRole(roleInput.value);

        roleCards.forEach(btn => {
            btn.addEventListener("click", () => {
                const role = btn.dataset.role;
                roleInput.value = role;
                highlightRole(role);
            });
        });

        function highlightRole(role) {
            roleCards.forEach(b => b.style.boxShadow = "none");
            const active = Array.from(roleCards).find(b => b.dataset.role === role);
            if (active) active.style.boxShadow = "0 0 0 3px rgba(180,165,225,0.9)";
        }
    });
    </script>
</body>
</html>
