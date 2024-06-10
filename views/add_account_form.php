<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Account</title>
    <link rel="stylesheet" href="../public/css/styles.css"> <!-- Link to your CSS file -->
</head>
<body>
    <h1>Add Account</h1>

    <form action="../admin/add_account.php" method="post">
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
