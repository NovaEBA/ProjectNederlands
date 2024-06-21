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

// Handle editing a word
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['word_id'])) {
    $id = $_POST['word_id'];
    $word = $_POST['word'];
    $meaning = $_POST['meaning'];
    $standalone = isset($_POST['standalone']) ? 1 : 0;
    $document_id = !$standalone && isset($_POST['document_id']) ? $_POST['document_id'] : null;

    $message = $admin->updateWord($id, $word, $meaning, $standalone, $document_id);
    echo "<script>alert('$message');</script>";
}

// Handle deleting a word
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_word'])) {
    $id = $_POST['delete_word'];
    $message = $admin->deleteWord($id);
    echo "<script>alert('$message');</script>";
}

// Fetch words along with the document title
$query = "SELECT w.id, w.word, w.meaning, w.source, d.title AS document_title 
          FROM words w 
          LEFT JOIN documents d ON w.source = d.id";
$result = $conn->query($query);

$words = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $words[] = $row;
    }
}

// Function to fetch document by source
function getDocumentBySource($source) {
    global $conn;
    $stmt = $conn->prepare("SELECT title FROM documents WHERE id = ?");
    $stmt->bind_param("i", $source);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
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
    <section class="words">
        <div class="container mt-5">
            <div class="modal-overlay" id="wordModalOverlay" style="display:none;">
                <div class="modal-content">
                    <span class="close-modal" id="closeWordModal">&times;</span>
                    <h4>Nieuw woord</h4>
                    <form action="admin_dashboard.php" method="post" id="wordForm">
                        <div class="form-group">
                            <label for="word">Woord:</label>
                            <input type="text" class="form-control" id="word" name="word" placeholder="Woord" required>
                        </div>
                        <div class="form-group">
                            <label for="meaning">Betekenis:</label>
                            <textarea class="form-control" id="meaning" name="meaning" placeholder="Betekenis" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="source">Kies een document:</label>
                            <select class="form-control" id="source" name="source">
                                <option value="">Selecteer een document</option>
                                <?php foreach ($files as $file): ?>
                                    <option value="<?= $file['id'] ?>"><?= htmlspecialchars($file['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" id="addWordBtn">Toevoegen</button>
                    </form>
                </div>
            </div>
            <button id="openWordModal" class="btn btn-dark">
                <strong>Nieuw woord</strong>  <i class="fas fa-plus"></i>
            </button>
            <table class="table">
                <thead>
                    <tr>
                        <th>Woord</th>
                        <th>Betekenis</th>
                        <th>Document</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($words as $word): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($word['word'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($word['meaning'] ?? ''); ?></td>
                            <td>
                                <?php
                                if (!empty($word['source'])) {
                                    echo htmlspecialchars($word['document_title'] ?? '');
                                } else {
                                    echo 'Standalone';
                                }
                                ?>
                            </td>
                            <td>
                                <button class="btn btn-link edit-word" 
                                        data-id="<?php echo htmlspecialchars($word['id'] ?? ''); ?>" 
                                        data-word="<?php echo htmlspecialchars($word['word'] ?? ''); ?>" 
                                        data-meaning="<?php echo htmlspecialchars($word['meaning'] ?? ''); ?>"
                                        data-source="<?php echo htmlspecialchars($word['source'] ?? ''); ?>">
                                    <i class="fas fa-edit text-primary"></i>
                                </button>
                                <form method="post" class="delete-form" action="admin_dashboard.php" style="display:inline;">
                                    <input type="hidden" name="delete_word" value="<?php echo htmlspecialchars($word['id'] ?? ''); ?>">
                                    <button type="submit" class="delete-btn btn btn-link p-0"><i class="fas fa-trash-alt text-danger"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Edit Word Modal -->
    <div class="modal-overlay" id="editWordModalOverlay" style="display:none;">
        <div class="modal-content">
            <span class="close-modal" id="closeEditWordModal">&times;</span>
            <h4>Woord bewerken</h4><br>
            <form action="admin_dashboard.php" method="post">
                <input type="hidden" name="word_id" id="edit_word_id">
                <div class="form-group">
                    <label for="edit_word">Woord:</label>
                    <input type="text" class="form-control" id="edit_word" name="word" required>
                </div>
                <div class="form-group">
                    <label for="edit_meaning">Betekenis:</label>
                    <textarea class="form-control" id="edit_meaning" name="meaning" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_document_id">Kies een document:</label>
                    <select class="form-control" id="edit_document_id" name="document_id">
                        <option value="">Selecteer document</option>
                        <?php foreach ($files as $file): ?>
                            <option value="<?php echo htmlspecialchars($file['id']); ?>"><?php echo htmlspecialchars($file['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
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
    $(document).ready(function() {
        // Show add word modal
        $('#openWordModal').click(function() {
            $('#wordModalOverlay').fadeIn(); // Show modal overlay
        });

        // Close add word modal
        $('#closeWordModal').click(function() {
            $('#wordModalOverlay').fadeOut(); // Hide modal overlay on close button click
        });

        // Close modal on outside click for adding a word
        $(window).click(function(event) {
            if ($(event.target).is('#wordModalOverlay')) {
                $('#wordModalOverlay').fadeOut(); // Hide modal overlay on outside click
            }
        });

        // Show edit word modal with pre-filled data
        $('.edit-word').click(function() {
            var id = $(this).data('id');
            var word = $(this).data('word');
            var meaning = $(this).data('meaning');
            var documentId = $(this).data('source');

            $('#edit_word_id').val(id);
            $('#edit_word').val(word);
            $('#edit_meaning').val(meaning);
            $('#edit_document_id').val(documentId);

            $('#editWordModalOverlay').fadeIn(); // Show edit modal overlay
        });

        // Close edit word modal
        $('#closeEditWordModal').click(function() {
            $('#editWordModalOverlay').fadeOut(); // Hide modal overlay on close button click
        });

        // Close edit modal on outside click for editing a word
        $(window).click(function(event) {
            if ($(event.target).is('#editWordModalOverlay')) {
                $('#editWordModalOverlay').fadeOut(); // Hide modal overlay on outside click
            }
        });
    });
    </script>
</body>
</html>

