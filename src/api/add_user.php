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
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Method not allowed");
    }

    // Get JSON data from request
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("Invalid JSON data");
    }

    // Validate required fields
    $requiredFields = ['firstname', 'lastname', 'email', 'username', 'password', 'role'];
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
    $middlename = isset($data['middlename']) ? $data['middlename'] : null;
    $department = isset($data['department']) ? $data['department'] : null;

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

    if (!$stmt->execute()) {
        throw new Exception("Error adding user: " . $stmt->error);
    }

    $userId = $stmt->insert_id;
    $stmt->close();
    $conn->close();

    // Return success response
    echo json_encode([
        "success" => true,
        "message" => "User added successfully",
        "user_id" => $userId
    ]);

} catch (Exception $e) {
    // Log error to server log
    error_log("Error in add_user.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>