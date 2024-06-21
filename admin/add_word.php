<?php
include('../config/db.php');
include('../functions/functions.php');
include('../classes/Admin.php');

checkAdminRole();

$admin = new Admin($conn);

$sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

if ($stmt->execute()) {
    echo ucfirst($role) . " account created successfully!";
    $conn->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Account</title>
<link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
<h1>Add Account</h1>
<?php if (!empty($message)): ?>
    <p><?php echo $message; ?></p>
<?php endif; ?>
<form action="add_account.php" method="post">
    <label for="name">Name:</label><br>
    <input type="text" id="name" name="name" required><br><br>

    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" required><br><br>

    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password" required><br><br>

    <label for="role">Role:</label><br>
    <select id="role" name="role" required>
        <option value="admin">Admin</option>
        <option value="student">Student</option>
    </select><br><br>

    <input type="submit" value="Add Account">
</form>
</body>
</html>
