<?php
// Determine the current page
$current_page = basename($_SERVER['PHP_SELF']);

// Set the header content based on the current page
$header_content = '';

switch ($current_page) {
    case 'admin_dashboard.php':
        $header_content = 'Bekijk jouw <strong>Overzicht!</strong>';
        break;
    case 'studenten_admin.php':
        $header_content = 'Bekijk jouw <strong>Studenten overzicht!</strong>';
        break;
    case 'bronnen_admin.php':
        $header_content = 'Bekijk jouw <strong>bronnen overzicht!</strong>';
        break;
}
?>

<!DOCTYPE html>
<head>
<!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="..\public\css\style.css" >
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navigationbar fixed-top">
        <div class="container">
            <div class="row justify-content-center">
            <div class="col-sm-10 justify-content-center">
                <nav class="navbar navbar-expand-lg justify-content-center">
                <div class="container">
                    <a class="navbar-brand col-auto" href="../admin/admin_dashboard.php">
                        <img src="../public/img/light.png" height="27" class="d-inline-block" style="opacity:0.7; margin-bottom:8px" alt="lightbulb icoon">
                        <p class="d-inline-block"><strong>WoordDossier</strong></p>
                    </a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="..\admin\admin_dashboard.php">Woorden</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="..\admin\studenten_admin.php">Studenten</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="..\admin\bronnen_admin.php">Bronnen</a>
                            </li>
                            <li class="nav-item col-auto">
                                <a class="btn btn-dark" href="../views/logout.php" role="button">
                                    <i class="fas fa-sign-out-alt"></i> Log uit
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                </nav>
            </div>
            </div>
        </div>
    </div>
    <!-- Header -->
    <header class="header-image">
    <div class="header-text-box">
            <h1><?php echo $header_content; ?></h1>
        </div>
    </header>
    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>