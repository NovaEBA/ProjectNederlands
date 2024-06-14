<?php
include('../config/db.php');
include('../functions/functions.php');
include('../classes/Student.php');

checkStudentRole();

$student = new Student($conn);

$student_id = $_SESSION['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../public/css/styles.css"> <!-- Link to your CSS file -->
</head>
<body>

<p><a href="../views/logout.php">Logout</a>
</body>
</html>