<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
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
    // Create users table if it doesn't exist
    $createTableSQL = "CREATE TABLE users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        firstname VARCHAR(255) NOT NULL,
        middlename VARCHAR(255),
        lastname VARCHAR(255) NOT NULL,
        department VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        username VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'student',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
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

// Extract data
$firstName = $data["firstName"];
$middleName = $data["middleName"];
$lastName = $data["lastName"];
$department = $data["department"];
$email = $data["email"];
$username = $data["username"];
$password = $data["password"];

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