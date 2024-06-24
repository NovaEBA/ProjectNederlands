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

// Fetch documents
$query = "SELECT * FROM documents WHERE title LIKE ?";
$search_param = "%$search%";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $search_param);
$stmt->execute();
$result = $stmt->get_result();
$files = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate pagination variables
$itemsPerPage = 6;
$totalRows = count($files);
$totalPages = ceil($totalRows / $itemsPerPage);
$page = isset($_GET['page']) ? max(1, min($totalPages, $_GET['page'])) : 1;
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


<meta charset="UTF-8">
    <title>Bronnen</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../public/css/student.css" >
</head>
<body>
    <?php include 'header.php'; ?>
    <section class="documents">
        <div class="container mt-5">
            <div class="cards">
                <div class="container mt-5">
                    <h3>Documenten</h3>
                    <div class="row justify-content-center">
                        <div class="col">
                            <form class="form-inline mb-4" method="GET" action="bronnen_student.php">
                                <input class="form-control mr-sm-2" type="search" name="search" placeholder="Zoek document bij naam.." aria-label="Search" value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-primary my-2 my-sm-0" type="submit">Zoeken</button>
                            </form>
                        </div>
                    </div>
                    <div class="row">
                        <?php foreach ($files as $file): ?>
                            <div class="col-md-4">
                                <div class="card mb-4" style="border:none">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h5 class="card-title"><?php echo htmlspecialchars($file['title']); ?></h5>
                                        </div>
                                        <p class="card-text"><?php echo htmlspecialchars($file['description']); ?></p>
                                        <a href="<?php echo htmlspecialchars($file['file_path']); ?>" class="btn btn-primary" target="_blank">Lezen</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a></li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
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
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
</body>
</html>