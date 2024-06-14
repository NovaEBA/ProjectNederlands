<?php
class Student {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Method to create a sentence using a word
    public function createSentence($user_id, $word_id, $sentence) {
        $sql = "INSERT INTO student_sentences (user_id, word_id, sentence) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $user_id, $word_id, $sentence);

        if ($stmt->execute()) {
            return "Sentence created successfully!";
        } else {
            return "Error: " . $stmt->error;
        }
    }

    // Method to view comments added by the admin
    public function getCommentsBySentence($sentence_id) {
        $sql = "SELECT comment, user_id, created_at FROM comments WHERE sentence_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $sentence_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }

    // Method to list all words added by the student
    public function listWords($user_id) {
        $sql = "SELECT id, word, meaning, source FROM words";
        $stmt = $this->conn->prepare($sql);
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