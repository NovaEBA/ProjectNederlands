<?php
include('../config/db.php');
include('../functions/functions.php');
include('../classes/Note.php');

// Ensure the user is an admin
checkAdminRole();

$note = new Note($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_sentence_id = $_POST['sentence_id'];
    $user_id = $_SESSION['id']; 
    $comment = $_POST['comment'];

    $message = $note->addNoteToSentence($student_sentence_id,  $user_id, $comment);
    if ($message === "Comment added successfully!") {
        header("Location: ../admin/admin_woorden.php");
        exit();
    } else {
        echo $message;
    }
} else {
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Add Note</title>
        <link rel="stylesheet" href="../public/css/styles.css">
    </head>
    <body>
        <h1>Add Comment</h1>
        <form action="add_comment.php" method="post">
            <label for="sentence_id">Sentence ID:</label><br>
            <input type="text" id="sentence_id" name="sentence_id" required><br><br>
            <label for="comment">Comment:</label><br>
            <textarea id="comment" name="comment" required></textarea><br><br>
            <input type="submit" value="Add Comment">
        </form>
    </body>
    </html>
    <?php
}
?>
