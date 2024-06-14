<?php
include('../config/db.php');
include('../functions/functions.php');
include('../classes/Admin.php');

checkAdminRole();

$admin = new Admin($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_id'])) {
        $document_id = $_POST['delete_id'];
        $message = $admin->deleteDocument($document_id);
        echo "<script>alert('$message');</script>";
    } else {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $file = $_FILES['file'];
        $user_id = $_SESSION['id'];

        $message = $admin->addDocument($user_id, $file, $description, $title);
        echo "<script>alert('$message');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 500px;
        }
        .close-modal {
            float: right;
            font-size: 1.5rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include '../templates/header.php'; ?>
    <div class="container mt-5">
        <h1 class="text-center">File Upload and Word Entry</h1>
        <button class="btn btn-primary" id="addFileBtn">Add Document</button>

        <div class="modal-overlay" id="modalOverlay">
            <div class="modal-content">
                <span class="close-modal" id="closeModal">&times;</span>
                <h1>Add Document</h1>
                <form action="admin_dashboard.php" method="post" enctype="multipart/form-data">
                    <label for="title">Title:</label><br>
                    <input type="text" id="title" name="title" required><br><br>
                    <label for="description">Description:</label><br>
                    <textarea id="description" name="description" required></textarea><br><br>
                    <label for="file">Select File:</label><br>
                    <input type="file" id="file" name="file" required><br><br>
                    <input type="submit" value="Add Document">
                </form>
            </div>
        </div>

        <div class="row mt-5" id="fileDisplaySection">
            <!-- PHP code to fetch and display uploaded files from the database -->
            <?php
            $files = $admin->getUploadedFiles();

            foreach ($files as $file) {
                echo '<div class="col-md-4">';
                echo '<div class="card">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . htmlspecialchars($file['title']) . '</h5>';
                echo '<p class="card-text">' . htmlspecialchars($file['description']) . '</p>';
                echo '<p class="card-text"><a href="' . htmlspecialchars($file['file_path']) . '">Download</a></p>';
                echo '<form method="post" class="delete-form">';
                echo '<input type="hidden" name="delete_id" value="' . htmlspecialchars($file['id']) . '">';
                echo '<button type="button" class="btn btn-danger delete-btn">Delete</button>';
                echo '</form>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#addFileBtn').click(function(){
                $('#modalOverlay').css('display', 'flex'); // Changed to flex to use justify-content and align-items
            });
            $('#closeModal').click(function(){
                $('#modalOverlay').hide();
            });
            $(window).click(function(event){
                if ($(event.target).is('#modalOverlay')) {
                    $('#modalOverlay').hide();
                }
            });

            // Handle delete button click
            $('.delete-btn').click(function(){
                if (confirm("Are you sure you want to delete this document?")) {
                    $(this).closest('form').submit();
                }
            });
        });
    </script>
</body>
</html>