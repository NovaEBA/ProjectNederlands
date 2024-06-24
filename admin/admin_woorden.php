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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['word']) && isset($_POST['meaning']) && !isset($_POST['word_id'])) {
    $word = $_POST['word'];
    $meaning = $_POST['meaning'];
    $source = isset($_POST['source']) && !empty($_POST['source']) ? $_POST['source'] : 0; // 0 for standalone

    $stmt = $conn->prepare("INSERT INTO words (word, meaning, source) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $word, $meaning, $source);
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: admin_woorden.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}


// Handle editing a word
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['word_id']) && isset($_POST['word']) && isset($_POST['meaning'])) {
    $id = $_POST['word_id'];
    $word = $_POST['word'];
    $meaning = $_POST['meaning'];
    $source = isset($_POST['source']) && !empty($_POST['source']) ? $_POST['source'] : 0; // 0 for standalone

    $message = $admin->updateWord($id, $word, $meaning, $source);
    echo "<script>alert('$message');</script>";
}

// Handle word deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $word_id = $_POST['word_id'];

    $stmt = $conn->prepare("DELETE FROM words WHERE id = ?");
    $stmt->bind_param("i", $word_id);
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: admin_woorden.php");
        exit;
    } else {
        echo "Error deleting word: " . $stmt->error;
    }
}

// Fetch documents for the form
$files = $admin->getUploadedFiles();
usort($files, function($a, $b) {
    return strcmp($a['title'], $b['title']);
});

$words = $admin->getWords();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;

$totalWords = $admin->fetchTotalWords($conn, $search);
$words = $admin->fetchWords($conn, $search, $page, $limit);
$totalPages = ceil($totalWords / $limit);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Woord Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../public/css/admin.css" >
</head>
<body>
    <?php include 'header.php'; ?>
    <section class="words">
    <div class="container">
        <div class="row toolsRow">
            <div class="col">
                <button id="openWordModal" class="btn btn-dark">
                    <strong>Add Word</strong> <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="col-auto">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Zoeken.." value="<?= htmlspecialchars($search) ?>">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">Zoek</button>
                    </div>
                </div>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Word</th>
                    <th>Meaning</th>
                    <th>Source</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($words as $word): ?>
                <tr>
                    <td><?= htmlspecialchars($word['word']); ?></td>
                    <td><?= htmlspecialchars($word['meaning']); ?></td>
                    <td><?= htmlspecialchars($word['document_title']); ?></td>
                    <td>
                        <button class="btn btn-link edit-word"
                                data-word-id="<?= htmlspecialchars($word['id']); ?>"
                                data-word="<?= htmlspecialchars($word['word']); ?>"
                                data-meaning="<?= htmlspecialchars($word['meaning']); ?>"
                                data-source="<?= htmlspecialchars($word['source']); ?>">
                            <i class="fas fa-edit text-primary"></i>
                        </button>
                        <form method="post" class="delete-form" action="admin_woorden.php" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="word_id" value="<?= htmlspecialchars($word['id']); ?>">
                            <button type="submit" class="btn btn-link delete-word">
                                <i class="fas fa-trash-alt text-danger"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= htmlspecialchars($search) ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= htmlspecialchars($search) ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>          
    <!-- Add/Edit Word Modal -->
    <div class="modal-overlay" id="wordModalOverlay" style="display:none;">
        <div class="modal-content">
            <span class="close-modal" id="closeWordModal">&times;</span>
                <h4 class="modalTitle" id="wordModalLabel">Woord toevoegen</h4><br>
                    <form action="admin_woorden.php" id="wordForm" method="post">
                        <input type="hidden" id="word_id" name="word_id">
                            <div class="form-group">
                                <label for="word">Woord:</label>
                                <input type="text" class="form-control" id="word" name="word" placeholder="Woord" required>
                            </div>
                            <div class="form-group">
                                <label for="meaning">Betekenis:</label>
                                <textarea class="form-control" id="meaning" name="meaning" rows="3" placeholder="Betekenis..." required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="source">Bron:</label>
                                    <select class="form-control" id="source" name="source">
                                    <option value="0">Standalone</option>
                                    <?php foreach ($files as $file): ?>
                                    <option value="<?= htmlspecialchars($file['id']) ?>"><?= htmlspecialchars($file['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <button type="submit" class="btn btn-primary" id="addUserBtn">Opslaan</button>
                    </form>
                </div>      
            </div>
        </div>
    </section>            
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#openWordModal').click(function() {
                $('#modalTitle').text('Woord toevoegen');
                $('#wordForm')[0].reset();
                $('#word_id').val('');
                $('#wordModalOverlay').fadeIn();
            });
            $('#closeWordModal').click(function() {
                $('#wordModalOverlay').fadeOut();
            });

            $('.edit-word').click(function() {
                var word_id = $(this).data('word-id');
                var word = $(this).data('word');
                var meaning = $(this).data('meaning');
                var source = $(this).data('source');

                $('#modalTitle').text('Woord aanpassen');
                $('#word_id').val(word_id);
                $('#word').val(word);
                $('#meaning').val(meaning);
                $('#source').val(source);
                $('#wordModalOverlay').fadeIn();
            });

            $('#wordForm').submit(function(e) {
                e.preventDefault();
                var action = ($('#word_id').val() !== '') ? 'edit' : 'add';
                var formData = $(this).serialize();

                $.post('admin_woorden.php', formData + '&action=' + action, function(response) {
                    alert(response);
                    $('#wordModal').modal('hide');
                    location.reload();
                });
            });

            $('.delete-form').click(function(e) {
                e.preventDefault();
                if (confirm('Weet je zeker dat je dit woord wilt verwijderen?')) {
                    $(this).closest('form').submit();
                }
            });
        // Show edit word modal with pre-filled data
        $('.edit-word').click(function() {
            var id = $(this).data('id');
            var word = $(this).data('word');
            var meaning = $(this).data('meaning');
            var source = $(this).data('source');

            $('#edit_word_id').val(id);
            $('#edit_word').val(word);
            $('#edit_meaning').val(meaning);
            $('#source').val(source);
            $('#editWordModalOverlay').fadeIn(); // Show edit modal overlay
        });
    });
    </script>
</body>
</html>

