<?php
include('../config/db.php');
include('../functions/functions.php');
include('../classes/Student.php');

checkStudentRole();

$student = new Student($conn);

$student_id = $_SESSION['id'];

$words = $student->listWords($student_id);

$sentences = $student->getSentencesByStudent($student_id);

$comments = $student->getCommentsByStudent($student_id);
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
    <h1>Welcome, <?php echo $_SESSION['name']; ?>!</h1>

    <h2>Words Added:</h2>
    <ul>
        <?php foreach ($words as $word): ?>
            <li><?= $word['word'] ?> - <?= $word['meaning'] ?></li>
        <?php endforeach; ?>
    </ul>

    <h2>Sentences Created:</h2>
    <ul>
        <?php foreach ($sentences as $sentence): ?>
            <li><?= $sentence['sentence'] ?></li>
        <?php endforeach; ?>
    </ul>

    <h2>Comments from Admin:</h2>
    <ul>
        <?php foreach ($comments as $comment): ?>
            <li><?= $comment['comment'] ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>