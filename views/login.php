<?php
include('../config/db.php');
include('../functions/functions.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user_type = validateUser($email, $password, $conn); // Assuming validateUser returns user type (e.g., 'admin', 'student')

    if ($user_type) {
        $_SESSION['id'] = getUserID($email, $conn);
        $_SESSION['name'] = getUserName($_SESSION['id'], $conn);

        if ($user_type === 'admin') {
            header("Location: ../admin/admin_dashboard.php");
        } elseif ($user_type === 'student') {
            header("Location: ../student/student_dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid email or password";
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

    <?php if(isset($error)): ?>
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
