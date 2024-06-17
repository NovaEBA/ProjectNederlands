<?php
include('../config/db.php');
include('../functions/functions.php');
include('../classes/Admin.php');

checkAdminRole();

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

$admin = new Admin($conn);

error_reporting(E_ALL);
ini_set('display_errors', 1);

$itemsPerPage = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$offset = ($page - 1) * $itemsPerPage;

$totalItems = $admin->getUploadedFilesCount($search);
$totalPages = ceil($totalItems / $itemsPerPage);

$files = $admin->getUploadedFilesWithPagination($offset, $itemsPerPage, $search);

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
    <link rel="stylesheet" href="../public/css/admin.css" >
</head>
<body>
    <?php include 'header.php'; ?>
    <section class="documents">
    <div class="container mt-5">
            <div class="modal-overlay" id="modalOverlay">
                <div class="modal-content">
                    <span class="close-modal" id="closeModal">&times;</span>
                    <h4>Nieuw bestand</h4><br>
                    <form action="admin_dashboard.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="title">Titel:</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Titel" required><br>
                        </div>
                        <div class="form-group">
                            <label for="description">Omschrijving:</label>
                            <textarea class="form-control" id="description" name="description" placeholder="Dit artikel gaat over.." required></textarea><br>
                        </div>
                        <div class="form-group d-flex justify-content-between align-items-center">
                        <div class="custom-file">
                            <input type="file" class="form-control-file" id="file" name="file" required>
                        </div>
                        <button type="submit" class="btn btn-primary ml-2">Check</button>
                    </div>
                    </form>
                </div>
            </div>
            <div class="cards">
                <div class="container mt-5">
                    <div class="row justify-content-center">
                        <div class="col">
                            <h3>Artikel dashboard</h3>
                        </div>
                        <div class="col-auto ">
                            <button class="btn btn-primary" id="addFileBtn">Nieuw artikel</button>
                        </div>
                    </div>
                    <form class="form-inline mb-4" method="GET" action="admin_dashboard.php">
                        <input class="form-control mr-sm-2" type="search" name="search" placeholder="Search by title" aria-label="Search" value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
                    </form>
                    <div class="row">
                        <?php foreach ($files as $file): ?>
                            <div class="col-md-4">
                                <div class="card mb-4 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($file['title']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($file['description']); ?></p>
                                        <p class="card-text"><a href="<?php echo htmlspecialchars($file['file_path']); ?>">Download</a></p>
                                        <form method="post" class="delete-form" action="admin_dashboard.php">
                                            <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($file['id']); ?>">
                                            <button type="button" class="btn btn-danger delete-btn">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php if ($i == $page) echo 'active'; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a></li>
                            <?php endfor; ?>
                            <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
         </div>
    </section>
    
    <div class="footer container-fluid">
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function(){
        $('#addFileBtn').click(function(){
            $('#modalOverlay').fadeIn(); // Show modal overlay
        });

        $('#closeModal').click(function(){
            $('#modalOverlay').fadeOut(); // Hide modal overlay on close button click
        });

        $(window).click(function(event){
            if ($(event.target).is('#modalOverlay')) {
                $('#modalOverlay').fadeOut(); // Hide modal overlay on outside click
            }
        })});

    $(document).ready(function(){
    // Handle delete form submission
    $('.delete-form').submit(function(e){
        e.preventDefault(); // Prevent default form submission
        
        if (confirm("Are you sure you want to delete this document?")) {
            var form = $(this);
            var url = form.attr('action');
            var formData = form.serialize();

            $.post(url, formData)
                .done(function(response) {
                    alert(response); // Display success message
                    location.reload(); // Reload the page to update the document list
                })
                .fail(function() {
                    alert("Error deleting document."); // Display error message
                });
        }
    });
});
</script>
<?php include '..\templates\footer.php'; ?> 
</body>
</html>