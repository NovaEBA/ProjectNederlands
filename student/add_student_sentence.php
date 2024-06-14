<?php
include('../config/db.php');
include('../functions/functions.php');
include('../classes/Student.php');

checkStudentRole();

$student = new Student($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $word_id = $_POST['word_id'];
    $sentence = $_POST['sentence'];
    $user_id = $_SESSION['id'];
    
    $message = $student->createSentence($user_id, $word_id, $sentence);
    echo $message;
} else {
    $words = $student->listWords($_SESSION['id']);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Add Sentence</title>
        <link rel="stylesheet" href="../public/css/styles.css">
    </head>
    <body>
        <h1>Add Sentence</h1>
        <form action="add_student_sentence.php" method="post">
            <label for="word_id">Word:</label><br>
            <select id="word_id" name="word_id" required>
                <?php foreach ($words as $word): ?>
                    <option value="<?= $word['id'] ?>"><?= $word['word'] ?></option>
                <?php endforeach; ?>
            </select><br><br>
            <label for="sentence">Sentence:</label><br>
            <input type="text" id="sentence" name="sentence" required><br><br>
            <input type="submit" value="Add Sentence">
        </form>
    </body>
    </html>
    <?php
}
?>
