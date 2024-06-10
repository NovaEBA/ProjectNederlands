<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydatabase";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch articles from the database
$sql = "SELECT id, title, description, image_url FROM articles LIMIT 12"; // Adjust the LIMIT as needed
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $count = 0;
    while($row = $result->fetch_assoc()) {
        if ($count % 3 == 0) {
            echo '<div class="row">';
        }
        
        echo '<div class="col-md-6 col-lg-4">';
        echo '  <div class="card">';
        echo '    <img src="' . htmlspecialchars($row["image_url"]) . '" class="card-img-top" alt="' . htmlspecialchars($row["title"]) . '">';
        echo '    <div class="card-body">';
        echo '      <h5 class="card-title">' . htmlspecialchars($row["title"]) . '</h5>';
        echo '      <p class="card-text">' . htmlspecialchars($row["description"]) . '</p>';
        echo '      <a href="article.php?id=' . htmlspecialchars($row["id"]) . '" class="btn btn-primary">Read More</a>';
        echo '    </div>';
        echo '  </div>';
        echo '</div>';

        $count++;
        if ($count % 3 == 0) {
            echo '</div>';
        }
    }
    if ($count % 3 != 0) {
        echo '</div>'; // Close the last row if not complete
    }
} else {
    echo "No articles found.";
}

$conn->close();
?>
