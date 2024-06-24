<?php
include('../config/db.php');
include('../functions/functions.php');

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect admin if already logged in
if (isset($_SESSION['id']) && $_SESSION['role'] === 'admin') {
    header("Location: ../admin/admin_woorden.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $loginResult = loginUser($email, $password, $conn);
    if ($loginResult !== true) {
        $error = $loginResult;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('../public/img/header.jpg');
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
            font-family: Arial, Helvetica, sans-serif;
        }

        .login-form {
            padding: 30px 80px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: rgba(0, 0, 0, 0.19) 0px 10px 20px, rgba(0, 0, 0, 0.23) 0px 6px 6px;
        }
        h1{
            text-align:center;
            color:white;
            margin-bottom:50px;
        }
        h4{
            color:lightgray;
            font-size:25px;
            padding-top:15px;
        }
    </style>
</head>
<body>
    <div class="container mt-3">
            <h1>Welkom bij het <strong>WoordDossier!</strong></h1>
    <div class="row justify-content-center">
        <div class="login-form">
            <h4 class="text-center">Inloggen Admin</h4>

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="admin_login.php" method="post">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-dark btn-block">Login</button>
            </form>

            <div class="mt-3 text-center">
                <p>Student? <a href="login.php">Login als student</a></p>
            </div>
        </div>
    </div>
</div>
<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
