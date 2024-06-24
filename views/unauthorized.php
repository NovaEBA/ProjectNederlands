<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access</title>
    <link rel="stylesheet" href="../public/css/styles.css"> <!-- Link to your CSS file -->
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

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black overlay */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .block {
            background-color: white;
            padding: 40px;
            text-align: center;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .block h1 {
            font-size: 25px;
            color: gray;
            margin-bottom: 20px;
        }

        .block p {
            font-size: 20px;
            color: #333;
        }

        .block a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container mt-3">
    <div class="overlay">
        <div class="block">
            <h1>Geen toestemming voor deze pagina</h1>
            <div class="mt-3">
                <p>Je bent niet ingelogd, <a href="login.php"><u>Log in</u></a></p>
            </div>
        </div>
    </div>
</div>
</body>
</html>

