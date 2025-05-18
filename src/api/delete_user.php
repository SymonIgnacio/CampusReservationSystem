<?php
// delete_user.php - Delete a user from the database
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
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

if (!$data || !isset($data['userId'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing user ID"
    ]);
    exit();
}

$userId = $data['userId'];

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
    echo json_encode([
        "success" => false,
        "message" => "Users table does not exist"
    ]);
    exit();
}

// Check if user exists
$checkStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$checkStmt->bind_param("i", $userId);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);
    $checkStmt->close();
    $conn->close();
    exit();
}

// Check if user is an admin
$user = $result->fetch_assoc();
if ($user['role'] === 'admin') {
    echo json_encode([
        "success" => false,
        "message" => "Cannot delete admin user"
    ]);
    $checkStmt->close();
    $conn->close();
    exit();
}
$checkStmt->close();

// Delete user
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "User deleted successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Error deleting user: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>