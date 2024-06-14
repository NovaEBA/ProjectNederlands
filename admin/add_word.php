<?php
include('../config/db.php');
include('../functions/functions.php');
include('../classes/Admin.php');

checkAdminRole();

$admin = new Admin($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $word = $_POST['word'];
    $meaning = $_POST['meaning'];
    $source = $_POST['source'];
    
    $message = $admin->addWord($word, $meaning, $source);
    echo $message;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Word</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <h1>Add Word</h1>
    <form action="add_word.php" method="post">
        <label for="word">Word:</label><br>
        <input type="text" id="word" name="word" required><br><br>
        <label for="meaning">Meaning:</label><br>
        <input type="text" id="meaning" name="meaning" required><br><br>
        <label for="source">Source:</label><br>
        <input type="text" id="source" name="source"><br><br>
        <input type="submit" value="Add Word">
    </form>
</body>
</html>
