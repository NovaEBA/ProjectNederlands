<?php
class User {
    protected $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Method to register a new user
    public function register($name, $email, $password, $role) {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

        if ($stmt->execute()) {
            return "User registered successfully!";
        } else {
            return "Error: " . $stmt->error;
        }
    }

    // Method for user login
    public function login($email, $password) {
        $sql = "SELECT id, name, password, role FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $name, $hashed_password, $role);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['id'] = $id;
                $_SESSION['name'] = $name;
                $_SESSION['role'] = $role;

                return "Login successful!";
            } else {
                return "Invalid password.";
            }
        } else {
            return "No user found with that email address.";
        }
    }

    // Method to get user details by ID
    public function getUserById($user_id) {
        $sql = "SELECT id, name, email, role FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    // Method to get all users by role
    public function getUsersByRole($role) {
        $sql = "SELECT id, name, email FROM users WHERE role = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $role);
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
