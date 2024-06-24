<?php
include('../config/db.php');
include('../functions/functions.php');
include('../classes/Student.php');

checkStudentRole();

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student = new Student($conn);

$conn->set_charset("utf8");

$student_id = $_SESSION['id'];

error_reporting(E_ALL);
ini_set('display_errors', 1);

$search = $_GET['search'] ?? '';

// Fetch words from the database
$query = "SELECT id, word, meaning FROM words";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$words = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle form submission for adding a sentence
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['word_id']) && isset($_POST['sentence'])) {
    $word_id = $_POST['word_id'];
    $sentence = $_POST['sentence'];

    // Insert into student_sentences table
    $insertQuery = "INSERT INTO student_sentences (user_id, word_id, sentence, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("iis", $student_id, $word_id, $sentence);
    
    if ($stmt->execute()) {
        // Success message or redirection
        echo "<script>alert('Sentence added successfully.');</script>";
    } else {
        // Error message
        echo "<script>alert('Error adding sentence: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Handle delete request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Delete from student_sentences table
    $deleteQuery = "DELETE FROM student_sentences WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("ii", $delete_id, $student_id);

    if ($stmt->execute()) {
        // Success message or redirection
        echo "<script>alert('Sentence deleted successfully.');</script>";
    } else {
        // Error message
        echo "<script>alert('Error deleting sentence: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}
// Handle edit request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id']) && isset($_POST['edit_sentence'])) {
    $edit_id = $_POST['edit_id'];
    $edit_sentence = $_POST['edit_sentence'];

    // Update student_sentences table
    $updateQuery = "UPDATE student_sentences SET sentence = ?, updated_at = NOW() WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sii", $edit_sentence, $edit_id, $student_id);

    if ($stmt->execute()) {
        // Success message or redirection
        echo "<script>alert('Sentence updated successfully.');</script>";
    } else {
        // Error message
        echo "<script>alert('Error updating sentence: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

$query = "SELECT * FROM sentences WHERE title LIKE ?";
$search_param = "%$search%";

// Fetch total number of rows for pagination
$totalQuery = "SELECT COUNT(*) as total FROM student_sentences WHERE user_id = ?";
$stmt = $conn->prepare($totalQuery);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$totalRows = $result->fetch_assoc()['total'];
$stmt->close();

$itemsPerPage = 6;
$totalPages = ceil($totalRows / $itemsPerPage);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Fetch sentences associated with words with pagination
$querySentences = "SELECT s.id, w.word, w.meaning, s.sentence
                   FROM student_sentences s
                   INNER JOIN words w ON s.word_id = w.id
                   WHERE s.user_id = ?
                   ORDER BY s.created_at DESC
                   LIMIT ? OFFSET ?";
$stmt = $conn->prepare($querySentences);
$stmt->bind_param("iii", $student_id, $itemsPerPage, $offset);
$stmt->execute();
$resultSentences = $stmt->get_result();
$sentences = $resultSentences->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>


<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Woorden</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../public/css/student.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container mt-5">
    <div class="row toolsRow">
                <div class="col">
                    <button class="btn btn-primary" id="addZinBtn">Nieuwe zin</button>
                </div>
                <div class="col-auto">
                    <form method="get" action="words_student.php">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Zoeken.." value="<?= htmlspecialchars($search) ?>">
                            <div class="input-group-append">
                                <button class="btn btn-outline-primary" type="submit">Zoek</button>
                            </div>
                        </div>
                    </form>
                </div>
                <form method="get" action="words_student.php" class="mb-3">  
            </form>
            </div>
    <!-- Modal for adding a word -->
    <div class="modal-overlay" id="modalOverlay">
                <div class="modal-content">
                    <span class="close-modal" id="closeModal">&times;</span>
                    <h4>Nieuwe zin</h4><br>
                    <form action="words_student.php" method="post">
                        <div class="form-group">
                        <label for="wordSelect">Kies een woord:</label>
                            <select class="form-control" id="wordSelect" name="word_id">
                                <option value="">Kies een woord...</option>
                                <?php foreach ($words as $word): ?>
                                    <option value="<?= $word['id']; ?>" data-meaning="<?= htmlspecialchars($word['meaning']); ?>"><?= htmlspecialchars($word['word']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="wordMeaning">Betekenis:</label>
                            <input type="text" class="form-control meaningText" id="wordMeaning" readonly>
                        </div>
                        <div class="form-group">
                            <label for="sentence">Voer een zin in:</label>
                            <input type="text" class="form-control" id="sentence" name="sentence" placeholder="Voer een zin in...">
                        </div>
                        <button type="submit" class="btn btn-primary ml-2">Verstuur</button>
                    </form>
                </div>
            </div>
        <div class="container mt-5">
            <div class="row mt-5">
            <?php foreach ($sentences as $sentence): ?>
                <div class="col-md-4">
                    <div class="card mb-4" style="border:none">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                 <h5 class="card-title"><?= htmlspecialchars($sentence['word']); ?></h5>
                                 <div class="div">
                                    <!-- Delete Form -->
                                    <form method="post" action="words_student.php" style="display:inline;">
                                        <input type="hidden" name="delete_id" value="<?= $sentence['id']; ?>">
                                        <button type="submit" class="delete-btn btn btn-link p-0"><i class="fas fa-trash-alt text-danger"></i></button>
                                    </form>
                                    <!-- Edit Button -->
                                    <button type="button" class="edit-btn btn btn-link p-0 ml-2" 
                                        data-id="<?= $sentence['id']; ?>" 
                                        data-sentence="<?= htmlspecialchars($sentence['sentence']); ?>"><i class="fas fa-edit text-primary"></i></button>
                                </div>
                            </div>
                            <p class="card-text meaningText"><?= htmlspecialchars($sentence['meaning']); ?></p>
                            <p class="card-text"><?= htmlspecialchars($sentence['sentence']); ?></p>
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
    <!-- Edit Modal -->
    <div class="modal-overlay" id="editModalOverlay" style="display:none;">
        <div class="modal-content">
            <span class="close-modal" id="closeEditModal">&times;</span>
            <h4>Zin bewerken</h4><br>
            <form action="words_student.php" method="post">
                <input type="hidden" name="edit_id" id="editId">
                <div class="form-group">
                    <label for="editSentence">Bewerk de zin:</label>
                    <input type="text" class="form-control" id="editSentence" name="edit_sentence" required>
                </div>
                <button type="submit" class="btn btn-primary">Opslaan</button>
            </form>
        </div>
    </div>
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>

    <script>
    $(document).ready(function(){
            // Show add sentence modal
            $('#addZinBtn').click(function(){
                $('#modalOverlay').fadeIn();
            });

            // Hide add sentence modal
            $('#closeModal').click(function(){
                $('#modalOverlay').fadeOut();
            });

            // Hide add sentence modal on outside click
            $(window).click(function(event){
                if ($(event.target).is('#modalOverlay')) {
                    $('#modalOverlay').fadeOut();
                }
            });

            // Auto-fill meaning field
            $('#wordSelect').change(function(){
                var selectedMeaning = $('#wordSelect option:selected').data('meaning');
                $('#wordMeaning').val(selectedMeaning);
            });

            // Show edit modal
            $('.edit-btn').click(function(){
                var id = $(this).data('id');
                var sentence = $(this).data('sentence');
                $('#editId').val(id);
                $('#editSentence').val(sentence);
                $('#editModalOverlay').fadeIn();
            });

            // Hide edit modal
            $('#closeEditModal').click(function(){
                $('#editModalOverlay').fadeOut();
            });

            // Hide edit modal on outside click
            $(window).click(function(event){
                if ($(event.target).is('#editModalOverlay')) {
                    $('#editModalOverlay').fadeOut();
                }
            });
        });
    </script>
</body>
</html>