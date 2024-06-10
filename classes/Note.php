<?php
class Note {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Method to add a note to a student's sentence
    public function addNoteToSentence($student_sentence_id, $comment, $user_id) {
        $sql = "INSERT INTO comments (student_sentence_id, user_id, comment) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isi", $student_sentence_id, $comment, $user_id);

        if ($stmt->execute()) {
            return "Comment added successfully!";
        } else {
            return "Error: " . $stmt->error;
        }
    }

    // Method to retrieve all notes for a specific sentence
    public function getNotesBySentence($student_sentence_id) {
        $sql = "SELECT comment, user_id, created_at FROM comments WHERE student_sentence_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $student_sentence_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }
}
?>
