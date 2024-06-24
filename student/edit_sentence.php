<?php
include('../config/db.php');
include('../functions/functions.php');
include('../classes/student.php');
session_start();

checkStudentRole();

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student = new Student($conn);

$conn->set_charset("utf8");

$student_id = $_SESSION['id'];

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle form submission for editing sentence
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sentence_id']) && isset($_POST['edit_sentence'])) {
    $sentence_id = $_POST['sentence_id'];
    $edit_sentence = $_POST['edit_sentence'];

    // Update sentence in student_sentences table
    $updateSentenceQuery = "UPDATE student_sentences SET sentence = ?, status = 'pending', updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($updateSentenceQuery);
    $stmt->bind_param("si", $edit_sentence, $sentence_id);
    $stmt->execute();
    $stmt->close();

    // Redirect back to student_dashboard.php
    header("Location: student_dashboard.php");
    exit();
}

$conn->close();
?>