<?php
// Disable error display in response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Direct CORS headers first - must be before any output
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get JSON data from request
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("Invalid JSON data");
    }

    // Validate required fields
    $requiredFields = ['firstName', 'lastName', 'username', 'email', 'password'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Connect to DB
    $host = "localhost";
    $dbname = "campus_db"; 
    $dbuser = "root";
    $dbpass = "";

    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if users table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
    if ($tableCheck->num_rows == 0) {
        // Create users table with the correct structure from campus_db.sql
        $createTableSQL = "CREATE TABLE `users` (
            `user_id` int(11) NOT NULL AUTO_INCREMENT,
            `firstname` varchar(50) NOT NULL,
            `middlename` varchar(50) DEFAULT NULL,
            `lastname` varchar(50) NOT NULL,
            `department` varchar(100) DEFAULT NULL,
            `email` varchar(100) NOT NULL,
            `username` varchar(50) NOT NULL,
            `password` varchar(255) NOT NULL,
            `role` enum('student','faculty','admin') DEFAULT 'student',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`user_id`),
            UNIQUE KEY `email` (`email`),
            UNIQUE KEY `username` (`username`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        if (!$conn->query($createTableSQL)) {
            throw new Exception("Failed to create users table: " . $conn->error);
        }
    }

    // Check if username or email already exists
    $checkStmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $checkStmt->bind_param("ss", $data['username'], $data['email']);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['username'] === $data['username']) {
            throw new Exception("Username already exists");
        } else {
            throw new Exception("Email already exists");
        }
    }
    $checkStmt->close();

    // Hash password
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

    // Set default values for optional fields
    $middlename = isset($data['middleName']) ? $data['middleName'] : '';
    $department = isset($data['department']) ? $data['department'] : '';
    $role = "student"; // Default role for new users

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (firstname, middlename, lastname, department, email, username, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", 
        $data['firstName'], 
        $middlename, 
        $data['lastName'], 
        $department, 
        $data['email'], 
        $data['username'], 
        $hashedPassword, 
        $role
    );

    if (!$stmt->execute()) {
        throw new Exception("Error adding user: " . $stmt->error);
    }

    $userId = $stmt->insert_id;
    $stmt->close();
    $conn->close();

    // Return success response
    echo json_encode([
        "success" => true,
        "message" => "Registration successful! You can now log in."
    ]);

} catch (Exception $e) {
    // Log error to server log
    error_log("Error in signup.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>