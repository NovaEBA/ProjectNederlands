<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WoordDossier</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css" >
</head>
<body>

<!-- Navigation Bar -->
<div class="navigationbar">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-sm-10 justify-content-center">
        <nav class="navbar navbar-expand-lg justify-content-center">
          <div class="container">
              <a class="navbar-brand" href="index.html">
                <img src="img/light.png" width="30" height="30" class="d-inline-block align-top" style="opacity:0.7" alt="lightbulb icoon">
                WoordDossier
              </a>
              <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon"></span>
              </button>
              <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                  <ul class="navbar-nav">
                      <li class="nav-item">
                          <a class="nav-link" href="#">Dashboard</a>
                      </li>
                      <li class="nav-item">
                          <a class="nav-link" href="#">Woorden</a>
                      </li>
                      <li class="nav-item">
                          <a class="nav-link" href="#">Bronnen</a>
                      </li>
                      <li class="nav-item">
                          <a class="nav-link" href="#">Log uit
                            <img src="img/user.png" width="30" height="30" class="d-inline-block align-top" style="opacity:0.2; margin-left:5px;" alt="Log out icon">
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
            <h1>Bekijk jouw <strong>WoordDossier!</strong></h1>
            <button type="button" class="btn">Dashboard</button>
        </div>
    </header>

    <!-- Centered Section -->
    <section class="text">
        <h2>Centered Section</h2>
        <p>This section is centered and constrained to a narrower width. You can add your content here.</p>
    </section>

    <!-- Article Section -->
    <section class="bronnen">
        <div class="container">
            <h2 class="text-center">Featured Articles</h2>
            <div class="row">
                <div class="col-md-6 col-lg-3">
                    <div class="card">
                        <img src="https://via.placeholder.com/300" class="card-img-top" alt="Article 1">
                        <div class="card-body">
                            <h5 class="card-title">Article 1</h5>
                            <p class="card-text">This is a sample article description.</p>
                            <a href="#" class="btn btn-primary">Read More</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card">
                        <img src="https://via.placeholder.com/300" class="card-img-top" alt="Article 2">
                        <div class="card-body">
                            <h5 class="card-title">Article 2</h5>
                            <p class="card-text">This is a sample article description.</p>
                            <a href="#" class="btn btn-primary">Read More</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card">
                        <img src="https://via.placeholder.com/300" class="card-img-top" alt="Article 3">
                        <div class="card-body">
                            <h5 class="card-title">Article 3</h5>
                            <p class="card-text">This is a sample article description.</p>
                            <a href="#" class="btn btn-primary">Read More</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card">
                        <img src="https://via.placeholder.com/300" class="card-img-top" alt="Article 4">
                        <div class="card-body">
                            <h5 class="card-title">Article 4</h5>
                            <p class="card-text">This is a sample article description.</p>
                            <a href="#" class="btn btn-primary">Read More</a>
                        </div>
                    </div>
                </div>
            </div>
            <a href="#" class="btn btn-primary more-button">More</a>
        </div>
    </section>
    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>




