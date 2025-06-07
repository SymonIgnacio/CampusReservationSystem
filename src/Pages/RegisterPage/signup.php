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
    exit;
}

// Get raw input
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

// Debug: Check if input is empty
if (!$data) {
    echo json_encode(["success" => false, "message" => "No JSON received or invalid format"]);
    exit;
}

// Check if users table exists
$host = "localhost";
$dbname = "campus_db"; 
$dbuser = "root";
$dbpass = "";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit;
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
        echo json_encode(["success" => false, "message" => "Failed to create users table: " . $conn->error]);
        exit;
    }
}

// Check if username or email already exists
$checkSql = "SELECT * FROM users WHERE username = ? OR email = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("ss", $data["username"], $data["email"]);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if ($user["username"] === $data["username"]) {
        echo json_encode(["success" => false, "message" => "Username already exists"]);
    } else {
        echo json_encode(["success" => false, "message" => "Email already exists"]);
    }
    $checkStmt->close();
    $conn->close();
    exit;
}
$checkStmt->close();

// Debug: Log received data
error_log("Received data: " . print_r($data, true));

// Extract data
$firstName = isset($data["firstName"]) ? $data["firstName"] : '';
$middleName = isset($data["middleName"]) ? $data["middleName"] : '';
$lastName = isset($data["lastName"]) ? $data["lastName"] : '';
$department = isset($data["department"]) ? $data["department"] : '';
$email = isset($data["email"]) ? $data["email"] : '';
$username = isset($data["username"]) ? $data["username"] : '';
$password = isset($data["password"]) ? $data["password"] : '';

// Hash password before storing
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Set default role for new users
$role = "student";

// Insert new user
$sql = "INSERT INTO users (firstname, middlename, lastname, department, email, username, password, role)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssss", $firstName, $middleName, $lastName, $department, $email, $username, $hashedPassword, $role);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Registration successful!"]);
} else {
    echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>