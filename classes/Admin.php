<?php
class Admin {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }
    public function getUploadedFilesCount($search = '') {
        $query = "SELECT COUNT(*) as total FROM documents WHERE title LIKE ?";
        $stmt = $this->conn->prepare($query);
        $searchTerm = '%' . $search . '%';
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['total'];
    }

    public function getUploadedFilesWithPagination($offset, $limit, $search = '') {
        $query = "SELECT * FROM documents WHERE title LIKE ? ORDER BY uploaded_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);
        $searchTerm = '%' . $search . '%';
        $stmt->bind_param("sii", $searchTerm, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    // Method to add a document
    public function addDocument($user_id, $file, $description, $title) {
        // Handle file upload
        $targetDir = "..\uploads";
        $targetFile = $targetDir . basename($file["name"]);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if file is a real file
    if (isset($_POST["submit"])) {
        $check = getimagesize($file["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            return "Bestand is geen afbeelding.";
            $uploadOk = 0;
        }
    }

    // Check file size
    if ($file["size"] > 500000) {
        return "Het gekozen bestand is te groot.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    $allowedTypes = ['pdf', 'doc', 'docx', 'txt'];
    if (!in_array($fileType, $allowedTypes)) {
        return "Alleen PDF, DOC, DOCX & TXT mogelijk";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        return "Het bestand is niet geupload";
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
                return "Het bestand is toegevoegd!";
            } else {
                return "Error: " . $stmt->error;
            }
        } else {
            return "Het uploaden van het bestand is helaas mislukt";
        }
     }
    }

    // Method to get uploaded files
    public function getUploadedFiles() {
        $sql = "SELECT id, title, description, file_path FROM documents";
        $result = $this->conn->query($sql);

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }

    //Delete docuemnt
    public function deleteDocument($document_id) {
        $query = "DELETE FROM documents WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $document_id); // Assuming id is an integer
        if ($stmt->execute()) {
            return "Document deleted successfully";
        } else {
            return "Error deleting document: " . $stmt->error;
        }
    }

   // Update Document
   public function updateDocument($id, $title, $description) {
    $stmt = $this->conn->prepare("UPDATE documents SET title = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $title, $description, $id);

        if ($stmt->execute()) {
            return "Document updated successfully.";
        } else {
            return "Error updating document: " . $this->conn->error;
        }
    }


    // Helper method to handle file upload (if needed)
    private function uploadFile($file) {
        $targetDir = "..\uploads";
        $targetFile = $targetDir . basename($file["name"]);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if file is a real file
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            return false; // File is not an image
        }

        // Check file size
        if ($file["size"] > 500000) {
            return false; // File size exceeds limit
        }

        // Allow certain file formats
        $allowedTypes = ['pdf', 'doc', 'docx', 'txt'];
        if (!in_array($fileType, $allowedTypes)) {
            return false; // Invalid file type
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            return false; // File upload failed
        } else {
            // Attempt to move uploaded file
            if (move_uploaded_file($file["tmp_name"], $targetFile)) {
                return $targetFile; // Return file path if upload successful
            } else {
                return false; // File upload failed
            }
        }
    }

    // Method to add a comment to a student's sentence
    public function addComment($sentence_id, $comment, $admin_id) {
        $sql = "INSERT INTO comments (sentence_id, comment, admin_id) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isi", $sentence_id, $comment, $admin_id);

        if ($stmt->execute()) {
            return "Opmerking toegevoegd!!";
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

    // Methods to add, update, delete words
    public function addWord($word, $meaning, $source) {
        $stmt = $this->conn->prepare("INSERT INTO words (word, meaning, source) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $word, $meaning, $source);
        if ($stmt->execute()) {
            return "Word added successfully.";
        } else {
            return "Error: " . $stmt->error;
        }
    }

    public function updateWord($id, $word, $meaning, $source) {
        $stmt = $this->conn->prepare("UPDATE words SET word = ?, meaning = ?, source = ? WHERE id = ?");
        $stmt->bind_param("ssii", $word, $meaning, $source, $id);
        
        if ($stmt->execute()) {
            return "Word updated successfully.";
        } else {
            return "Error updating word: " . $stmt->error;
        }
    }

    public function deleteWord($id) {
        $stmt = $this->conn->prepare("DELETE FROM words WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return "Word deleted successfully.";
        } else {
            return "Error: " . $stmt->error;
        }
    }

     // Fetch woorden
     public function getWords() {
        $stmt = $this->conn->prepare("
            SELECT w.id, w.word, w.meaning, w.source,
                   IF(w.source = 0, 'standalone', d.title) AS document_title
            FROM words w
            LEFT JOIN documents d ON w.source = d.id
            ORDER BY w.word ASC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Pagination methods
    public function fetchTotalWords($conn, $search) {
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM words WHERE word LIKE ?");
        $search = '%' . $search . '%';
        $stmt->bind_param("s", $search);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }

    public function fetchWords($conn, $search, $page, $limit) {
        $offset = ($page - 1) * $limit;
        $stmt = $conn->prepare("SELECT words.*, COALESCE(documents.title, 'standalone') AS document_title 
                                FROM words 
                                LEFT JOIN documents ON words.source = documents.id 
                                WHERE words.word LIKE ? 
                                LIMIT ? OFFSET ?");
        $search = '%' . $search . '%';
        $stmt->bind_param("sii", $search, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // User toevoegen
    public function addUser($name, $email, $password, $role) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Validate role
        if ($role !== 'admin' && $role !== 'student') {
            return "Invalid role";
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

        if ($stmt->execute()) {
            return ucfirst($role) . " account created successfully!";
        } else {
            return "Error: " . $stmt->error;
        }
    }

    //User editen
    public function editUser($id, $name, $email, $password, $role) {
        // Check if ID exists
        $sql_check = "SELECT * FROM users WHERE id = ?";
        $stmt_check = $this->conn->prepare($sql_check);
        $stmt_check->bind_param("i", $id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows === 0) {
            return "User not found";
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Validate role
        if ($role !== 'admin' && $role !== 'student') {
            return "Invalid role";
        }

        $sql = "UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $email, $hashed_password, $role, $id);

        if ($stmt->execute()) {
            return "User updated successfully!";
        } else {
            return "Error: " . $stmt->error;
        }
    }

    //User verwijderen
    public function deleteUser($id) {
        // Check if ID exists
        $sql_check = "SELECT * FROM users WHERE id = ?";
        $stmt_check = $this->conn->prepare($sql_check);
        $stmt_check->bind_param("i", $id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows === 0) {
            return "User not found";
        }

        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            return "User deleted successfully!";
        } else {
            return "Error: " . $stmt->error;
        }
    }

    //lijst maken van alle users
    public function listUsers() {
        $sql = "SELECT id, name, email, role FROM users ORDER BY name ASC";
        $result = $this->conn->query($sql);

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }

    function fetchTotalUsers($conn, $search = '') {
        $search = '%' . $conn->real_escape_string($search) . '%';
        $sql = "SELECT COUNT(*) as total FROM users WHERE name LIKE ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result->fetch_assoc()['total'];
        
        $stmt->close();
        return $total;
    }

    // Fetch users
    function fetchUsers($conn, $search = '', $page = 1, $limit = 3) {
        $offset = ($page - 1) * $limit;
        $search = '%' . $conn->real_escape_string($search) . '%';
        
        $sql = "SELECT id, name, email, role FROM users 
                WHERE name LIKE ? 
                ORDER BY name 
                LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $search, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $users = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        
        $stmt->close();
        return $users;
    }
}