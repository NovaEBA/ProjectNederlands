<?php
class Admin {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

 // Method to add a document
 public function addDocument($user_id, $file, $description, $title) {
    // Handle file upload
    $targetDir = "../uploads";
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

    // Method to get uploaded files
    public function getUploadedFiles() {
        $sql = "SELECT title, description, file_path FROM documents";
        $result = $this->conn->query($sql);

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }
    // Method to delete a document
public function deleteDocument($document_id) {
    // Initialize $file_path variable
    $file_path = '';

    // Get file path to delete the file from the server
    $sql = "SELECT file_path FROM documents WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $document_id);
    
    if ($stmt->execute()) {
        $stmt->bind_result($file_path);
        $stmt->fetch();
        $stmt->close();
    } else {
        return "Error retrieving file path: " . $stmt->error;
    }

    // Check if $file_path is empty or null (no record found)
    if (empty($file_path)) {
        return "Document not found or file path is empty.";
    }

    // Delete the file from the server
    if (file_exists($file_path)) {
        if (unlink($file_path)) {
            // Delete the document from the database
            $sql = "DELETE FROM documents WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $document_id);

            if ($stmt->execute()) {
                return "Document deleted successfully!";
            } else {
                return "Error deleting document from database: " . $stmt->error;
            }
        } else {
            return "Error deleting file from server.";
        }
    } else {
        return "File does not exist on server.";
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
