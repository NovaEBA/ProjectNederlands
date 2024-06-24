<?php
include('../config/db.php');
include('../functions/functions.php');
include('../classes/Student.php');
session_start();

checkStudentRole();

$student = new Student($conn);

$conn = new mysqli($servername, $username, $password, $dbname);

$conn->set_charset("utf8");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$student_id = $_SESSION['id'];

// Fetch passed sentences
$passedQuery = "SELECT s.id, s.sentence, s.created_at, u.name, w.word, w.meaning
                FROM student_sentences s
                INNER JOIN users u ON s.user_id = u.id
                INNER JOIN words w ON s.word_id = w.id
                WHERE s.status = 'passed'
                ORDER BY s.created_at DESC";
$resultPassed = $conn->query($passedQuery);
$passedSentences = $resultPassed->fetch_all(MYSQLI_ASSOC);

// Fetch failed sentences
$failedQuery = "SELECT s.id, s.sentence, s.created_at, u.name, w.word, w.meaning
                FROM student_sentences s
                INNER JOIN users u ON s.user_id = u.id
                INNER JOIN words w ON s.word_id = w.id
                WHERE s.status = 'failed'
                ORDER BY s.created_at DESC";
$resultFailed = $conn->query($failedQuery);
$failedSentences = $resultFailed->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../public/css/student.css" >
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
    <h2>Passed Sentences</h2>
    <div class="row">
        <?php foreach ($passedSentences as $sentence): ?>
            <div class="col-md-4">
                <div class="card sentence-card" style="border:none">
                    <div class="card-body">
                        <p class="card-title" style="font-weight:bold; font-size:18px;"><?= htmlspecialchars($sentence['word']); ?></p>
                        <p class="card-text"><?= htmlspecialchars($sentence['sentence']); ?></p>
                        <p class="username">
                            <i class="fas fa-user" style="color: #007bff;"></i> <!-- User icon -->
                            <span style="font-weight: bold; margin-left: 5px;"><?= htmlspecialchars($sentence['name']); ?></span>
                        </p>
                        <p class="created-at">
                            <i class="far fa-calendar-alt" style="color: #28a745;"></i> <!-- Calendar icon -->
                            <span style="margin-left: 5px;"><?= htmlspecialchars($sentence['created_at']); ?></span>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h2>Failed Sentences</h2>
    <div class="row">
        <?php foreach ($failedSentences as $sentence): ?>
            <div class="col-md-4">
                <div class="card sentence-card" style="border:none">
                    <div class="card-body">
                        <p class="card-title" style="font-weight:bold; font-size:18px;"><?= htmlspecialchars($sentence['word']); ?></p>
                        <p class="card-text"><?= htmlspecialchars($sentence['sentence']); ?></p>
                        <p class="username">
                            <i class="fas fa-user" style="color: #007bff;"></i> <!-- User icon -->
                            <span style="font-weight: bold; margin-left: 5px;"><?= htmlspecialchars($sentence['name']); ?></span>
                        </p>
                        <p class="created-at">
                            <i class="far fa-calendar-alt" style="color: #28a745;"></i> <!-- Calendar icon -->
                            <span style="margin-left: 5px;"><?= htmlspecialchars($sentence['created_at']); ?></span>
                        </p>
                        <button class="btn btn-primary edit-btn"
                                data-sentence-id="<?= $sentence['id']; ?>"
                                data-word="<?= htmlspecialchars($sentence['word']); ?>"
                                data-meaning="<?= htmlspecialchars($sentence['meaning']); ?>"
                                data-sentence="<?= htmlspecialchars($sentence['sentence']); ?>"
                                data-user="<?= htmlspecialchars($sentence['name']); ?>">
                            Edit
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModalOverlay" style="display:none;">
    <div class="modal-content">
        <span class="close-modal" id="closeEditModal">&times;</span>
        <h4>Edit Sentence</h4><br>
        <form action="edit_sentence.php" method="post">
            <input type="hidden" name="sentence_id" id="editSentenceId">
            <div class="form-group">
                <label for="editWord">Word:</label>
                <input type="text" class="form-control" id="editWord" readonly>
            </div>
            <div class="form-group">
                <label for="editMeaning">Meaning:</label>
                <input type="text" class="form-control" id="editMeaning" readonly>
            </div>
            <div class="form-group">
                <label for="editSentence">Sentence:</label>
                <input type="text" class="form-control" id="editSentence" name="edit_sentence" required>
            </div>
            <div class="form-group">
                <label for="editUser">User:</label>
                <input type="text" class="form-control" id="editUser" readonly>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>
</body>
<script>
    // JavaScript to handle modal interaction
    document.addEventListener('DOMContentLoaded', function () {
        const editButtons = document.querySelectorAll('.edit-btn');
        const editModalOverlay = document.getElementById('editModalOverlay');
        const closeEditModal = document.getElementById('closeEditModal');

        editButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const sentenceId = this.getAttribute('data-sentence-id');
                const word = this.getAttribute('data-word');
                const meaning = this.getAttribute('data-meaning');
                const sentence = this.getAttribute('data-sentence');
                const user = this.getAttribute('data-user');

                // Set modal content dynamically
                document.getElementById('editSentenceId').value = sentenceId;
                document.getElementById('editWord').value = word;
                document.getElementById('editMeaning').value = meaning;
                document.getElementById('editSentence').value = sentence;
                document.getElementById('editUser').value = user;

                // Show the modal
                editModalOverlay.style.display = 'block';
            });
        });

        // Close the modal when the close button is clicked
        closeEditModal.addEventListener('click', function () {
            editModalOverlay.style.display = 'none';
        });

        // Close the modal when clicking outside the modal content
        window.addEventListener('click', function (event) {
            if (event.target == editModalOverlay) {
                editModalOverlay.style.display = 'none';
            }
        });
    });
</script>
</html>