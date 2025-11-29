<?php
session_start();
include "../database.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../12. Create System Element/SystemElement.css" />
</head>
<body>
    <button id="home-button" onclick="window.location.href='../09. Admin Dashboard/AdminDashboard.php'">
        <img src="home.png" alt="Home Icon">
    </button>

      <div id = 'row'> 
        <div id = 'chairman' onclick="window.location.href='../13. Create Chairman Page/CreateChairman.php'">
            <span id = 'create_chairman'> Create Chairman Profile <br> ... </span> 
        </div>
        <div id = 'teacher'onclick="window.location.href='../14. Create Teacher Page/CreateTeacher.php'"> 
            <span id = 'create_chairman'> Create Teacher Profile <br> ... </span> 
        </div> 
      </div> 
</body>
</html>