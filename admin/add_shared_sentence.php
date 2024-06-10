<?php
include('../config/db.php');
include('../functions/functions.php');
include('../classes/Admin.php');

checkAdminRole();

$admin = new Admin($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sentence = $_POST['sentence'];
    $admin_id = $_SESSION['user_id']; // Assuming the user ID is stored in the session

    $message = $admin->addSharedSentence($sentence, $admin_id);
    echo $message;
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Add Shared Sentence</title>
        <link rel="stylesheet" href="../public/css/styles.css">
    </head>
    <body>
        <h1>Add Shared Sentence</h1>
        <form action="add_shared_sentence.php" method="post">
            <label for="sentence">Sentence:</label><br>
            <textarea id="sentence" name="sentence" required></textarea><br><br>
            <input type="submit" value="Add Sentence">
        </form>
    </body>
    </html>
    <?php
}
?>
