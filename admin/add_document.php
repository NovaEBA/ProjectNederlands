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
<form action="" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" class="form-control" id="title" name="title" required>
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea class="form-control" id="description" name="description" required></textarea>
    </div>
    <div class="form-group">
        <label for="file">Select File</label>
        <input type="file" class="form-control-file" id="file" name="file" required>
    </div>
    <button type="submit" class="btn btn-success">Submit</button>
</form>
