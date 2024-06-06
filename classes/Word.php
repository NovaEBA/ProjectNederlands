<?php
class Word {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Method to add a new word
    public function addWord($word, $meaning, $source) {
        $sql = "INSERT INTO words (word, meaning, source) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $word, $meaning, $source);

        if ($stmt->execute()) {
            return "Word added successfully!";
        } else {
            return "Error: " . $stmt->error;
        }
    }

    // Method to list all words for a specific user
    public function listWords($user_id) {
        $sql = "SELECT id, word, meaning, source FROM words WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }

    // Method to add a sentence for a word
    public function addSentence($word_id, $sentence, $user_id) {
        $sql = "UPDATE words SET sentence = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $sentence, $word_id, $user_id);

        if ($stmt->execute()) {
            return "Sentence added successfully!";
        } else {
            return "Error: " . $stmt->error;
        }
    }

    // Method to get a word by ID
    public function getWordById($word_id, $user_id) {
        $sql = "SELECT id, word, meaning, sentence, source, difficulty FROM words WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $word_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    // Method to list shared sentences
    public function listSharedSentences() {
        $sql = "SELECT id, sentence, created_at FROM shared_sentences";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }

    // Method to add a shared sentence
    public function addSharedSentence($sentence) {
        $sql = "INSERT INTO shared_sentences (sentence) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $sentence);

        if ($stmt->execute()) {
            return "Shared sentence added successfully!";
        } else {
            return "Error: " . $stmt->error;
        }
    }
}
?>
