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


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = $_POST['name'];
                $email = $_POST['email'];
                $password = $_POST['password'];
                $role = $_POST['role'];

                $message = $admin->addUser($name, $email, $password, $role);
                echo "<script>alert('$message');</script>";
                break;
            case 'edit':
                $id = $_POST['id'];
                $name = $_POST['name'];
                $email = $_POST['email'];
                $password = $_POST['password'];
                $role = $_POST['role'];

                $message = $admin->editUser($id, $name, $email, $password, $role);
                echo "<script>alert('$message');</script>";
                break;
            case 'delete':
                $id = $_POST['id'];
                $message = $admin->deleteUser($id);
                echo "<script>alert('$message');</script>";
                break;
            default:
                // Handle unknown action
                break;
        }
    }
}

    
$users = $admin->listUsers();
    
    
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$totalUsers = $admin->fetchTotalUsers($conn, $search);
$users = $admin->fetchUsers($conn, $search, $page, $limit);
$totalPages = ceil($totalUsers / $limit);
        
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../public/css/admin.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <section class="users">
        <div class="container">
            <div class="row toolsRow">
                <div class="col">
                    <button id="openUserModal" class="btn btn-dark">
                     <strong>Account</strong>  <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="col-auto">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Zoeken.." value="<?= htmlspecialchars($search) ?>">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">Zoek</button>
                        </div>
                    </div>
                </div>
                <form method="get" action="studenten_admin.php" class="mb-3">  
                </div>
            </form>
            <table class="table">
                <thead>
                    <tr>
                        <th>Naam</th>
                        <th>Email</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td>
                                <button class="btn btn-link edit-user"
                                        data-id="<?php echo htmlspecialchars($user['id']); ?>"
                                        data-name="<?php echo htmlspecialchars($user['name']); ?>"
                                        data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                        data-role="<?php echo htmlspecialchars($user['role']); ?>">
                                        <i class="fas fa-edit text-primary"></i>
                                </button>
                                <form method="post" class="delete-form" action="studenten_admin.php" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                    <button class="btn btn-link delete-user" 
                                        data-id="<?= htmlspecialchars($user['id']) ?>">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= htmlspecialchars($search) ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
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
        </div>
    </section>

    <!-- Add -->
    <div class="modal-overlay" id="userModalOverlay" style="display:none;">
        <div class="modal-content">
            <span class="close-modal" id="closeUserModal">&times;</span>
            <h4 id="modalTitle">Gebruiker toevoegen</h4><br>
            <form action="studenten_admin.php" method="post" id="userForm">
                <input type="hidden" id="userId" name="id">
                <div class="form-group">
                    <label for="name">Naam:</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <label for="password">Wachtwoord:</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <label for="role">Beheer:</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="admin">Admin</option>
                        <option value="student">Student</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" id="addUserBtn">Toevoegen</button>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>                    
    <script>
        $(document).ready(function() {
            $('#openUserModal').click(function() {
                $('#userForm')[0].reset();
                $('#userId').val('');
                $('#modalTitle').text('Add User');
                $('#userModalOverlay').fadeIn();
            });

            $('#closeUserModal').click(function() {
                $('#userModalOverlay').fadeOut();
            });

            $('.edit-user').click(function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const email = $(this).data('email');
                const role = $(this).data('role');

                $('#userId').val(id);
                $('#name').val(name);
                $('#email').val(email);
                $('#password').val('');
                $('#role').val(role);
                $('#modalTitle').text('Edit User');
                $('#userModalOverlay').fadeIn();
            });

            $('.delete-form').submit(function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this user?')) {
                    $(this).unbind('submit').submit();
                }
            });

            $('#userForm').submit(function(e) {
                e.preventDefault();
                const id = $('#userId').val();
                const name = $('#name').val();
                const email = $('#email').val();
                const password = $('#password').val();
                const role = $('#role').val();
                const action = id ? 'edit' : 'add';

                $.post('studenten_admin.php', {
                    action: action,
                    id: id,
                    name: name,
                    email: email,
                    password: password,
                    role: role
                }, function(response) {
                    alert(response);
                    location.reload();
                });
            });
        });
    </script>
</body>
</html>

