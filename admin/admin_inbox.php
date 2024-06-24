<?php
include('../config/db.php');
include('../functions/functions.php');
include('../classes/Admin.php');
session_start();

checkAdminRole();

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

$admin = new Admin($conn);

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in as admin
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../admin_login.php"); // Redirect to admin login page if not logged in as admin
    exit();
}

// Handle form submission for review
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_sentence_id']) && isset($_POST['status'])) {
    $sentence_id = $_POST['student_sentence_id'];
    $status = $_POST['status'];
    $additional_notes = $_POST['additional_notes'] ?? '';

    // Insert comment into comments table
    $insertCommentQuery = "INSERT INTO comments (student_sentence_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($insertCommentQuery);
    $user_id = $_POST['user_id']; // Assuming you retrieve user_id from session or form
    $stmt->bind_param("iis", $sentence_id, $user_id, $additional_notes);
    if ($stmt->execute()) {
        echo "Comment inserted successfully.";
    } else {
        echo "Error inserting comment: " . $stmt->error;
    }
    $stmt->close();

    // Update sentence status in student_sentences table
    $updateStatusQuery = "UPDATE student_sentences SET status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($updateStatusQuery);
    $stmt->bind_param("si", $status, $sentence_id);
    if ($stmt->execute()) {
        echo "Status updated successfully.";
    } else {
        echo "Error updating status: " . $stmt->error;
    }
    $stmt->close();
}

// Pagination configuration
$itemsPerPage = 6;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';

// SQL query to fetch sentences with user information
$sql = "SELECT s.id, s.sentence, s.created_at, u.name, w.word, w.meaning
        FROM student_sentences s
        INNER JOIN users u ON s.user_id = u.id
        INNER JOIN words w ON s.word_id = w.id
        WHERE u.name LIKE ?
        ORDER BY s.created_at DESC
        LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$stmt->bind_param("sii", $search_param, $offset, $limit); // Adjust $offset and $limit based on pagination logic
$stmt->execute();
$result = $stmt->get_result();

// Fetch total number of rows for pagination and search
$totalQuery = "SELECT COUNT(*) as total 
               FROM student_sentences s
               INNER JOIN users u ON s.user_id = u.id
               INNER JOIN words w ON s.word_id = w.id
               WHERE u.name LIKE ?";
$stmt = $conn->prepare($totalQuery);
$stmt->bind_param("s", $search_param);
$stmt->execute();
$resultTotal = $stmt->get_result();
$totalRows = $resultTotal->fetch_assoc()['total'];
$stmt->close();


$totalPages = ceil($totalRows / $itemsPerPage);

// Fetch sentences associated with words with pagination and search
$querySentences = "$sql";
$stmt = $conn->prepare($querySentences);
$stmt->bind_param("sii", $search_param, $offset, $itemsPerPage);
$stmt->execute();
$resultSentences = $stmt->get_result();
$sentences = $resultSentences->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inbox dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../public/css/admin.css" >
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col">
                <h4 class="mb-4">Admin Dashboard</h4>
            </div>
            <!-- Search form -->
            <div class="col-auto">
                <form class="form-inline mb-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Gebruiker zoeken.." value="<?= htmlspecialchars($search) ?>">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Display sentences in cards -->
        <div class="row">
            <?php foreach ($sentences as $sentence): ?>
                <div class="col-md-4">
                    <div class="card sentence-card"style="border:none">
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
                            <?php if ($sentence['word'] !== null && $sentence['meaning'] !== null && $sentence['sentence'] !== null && $sentence['name'] !== null): ?>
                                <button class="btn btn-primary review-btn" data-toggle="modal" data-target="#reviewModal"
                                        data-word="<?= htmlspecialchars($sentence['word']); ?>"
                                        data-meaning="<?= htmlspecialchars($sentence['meaning']); ?>"
                                        data-sentence="<?= htmlspecialchars($sentence['sentence']); ?>"
                                        data-user="<?= htmlspecialchars($sentence['name']); ?>">
                                    Review
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination arrows -->
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <!-- Previous page arrow -->
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?= $page - 1; ?>&search=<?= htmlspecialchars($search); ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <!-- Page numbers -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?= $i; ?>&search=<?= htmlspecialchars($search); ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Next page arrow -->
                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?= $page + 1; ?>&search=<?= htmlspecialchars($search); ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
<!-- Review Modal -->
<div class="modal-overlay" id="reviewModalOverlay" style="display:none;">
    <div class="modal-content">
        <span class="close-modal" id="closeReviewModal">&times;</span>
        <h4>Woord overzicht</h4><br>
        <form action="admin_inbox.php" method="post">
            <input type="hidden" name="student_sentence_id" id="reviewSentenceId">
            <input type="hidden" name="user_id" value="<?= $_SESSION['id']; ?>"> <!-- Admin ID -->
            <div class="row justify-content-in-between">
                <div class="form-group col-8">
                    <label for="reviewWord">Woord:</label>
                    <input type="text" class="form-control" id="reviewWord" readonly>
                </div>
                <div class="form-group col-4">
                    <label for="reviewUser">Gebruiker:</label>
                    <input type="text" class="form-control" id="reviewUser" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="reviewMeaning">Betekenis:</label>
                <input type="text" class="form-control" id="reviewMeaning" readonly>
            </div>
            <div class="form-group">
                <label for="reviewSentence">Zin:</label>
                <input type="text" class="form-control" id="reviewSentence" readonly>
            </div>
            <div class="form-group">
                <label for="additionalNotes">Opmerking:</label>
                <textarea class="form-control" id="additionalNotes" name="additional_notes"></textarea>
            </div>
            <div class="form-group">
                <button type="submit" name="status" value="passed" class="btn btn-success">
                    <i class="fas fa-check"></i>
                </button>
                <button type="submit" name="status" value="failed" class="btn btn-danger">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </form>
    </div>
</div>
</body>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
<script>
     // JavaScript to handle modal interaction
     document.addEventListener('DOMContentLoaded', function () {
        const reviewButtons = document.querySelectorAll('.review-btn');
        const reviewModalOverlay = document.getElementById('reviewModalOverlay');
        const closeReviewModal = document.getElementById('closeReviewModal');

        reviewButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const sentenceId = this.getAttribute('data-sentence-id');
                const word = this.getAttribute('data-word');
                const meaning = this.getAttribute('data-meaning');
                const sentence = this.getAttribute('data-sentence');
                const user = this.getAttribute('data-user');

                // Set modal content dynamically
                document.getElementById('reviewSentenceId').value = sentenceId;
                document.getElementById('reviewWord').value = word;
                document.getElementById('reviewMeaning').value = meaning;
                document.getElementById('reviewSentence').value = sentence;
                document.getElementById('reviewUser').value = user;

                // Show the modal
                reviewModalOverlay.style.display = 'block';
            });
        });

        // Close the modal when the close button is clicked
        closeReviewModal.addEventListener('click', function () {
            reviewModalOverlay.style.display = 'none';
        });

        // Close the modal when clicking outside the modal content
        window.addEventListener('click', function (event) {
            if (event.target == reviewModalOverlay) {
                reviewModalOverlay.style.display = 'none';
            }
        });
    });
</script>
</html>