<?php
include('../config/db.php');
include('../functions/functions.php');

if (isset($_SESSION['id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/admin_dashboard.php");
    } elseif ($_SESSION['role'] === 'student') {
        header("Location: ../student/student_dashboard.php");
    }
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
    <title>Login</title>
    <link rel="stylesheet" href="../public/css/styles.css"> <!-- Link to your CSS file -->
</head>
<body>
    <h1>Login</h1>

    <?php if(!empty($error)): ?>
        <p><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="login.php" method="post">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="Login">
    </form>
</body>
</html>
