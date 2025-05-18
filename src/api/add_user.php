<?php
// add_user.php - Add a new user to the database
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add CORS headers
if (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Content-Type: application/json");
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
    exit();
}

// Get JSON data from request
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON data"
    ]);
    exit();
}

// Validate required fields
$requiredFields = ['firstname', 'lastname', 'username', 'email', 'password', 'role'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        echo json_encode([
            "success" => false,
            "message" => "Missing required field: $field"
        ]);
        exit();
    }
}

// Connect to DB
$host = "localhost";
$dbname = "campus_db"; 
$dbuser = "root";
$dbpass = "";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Connection failed: " . $conn->connect_error
    ]);
    exit();
}

// Check if users table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
if ($tableCheck->num_rows == 0) {
    // Create users table
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
        echo json_encode([
            "success" => false,
            "message" => "Failed to create users table: " . $conn->error
        ]);
        exit();
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
        echo json_encode([
            "success" => false,
            "message" => "Username already exists"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Email already exists"
        ]);
    }
    $checkStmt->close();
    $conn->close();
    exit();
}
$checkStmt->close();

// Hash password
$hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

// Set default values for optional fields
$middlename = isset($data['middlename']) ? $data['middlename'] : '';
$department = isset($data['department']) ? $data['department'] : '';

// Insert new user
$stmt = $conn->prepare("INSERT INTO users (firstname, middlename, lastname, department, email, username, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssss", 
    $data['firstname'], 
    $middlename, 
    $data['lastname'], 
    $department, 
    $data['email'], 
    $data['username'], 
    $hashedPassword, 
    $data['role']
);

if ($stmt->execute()) {
    $userId = $stmt->insert_id;
    echo json_encode([
        "success" => true,
        "message" => "User added successfully",
        "user_id" => $userId
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Error adding user: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>