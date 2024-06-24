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

$search = $_GET['search'] ?? '';

// Handle document addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $file = $_FILES['file'];

    $uploads_dir = '../uploads';
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0777, true);
    }

    $file_path = $uploads_dir . '/' . basename($file['name']);
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        $stmt = $conn->prepare("INSERT INTO documents (title, description, file_path) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $description, $file_path);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "Error uploading file.";
    }
    header("Location: bronnen_admin.php");
    exit;
}

// Handle document deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Fetch the file path to delete the file from the server
    $stmt = $conn->prepare("SELECT file_path FROM documents WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();
    $stmt->close();

    if ($file && file_exists($file['file_path'])) {
        unlink($file['file_path']); // Delete the file from the server
    }

    // Delete the document from the database
    $stmt = $conn->prepare("DELETE FROM documents WHERE id = ?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        $stmt->close();
        echo "Document deleted successfully.";
    } else {
        echo "Error deleting document: " . $stmt->error;
        $stmt->close();
    }

    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $edit_id = $_POST['edit_id'];
    $title = $_POST['edit_title'];
    $description = $_POST['edit_description'];
    $file_path = $_POST['edit_file_path'];

    $stmt = $conn->prepare("UPDATE documents SET title = ?, description = ?, file_path = ? WHERE id = ?");
    $stmt->bind_param("sssi", $title, $description, $file_path, $edit_id);

    if ($stmt->execute()) {
        echo "Document updated successfully.";
    } else {
        echo "Error updating document: " . $stmt->error;
    }

    $stmt->close();
    exit;
}

// Fetch documents
$query = "SELECT * FROM documents WHERE title LIKE ?";
$search_param = "%$search%";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $search_param);
$stmt->execute();
$result = $stmt->get_result();
$files = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch total number of rows for pagination
$totalQuery = "SELECT COUNT(*) as total FROM documents WHERE title LIKE ?";
$stmt = $conn->prepare($totalQuery);
$stmt->bind_param("s", $search_param);
$stmt->execute();
$result = $stmt->get_result();
$totalRows = $result->fetch_assoc()['total'];
$stmt->close();

$itemsPerPage = 6;
$totalPages = ceil($totalRows / $itemsPerPage);
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $itemsPerPage;

// Fetch documents for the current page
$query .= " LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sii", $search_param, $offset, $itemsPerPage);
$stmt->execute();
$result = $stmt->get_result();
$files = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
                    <form action="bronnen_admin.php" method="post" enctype="multipart/form-data">
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
                    <h3>Artikel dashboard</h3>
                    <div class="row justify-content-center">
                        <div class="col">
                            <form class="form-inline mb-4" method="GET" action="bronnen_admin.php">
                                <input class="form-control mr-sm-2" type="search" name="search" placeholder="Zoek artikel bij naam.." aria-label="Search" value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-primary my-2 my-sm-0" type="submit">Zoeken</button>
                            </form>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" id="addFileBtn">Nieuw artikel</button>
                        </div>
                    </div>
                    <div class="row">
                        <?php foreach ($files as $file): ?>
                            <div class="col-md-4">
                                <div class="card mb-4" style="border:none">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h5 class="card-title"><?php echo htmlspecialchars($file['title']); ?></h5>
                                            <div>
                                                <form method="post" class="delete-form" action="bronnen_admin.php" style="display:inline;">
                                                    <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($file['id']); ?>">
                                                    <button type="submit" class="delete-btn btn btn-link p-0"><i class="fas fa-trash-alt text-danger"></i></button>
                                                </form>
                                                <button type="button" class="edit-btn btn btn-link p-0 ml-2" 
                                                        data-id="<?php echo htmlspecialchars($file['id']); ?>" 
                                                        data-title="<?php echo htmlspecialchars($file['title']); ?>" 
                                                        data-description="<?php echo htmlspecialchars($file['description']); ?>"
                                                        data-file-path="<?php echo htmlspecialchars($file['file_path']); ?>"><i class="fas fa-edit text-primary"></i></button>
                                            </div>
                                        </div>
                                        <p class="card-text"><?php echo htmlspecialchars($file['description']); ?></p>
                                        <a href="<?php echo htmlspecialchars( $file['file_path']); ?>" class="btn btn-primary" target="_blank">Read</a>
                                        <a href="<?php echo htmlspecialchars($file['file_path']); ?>" class="download-link">
                                            <i class="fas fa-download float-right"></i>
                                        </a>
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
    </section>
    <!-- Edit Modal -->
    <div class="modal-overlay" id="editModalOverlay" style="display:none;">
        <div class="modal-content">
            <span class="close-modal" id="closeEditModal">&times;</span>
            <h4>Document bewerken</h4><br>
            <form action="bronnen_admin.php" method="post">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($file['id']); ?>">
                <div class="form-group">
                    <label for="title">Titel:</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($file['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Beschrijving:</label>
                    <textarea class="form-control" id="description" name="description" required><?php echo htmlspecialchars($file['description']); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Opslaan</button>
            </form>
        </div>
    </div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
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
        });
        $('.edit-btn').click(function(){
            var id = $(this).data('id');
            var title = $(this).data('title');
            var description = $(this).data('description');
            var filePath = $(this).data('file-path');

            $('#edit_id').val(id);
            $('#edit_title').val(title);
            $('#edit_description').val(description);
            // If you want to pre-fill the file field with the current file path (optional)
            $('#edit_file').attr('placeholder', filePath);

            $('#editModalOverlay').fadeIn(); // Show edit modal overlay
            $.post('admin_woorden.php', formData)
            .done(function(response) {
                alert(response); // Show success message
                $('#editModal').modal('hide'); // Hide the modal
                location.reload(); // Reload the page to update document list
            })
            .fail(function() {
                alert("Error updating document."); // Show error message
            });
        });

        $('#closeEditModal').click(function(){
            $('#editModalOverlay').fadeOut(); // Hide edit modal overlay on close button click
        });

        $(window).click(function(event){
            if ($(event.target).is('#editModalOverlay')) {
                $('#editModalOverlay').fadeOut(); // Hide edit modal overlay on outside click
            }
        });
});
</script>
</body>
</html>

