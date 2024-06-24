<?php
session_start();

// function to check if the user is an admin
function checkAdminRole() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../views/unauthorized.php");
        exit();
    }
}

// function to check if the user is a student 
function checkStudentRole() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
        header("Location: ../views/unauthorized.php");
        exit();
    }
}

// function to log in the user
function loginUser($email, $password, $conn) {
    // Retrieve user details from the database based on email
    $sql = "SELECT id, email, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $hashed_password = $user['password'];

        // Debugging: Print the fetched user details
        echo "User fetched: ";
        print_r($user);
        
        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Password is correct, set session variables and redirect
            session_start();
            $_SESSION['id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/admin_woorden.php");
            } elseif ($user['role'] === 'student') {
                header("Location: ../student/student_dashboard.php");
            }
            exit();
        } else {
            return "Incorrect password";
        }
    } else {
        return "User not found";
    }
}

// function to register a new user
function registerUser($name, $email, $password, $role, $conn) {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $passwordHash, $role);
    
    if ($stmt->execute()) {
        return ucfirst($role) . " account created successfully!";
    } else {
        return "Error: " . $stmt->error;
    }
}

// function to log out the user
function logoutUser() {
    session_unset();
    session_destroy();
    header("Location: ../public/index.php");
    exit();
}

// function to validate the user
function validateUser($email, $password, $conn) {
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            return $user['role'];
        }
    }

    return false; 
}

// function to get the user ID
function getUserID($email, $conn) {
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->getresult();
    $user = $result->fetch_assoc();

    return $user['id'];
}

function getUserName($user_id, $conn) {
    $sql = "SELECT name FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    return $user['name'];
}
?>