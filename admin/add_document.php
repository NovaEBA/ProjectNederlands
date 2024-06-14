<?php
include('../config/db.php');
include('../functions/functions.php');
include('../classes/Admin.php');

checkAdminRole();

$admin = new Admin($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $file = $_FILES['file'];
    $user_id = $_SESSION['id'];

    $message = $admin->addDocument($user_id, $file, $description, $title);
    echo $message;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Document</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <h1>Add Document</h1>
    <form action="add_document.php" method="post" enctype="multipart/form-data">
        <label for="title">Title:</label><br>
        <input type="text" id="title" name="title" required><br><br>
        <label for="description">Description:</label><br>
        <textarea id="description" name="description" required></textarea><br><br>
        <label for="file">Select File:</label><br>
        <input type="file" id="file" name="file" required><br><br>
        <input type="submit" value="Add Document">
    </form>
</body>
</html>
