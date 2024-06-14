<?php
class Admin {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Method to add a document
    public function addDocument($user_id, $file, $description, $title) {
        // Handle file upload
        $targetDir = "../uploads/documents";
        $targetFile = $targetDir . basename($file["name"]);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if file is a real file
        if (isset($_POST["submit"])) {
            $check = getimagesize($file["tmp_name"]);
            if ($check !== false) {
                $uploadOk = 1;
            } else {
                return "File is not an image.";
                $uploadOk = 0;
            }
        }

        // Check file size
        if ($file["size"] > 500000) {
            return "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        $allowedTypes = ['pdf', 'doc', 'docx', 'txt'];
        if (!in_array($fileType, $allowedTypes)) {
            return "Sorry, only PDF, DOC, DOCX & TXT files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            return "Sorry, your file was not uploaded.";
        // If everything is ok, try to upload file
        } else {
            if (move_uploaded_file($file["tmp_name"], $targetFile)) {
                // Insert document details into database
                $sql = "INSERT INTO documents (user_id, file_path, description, title) VALUES (?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                if ($stmt === false) {
                    // Handle error in preparation
                    return 'prepare() failed: ' . htmlspecialchars($this->conn->error);
                }
                $stmt->bind_param("isss", $user_id, $targetFile, $description, $title);

                if ($stmt->execute()) {
                    return "Document added successfully!";
                } else {
                    return "Error: " . $stmt->error;
                }
            } else {
                return "Sorry, there was an error uploading your file.";
            }
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

    public function addWord($word, $meaning, $source) {
        $sql = "INSERT INTO words (word, meaning, source) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            // Handle error in preparation
            return 'prepare() failed: ' . htmlspecialchars($this->conn->error);
        }
        $stmt->bind_param("sss", $word, $meaning, $source);

        if ($stmt->execute()) {
            return "Word added successfully!";
        } else {
            return "Error: " . $stmt->error;
        }
    }
}
?>
