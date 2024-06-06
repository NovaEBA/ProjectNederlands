<?php
class Admin {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Method to add a shared sentence
    public function addSharedSentence($sentence, $admin_id) {
        $sql = "INSERT INTO shared_sentences (sentence, admin_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $sentence, $admin_id);

        if ($stmt->execute()) {
            return "Shared sentence added successfully!";
        } else {
            return "Error: " . $stmt->error;
        }
    }

    // Method to add a document
    public function addDocument($filePath, $description, $admin_id) {
        $sql = "INSERT INTO documents (file_path, description, admin_id) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $filePath, $description, $admin_id);

        if ($stmt->execute()) {
            return "Document added successfully!";
        } else {
            return "Error: " . $stmt->error;
        }
    }

    // Method to add a comment to a student's sentence
    public function addComment($sentence_id, $comment, $admin_id) {
        $sql = "INSERT INTO comments (sentence_id, comment, admin_id) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isi", $sentence_id, $comment, $admin_id);

        if ($stmt->execute()) {
            return "Comment added successfully!";
        } else {
            return "Error: " . $stmt->error;
        }
    }

    // Method to manage students (e.g., list students)
    public function listStudents() {
        $sql = "SELECT id, name, email FROM users WHERE role = 'student'";
        $result = $this->conn->query($sql);

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }
}
?>
