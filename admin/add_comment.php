<?php
include('../config/db.php');
include('../functions/functions.php');
include('../classes/Note.php');

// Ensure the user is an admin
checkAdminRole();

$note = new Note($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sentence_id = $_POST['id'];
    $note_text = $_POST['comment'];
    $admin_id = $_SESSION['id']; // Assuming the user ID is stored in the session

    $message = $note->addNoteToSentence($sentence_id, $note_text, $admin_id);
    echo $message;
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
        <h1>Add Note</h1>
        <form action="add_note.php" method="post">
            <label for="sentence_id">Sentence ID:</label><br>
            <input type="text" id="sentence_id" name="sentence_id" required><br><br>
            <label for="note">Note:</label><br>
            <textarea id="note" name="note" required></textarea><br><br>
            <input type="submit" value="Add Note">
        </form>
    </body>
    </html>
    <?php
}
?>
